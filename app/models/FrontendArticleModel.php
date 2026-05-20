<?php
namespace App\Models;

use App\Core\Model;

class FrontendArticleModel extends Model
{
    protected string $table = 'tn_articles';

    private function baseSelect(): string
    {
        return "SELECT a.id, a.title, a.slug, a.excerpt, a.content_type,
                       a.youtube_video_id, a.is_breaking, a.is_editors_pick, a.is_featured,
                       a.view_count, a.whatsapp_shares, a.rating_avg, a.rating_count,
                       a.published_at, a.read_time,
                       c.name AS category_name, c.name_tamil AS category_tamil, c.slug AS category_slug,
                       m.filepath AS image_url, m.thumb_path AS thumb_url,
                       u.name AS author_name,
                       ct.name AS contributor_name, ct.avatar AS contributor_avatar
                FROM tn_articles a
                LEFT JOIN tn_categories c  ON c.id  = a.category_id
                LEFT JOIN tn_media m       ON m.id  = a.media_id
                LEFT JOIN tn_users u       ON u.id  = a.user_id
                LEFT JOIN tn_contributors ct ON ct.id = a.contributor_id";
    }

    public function breaking(int $limit = 8): array
    {
        return $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published' AND a.is_breaking=1
              AND (a.breaking_expires_at IS NULL OR a.breaking_expires_at > NOW())
              ORDER BY a.published_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function featured(int $limit = 1): array|false
    {
        $rows = $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published' AND a.is_featured=1
              ORDER BY a.published_at DESC LIMIT ?",
            [$limit]
        );
        return $limit === 1 ? ($rows[0] ?? false) : $rows;
    }

