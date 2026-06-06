<?php
namespace App\Controllers\Frontend;
use App\Core\{Controller, Session};

class LangController extends Controller
{
    public function switch(string $lang): void
    {
        $allowed = ['ta', 'en', 'hi'];
        if (!in_array($lang, $allowed)) $lang = 'ta';

        // Store in session (reliable across all setups)
        Session::set('site_lang', $lang);

        // Also try cookie with correct path
        $path = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/') . '/';
        setcookie('site_lang', $lang, [
            'expires'  => time() + 31536000,
            'path'     => $path ?: '/',
            'secure'   => false,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);

        $return = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/public/');
        header('Location: ' . $return);
        exit;
    }
}
