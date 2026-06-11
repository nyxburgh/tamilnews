<?php
namespace App\Models;

use App\Core\Model;

class LiveBlogModel extends Model
{
    protected string $table = 'tn_live_blogs';

    public function allWithStats(): array
    {
        return $this->fetchAll(
            "SELECT lb.*, a.title AS article_title, a.slug AS article_slug,
                    u.name AS author_name,
                    COUNT(le.id) AS entry_count
             FROM tn_live_blogs lb
             LEFT JOIN tn_articles a ON a.id = lb.article_id
             LEFT JOIN tn_users u ON u.id = lb.user_id
             LEFT JOIN tn_live_entries le ON le.blog_id = lb.id
             GROUP BY lb.id
             ORDER BY lb.created_at DESC"
        );
    }

    public function findWithEntries(int $id): array|false
    {
        $blog = $this->fetchOne(
            "SELECT lb.*, a.title AS article_title, a.slug AS article_slug,
                    u.name AS author_name
             FROM tn_live_blogs lb
             LEFT JOIN tn_articles a ON a.id = lb.article_id
             LEFT JOIN tn_users u ON u.id = lb.user_id
             WHERE lb.id = ?",
            [$id]
        );
        if (!$blog) return false;
        $blog['entries'] = $this->entries($id);
        return $blog;
    }

    public function findBySlug(string $slug): array|false
    {
        return $this->fetchOne(
            "SELECT lb.*, a.title AS article_title, a.slug AS article_slug
             FROM tn_live_blogs lb
             LEFT JOIN tn_articles a ON a.id = lb.article_id
             WHERE lb.slug = ?",
            [$slug]
        );
    }

    public function entries(int $liveBlogId, int $afterId = 0, int $limit = 100): array
    {
        $where = $afterId ? "WHERE e.live_blog_id = ? AND e.id > ?" : "WHERE e.live_blog_id = ?";
        $params = $afterId ? [$liveBlogId, $afterId] : [$liveBlogId];
        return $this->fetchAll(
            "SELECT e.*, u.name AS author_name
             FROM tn_live_entries e
             LEFT JOIN tn_users u ON u.id = e.user_id
             {$where}
             ORDER BY e.id DESC LIMIT {$limit}",
            $params
        );
    }

    public function latestEntryId(int $liveBlogId): int
    {
        return (int)$this->fetchColumn(
            "SELECT COALESCE(MAX(id), 0) FROM tn_live_entries WHERE live_blog_id = ?",
            [$liveBlogId]
        );
    }

    public function addEntry(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tn_live_entries
             (live_blog_id, user_id, content, label, label_color,
              score_home, score_away, image_url, youtube_video_id, is_pinned)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $ytId = null;
        if (!empty($data['youtube_url'])) {
            preg_match('/(?:v=|\/embed\/|\/shorts\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $data['youtube_url'], $m);
            $ytId = $m[1] ?? null;
        }
        $stmt->execute([
            $data['live_blog_id'], $data['user_id'], $data['content'],
            $data['label'] ?? null, $data['label_color'] ?? '#C0001A',
            $data['score_home'] ?? null, $data['score_away'] ?? null,
            $data['image_url'] ?? null, $ytId,
            $data['is_pinned'] ?? 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteEntry(int $entryId): void
    {
        $this->db->prepare("DELETE FROM tn_live_entries WHERE id = ?")->execute([$entryId]);
    }

    public function setStatus(int $id, string $status): void
    {
        $ended = $status === 'ended' ? date('Y-m-d H:i:s') : null;
        $this->query(
            "UPDATE tn_live_blogs SET status = ?, ended_at = ? WHERE id = ?",
            [$status, $ended, $id]
        );
    }

    public function activeBlogs(): array
    {
        return $this->fetchAll(
            "SELECT lb.*, a.title AS article_title, a.slug AS article_slug,
                    COUNT(le.id) AS entry_count
             FROM tn_live_blogs lb
             LEFT JOIN tn_articles a ON a.id = lb.article_id
             LEFT JOIN tn_live_entries le ON le.blog_id = lb.id
             WHERE lb.status = 'active'
             GROUP BY lb.id
             ORDER BY lb.created_at DESC"
        );
    }
}
