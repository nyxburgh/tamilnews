<?php
namespace App\Models;
use App\Core\Model;

class UserModel extends Model
{
    protected string $table = 'tn_users';

    public function findByEmail(string $email): array|false
    {
        return $this->fetchOne(
            "SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM tn_users u JOIN tn_roles r ON r.id = u.role_id
             WHERE u.email = ? AND u.is_active = 1 AND u.is_blocked = 0",
            [$email]
        );
    }

    public function findWithRole(int $id): array|false
    {
        return $this->fetchOne(
            "SELECT u.*, r.slug AS role_slug, r.name AS role_name,
                    d.name AS assigned_district_name
             FROM tn_users u
             JOIN tn_roles r ON r.id = u.role_id
             LEFT JOIN tn_districts d ON d.id = u.assigned_district_id
             WHERE u.id = ?",
            [$id]
        );
    }

    public function allWithRoles(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $data   = $this->fetchAll(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug,
                    d.name AS district_name,
                    COUNT(a.id) AS article_count
             FROM tn_users u
             JOIN tn_roles r ON r.id = u.role_id
             LEFT JOIN tn_districts d ON d.id = u.assigned_district_id
             LEFT JOIN tn_articles a ON a.user_id = u.id
             GROUP BY u.id
             ORDER BY r.id ASC, u.id DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        return ['data' => $data, 'total' => $this->count(), 'page' => $page, 'per_page' => $perPage];
    }

    public function getRoles(): array
    {
        return $this->fetchAll("SELECT * FROM tn_roles ORDER BY id");
    }

    public function getBadges(): array
    {
        try {
            return $this->fetchAll("SELECT * FROM tn_user_badges ORDER BY id");
        } catch (\Exception $e) { return []; }
    }

    public function userBadges(int $userId): array
    {
        try {
            return $this->fetchAll(
                "SELECT ub.* FROM tn_user_badges ub
                 JOIN tn_user_badge_assignments uba ON uba.badge_id = ub.id
                 WHERE uba.user_id = ?", [$userId]
            );
        } catch (\Exception $e) { return []; }
    }

    public function assignBadge(int $userId, int $badgeId): void
    {
        $this->db->prepare("INSERT IGNORE INTO tn_user_badge_assignments (user_id, badge_id) VALUES (?,?)")
            ->execute([$userId, $badgeId]);
    }

    public function removeBadge(int $userId, int $badgeId): void
    {
        $this->db->prepare("DELETE FROM tn_user_badge_assignments WHERE user_id=? AND badge_id=?")
            ->execute([$userId, $badgeId]);
    }

    // ── EDITOR PERMISSIONS ────────────────────────────────────

    public function getPermissions(int $userId): array
    {
        try {
            return $this->fetchAll(
                "SELECT ep.*, d.name AS district_name, c.name AS category_name
                 FROM tn_editor_permissions ep
                 LEFT JOIN tn_districts d ON d.id = ep.ref_id AND ep.perm_type = 'district'
                 LEFT JOIN tn_categories c ON c.id = ep.ref_id AND ep.perm_type = 'category'
                 WHERE ep.user_id = ?",
                [$userId]
            );
        } catch (\Exception $e) { return []; }
    }

    public function addPermission(int $userId, string $type, int $refId, bool $canPublish = false): void
    {
        try {
            $this->db->prepare(
                "INSERT INTO tn_editor_permissions (user_id, perm_type, ref_id, can_approve, can_publish)
                 VALUES (?,?,?,1,?) ON DUPLICATE KEY UPDATE can_publish=VALUES(can_publish)"
            )->execute([$userId, $type, $refId, $canPublish ? 1 : 0]);
        } catch (\Exception $e) {}
    }

    public function removePermission(int $id): void
    {
        try {
            $this->db->prepare("DELETE FROM tn_editor_permissions WHERE id=?")->execute([$id]);
        } catch (\Exception $e) {}
    }

    // Returns district_ids or category_ids this editor can approve
    public function getEditorScope(int $userId): array
    {
        try {
            $perms = $this->fetchAll(
                "SELECT * FROM tn_editor_permissions WHERE user_id = ?", [$userId]
            );
            $districtIds  = [];
            $categoryIds  = [];
            $canPublish   = false;
            foreach ($perms as $p) {
                if ($p['perm_type'] === 'district') $districtIds[] = (int)$p['ref_id'];
                if ($p['perm_type'] === 'category') $categoryIds[] = (int)$p['ref_id'];
                if ($p['can_publish']) $canPublish = true;
            }
            return compact('districtIds', 'categoryIds', 'canPublish');
        } catch (\Exception $e) {
            return ['districtIds' => [], 'categoryIds' => [], 'canPublish' => false];
        }
    }

    public function block(int $id): void   { $this->query("UPDATE tn_users SET is_blocked = 1 WHERE id = ?", [$id]); }
    public function unblock(int $id): void { $this->query("UPDATE tn_users SET is_blocked = 0 WHERE id = ?", [$id]); }
    public function updateLastLogin(int $id): void { $this->query("UPDATE tn_users SET last_login = NOW() WHERE id = ?", [$id]); }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $sql    = "SELECT COUNT(*) FROM tn_users WHERE email = ?";
        $params = [$email];
        if ($excludeId) { $sql .= ' AND id != ?'; $params[] = $excludeId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }
}
