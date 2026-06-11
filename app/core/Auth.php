<?php
namespace App\Core;

class Auth
{
    const EDITORIAL_ROLES = ['chief_editor','editor','district_editor','category_editor','reporter','senior_reporter'];
    const EDITOR_ROLES    = ['chief_editor','editor','district_editor','category_editor'];

    public static function check(): bool   { return Session::has('user_id'); }
    public static function user(): ?array  { return self::check() ? Session::get('user') : null; }
    public static function id(): ?int      { return Session::get('user_id'); }
    public static function role(): ?string { return self::user()['role_slug'] ?? null; }

    public static function isAdmin(): bool       { return self::role() === 'admin'; }
    public static function isChiefEditor(): bool { return in_array(self::role(), ['admin','chief_editor']); }
    public static function isAnyEditor(): bool   { return in_array(self::role(), self::EDITOR_ROLES); }
    public static function isReporter(): bool    { return self::check(); }

    /**
     * Permission check — uses DB tn_role_permissions if available,
     * falls back to inline matrix for compatibility.
     */
    public static function can(string $permission): bool
    {
        $role = self::role();
        if (!$role) return false;

        // Try DB-backed permissions (cached per request)
        static $dbPerms = null;
        if ($dbPerms === null) {
            try {
                $db  = Database::getInstance();
                $uid = self::id();
                if ($uid) {
                    $stmt = $db->prepare(
                        "SELECT p.slug FROM tn_permissions p
                         JOIN tn_role_permissions rp ON rp.permission_id = p.id
                         JOIN tn_users u ON u.role_id = rp.role_id
                         WHERE u.id = ?"
                    );
                    $stmt->execute([$uid]);
                    $rows    = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                    $dbPerms = array_flip($rows);
                }
            } catch (\Exception $e) {
                $dbPerms = [];
            }
        }

        if (!empty($dbPerms) && isset($dbPerms[$permission])) return true;

        // Fallback inline matrix (covers all old calls)
        $matrix = [
            'manage_users'          => ['admin'],
            'manage_settings'       => ['admin'],
            'manage_youtube'        => ['admin'],
            'manage_rss'            => ['admin'],
            'manage_ads'            => ['admin','chief_editor'],
            'manage_packages'       => ['admin','chief_editor'],
            'manage_widgets'        => ['admin','chief_editor'],
            'manage_payments'       => ['admin','chief_editor'],
            'manage_rates'          => ['admin','chief_editor'],
            'manage_categories'     => ['admin','chief_editor'],
            'manage_tags'           => ['admin','chief_editor'],
            'manage_locations'      => ['admin','chief_editor'],
            'manage_contributors'   => ['admin','chief_editor'],
            'manage_live_blog'      => ['admin','chief_editor','editor'],
            'manage_premium'        => ['admin','chief_editor'],
            'manage_special_cats'   => ['admin','chief_editor'],
            'manage_polls'          => ['admin','chief_editor','editor'],
            'send_push'             => ['admin','chief_editor'],
            'view_analytics'        => ['admin','chief_editor','editor','district_editor'],
            'manage_media'          => ['admin','chief_editor','editor','district_editor','category_editor','reporter','senior_reporter','ads_manager'],
            'edit_all_articles'     => ['admin','chief_editor','editor','district_editor','category_editor'],
            'approve_articles'      => ['admin','chief_editor','editor','district_editor','category_editor'],
            'publish_articles'      => ['admin','chief_editor','editor','district_editor','category_editor'],
            'create_articles'       => ['admin','chief_editor','editor','district_editor','category_editor','reporter','senior_reporter'],
            'view_own_articles'     => ['admin','chief_editor','editor','district_editor','category_editor','reporter','senior_reporter'],
            'assign_reporters'      => ['admin','chief_editor'],
            'set_auto_approve'      => ['admin','chief_editor'],
            'approve_escalated'     => ['admin','chief_editor'],
            'escalate_articles'     => ['admin','editor','district_editor','category_editor'],
            'create_ad'             => ['admin','chief_editor','editor','district_editor','category_editor','reporter','senior_reporter','ads_manager'],
            'manage_own_ads'        => ['admin','chief_editor','editor','district_editor','category_editor','reporter','senior_reporter','ads_manager'],
            'approve_ad'            => ['admin','chief_editor'],
            'confirm_ad_payment'    => ['admin','chief_editor'],
            'promote_user'          => ['admin'],
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

    /** Reload user session from DB (after role change etc.) */
    public static function refresh(): void
    {
        $id = self::id();
        if (!$id) return;
        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare(
                "SELECT u.*, r.slug AS role_slug, r.name AS role_name
                 FROM tn_users u JOIN tn_roles r ON r.id = u.role_id
                 WHERE u.id = ? LIMIT 1"
            );
            $stmt->execute([$id]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user) Session::set('user', $user);
        } catch (\Exception $e) {}
    }
}
