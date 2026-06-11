<?php
namespace App\Models;

use App\Core\Model;

class RatingModel extends Model
{
    protected string $table = 'tn_article_ratings';

    public function forArticle(int $articleId): array
    {
        return $this->fetchOne(
            "SELECT
                COUNT(*)                     AS total,
                ROUND(AVG(rating), 1)        AS average,
                SUM(rating = 5)              AS five,
                SUM(rating = 4)              AS four,
                SUM(rating = 3)              AS three,
                SUM(rating = 2)              AS two,
                SUM(rating = 1)              AS one
             FROM tn_article_ratings WHERE article_id = ?",
            [$articleId]
        ) ?: ['total' => 0, 'average' => 0];
    }

    public function readerRating(int $articleId, int $readerId): array|false
    {
        return $this->fetchOne(
            "SELECT * FROM tn_article_ratings WHERE article_id = ? AND reader_id = ?",
            [$articleId, $readerId]
        );
    }

    public function upsert(int $articleId, int $readerId, int $rating, string $review = ''): void
    {
        $existing = $this->readerRating($articleId, $readerId);
        if ($existing) {
            $this->query(
                "UPDATE tn_article_ratings SET rating = ?, review = ?, updated_at = NOW()
                 WHERE article_id = ? AND reader_id = ?",
                [$rating, $review, $articleId, $readerId]
            );
        } else {
            $this->insert([
                'article_id' => $articleId,
                'reader_id'  => $readerId,
                'rating'     => $rating,
                'review'     => $review,
            ]);
        }
        // Update cached average on article
        $this->query(
            "UPDATE tn_articles SET
                rating_avg   = (SELECT AVG(rating) FROM tn_article_ratings WHERE article_id = ?),
                rating_count = (SELECT COUNT(*)    FROM tn_article_ratings WHERE article_id = ?)
             WHERE id = ?",
            [$articleId, $articleId, $articleId]
        );
    }

    public function topRated(int $limit = 10): array
    {
        return $this->fetchAll(
            "SELECT a.id, a.title, a.slug, COALESCE((SELECT AVG(ar.rating) FROM tn_article_ratings ar WHERE ar.article_id=a.id),0) AS rating_avg,
                       COALESCE((SELECT COUNT(*) FROM tn_article_ratings ar WHERE ar.article_id=a.id),0) AS rating_count, c.name AS category_name
             FROM tn_articles a
             LEFT JOIN tn_categories c ON c.id = a.category_id
             WHERE a.status = 'published' AND 0 AS rating_count > 0
             ORDER BY 0 AS rating_avg DESC, 0 AS rating_count DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function recentReviews(int $articleId, int $limit = 10): array
    {
        return $this->fetchAll(
            "SELECT r.*, rd.name AS reader_name, rd.avatar AS reader_avatar
             FROM tn_article_ratings r
             JOIN tn_readers rd ON rd.id = r.reader_id
             WHERE r.article_id = ? AND r.review != ''
             ORDER BY r.created_at DESC LIMIT ?",
            [$articleId, $limit]
        );
    }
}
