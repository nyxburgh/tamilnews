<?php
namespace App\Models;
use App\Core\Model;

class PollModel extends Model
{
    protected string $table = 'tn_polls';

    public function active(): array
    {
        return $this->fetchAll(
            "SELECT p.*, GROUP_CONCAT(po.id ORDER BY po.sort_order SEPARATOR '|') AS option_ids,
                    GROUP_CONCAT(po.option_text ORDER BY po.sort_order SEPARATOR '|') AS option_texts,
                    GROUP_CONCAT(po.option_text_ta ORDER BY po.sort_order SEPARATOR '|') AS option_texts_ta,
                    GROUP_CONCAT(po.vote_count ORDER BY po.sort_order SEPARATOR '|') AS vote_counts
             FROM tn_polls p
             LEFT JOIN tn_poll_options po ON po.poll_id = p.id
             WHERE p.is_active=1 AND (p.expires_at IS NULL OR p.expires_at > NOW())
             GROUP BY p.id
             ORDER BY p.created_at DESC LIMIT 10"
        );
    }

    public function findWithOptions(int $id): array|false
    {
        $poll = $this->fetchOne("SELECT * FROM tn_polls WHERE id=?", [$id]);
        if (!$poll) return false;
        $poll['options'] = $this->fetchAll(
            "SELECT * FROM tn_poll_options WHERE poll_id=? ORDER BY sort_order", [$id]
        );
        return $poll;
    }

    public function allForAdmin(int $page = 1, int $per = 15): array
    {
        $offset = ($page-1)*$per;
        $data = $this->fetchAll(
            "SELECT p.*, u.name AS created_by_name,
                    COUNT(pv.id) AS vote_count
             FROM tn_polls p
             LEFT JOIN tn_users u ON u.id = p.created_by
             LEFT JOIN tn_poll_votes pv ON pv.poll_id = p.id
             GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?",
            [$per, $offset]
        );
        $total = (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_polls");
        return ['data'=>$data,'total'=>$total,'page'=>$page,'per_page'=>$per];
    }

    public function createWithOptions(array $poll, array $options): int
    {
        $id = $this->insert($poll);
        foreach ($options as $i => $opt) {
            if (empty(trim($opt['text']))) continue;
            $this->db->prepare(
                "INSERT INTO tn_poll_options (poll_id, option_text, option_text_ta, sort_order)
                 VALUES (?,?,?,?)"
            )->execute([$id, $opt['text'], $opt['text_ta'] ?? '', $i]);
        }
        return $id;
    }

    public function vote(int $pollId, int $optionId, ?int $readerId, string $ipHash): bool
    {
        // Prevent duplicate
        $existing = $this->fetchOne(
            "SELECT id FROM tn_poll_votes WHERE poll_id=? AND (reader_id=? OR ip_hash=?)",
            [$pollId, $readerId, $ipHash]
        );
        if ($existing) return false;

        $this->db->prepare(
            "INSERT INTO tn_poll_votes (poll_id, option_id, reader_id, ip_hash) VALUES (?,?,?,?)"
        )->execute([$pollId, $optionId, $readerId, $ipHash]);

        $this->query(
            "UPDATE tn_poll_options SET vote_count = vote_count + 1 WHERE id=?", [$optionId]
        );
        $this->query(
            "UPDATE tn_polls SET total_votes = total_votes + 1 WHERE id=?", [$pollId]
        );
        return true;
    }

    public function hasVoted(int $pollId, ?int $readerId, string $ipHash): bool
    {
        return (bool)$this->fetchOne(
            "SELECT id FROM tn_poll_votes WHERE poll_id=? AND (reader_id=? OR ip_hash=?)",
            [$pollId, $readerId, $ipHash]
        );
    }

    public function toggle(int $id): void
    {
        $this->query("UPDATE tn_polls SET is_active = 1 - is_active WHERE id=?", [$id]);
    }
}
