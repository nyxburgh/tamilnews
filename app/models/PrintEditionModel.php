<?php
namespace App\Models;

use App\Core\Model;

class PrintEditionModel extends Model
{
    protected string $table = 'tn_print_editions';

    public function allWithCount(): array
    {
        return $this->fetchAll(
            "SELECT e.*, u.name AS created_by_name,
                    COUNT(ea.article_id) AS article_count
             FROM tn_print_editions e
             LEFT JOIN tn_users u ON u.id = e.user_id
             LEFT JOIN tn_print_edition_articles ea ON ea.edition_id = e.id
             GROUP BY e.id
             ORDER BY e.edition_date DESC, e.created_at DESC"
        );
    }

    public function findWithArticles(int $id): array|false
    {
        $edition = $this->find($id);
        if (!$edition) return false;
        $edition['articles'] = $this->editionArticles($id);
        return $edition;
    }

    public function editionArticles(int $editionId): array
    {
        return $this->fetchAll(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.published_at,
                    a.view_count, a.word_count, a.content_type,
                    c.name AS category_name, c.name_tamil AS category_tamil,
                    m.filepath AS image_url,
                    u.name AS author_name,
                    ea.sort_order
             FROM tn_print_edition_articles ea
             JOIN tn_articles a ON a.id = ea.article_id
             LEFT JOIN tn_categories c ON c.id = a.category_id
             LEFT JOIN tn_media m ON m.id = a.media_id
             LEFT JOIN tn_users u ON u.id = a.user_id
             WHERE ea.edition_id = ?
             ORDER BY ea.sort_order ASC, ea.added_at ASC",
            [$editionId]
        );
    }

    public function addArticle(int $editionId, int $articleId): void
    {
        try {
            $maxSort = (int)$this->fetchColumn(
                "SELECT COALESCE(MAX(sort_order),0) FROM tn_print_edition_articles WHERE edition_id = ?",
                [$editionId]
            );
            $this->db->prepare(
                "INSERT IGNORE INTO tn_print_edition_articles (edition_id, article_id, sort_order)
                 VALUES (?,?,?)"
            )->execute([$editionId, $articleId, $maxSort + 1]);
        } catch (\Exception $e) {}
    }

    public function removeArticle(int $editionId, int $articleId): void
    {
        $this->db->prepare(
            "DELETE FROM tn_print_edition_articles WHERE edition_id=? AND article_id=?"
        )->execute([$editionId, $articleId]);
    }

    public function updateSort(int $editionId, array $articleIds): void
    {
        foreach ($articleIds as $i => $artId) {
            $this->db->prepare(
                "UPDATE tn_print_edition_articles SET sort_order=? WHERE edition_id=? AND article_id=?"
            )->execute([$i + 1, $editionId, $artId]);
        }
    }

    public function isArticleInEdition(int $editionId, int $articleId): bool
    {
        $count = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_print_edition_articles WHERE edition_id=? AND article_id=?",
            [$editionId, $articleId]
        );
        return $count > 0;
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->query("UPDATE tn_print_editions SET status=? WHERE id=?", [$status, $id]);
    }
}
