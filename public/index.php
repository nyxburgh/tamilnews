<?php
declare(strict_types=1);

define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH',   APP_PATH  . '/views');
define('STORAGE_PATH',ROOT_PATH . '/storage');

// Resolved after .env loads — set below after env parse

// Global URL helper — available in all views
function url(string $path = ''): string {
    return rtrim(ASSET_URL ?? '', '/') . '/' . ltrim($path, '/');
}

date_default_timezone_set('Asia/Kolkata');

// ── ENV ──────────────────────────────────────────
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
    }
}

// ── DEBUG ─────────────────────────────────────────
$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
define('APP_DEBUG', $debug);

// ── BASE & ASSET URL ─────────────────────────────
$_appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost/thinathulir', '/');
define('BASE_URL',  $_appUrl);
define('ASSET_URL', $_appUrl);  // No /public — root .htaccess forwards to public/
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/php_errors.log');
}

// ── AUTOLOADER ───────────────────────────────────
// Converts App\Controllers\Frontend\HomeController
// → /app/controllers/frontend/HomeController.php
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;

    // Strip App\ prefix, get relative path
    $relative = substr($class, strlen($prefix)); // e.g. Controllers\Frontend\HomeController

    // Split into segments
    $segments = explode('\\', $relative);

    // Lowercase all directory segments, keep filename as-is
    $filename  = array_pop($segments);
    $dirParts  = array_map('strtolower', $segments);
    $dirParts[] = $filename; // filename keeps its case

    $file = APP_PATH . '/' . implode('/', $dirParts) . '.php';

    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Fallback: fully lowercase path
    $fileLower = APP_PATH . '/' . strtolower(implode('/', $segments)) . '/' . $filename . '.php';
    if (file_exists($fileLower)) {
        require_once $fileLower;
    }
});

// ── SESSION ──────────────────────────────────────
\App\Core\Session::start();

// ── ROUTE ────────────────────────────────────────
$router = new \App\Core\Router();
$router->load();
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);
