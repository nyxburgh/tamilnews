<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Helper;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            Helper::redirect('/admin/login');
        }
    }
}
