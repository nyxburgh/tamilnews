<?php
namespace App\Models;
use App\Core\Model;

class PushModel extends Model
{
    protected string $table = 'tn_push_notifications';
    public function allTopics(): array { return $this->fetchAll("SELECT * FROM tn_fcm_topics ORDER BY id"); }
    public function history(int $limit = 30): array
    {
        return $this->fetchAll(
            "SELECT p.*, t.name AS topic_name, u.name AS sender_name
             FROM tn_push_notifications p
             LEFT JOIN tn_fcm_topics t ON t.id = p.topic_id
             LEFT JOIN tn_users u ON u.id = p.user_id
             ORDER BY p.created_at DESC LIMIT ?", [$limit]
        );
    }
    public function store(array $data): int { return $this->insert($data); }
    public function markSent(int $id): void { $this->query("UPDATE tn_push_notifications SET status='sent', sent_at=NOW() WHERE id=?", [$id]); }
    public function markFailed(int $id): void { $this->query("UPDATE tn_push_notifications SET status='failed' WHERE id=?", [$id]); }
}
