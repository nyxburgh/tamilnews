<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ArticleModel extends Model
{
    protected string $table = 'tn_articles';

    public function listPaginated(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status']))      { $where[] = 'a.status = ?';       $params[] = $filters['status']; }
        if (!empty($filters['category_id'])) { $where[] = 'a.category_id = ?';  $params[] = $filters['category_id']; }
        if (!empty($filters['content_type'])){ $where[] = 'a.content_type = ?'; $params[] = $filters['content_type']; }
        if (!empty($filters['user_id']))        { $where[] = 'a.user_id = ?';         $params[] = $filters['user_id']; }
        if (!empty($filters['contributor_id'])) { $where[] = 'a.contributor_id = ?';  $params[] = $filters['contributor_id']; }
        if (!empty($filters['search']))      { $where[] = 'a.title LIKE ?';     $params[] = '%' . $filters['search'] . '%'; }
        if (!empty($filters['date']))        { $where[] = 'DATE(a.created_at) = ?'; $params[] = $filters['date']; }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset   = ($page - 1) * $perPage;

        $sql = "SELECT a.*, c.name AS category_name, u.name AS author_name
                FROM tn_articles a
                LEFT JOIN tn_categories c ON c.id = a.category_id
                LEFT JOIN tn_users u ON u.id = a.user_id
                {$whereSQL}
                ORDER BY a.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $countSql = "SELECT COUNT(*) FROM tn_articles a {$whereSQL}";

        return [
            'data'     => $this->fetchAll($sql, $params),
            'total'    => (int)$this->fetchColumn($countSql, $params),
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    public function findFull(int $id): array|false
    {
        return $this->fetchOne(
            "SELECT a.*, c.name AS category_name, u.name AS author_name, ci.name AS city_name
             FROM tn_articles a
             LEFT JOIN tn_categories c ON c.id = a.category_id
             LEFT JOIN tn_users u ON u.id = a.user_id
             LEFT JOIN tn_cities ci ON ci.id = a.city_id
             WHERE a.id = ?",
            [$id]
        );
    }

    public function store(array $data): int
    {
        return $this->insert($data);
    }

    public function updateArticle(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function bulkAction(array $ids, string $action): void
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        match($action) {
            'publish' => $this->query(
                "UPDATE tn_articles SET status = 'published', published_at = IFNULL(published_at, NOW()) WHERE id IN ({$placeholders})",
                $ids
            ),
            'draft'   => $this->query("UPDATE tn_articles SET status = 'draft' WHERE id IN ({$placeholders})", $ids),
            'delete'  => $this->query("DELETE FROM tn_articles WHERE id IN ({$placeholders})", $ids),
            default   => null,
        };
    }

    public function toggleBreaking(int $id, int $expiryHours = 6): void
    {
        $article = $this->find($id);
        if (!$article) return;
        $breaking = $article['is_breaking'] ? 0 : 1;
        $expiry   = $breaking ? date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours")) : null;
        $this->query(
            "UPDATE tn_articles SET is_breaking = ?, breaking_expires_at = ? WHERE id = ?",
            [$breaking, $expiry, $id]
        );
    }

    /* ── Dashboard stats ── */

    public function countByStatus(string $status): int
    {
        return (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_articles WHERE status = ?", [$status]);
    }

    public function viewsToday(): int
    {
        try {
            return (int)$this->fetchColumn(
                "SELECT COALESCE(SUM(views),0) FROM tn_analytics_daily WHERE date = CURDATE()"
            );
        } catch (\Exception $e) { return 0; }
    }

    public function scheduled(): array
    {
        return $this->fetchAll(
            "SELECT a.*, c.name AS category_name FROM tn_articles a
             LEFT JOIN tn_categories c ON c.id = a.category_id
             WHERE a.status = 'scheduled' AND a.scheduled_at > NOW()
             ORDER BY a.scheduled_at ASC LIMIT 10"
        );
    }

    public function recentPublished(int $limit = 8): array
    {
        return $this->fetchAll(
            "SELECT a.*, c.name AS category_name, u.name AS author_name
             FROM tn_articles a
             LEFT JOIN tn_categories c ON c.id = a.category_id
             LEFT JOIN tn_users u ON u.id = a.user_id
             WHERE a.status = 'published'
             ORDER BY a.published_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function topByViews(int $limit = 10, string $period = 'today'): array
    {
        try {
            $dateFilter = match($period) {
                'week'  => 'AND ad.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
                'month' => 'AND ad.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)',
                default => 'AND ad.date = CURDATE()',
            };
            return $this->fetchAll(
                "SELECT a.id, a.title, a.slug, a.view_count, a.published_at, c.name AS category_name,
                        COALESCE(SUM(ad.views),0) AS period_views
                 FROM tn_articles a
                 LEFT JOIN tn_categories c ON c.id = a.category_id
                 LEFT JOIN tn_analytics_daily ad ON ad.article_id = a.id {$dateFilter}
                 WHERE a.status = 'published'
                 GROUP BY a.id
                 ORDER BY period_views DESC LIMIT ?",
                [$limit]
            );
        } catch (\Exception $e) { return []; }
    }

    // ── Contributor-specific ─────────────────────────

    public function byContributor(int $contributorId, int $page = 1, int $perPage = 15, string $status = ''): array
    {
        $where  = 'a.contributor_id = ?';
        $params = [$contributorId];
        if ($status) { $where .= ' AND a.status = ?'; $params[] = $status; }

        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT a.*, c.name AS category_name
                   FROM tn_articles a
                   LEFT JOIN tn_categories c ON c.id = a.category_id
                   WHERE {$where}
                   ORDER BY a.created_at DESC
                   LIMIT {$perPage} OFFSET {$offset}";

        return [
            'data'     => $this->fetchAll($sql, $params),
            'total'    => (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_articles a WHERE {$where}", $params),
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    public function countByContributor(int $contributorId, string $status = ''): int
    {
        $sql    = "SELECT COUNT(*) FROM tn_articles WHERE contributor_id = ?";
        $params = [$contributorId];
        if ($status) { $sql .= ' AND status = ?'; $params[] = $status; }
        return (int)$this->fetchColumn($sql, $params);
    }

    public function viewTrend(int $days = 7): array
    {
        try {
            return $this->fetchAll(
                "SELECT date, COALESCE(SUM(views),0) AS views
                 FROM tn_analytics_daily
                 WHERE date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 GROUP BY date ORDER BY date ASC",
                [$days]
            );
        } catch (\Exception $e) { return []; }
    }

    // ── Edit approval flow ────────────────────────────────────

    public function submitEdit(int $id, array $data, int $userId): void
    {
        // Store pending edit as JSON — admin reviews before applying
        $pending = json_encode($data);
        $this->query(
            "UPDATE tn_articles SET pending_edit = ?, pending_edit_by = ?, pending_edit_at = NOW() WHERE id = ?",
            [$pending, $userId, $id]
        );
    }

    public function applyEdit(int $id): void
    {
        $article = $this->find($id);
        if (!$article || !$article['pending_edit']) return;
        $data = json_decode($article['pending_edit'], true);
        unset($data['id']);
        $data['pending_edit']    = null;
        $data['pending_edit_by'] = null;
        $data['pending_edit_at'] = null;
        $this->update($id, $data);
    }

    public function rejectEdit(int $id): void
    {
        $this->query(
            "UPDATE tn_articles SET pending_edit = NULL, pending_edit_by = NULL, pending_edit_at = NULL WHERE id = ?",
            [$id]
        );
    }

    public function pendingEdits(): array
    {
        return $this->fetchAll(
            "SELECT a.id, a.title, a.slug, a.status, a.pending_edit_at,
                    c.name AS category_name, u.name AS editor_name
             FROM tn_articles a
             LEFT JOIN tn_categories c ON c.id = a.category_id
             LEFT JOIN tn_users u ON u.id = a.pending_edit_by
             WHERE a.pending_edit IS NOT NULL
             ORDER BY a.pending_edit_at DESC"
        );
    }


    // ── EDITOR PERMISSION-BASED QUEUE ────────────────────────

    public function reviewQueueForEditor(array $scope, int $page = 1, int $perPage = 20): array
    {
        $where  = ["a.status = 'review'"];
        $params = [];

        if (!empty($scope['districtIds']) || !empty($scope['categoryIds'])) {
            $conditions = [];
            if (!empty($scope['districtIds'])) {
                $ph = implode(',', array_fill(0, count($scope['districtIds']), '?'));
                $conditions[] = "a.city_id IN (SELECT id FROM tn_cities WHERE district_id IN ({$ph}))";
                $params = array_merge($params, $scope['districtIds']);
            }
            if (!empty($scope['categoryIds'])) {
                $ph = implode(',', array_fill(0, count($scope['categoryIds']), '?'));
                $conditions[] = "a.category_id IN ({$ph})";
                $params = array_merge($params, $scope['categoryIds']);
            }
            $where[] = '(' . implode(' OR ', $conditions) . ')';
        }

        $whereStr = 'WHERE ' . implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $data = $this->fetchAll(
            "SELECT a.id, a.title, a.slug, a.status, a.created_at, a.published_at,
                    a.is_breaking, a.is_premium, a.pending_edit,
                    c.name AS category_name, c.slug AS category_slug,
                    u.name AS author_name, r.slug AS author_role,
                    d.name AS district_name
             FROM tn_articles a
             LEFT JOIN tn_categories c  ON c.id = a.category_id
             LEFT JOIN tn_users u       ON u.id = a.user_id
             LEFT JOIN tn_roles r       ON r.id = u.role_id
             LEFT JOIN tn_cities ci     ON ci.id = a.city_id
             LEFT JOIN tn_districts d   ON d.id = ci.district_id
             {$whereStr}
             ORDER BY a.created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $totalStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tn_articles a
             LEFT JOIN tn_cities ci ON ci.id = a.city_id
             {$whereStr}"
        );
        $totalStmt->execute($params);
        $total = (int)$totalStmt->fetchColumn();

        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

}