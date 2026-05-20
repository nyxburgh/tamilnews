<?php
/**
 * YouTube Auto Import Cron
 * Schedule: 0 * * * * php /path/to/cron/youtube_import.php
 */

define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH',   APP_PATH  . '/views');
define('STORAGE_PATH',ROOT_PATH . '/storage');
define('APP_DEBUG',   false);

date_default_timezone_set('Asia/Kolkata');

// Load .env
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $val] = explode('=', $line, 2) + [1 => ''];
        $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
    }
}

spl_autoload_register(function (string $class): void {
    $file = APP_PATH . '/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) require_once $file;
});

$startTime = microtime(true);
$db        = \App\Core\Database::getInstance();
$settings  = $db->query("SELECT value FROM tn_settings WHERE `key` = 'youtube_api_key'")->fetchColumn();
$apiKey    = $_ENV['YOUTUBE_API_KEY'] ?? $settings ?? '';

if (!$apiKey) {
    logCron($db, 'youtube_import', 'error', 'No YouTube API key configured', 0, $startTime);
    exit(1);
}

// Get active channels due for fetch
$channels = $db->query(
    "SELECT * FROM tn_youtube_channels
     WHERE is_active = 1
     AND (last_fetched_at IS NULL
          OR (fetch_interval = 'hourly' AND last_fetched_at < DATE_SUB(NOW(), INTERVAL 1 HOUR))
          OR (fetch_interval = 'daily'  AND last_fetched_at < DATE_SUB(NOW(), INTERVAL 1 DAY)))"
)->fetchAll(PDO::FETCH_ASSOC);

$imported = 0;

foreach ($channels as $ch) {
    $playlistId = $ch['playlist_id'];

    // Get uploads playlist if no playlist specified
    if (!$playlistId) {
        $channelRes = fetchYouTube("channels?part=contentDetails&id={$ch['channel_id']}&key={$apiKey}");
        $playlistId = $channelRes['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? null;
        if (!$playlistId) continue;
    }

    // Fetch latest videos
    $res    = fetchYouTube("playlistItems?part=snippet&maxResults=20&playlistId={$playlistId}&key={$apiKey}");
    $items  = $res['items'] ?? [];

    foreach ($items as $item) {
        $snippet = $item['snippet'];
        $videoId = $snippet['resourceId']['videoId'] ?? null;
        if (!$videoId) continue;

        // Duplicate check
        $exists = $db->prepare("SELECT id FROM tn_youtube_imports WHERE video_id = ?");
        $exists->execute([$videoId]);
        if ($exists->fetchColumn()) continue;

        $title       = $snippet['title'] ?? '';
        $description = $snippet['description'] ?? '';
        $thumbnail   = $snippet['thumbnails']['high']['url'] ?? $snippet['thumbnails']['default']['url'] ?? null;
        $publishedAt = $snippet['publishedAt'] ?? null;

        // Determine category
        $categoryId = $ch['category_id'];
        $keywords   = $db->prepare("SELECT * FROM tn_youtube_keyword_map WHERE channel_id = ?");
        $keywords->execute([$ch['id']]);
        foreach ($keywords->fetchAll(PDO::FETCH_ASSOC) as $kw) {
            if (stripos($title, $kw['keyword']) !== false || stripos($description, $kw['keyword']) !== false) {
                $categoryId = $kw['category_id'];
                break;
            }
        }

        // Insert import record
        $stmt = $db->prepare(
            "INSERT INTO tn_youtube_imports
             (channel_id, video_id, title, description, thumbnail, published_at, status)
             VALUES (?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $ch['id'], $videoId, $title, $description, $thumbnail,
            $publishedAt ? date('Y-m-d H:i:s', strtotime($publishedAt)) : null,
            'pending',
        ]);
        $importId = $db->lastInsertId();

        // Auto-publish or save as draft
        if ($ch['auto_publish']) {
            $slug = generateSlug($title);
            $slug = makeUniqueSlug($db, $slug);

            $articleStmt = $db->prepare(
                "INSERT INTO tn_articles
                 (user_id, category_id, title, slug, content, content_type, youtube_url,
                  youtube_video_id, status, is_auto_imported, import_source, published_at, created_at, updated_at)
                 VALUES (1,?,?,?,'',  'video', ?,?,  'published',1,'youtube', NOW(), NOW(), NOW())"
            );
            $articleStmt->execute([
                $categoryId, $title, $slug,
                "https://www.youtube.com/watch?v={$videoId}", $videoId
            ]);
            $articleId = $db->lastInsertId();

            $db->prepare("UPDATE tn_youtube_imports SET status='imported', article_id=?, imported_at=NOW() WHERE id=?")
               ->execute([$articleId, $importId]);
        }

        $imported++;
    }

    // Update last fetched
    $db->prepare("UPDATE tn_youtube_channels SET last_fetched_at = NOW() WHERE id = ?")
       ->execute([$ch['id']]);
}

logCron($db, 'youtube_import', 'success', "Fetched {$imported} new videos", $imported, $startTime);
echo "Done. Imported: {$imported}\n";

// ── HELPERS ──────────────────────────────────────

function fetchYouTube(string $endpoint): array {
    $url = "https://www.googleapis.com/youtube/v3/{$endpoint}";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}

function generateSlug(string $text): string {
    $text = mb_strtolower(trim($text));
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: substr(md5(uniqid()), 0, 8);
}

function makeUniqueSlug(\PDO $db, string $slug): string {
    $base = $slug; $i = 1;
    while ($db->prepare("SELECT id FROM tn_articles WHERE slug = ?")->execute([$slug]) &&
           $db->query("SELECT COUNT(*) FROM tn_articles WHERE slug = '{$slug}'")->fetchColumn() > 0) {
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

function logCron(\PDO $db, string $job, string $status, string $message, int $records, float $start): void {
    $ms = (int)((microtime(true) - $start) * 1000);
    $db->prepare("INSERT INTO tn_cron_logs (job, status, message, records, duration_ms) VALUES (?,?,?,?,?)")
       ->execute([$job, $status, $message, $records, $ms]);
}
