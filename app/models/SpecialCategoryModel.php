<?php
namespace App\Models;

use App\Core\Model;

class SpecialCategoryModel extends Model
{
    protected string $table = 'tn_special_categories';

    public function allActive(): array
    {
        return $this->fetchAll(
            "SELECT sc.*, c.name AS base_category_name
             FROM tn_special_categories sc
             LEFT JOIN tn_categories c ON c.id = sc.category_id
             WHERE sc.is_active = 1
             ORDER BY sc.id ASC, sc.id DESC"
        );
    }

    public function allSpecial(): array
    {
        return $this->fetchAll(
            "SELECT sc.*, c.name AS base_category_name
             FROM tn_special_categories sc
             LEFT JOIN tn_categories c ON c.id = sc.category_id
             ORDER BY sc.id ASC, sc.id DESC"
        );
    }

    public function findBySlug(string $slug): array|false
    {
        return $this->fetchOne(
            "SELECT sc.*, c.name AS base_category_name
             FROM tn_special_categories sc
             LEFT JOIN tn_categories c ON c.id = sc.category_id
             WHERE sc.slug = ? AND sc.is_active = 1",
            [$slug]
        );
    }

    public function articlesForSpecial(int $specialId, int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $data   = \App\Core\Database::getInstance()->prepare(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.published_at, a.view_count,
                    0 AS rating_avg, a.is_breaking, a.content_type, a.youtube_video_id,
                    c.name AS category_name, c.slug AS category_slug,
                    m.filepath AS image_url, m.thumb_path AS thumb_url
             FROM tn_articles a
             JOIN tn_special_category_articles sca ON sca.article_id = a.id
             LEFT JOIN tn_categories c ON c.id = a.category_id
             LEFT JOIN tn_media m ON m.id = a.media_id
             WHERE sca.special_id = ? AND a.status = 'published'
             ORDER BY sca.sort_order ASC, a.published_at DESC
             LIMIT ? OFFSET ?"
        );
        $data->execute([$specialId, $perPage, $offset]);
        $rows  = $data->fetchAll(\PDO::FETCH_ASSOC);
        $total = (int)\App\Core\Database::getInstance()
            ->prepare("SELECT COUNT(*) FROM tn_special_category_articles WHERE special_id = ?")
            ->execute([$specialId]) ? \App\Core\Database::getInstance()
            ->query("SELECT COUNT(*) FROM tn_special_category_articles WHERE special_id = {$specialId}")
            ->fetchColumn() : 0;
        return ['data' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function syncArticle(int $specialId, int $articleId, int $sort = 0): void
    {
        \App\Core\Database::getInstance()
            ->prepare("INSERT IGNORE INTO tn_special_category_articles (special_id, article_id, sort_order) VALUES (?,?,?)")
            ->execute([$specialId, $articleId, $sort]);
    }

    public function removeArticle(int $specialId, int $articleId): void
    {
        \App\Core\Database::getInstance()
            ->prepare("DELETE FROM tn_special_category_articles WHERE special_id=? AND article_id=?")
            ->execute([$specialId, $articleId]);
    }

    public function activeElections(): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_special_categories WHERE type='election' AND is_active=1 ORDER BY sort_order ASC"
        );
    }

    public function activeFestivals(): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_special_categories WHERE type='festival' AND is_active=1
             AND (ends_at IS NULL OR ends_at >= NOW()) ORDER BY sort_order ASC"
        );
    }
}
