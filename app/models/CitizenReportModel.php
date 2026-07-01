<?php
namespace App\Models;

use App\Core\Model;

class CitizenReportModel extends Model
{
    protected string $table = 'tn_citizen_reports';

    public function submit(array $data): int
    {
        $cols   = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($data)));
        $places = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO tn_citizen_reports ({$cols}) VALUES ({$places})", array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function pending(): array
    {
        return $this->fetchAll(
            "SELECT r.*, d.name AS district_name
             FROM tn_citizen_reports r
             LEFT JOIN tn_districts d ON d.id = r.district_id
             WHERE r.status = 'pending'
             ORDER BY r.created_at DESC"
        );
    }

    public function find(int $id): array|false
    {
        return $this->fetchOne(
            "SELECT r.*, d.name AS district_name
             FROM tn_citizen_reports r
             LEFT JOIN tn_districts d ON d.id = r.district_id
             WHERE r.id = ?",
            [$id]
        );
    }

    public function approve(int $id, int $reviewerId, int $articleId): void
    {
        $set    = "`status`='approved', `reviewed_by`=?, `article_id`=?";
        $this->query("UPDATE tn_citizen_reports SET {$set} WHERE id=?",
                     [$reviewerId, $articleId, $id]);
    }

    public function reject(int $id, int $reviewerId, string $reason): void
    {
        $this->query(
            "UPDATE tn_citizen_reports SET status='rejected', reviewed_by=?, rejection_reason=? WHERE id=?",
            [$reviewerId, $reason, $id]
        );
    }

    public function all(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $data   = $this->fetchAll(
            "SELECT r.*, d.name AS district_name
             FROM tn_citizen_reports r
             LEFT JOIN tn_districts d ON d.id = r.district_id
             ORDER BY r.created_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        $total = (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_citizen_reports");
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function pendingCount(): int
    {
        return (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_citizen_reports WHERE status='pending'"
        );
    }
}