    public function topStories(int $limit = 3): array
    {
        return $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published'
              ORDER BY a.view_count DESC, a.published_at DESC LIMIT ?",
            [$limit]
        );
    }


    // Google News sitemap — last 2 days only (Google requirement)
    public function latestForNews(int $limit = 500): array
    {
        return $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published'
              AND a.published_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
              ORDER BY a.published_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function latest(int $limit = 10, int $offset = 0): array
    {
        return $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published'
              ORDER BY a.published_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function byCategory(string $categorySlug, int $page = 1, int $perPage = 6): array
    {
        $offset = ($page - 1) * $perPage;
        $data   = $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published' AND c.slug = ?
              ORDER BY a.published_at DESC LIMIT ? OFFSET ?",
            [$categorySlug, $perPage, $offset]
        );
        $total = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_articles a
             JOIN tn_categories c ON c.id = a.category_id
             WHERE a.status='published' AND c.slug = ?",
            [$categorySlug]
        );
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function categoryBlock(string $categorySlug, int $limit = 4): array
    {
        return $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published' AND c.slug = ?
              ORDER BY a.published_at DESC LIMIT ?",
            [$categorySlug, $limit]
        );
    }

    public function bySlug(string $slug): array|false
    {
        return $this->fetchOne(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.content_type,
                    a.youtube_url, a.youtube_video_id, a.is_breaking, a.is_editors_pick,
                    a.is_featured, a.view_count, a.whatsapp_shares, a.rating_avg,
                    a.rating_count, a.published_at, a.read_time, a.meta_title, a.meta_desc,
                    c.name AS category_name, c.name_tamil AS category_tamil, c.slug AS category_slug,
                    m.filepath AS image_url, m.thumb_path AS thumb_url,
                    u.name AS author_name,
                    ct.name AS contributor_name, ct.avatar AS contributor_avatar,
                    GROUP_CONCAT(DISTINCT t.name ORDER BY t.usage_count DESC SEPARATOR '||') AS tag_names,
                    GROUP_CONCAT(DISTINCT t.slug ORDER BY t.usage_count DESC SEPARATOR '||') AS tag_slugs
             FROM tn_articles a
             LEFT JOIN tn_categories c    ON c.id  = a.category_id
             LEFT JOIN tn_media m         ON m.id  = a.media_id
             LEFT JOIN tn_users u         ON u.id  = a.user_id
             LEFT JOIN tn_contributors ct ON ct.id = a.contributor_id
             LEFT JOIN tn_article_tags at2 ON at2.article_id = a.id
             LEFT JOIN tn_tags t           ON t.id = at2.tag_id
             WHERE a.slug = ? AND a.status = 'published'
             GROUP BY a.id",
            [$slug]
        );
    }

    public function related(int $articleId, int $categoryId, int $limit = 4): array
    {
        return $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published' AND a.id != ? AND a.category_id = ?
              ORDER BY a.published_at DESC LIMIT ?",
            [$articleId, $categoryId, $limit]
        );
    }

    public function trending(int $limit = 5, string $period = 'today'): array
    {
        $dateFilter = match($period) {
            'week'  => "AND ad.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
            'month' => "AND ad.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
            default => "AND ad.date = CURDATE()",
        };
        return $this->fetchAll(
            "SELECT a.id, a.title, a.slug, a.view_count, a.published_at,
                    c.name AS category_name, c.slug AS category_slug,
                    m.thumb_path AS thumb_url,
                    COALESCE(SUM(ad.views),0) AS period_views
             FROM tn_articles a
             LEFT JOIN tn_categories c ON c.id = a.category_id
             LEFT JOIN tn_media m ON m.id = a.media_id
             LEFT JOIN tn_analytics_daily ad ON ad.article_id = a.id {$dateFilter}
             WHERE a.status = 'published'
             GROUP BY a.id
             ORDER BY period_views DESC, a.published_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function videos(int $limit = 6): array
    {
        return $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published' AND a.youtube_video_id IS NOT NULL
              ORDER BY a.published_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function editorsPicks(int $limit = 3): array
    {
        return $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published' AND a.is_editors_pick=1
              ORDER BY a.published_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function search(string $query, int $page = 1, int $perPage = 6): array
    {
        $offset = ($page - 1) * $perPage;
        $like   = '%' . $query . '%';
        $data   = $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published' AND (a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)
              ORDER BY a.published_at DESC LIMIT ? OFFSET ?",
            [$like, $like, $like, $perPage, $offset]
        );
        $total = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_articles a
             WHERE a.status='published' AND (a.title LIKE ? OR a.excerpt LIKE ?)",
            [$like, $like]
        );
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function incrementView(int $id): void
    {
        $this->query("UPDATE tn_articles SET view_count = view_count + 1 WHERE id = ?", [$id]);
        try {
            $this->query(
                "INSERT INTO tn_analytics_daily (article_id, date, views)
                 VALUES (?, CURDATE(), 1)
                 ON DUPLICATE KEY UPDATE views = views + 1",
                [$id]
            );
        } catch (\Exception $e) { /* analytics table optional */ }
    }

    public function trackWhatsApp(int $id): void
    {
        $this->query("UPDATE tn_articles SET whatsapp_shares = whatsapp_shares + 1 WHERE id = ?", [$id]);
    }

    public function byTag(string $tagSlug, int $page = 1, int $perPage = 6): array
    {
        $offset = ($page - 1) * $perPage;
        $data   = $this->fetchAll(
            $this->baseSelect() .
            " JOIN tn_article_tags at2 ON at2.article_id = a.id
              JOIN tn_tags t ON t.id = at2.tag_id
              WHERE a.status='published' AND t.slug = ?
              ORDER BY a.published_at DESC LIMIT ? OFFSET ?",
            [$tagSlug, $perPage, $offset]
        );
        $total = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_articles a
             JOIN tn_article_tags at2 ON at2.article_id = a.id
             JOIN tn_tags t ON t.id = at2.tag_id
             WHERE a.status='published' AND t.slug = ?",
            [$tagSlug]
        );
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function authorBySlug(string $slug): array|false
    {
        return $this->fetchOne(
            "SELECT u.id, u.name, u.avatar,
                    r.name AS role_name, r.slug AS role_slug,
                    COUNT(a.id) AS article_count
             FROM tn_users u
             JOIN tn_roles r ON r.id = u.role_id
             LEFT JOIN tn_articles a ON a.user_id = u.id AND a.status='published'
             WHERE LOWER(REPLACE(u.name,' ','-')) = ?
             GROUP BY u.id",
            [$slug]
        );
    }

    public function byAuthor(int $userId, int $page = 1, int $perPage = 6): array
    {
        $offset = ($page - 1) * $perPage;
        $data   = $this->fetchAll(
            $this->baseSelect() .
            " WHERE a.status='published' AND a.user_id = ?
              ORDER BY a.published_at DESC LIMIT ? OFFSET ?",
            [$userId, $perPage, $offset]
        );
        $total = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_articles WHERE status='published' AND user_id = ?",
            [$userId]
        );
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

}