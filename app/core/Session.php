<?php
namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $cfg = require CONFIG_PATH . '/app.php';
            $s   = $cfg['session'];
            session_name($s['name']);
            session_set_cookie_params([
                'lifetime' => $s['lifetime'],
                'path'     => $s['path'],
                'secure'   => $s['secure'],
                'httponly' => $s['httponly'],
                'samesite' => $s['samesite'],
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void   { $_SESSION[$key] = $value; }
    public static function get(string $key, mixed $default = null): mixed { return $_SESSION[$key] ?? $default; }
    public static function has(string $key): bool                 { return isset($_SESSION[$key]); }
    public static function delete(string $key): void              { unset($_SESSION[$key]); }
    public static function destroy(): void                        { session_destroy(); }

    public static function flash(string $key, mixed $value): void { $_SESSION['_flash'][$key] = $value; }
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $val = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}
