<?php
namespace App\Models;

use App\Core\Model;

class PremiumModel extends Model
{
    protected string $table = 'tn_premium_plans';

    public function activePlans(): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_premium_plans WHERE is_active = 1 ORDER BY price_inr ASC"
        );
    }

    public function hasAccess(int $readerId): bool
    {
        $count = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_premium_access
             WHERE reader_id = ? AND status = 'active' AND expires_at > NOW()",
            [$readerId]
        );
        return $count > 0;
    }

    public function grantAccess(int $readerId, int $planId, string $paymentRef = ''): int
    {
        $plan = $this->find($planId);
        if (!$plan) return 0;
        $starts  = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', strtotime("+{$plan['duration_days']} days"));
        $this->db->prepare(
            "INSERT INTO tn_premium_access (reader_id, plan_id, starts_at, expires_at, status, payment_ref)
             VALUES (?,?,?,?,'active',?)"
        )->execute([$readerId, $planId, $starts, $expires, $paymentRef]);
        return (int)$this->db->lastInsertId();
    }

    public function toggleArticlePremium(int $articleId, int $userId): bool
    {
        $current = (int)$this->fetchColumn(
            "SELECT is_premium FROM tn_articles WHERE id = ?", [$articleId]
        );
        $new = $current ? 0 : 1;
        $this->db->prepare(
            "UPDATE tn_articles SET is_premium = ?, premium_set_by = ? WHERE id = ?"
        )->execute([$new, $userId, $articleId]);
        return (bool)$new;
    }

    public function premiumArticles(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $data   = $this->fetchAll(
            "SELECT a.id, a.title, a.slug, a.status, a.published_at, a.view_count,
                    c.name AS category_name, u.name AS set_by_name
             FROM tn_articles a
             LEFT JOIN tn_categories c ON c.id = a.category_id
             LEFT JOIN tn_users u ON u.id = a.premium_set_by
             WHERE a.is_premium = 1
             ORDER BY a.published_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        $total = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_articles WHERE is_premium = 1"
        );
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function stats(): array
    {
        return [
            'total_premium_articles' => (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_articles WHERE is_premium = 1"),
            'total_subscribers'      => (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_premium_access WHERE status = 'active' AND expires_at > NOW()"),
            'total_plans'            => (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_premium_plans WHERE is_active = 1"),
        ];
    }
}
