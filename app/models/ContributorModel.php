<?php
namespace App\Models;

use App\Core\Model;

class ContributorModel extends Model
{
    protected string $table = 'tn_contributors';

    public function findByGoogle(string $googleId): array|false
    {
        return $this->fetchOne(
            "SELECT c.*, GROUP_CONCAT(cc.category_id) AS category_ids
             FROM tn_contributors c
             LEFT JOIN tn_contributor_categories cc ON cc.contributor_id = c.id
             WHERE c.email = ? AND c.is_active = 1
             GROUP BY c.id",
            [$googleId]
        );
    }

    public function findByEmail(string $email): array|false
    {
        return $this->fetchOne(
            "SELECT c.*, GROUP_CONCAT(cc.category_id) AS category_ids
             FROM tn_contributors c
             LEFT JOIN tn_contributor_categories cc ON cc.contributor_id = c.id
             WHERE c.email = ?
             GROUP BY c.id",
            [$email]
        );
    }

    public function findFull(int $id): array|false
    {
        return $this->fetchOne(
            "SELECT c.*, GROUP_CONCAT(cc.category_id) AS category_ids
             FROM tn_contributors c
             LEFT JOIN tn_contributor_categories cc ON cc.contributor_id = c.id
             WHERE c.id = ?
             GROUP BY c.id",
            [$id]
        );
    }

    public function allWithStats(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $data   = $this->fetchAll(
            "SELECT c.*,
                    COUNT(DISTINCT a.id) AS total_articles,
                    SUM(CASE WHEN a.status='published' THEN 1 ELSE 0 END) AS published_count,
                    SUM(CASE WHEN a.status='draft'     THEN 1 ELSE 0 END) AS draft_count,
                    SUM(CASE WHEN a.status='review'    THEN 1 ELSE 0 END) AS review_count
             FROM tn_contributors c
             LEFT JOIN tn_articles a ON a.contributor_id = c.id
             GROUP BY c.id
             ORDER BY c.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        return ['data' => $data, 'total' => $this->count(), 'page' => $page, 'per_page' => $perPage];
    }

    public function assignedCategories(int $id): array
    {
        return $this->fetchAll(
            "SELECT cat.* FROM tn_categories cat
             JOIN tn_contributor_categories cc ON cc.category_id = cat.id
             WHERE cc.contributor_id = ?",
            [$id]
        );
    }

    public function syncCategories(int $contributorId, array $categoryIds): void
    {
        $this->query("DELETE FROM tn_contributor_categories WHERE contributor_id = ?", [$contributorId]);
        foreach ($categoryIds as $catId) {
            $this->query(
                "INSERT IGNORE INTO tn_contributor_categories (contributor_id, category_id) VALUES (?,?)",
                [$contributorId, (int)$catId]
            );
        }
    }

    public function upsertFromGoogle(array $googleProfile): int
    {
        $existing = $this->findByEmail($googleProfile['email'] ?? '');
        if ($existing) {
            $this->update($existing['id'], [
                'name'       => $googleProfile['name'],
                'avatar'     => $googleProfile['avatar'],
                'last_login' => date('Y-m-d H:i:s'),
            ]);
            return $existing['id'];
        }
        return $this->insert([
            'name'       => $googleProfile['name'],
            'email'      => $googleProfile['email'],
            'avatar'     => $googleProfile['avatar'],
            'is_active'  => 0, // Admin must approve
            'last_login' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateLastLogin(int $id): void
    {
        $this->query("UPDATE tn_contributors SET last_login = NOW() WHERE id = ?", [$id]);
    }

    public function pendingApprovalCount(): int
    {
        try {
            return (int)$this->fetchColumn(
                "SELECT COUNT(*) FROM tn_contributors WHERE is_approved = 0 AND is_active = 1"
            );
        } catch (\Exception $e) { return 0; }
    }

    public function update(int $id, array $data): bool
    {
        if (empty($data)) return false;
        $cols = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        $vals = array_values($data);
        $vals[] = $id;
        $this->query("UPDATE tn_contributors SET $cols WHERE id = ?", $vals);
        return true;
    }

    public function findByGoogleId(string $googleId): array|false
    {
        return $this->query(
            "SELECT * FROM tn_contributors WHERE google_id = ? LIMIT 1",
            [$googleId]
        )->fetch(\PDO::FETCH_ASSOC);
    }

}
