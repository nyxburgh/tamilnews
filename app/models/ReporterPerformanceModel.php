<?php
namespace App\Models;
use App\Core\Model;

class ReporterPerformanceModel extends Model
{
    protected string $table = 'tn_reporter_performance';

    public function forUser(int $userId, int $months = 6): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_reporter_performance
             WHERE user_id=? ORDER BY month DESC LIMIT ?",
            [$userId, $months]
        );
    }

    public function leaderboard(string $month = ''): array
    {
        if (!$month) $month = date('Y-m-01');
        return $this->fetchAll(
            "SELECT rp.*, u.name, u.avatar, r.name AS role_name
             FROM tn_reporter_performance rp
             JOIN tn_users u ON u.id = rp.user_id
             JOIN tn_roles r ON r.id = u.role_id
             WHERE rp.month = ?
             ORDER BY rp.articles_published DESC, rp.total_views DESC
             LIMIT 20",
            [$month]
        );
    }

    public function recalculate(int $userId, string $month = ''): void
    {
        if (!$month) $month = date('Y-m-01');
        $monthEnd = date('Y-m-t', strtotime($month));

        $submitted = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_articles WHERE user_id=?
             AND DATE(created_at) BETWEEN ? AND ?",
            [$userId, $month, $monthEnd]
        );
        $published = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_articles WHERE user_id=?
             AND status='published' AND DATE(published_at) BETWEEN ? AND ?",
            [$userId, $month, $monthEnd]
        );
        $rejected = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_articles WHERE user_id=?
             AND status='rejected' AND DATE(updated_at) BETWEEN ? AND ?",
            [$userId, $month, $monthEnd]
        );
        $views = (int)$this->fetchColumn(
            "SELECT COALESCE(SUM(ad.views),0) FROM tn_analytics_daily ad
             JOIN tn_articles a ON a.id = ad.article_id
             WHERE a.user_id=? AND ad.date BETWEEN ? AND ?",
            [$userId, $month, $monthEnd]
        );

        $this->db->prepare(
            "INSERT INTO tn_reporter_performance
             (user_id, month, articles_submitted, articles_published, articles_rejected, total_views)
             VALUES (?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
             articles_submitted=VALUES(articles_submitted),
             articles_published=VALUES(articles_published),
             articles_rejected=VALUES(articles_rejected),
             total_views=VALUES(total_views)"
        )->execute([$userId, $month, $submitted, $published, $rejected, $views]);
    }
}
