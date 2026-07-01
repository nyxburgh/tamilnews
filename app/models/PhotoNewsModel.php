<?php
namespace App\Models;

use App\Core\{Database, Helper};

class PhotoNewsModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(int $limit = 20, int $offset = 0, ?int $userId = null): array
    {
        $where  = $userId ? "WHERE pn.created_by = ?" : "";
        $params = $userId ? [$userId, $limit, $offset] : [$limit, $offset];

        $stmt = $this->db->prepare(
            "SELECT pn.*, u.name AS author,
                    a.slug AS article_slug
             FROM tn_photo_news pn
             LEFT JOIN tn_users u ON u.id = pn.created_by
             LEFT JOIN tn_articles a ON a.id = pn.article_id
             $where
             ORDER BY pn.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count(?int $userId = null): int
    {
        if ($userId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM tn_photo_news WHERE created_by = ?");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        }
        return (int)$this->db->query("SELECT COUNT(*) FROM tn_photo_news")->fetchColumn();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM tn_photo_news WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Search photo news not yet linked to any article — used by the
    // article-list "Connect" picker (reverse direction)
    public function suggestUnlinked(string $q): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, image_path
             FROM tn_photo_news
             WHERE article_id IS NULL AND title LIKE ?
             ORDER BY created_at DESC
             LIMIT 10"
        );
        $stmt->execute(['%' . $q . '%']);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findBySlug(string $slug): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM tn_photo_news WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function store(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tn_photo_news (title, slug, image_path, status, approval_status, created_by)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['image_path'] ?? null,
            $data['status'] ?? 'published',
            $data['approval_status'] ?? 'pending',
            $data['created_by'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Returns [article_id => photo_news_id] for a list of article IDs
    public function articleLookup(array $articleIds): array
    {
        if (empty($articleIds)) return [];
        $placeholders = implode(',', array_fill(0, count($articleIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT id, article_id FROM tn_photo_news
             WHERE article_id IN ($placeholders)"
        );
        $stmt->execute($articleIds);
        $map = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $map[(int)$row['article_id']] = (int)$row['id'];
        }
        return $map;
    }

    public function linkArticle(int $photoNewsId, int $articleId): void
    {
        $this->db->prepare(
            "UPDATE tn_photo_news SET article_id = ? WHERE id = ?"
        )->execute([$articleId, $photoNewsId]);
    }

    public function findByArticleId(int $articleId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tn_photo_news WHERE article_id = ? LIMIT 1"
        );
        $stmt->execute([$articleId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data): void
    {
        $this->db->prepare(
            "UPDATE tn_photo_news SET title=?, slug=?, status=?, approval_status=?, updated_at=NOW() WHERE id=?"
        )->execute([
            $data['title'],
            $data['slug'],
            $data['status'] ?? 'published',
            $data['approval_status'] ?? 'pending',
            $id,
        ]);
    }

    public function updateImage(int $id, string $path): void
    {
        $this->db->prepare("UPDATE tn_photo_news SET image_path=? WHERE id=?")->execute([$path, $id]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM tn_photo_news_tags WHERE photo_news_id=?")->execute([$id]);
        $this->db->prepare("DELETE FROM tn_photo_news WHERE id=?")->execute([$id]);
    }

    public function syncTags(int $id, array $tagIds): void
    {
        $this->db->prepare("DELETE FROM tn_photo_news_tags WHERE photo_news_id=?")->execute([$id]);
        $stmt = $this->db->prepare("INSERT IGNORE INTO tn_photo_news_tags (photo_news_id, tag_id) VALUES (?,?)");
        foreach ($tagIds as $tid) { $stmt->execute([$id, (int)$tid]); }
    }

    public function tags(int $id): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.id, t.name, t.name_tamil
             FROM tn_tags t
             JOIN tn_photo_news_tags pt ON pt.tag_id = t.id
             WHERE pt.photo_news_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function uniqueSlug(string $slug, int $excludeId = 0): string
    {
        $base = $slug; $i = 1;
        while (true) {
            $stmt = $this->db->prepare("SELECT id FROM tn_photo_news WHERE slug=? AND id!=? LIMIT 1");
            $stmt->execute([$slug, $excludeId]);
            if (!$stmt->fetch()) break;
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
