<?php
/**
 * Scheduled Publishing Cron
 * Schedule: */5 * * * * php /path/to/cron/scheduled_publish.php
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

// Fetch articles due for publishing
$due = $db->query(
    "SELECT id, title FROM tn_articles
     WHERE status = 'scheduled'
     AND scheduled_at IS NOT NULL
     AND scheduled_at <= NOW()"
)->fetchAll(PDO::FETCH_ASSOC);

$published = 0;

foreach ($due as $article) {
    $db->prepare(
        "UPDATE tn_articles
         SET status = 'published',
             published_at = scheduled_at,
             updated_at = NOW()
         WHERE id = ?"
    )->execute([$article['id']]);

    // Log activity
    $db->prepare(
        "INSERT INTO tn_activity_log (action, entity, entity_id, description)
         VALUES ('auto_publish', 'article', ?, ?)"
    )->execute([$article['id'], "Auto-published: {$article['title']}"]);

    $published++;
}

// Also expire breaking news past expiry time
$db->query(
    "UPDATE tn_articles
     SET is_breaking = 0, breaking_expires_at = NULL
     WHERE is_breaking = 1
     AND breaking_expires_at IS NOT NULL
     AND breaking_expires_at < NOW()"
);

logCron($db, 'scheduled_publish', 'success', "Published {$published} scheduled articles", $published, $startTime);
echo "Done. Published: {$published}\n";

function logCron(\PDO $db, string $job, string $status, string $message, int $records, float $start): void {
    $ms = (int)((microtime(true) - $start) * 1000);
    $db->prepare("INSERT INTO tn_cron_logs (job, status, message, records, duration_ms) VALUES (?,?,?,?,?)")
       ->execute([$job, $status, $message, $records, $ms]);
}
