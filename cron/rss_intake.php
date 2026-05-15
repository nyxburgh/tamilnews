<?php
/**
 * RSS Intake Cron
 * Schedule: */30 * * * * php /path/to/cron/rss_intake.php
 */

define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH',   APP_PATH  . '/views');
define('STORAGE_PATH',ROOT_PATH . '/storage');
define('APP_DEBUG',   false);

date_default_timezone_set('Asia/Kolkata');

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

// Feeds due for fetch
$feeds = $db->query(
    "SELECT * FROM tn_rss_feeds
     WHERE is_active = 1
     AND (last_fetched_at IS NULL
          OR last_fetched_at < DATE_SUB(NOW(), INTERVAL fetch_interval MINUTE))"
)->fetchAll(PDO::FETCH_ASSOC);

$fetched = 0;

foreach ($feeds as $feed) {
    $items = fetchRss($feed['url']);
    if ($items === null) continue;

    foreach ($items as $item) {
        $title     = trim(strip_tags($item['title'] ?? ''));
        $sourceUrl = trim($item['link'] ?? $item['guid'] ?? '');
        if (!$title || !$sourceUrl) continue;

        $hash = hash('sha256', $sourceUrl);

        // Duplicate check
        $exists = $db->prepare("SELECT id FROM tn_rss_imports WHERE source_hash = ?");
        $exists->execute([$hash]);
        if ($exists->fetchColumn()) continue;

        // Extract content
        $content   = $item['description'] ?? $item['content'] ?? '';
        $pubDate   = isset($item['pubDate']) ? date('Y-m-d H:i:s', strtotime($item['pubDate'])) : null;

        // Insert as pending
        $stmt = $db->prepare(
            "INSERT INTO tn_rss_imports (feed_id, title, source_url, source_hash, status, fetched_at)
             VALUES (?, ?, ?, ?, 'pending', NOW())"
        );
        $stmt->execute([$feed['id'], $title, $sourceUrl, $hash]);
        $importId = $db->lastInsertId();

        // Also create draft article
        $slug = makeUniqueSlug($db, generateSlug($title));
        $articleStmt = $db->prepare(
            "INSERT INTO tn_articles
             (user_id, category_id, title, slug, excerpt, content, content_type,
              status, is_auto_imported, import_source, source_url, source_hash, created_at, updated_at)
             VALUES (1, ?, ?, ?, ?, ?, 'news', 'draft', 1, 'rss', ?, ?, NOW(), NOW())"
        );
        $excerpt = mb_substr(strip_tags($content), 0, 200);
        $articleStmt->execute([
            $feed['category_id'], $title, $slug, $excerpt,
            $content, $sourceUrl, $hash,
        ]);
        $articleId = $db->lastInsertId();

        // Link import to article
        $db->prepare("UPDATE tn_rss_imports SET article_id = ? WHERE id = ?")
           ->execute([$articleId, $importId]);

        $fetched++;
    }

    $db->prepare("UPDATE tn_rss_feeds SET last_fetched_at = NOW() WHERE id = ?")
       ->execute([$feed['id']]);
}

logCron($db, 'rss_intake', 'success', "Fetched {$fetched} new RSS items", $fetched, $startTime);
echo "Done. Fetched: {$fetched}\n";

// ── HELPERS ──────────────────────────────────────

function fetchRss(string $url): ?array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'TamilNewsPortal/1.0 RSS Reader',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $xml = curl_exec($ch);
    $err = curl_errno($ch);
    curl_close($ch);

    if ($err || !$xml) return null;

    libxml_use_internal_errors(true);
    $feed = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    if (!$feed) return null;

    $items  = [];
    $isAtom = isset($feed->entry);

    if ($isAtom) {
        foreach ($feed->entry as $entry) {
            $link = '';
            foreach ($entry->link as $l) {
                if ((string)$l['rel'] === 'alternate' || !$l['rel']) {
                    $link = (string)$l['href'];
                    break;
                }
            }
            $items[] = [
                'title'       => (string)$entry->title,
                'link'        => $link,
                'description' => (string)($entry->summary ?? $entry->content ?? ''),
                'pubDate'     => (string)($entry->published ?? $entry->updated ?? ''),
            ];
        }
    } else {
        foreach ($feed->channel->item ?? [] as $item) {
            $items[] = [
                'title'       => (string)$item->title,
                'link'        => (string)$item->link,
                'guid'        => (string)$item->guid,
                'description' => (string)$item->description,
                'pubDate'     => (string)$item->pubDate,
            ];
        }
    }

    return $items;
}

function generateSlug(string $text): string {
    $text = mb_strtolower(trim($text));
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: substr(md5(uniqid()), 0, 8);
}

function makeUniqueSlug(\PDO $db, string $slug): string {
    $base = $slug; $i = 1;
    do {
        $count = (int)$db->prepare("SELECT COUNT(*) FROM tn_articles WHERE slug = ?")->execute([$slug]) ?
                 $db->query("SELECT COUNT(*) FROM tn_articles WHERE slug = '" . addslashes($slug) . "'")->fetchColumn() : 1;
        if ($count === 0) break;
        $slug = $base . '-' . $i++;
    } while (true);
    return $slug;
}

function logCron(\PDO $db, string $job, string $status, string $message, int $records, float $start): void {
    $ms = (int)((microtime(true) - $start) * 1000);
    $db->prepare("INSERT INTO tn_cron_logs (job, status, message, records, duration_ms) VALUES (?,?,?,?,?)")
       ->execute([$job, $status, $message, $records, $ms]);
}
