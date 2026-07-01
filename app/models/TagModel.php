<?php
namespace App\Models;
use App\Core\Model;

class TagModel extends Model
{
    protected string $table = 'tn_tags';

    public function findBySlug(string $slug): array|false
    {
        return $this->fetchOne("SELECT * FROM tn_tags WHERE slug = ?", [$slug]);
    }

    public function popular(int $limit = 20): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_tags ORDER BY usage_count DESC LIMIT ?", [$limit]
        );
    }

    public function forArticle(int $articleId): array
    {
        return $this->fetchAll(
            "SELECT t.* FROM tn_tags t
             JOIN tn_article_tags at2 ON at2.tag_id = t.id
             WHERE at2.article_id = ?
             ORDER BY t.usage_count DESC",
            [$articleId]
        );
    }

    public function all(string $orderBy = 'usage_count', string $dir = 'DESC'): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_tags ORDER BY {$orderBy} {$dir}"
        );
    }


    public function syncArticleTags(int $articleId, array $tagIds): void
    {
        $this->db->prepare("DELETE FROM tn_article_tags WHERE article_id = ?")->execute([$articleId]);
        foreach ($tagIds as $tagId) {
            if ($tagId) {
                $this->db->prepare(
                    "INSERT IGNORE INTO tn_article_tags (article_id, tag_id) VALUES (?,?)"
                )->execute([$articleId, $tagId]);
            }
        }
        // Update usage counts
        $this->db->query(
            "UPDATE tn_tags t SET usage_count = (SELECT COUNT(*) FROM tn_article_tags WHERE tag_id = t.id)"
        );
    }

    public function suggest(string $q, int $limit = 10): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_tags WHERE name LIKE ? OR name_tamil LIKE ?
             ORDER BY usage_count DESC LIMIT ?",
            ["%{$q}%", "%{$q}%", $limit]
        );
    }

}