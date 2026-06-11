<?php
namespace App\Models;
use App\Core\Model;

class RssModel extends Model
{
    protected string $table = 'tn_rss_feeds';
    public function allFeeds(): array
    {
        return $this->fetchAll(
            "SELECT r.*, c.name AS category_name FROM tn_rss_feeds r
             LEFT JOIN tn_categories c ON c.id = r.category_id ORDER BY r.id DESC"
        );
    }
    public function imports(int $page = 1, int $perPage = 20, string $status = ''): array
    {
        $where  = $status ? "WHERE ri.status = ?" : '';
        $offset = ($page - 1) * $perPage;
        $params = $status ? [$status] : [];
        $data   = $this->fetchAll(
            "SELECT ri.*, rf.name AS feed_name FROM tn_rss_imports ri
             JOIN tn_rss_feeds rf ON rf.id = ri.feed_id
             {$where} ORDER BY ri.fetched_at DESC LIMIT {$perPage} OFFSET {$offset}", $params
        );
        $total  = (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_rss_imports {$where}", $params);
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }
    public function pendingCount(): int { try { return (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_rss_imports WHERE status='pending'"); } catch (\Exception $e) { return 0; } }
    public function updateImportStatus(int $id, string $status, ?int $articleId = null): void
    {
        $this->db->prepare("UPDATE tn_rss_imports SET status=?, article_id=? WHERE id=?")->execute([$status, $articleId, $id]);
    }
}
