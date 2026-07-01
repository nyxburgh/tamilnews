<?php
namespace App\Core;

class Helper
{
    public static function slug(string $text): string
    {
        // Unicode-aware slug: keeps Tamil script + Latin letters/numbers.
        // (Previous version ran an ASCII-only iconv transliteration afterward,
        // which silently dropped every Tamil character, leaving a random
        // fallback hash as the "slug" for any Tamil-only title.)
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[\s\-]+/', '-', $text);
        $text = preg_replace('/[^\p{L}\p{N}\-]/u', '', $text);
        $text = trim($text, '-');
        return $text !== '' ? mb_substr($text, 0, 120) : substr(md5(uniqid()), 0, 8);
    }

    public static function uniqueSlug(string $table, string $slug, int $excludeId = 0): string
    {
        $db   = Database::getInstance();
        $base = $slug;
        $i    = 1;
        do {
            $sql  = "SELECT COUNT(*) FROM `{$table}` WHERE `slug` = ?";
            $params = [$slug];
            if ($excludeId) { $sql .= ' AND `id` != ?'; $params[] = $excludeId; }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $count = (int)$stmt->fetchColumn();
            if ($count === 0) break;
            $slug = $base . '-' . $i++;
        } while (true);
        return $slug;
    }

    public static function readTime(string $content): int
    {
        $words = str_word_count(strip_tags($content));
        return max(1, (int)ceil($words / 200));
    }

    public static function excerpt(string $text, int $length = 160): string
    {
        $text = strip_tags($text);
        if (mb_strlen($text) <= $length) return $text;
        return mb_substr($text, 0, $length) . '…';
    }

    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function formatDate(string $date, string $format = 'd M Y, h:i A'): string
    {
        return date($format, strtotime($date));
    }

    /**
     * Normalize a stored filepath to a full URL, handling old/new path formats.
     */
    public static function assetUrl(?string $path): string
    {
        if (!$path) return '';
        if (str_starts_with($path, 'http')) return $path;
        // Strip any legacy /public prefix variants
        $path = preg_replace('#^/[^/]+/public/#', '/uploads/', $path);
        $path = preg_replace('#^/public/#', '/uploads/', $path);
        return rtrim(ASSET_URL, '/') . '/' . ltrim($path, '/');
    }

    public static function timeAgo(string $date): string
    {
        try {
            $then = new \DateTime($date, new \DateTimeZone('UTC'));
            $now  = new \DateTime('now', new \DateTimeZone('UTC'));
            $diff = $now->getTimestamp() - $then->getTimestamp();
        } catch (\Exception $e) {
            $diff = time() - strtotime($date);
        }
        if ($diff < 60)     return 'இப்போது';
        if ($diff < 3600)   return (int)($diff/60) . ' நிமிடம் முன்';
        if ($diff < 86400)  return (int)($diff/3600) . ' மணி முன்';
        if ($diff < 604800) return (int)($diff/86400) . ' நாள் முன்';
        return self::formatDate($date, 'd M Y');
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes/1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes/1024, 1) . ' KB';
        return $bytes . ' B';
    }

    public static function sanitize(string $input): string
    {
        // Strip tags only — do NOT htmlspecialchars here
        // Encoding happens at display time via e() / htmlspecialchars in views
        return strip_tags(trim($input));
    }

    public static function redirect(string $url): void
    {
        // For relative URLs (e.g. /admin/login), prepend base + /public
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            static $base = null;
            if ($base === null) {
                $cfg    = require CONFIG_PATH . '/app.php';
                $parsed = parse_url(rtrim($cfg['url'] ?? '', '/'), PHP_URL_PATH) ?? '';
                $base   = rtrim($parsed, '/') . '/public';
            }
            if ($base && !str_starts_with($url, $base)) {
                $url = $base . $url;
            }
        }
        header("Location: {$url}");
        exit;
    }

    public static function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public static function youtubeId(string $url): ?string
    {
        preg_match('/(?:v=|\/embed\/|\/shorts\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
        return $m[1] ?? null;
    }

    public static function youtubeThumbnail(string $videoId): string
    {
        return "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
    }

    public static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function generateHash(string $content): string
    {
        return hash('sha256', $content);
    }
}
