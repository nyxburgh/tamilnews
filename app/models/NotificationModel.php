<?php
namespace App\Models;

use App\Core\Model;

class NotificationModel extends Model
{
    protected string $table = 'tn_notifications';

    // ── Send notification ─────────────────────────────────────

    public function send(int $toUserId, string $type, string $message, ?int $articleId = null, ?int $fromId = null): void
    {
        try {
            $this->db->prepare(
                "INSERT INTO tn_notifications (user_id, from_id, type, article_id, message)
                 VALUES (?,?,?,?,?)"
            )->execute([$toUserId, $fromId, $type, $articleId, $message]);
        } catch (\Exception $e) {
            // Fail silently — notifications are non-critical
        }
    }

    public function sendToRole(string $roleSlug, string $type, string $message, ?int $articleId = null, ?int $fromId = null): void
    {
        try {
            $users = $this->fetchAll(
                "SELECT u.id FROM tn_users u JOIN tn_roles r ON r.id = u.role_id
                 WHERE r.slug = ? AND u.is_active = 1 AND u.is_blocked = 0",
                [$roleSlug]
            );
            foreach ($users as $u) {
                $this->send($u['id'], $type, $message, $articleId, $fromId);
            }
        } catch (\Exception $e) {}
    }

    // ── Chief editors/admins ──────────────────────────────────

    public function notifyChiefEditors(string $type, string $message, ?int $articleId = null, ?int $fromId = null): void
    {
        try {
            $users = $this->fetchAll(
                "SELECT u.id FROM tn_users u
                 JOIN tn_roles r ON r.id = u.role_id
                 WHERE r.slug IN ('admin','chief_editor')
                 AND u.is_active = 1 AND u.is_blocked = 0"
            );
            foreach ($users as $u) {
                $this->send($u['id'], $type, $message, $articleId, $fromId);
            }
        } catch (\Exception $e) {}
    }

    // ── Retrieve for user ─────────────────────────────────────

    public function forUser(int $userId, int $limit = 20): array
    {
        try {
            return $this->fetchAll(
                "SELECT n.*, a.title AS article_title, a.slug AS article_slug,
                        u.name AS from_name
                 FROM tn_notifications n
                 LEFT JOIN tn_articles a ON a.id = n.article_id
                 LEFT JOIN tn_users u    ON u.id = n.from_id
                 WHERE n.user_id = ?
                 ORDER BY n.created_at DESC LIMIT ?",
                [$userId, $limit]
            );
        } catch (\Exception $e) { return []; }
    }

    public function unreadCount(int $userId): int
    {
        try {
            return (int)$this->fetchColumn(
                "SELECT COUNT(*) FROM tn_notifications WHERE user_id = ? AND is_read = 0",
                [$userId]
            );
        } catch (\Exception $e) { return 0; }
    }

    public function markRead(int $userId, ?int $id = null): void
    {
        try {
            if ($id) {
                $this->query("UPDATE tn_notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$id, $userId]);
            } else {
                $this->query("UPDATE tn_notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
            }
        } catch (\Exception $e) {}
    }
}
