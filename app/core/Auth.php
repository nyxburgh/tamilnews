<?php
namespace App\Core;

class Auth
{
    // Roles that are editorial (non-admin) but have portal access
    const EDITORIAL_ROLES = ['chief_editor','editor','district_editor','category_editor','reporter'];
    const EDITOR_ROLES    = ['chief_editor','editor','district_editor','category_editor'];

    public static function check(): bool    { return Session::has('user_id'); }
    public static function user(): ?array   { return self::check() ? Session::get('user') : null; }
    public static function id(): ?int       { return Session::get('user_id'); }
    public static function role(): ?string  { return self::user()['role_slug'] ?? null; }

    public static function isAdmin(): bool        { return self::role() === 'admin'; }
    public static function isChiefEditor(): bool  { return in_array(self::role(), ['admin','chief_editor']); }
    public static function isAnyEditor(): bool    { return in_array(self::role(), self::EDITOR_ROLES); }
    public static function isReporter(): bool     { return self::check(); }

    /**
     * Permission matrix.
     * Chief editor = full editorial, no system/user management.
     */
    public static function can(string $permission): bool
    {
        $role = self::role();

        $matrix = [
            // ── ADMIN ONLY ────────────────────────────────────
            'manage_users'          => ['admin'],
            'manage_settings'       => ['admin'],
            'manage_youtube'        => ['admin'],
            'manage_rss'            => ['admin'],
            'manage_ads'            => ['admin'],
            'manage_payments'       => ['admin'],

            // ── CHIEF EDITOR + ADMIN ──────────────────────────
            'publish_articles'      => ['admin','chief_editor','editor','district_editor','category_editor'],
            'manage_categories'     => ['admin','chief_editor'],
            'manage_tags'           => ['admin','chief_editor'],
            'manage_locations'      => ['admin','chief_editor'],
            'manage_contributors'   => ['admin','chief_editor'],
            'manage_live_blog'      => ['admin','chief_editor'],
            'manage_premium'        => ['admin','chief_editor'],
            'manage_special_cats'   => ['admin','chief_editor'],
            'send_push'             => ['admin','chief_editor'],
            'view_analytics'        => ['admin','chief_editor'],
            'manage_media'          => ['admin','chief_editor','editor','district_editor','category_editor','reporter'],

            // ── ALL EDITORS ───────────────────────────────────
            'edit_all_articles'     => ['admin','chief_editor','editor','district_editor','category_editor'],
            'approve_articles'      => ['admin','chief_editor','editor','district_editor','category_editor'],
            'publish_district'      => ['admin','chief_editor','editor','district_editor','category_editor'],

            // ── ALL AUTHENTICATED ─────────────────────────────
            'create_articles'       => ['admin','chief_editor','editor','district_editor','category_editor','reporter'],
            'view_own_articles'     => ['admin','chief_editor','editor','district_editor','category_editor','reporter'],

            // ── CHIEF EDITOR SPECIAL ──────────────────────────
            'assign_reporters'      => ['admin','chief_editor'],
            'set_auto_approve'      => ['admin','chief_editor'],
            'approve_escalated'     => ['admin','chief_editor'],
            'escalate_articles'     => ['admin','editor','district_editor','category_editor'],
        ];

        return in_array($role, $matrix[$permission] ?? []);
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        Session::set('user_id', $user['id']);
        Session::set('user',    $user);
    }

    public static function logout(): void
    {
        Session::delete('user_id');
        Session::delete('user');
        Session::destroy();
    }
}
