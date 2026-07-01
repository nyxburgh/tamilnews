<?php
namespace App\Core;

class CSRF
{
    private const KEY = '_csrf_token';

    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set(self::KEY, $token);
        return $token;
    }

    public static function token(): string
    {
        if (!Session::has(self::KEY)) {
            return self::generate();
        }
        return Session::get(self::KEY);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . self::token() . '">';
    }

    public static function verify(string $token): bool
    {
        $stored = Session::get(self::KEY, '');
        return hash_equals($stored, $token);
    }

    public static function validate(): void
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!self::verify($token)) {
            http_response_code(419);
            die('CSRF token mismatch.');
        }
    }
}
