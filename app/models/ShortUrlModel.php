<?php
namespace App\Models;

use App\Core\Model;

class ShortUrlModel extends Model
{
    protected string $table = 'tn_short_urls';

    /** Get or create short URL for an article */
    public function forArticle(int $articleId, string $targetUrl): string
    {
        $existing = $this->fetchOne(
            "SELECT code FROM tn_short_urls WHERE article_id = ? LIMIT 1",
            [$articleId]
        );
        if ($existing) return $existing['code'];
        return $this->create($targetUrl, $articleId);
    }

    public function create(string $targetUrl, ?int $articleId = null): string
    {
        do {
            $code = substr(base_convert(bin2hex(random_bytes(4)), 16, 36), 0, 6);
        } while ($this->fetchColumn("SELECT id FROM tn_short_urls WHERE code=?", [$code]));

        $this->query(
            "INSERT INTO tn_short_urls (code, target_url, article_id) VALUES (?,?,?)",
            [$code, $targetUrl, $articleId]
        );
        return $code;
    }

    public function resolve(string $code): array|false
    {
        return $this->fetchOne(
            "SELECT * FROM tn_short_urls WHERE code = ?", [$code]
        );
    }

    public function incrementClicks(string $code): void
    {
        $this->query(
            "UPDATE tn_short_urls SET clicks = clicks + 1 WHERE code = ?", [$code]
        );
    }
}
