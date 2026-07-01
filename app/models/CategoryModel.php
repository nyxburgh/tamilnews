<?php
namespace App\Models;

use App\Core\Model;

class CategoryModel extends Model
{
    protected string $table = 'tn_categories';

    public function allWithParent(): array
    {
        return $this->fetchAll(
            "SELECT c.*, p.name AS parent_name,
                    (SELECT COUNT(*) FROM tn_articles a WHERE a.category_id = c.id) AS article_count
             FROM tn_categories c
             LEFT JOIN tn_categories p ON p.id = c.parent_id
             ORDER BY c.sort_order ASC, c.id ASC"
        );
    }

    public function topLevel(): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_categories WHERE parent_id IS NULL ORDER BY sort_order ASC"
        );
    }

    public function children(int $parentId): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_categories WHERE parent_id = ? ORDER BY sort_order ASC",
            [$parentId]
        );
    }

    public function findBySlug(string $slug): array|false
    {
        return $this->fetchOne("SELECT * FROM tn_categories WHERE slug = ?", [$slug]);
    }

    public function updateSort(array $ids): void
    {
        foreach ($ids as $order => $id) {
            $this->query(
                "UPDATE tn_categories SET sort_order = ? WHERE id = ?",
                [$order, $id]
            );
        }
    }

    public function articleCount(int $id): int
    {
        return (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_articles WHERE category_id = ? AND status = 'published'",
            [$id]
        );
    }
}
