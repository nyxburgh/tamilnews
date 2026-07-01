<?php
namespace App\Models;

use App\Core\Model;

class AdPackageModel extends Model
{
    protected string $table = 'tn_ad_packages';

    // ── Package definitions ──────────────────────────────────

    public function all(string $orderBy = 'sort_order', string $dir = 'ASC'): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_ad_packages ORDER BY sort_order ASC"
        );
    }

    public function active(): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_ad_packages WHERE is_active=1 ORDER BY sort_order ASC"
        );
    }

    /** Alias used by PackageController */
    public function allPackages(): array { return $this->all(); }

    public function find(int $id): array|false
    {
        return $this->fetchOne(
            "SELECT * FROM tn_ad_packages WHERE id=?", [$id]
        );
    }

    // ── Subscriptions ────────────────────────────────────────

    public function subscribe(array $data): int
    {
        $cols   = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($data)));
        $places = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO tn_ad_subscriptions ({$cols}) VALUES ({$places})", array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function findSubscription(int $id): array|false
    {
        return $this->fetchOne(
            "SELECT s.*,
                    p.name AS package_name, p.name_tamil,
                    p.slot_type, p.allow_images, p.max_images,
                    p.image_change_days, p.allow_news, p.news_quota,
                    p.news_interval_days, p.is_trial, p.rate_per_day,
                    p.amount AS package_amount,
                    b.business_name, b.slot_id,
                    u.name  AS owner_name,  u.email AS owner_email,
                    ua.name AS assigned_by_name
             FROM tn_ad_subscriptions s
             JOIN tn_ad_packages     p  ON p.id  = s.package_id
             JOIN tn_business_ads    b  ON b.id  = s.ad_id
             LEFT JOIN tn_users      u  ON u.id  = s.owner_user_id
             LEFT JOIN tn_users      ua ON ua.id = s.assigned_by
             WHERE s.id = ?",
            [$id]
        );
    }

    public function subscriptionsByAd(int $adId): array
    {
        return $this->fetchAll(
            "SELECT s.*,
                    p.name AS package_name, p.name_tamil,
                    p.allow_news, p.news_quota, p.allow_images, p.is_trial,
                    u.name AS owner_name, u.email AS owner_email
             FROM tn_ad_subscriptions s
             JOIN tn_ad_packages p  ON p.id = s.package_id
             LEFT JOIN tn_users   u ON u.id = s.owner_user_id
             WHERE s.ad_id = ?
             ORDER BY s.created_at DESC",
            [$adId]
        );
    }

    public function subscriptionsByOwner(int $ownerUserId): array
    {
        return $this->fetchAll(
            "SELECT s.*,
                    p.name AS package_name, p.name_tamil,
                    p.allow_news, p.news_quota, p.allow_images, p.is_trial,
                    p.rate_per_day, p.slot_type,
                    b.business_name,
                    sl.type AS slot_type_label, sl.name AS slot_name,
                    (SELECT COUNT(*) FROM tn_sponsored_news sn
                     WHERE sn.subscription_id = s.id
                       AND sn.status IN ('approved','published')) AS news_published
             FROM tn_ad_subscriptions s
             JOIN tn_ad_packages     p  ON p.id  = s.package_id
             JOIN tn_business_ads    b  ON b.id  = s.ad_id
             JOIN tn_ad_slots        sl ON sl.id = b.slot_id
             WHERE s.owner_user_id = ?
             ORDER BY s.valid_until DESC",
            [$ownerUserId]
        );
    }

    public function updateSubscription(int $id, array $data): void
    {
        $set    = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $values = array_values($data);
        $values[] = $id;
        $this->query("UPDATE tn_ad_subscriptions SET {$set} WHERE id = ?", $values);
    }

    public function assignOwner(int $subscriptionId, int $userId): void
    {
        $this->updateSubscription($subscriptionId, ['owner_user_id' => $userId]);
    }

    public function canChangeImage(int $subscriptionId): bool
    {
        $sub = $this->findSubscription($subscriptionId);
        if (!$sub || !$sub['allow_images']) return false;
        if (!$sub['image_last_changed']) return true;
        $daysSince = (int)floor((time() - strtotime($sub['image_last_changed'])) / 86400);
        return $daysSince >= $sub['image_change_days'];
    }

    public function markImageChanged(int $subscriptionId): void
    {
        $this->updateSubscription($subscriptionId, ['image_last_changed' => date('Y-m-d')]);
    }

    public function canPostNews(int $subscriptionId): bool|string
    {
        $sub = $this->findSubscription($subscriptionId);
        if (!$sub || !$sub['allow_news']) return 'not_allowed';

        $isVertical = ($sub['slot_type'] === 'vertical');
        $quota      = $isVertical ? ($sub['selected_days'] ?? 0) : (int)$sub['news_quota'];
        if ((int)$sub['news_used'] >= $quota) return 'quota_exhausted';

        if ((int)$sub['news_interval_days'] > 0) {
            $last = $this->fetchColumn(
                "SELECT MAX(created_at) FROM tn_sponsored_news
                 WHERE subscription_id=? AND status NOT IN ('rejected')",
                [$subscriptionId]
            );
            if ($last) {
                $daysSince = (int)floor((time() - strtotime($last)) / 86400);
                if ($daysSince < $sub['news_interval_days']) return 'too_soon';
            }
        }
        return true;
    }

    public function incrementNewsUsed(int $subscriptionId): void
    {
        $this->db->prepare(
            "UPDATE tn_ad_subscriptions SET news_used = news_used + 1 WHERE id = ?"
        )->execute([$subscriptionId]);
    }

    public function addSponsoredNews(array $data): int
    {
        $cols   = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($data)));
        $places = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO tn_sponsored_news ({$cols}) VALUES ({$places})", array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function sponsoredNewsBySubscription(int $subscriptionId): array
    {
        return $this->fetchAll(
            "SELECT sn.*, a.title, a.slug, a.status AS article_status,
                    a.published_at, u.name AS approved_by_name
             FROM tn_sponsored_news sn
             JOIN tn_articles a       ON a.id  = sn.article_id
             LEFT JOIN tn_users u     ON u.id  = sn.approved_by
             WHERE sn.subscription_id = ?
             ORDER BY sn.created_at DESC",
            [$subscriptionId]
        );
    }

    public function updateSponsoredNews(int $id, array $data): void
    {
        $set    = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $values = array_values($data);
        $values[] = $id;
        $this->query("UPDATE tn_sponsored_news SET {$set} WHERE id = ?", $values);
    }

    public function nearingExpiry(int $days = 7): array
    {
        $col = "expiry_notified_{$days}d";
        return $this->fetchAll(
            "SELECT s.*, p.name AS package_name, b.business_name,
                    u.email AS owner_email, u.name AS owner_name
             FROM tn_ad_subscriptions s
             JOIN tn_ad_packages p  ON p.id = s.package_id
             JOIN tn_business_ads b ON b.id = s.ad_id
             LEFT JOIN tn_users   u ON u.id = s.owner_user_id
             WHERE s.status = 'active'
               AND s.valid_until BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$days} DAY)
               AND s.{$col} = 0"
        );
    }

    public function autoExpire(): int
    {
        $stmt = $this->db->prepare(
            "UPDATE tn_ad_subscriptions
             SET status = 'expired'
             WHERE status = 'active' AND valid_until < CURDATE()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    // ── Package upgrade requests ──────────────────────────────

    public function findRequest(int $id): array|false
    {
        return $this->fetchOne(
            "SELECT * FROM tn_ad_package_requests WHERE id = ?", [$id]
        );
    }

    public function requestUpgrade(array $data): int
    {
        $cols   = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($data)));
        $places = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO tn_ad_package_requests ({$cols}) VALUES ({$places})", array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function pendingUpgradeRequests(): array
    {
        return $this->fetchAll(
            "SELECT r.*,
                    b.business_name,
                    cp.name AS current_pkg_name,
                    np.name AS requested_pkg_name,
                    np.amount AS new_amount, np.slot_type AS new_slot_type,
                    u.name AS requested_by_name
             FROM tn_ad_package_requests r
             JOIN tn_business_ads b  ON b.id  = r.ad_id
             JOIN tn_ad_packages cp  ON cp.id = r.current_pkg_id
             JOIN tn_ad_packages np  ON np.id = r.requested_pkg_id
             JOIN tn_users u         ON u.id  = r.requested_by
             WHERE r.status = 'pending'
             ORDER BY r.created_at DESC"
        );
    }

    public function updateRequest(int $id, array $data): void
    {
        $set    = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $values = array_values($data);
        $values[] = $id;
        $this->query("UPDATE tn_ad_package_requests SET {$set} WHERE id = ?", $values);
    }

    // ── Sponsored news — ad_id based (no subscription required) ──

    public function sponsoredNewsByAd(int $adId): array
    {
        return $this->fetchAll(
            "SELECT sn.*, a.title, a.slug, a.status AS article_status,
                    a.published_at, a.image_url, u.name AS approved_by_name
             FROM tn_sponsored_news sn
             JOIN tn_articles a      ON a.id = sn.article_id
             LEFT JOIN tn_users u    ON u.id = sn.approved_by
             WHERE sn.ad_id = ?
             ORDER BY sn.created_at DESC",
            [$adId]
        );
    }

    public function allPendingSponsoredNews(): array
    {
        return $this->fetchAll(
            "SELECT sn.*, a.title, a.slug, a.status AS article_status,
                    b.business_name, p.name AS package_name,
                    u.name AS owner_name
             FROM tn_sponsored_news sn
             JOIN tn_articles a        ON a.id  = sn.article_id
             JOIN tn_business_ads b    ON b.id  = sn.ad_id
             LEFT JOIN tn_ad_packages p ON p.id = b.package_id
             LEFT JOIN tn_users u      ON u.id  = b.owner_user_id
             WHERE sn.status = 'pending_approval'
             ORDER BY sn.created_at ASC"
        );
    }

    public function newsQuotaInfo(int $adId): array
    {
        $db   = $this->db;
        $stmt = $db->prepare(
            "SELECT b.news_used, p.news_quota, p.allow_news, p.news_interval_days,
                    p.name AS package_name,
                    (SELECT MAX(sn.created_at) FROM tn_sponsored_news sn WHERE sn.ad_id = b.id) AS last_news_at
             FROM tn_business_ads b
             LEFT JOIN tn_ad_packages p ON p.id = b.package_id
             WHERE b.id = ?"
        );
        $stmt->execute([$adId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    public function canPostSponsoredNews(int $adId): array
    {
        $info = $this->newsQuotaInfo($adId);
        if (empty($info) || empty($info['allow_news'])) {
            return ['allowed' => false, 'reason' => 'Package does not include sponsored news'];
        }
        $used  = (int)($info['news_used'] ?? 0);
        $quota = (int)($info['news_quota'] ?? 0);
        if ($quota > 0 && $used >= $quota) {
            return ['allowed' => false, 'reason' => "Quota exhausted ({$used}/{$quota})"];
        }
        if (!empty($info['last_news_at']) && $info['news_interval_days'] > 0) {
            $daysSince = (int)floor((time() - strtotime($info['last_news_at'])) / 86400);
            if ($daysSince < $info['news_interval_days']) {
                $next = $info['news_interval_days'] - $daysSince;
                return ['allowed' => false, 'reason' => "Next article available in {$next} day(s)"];
            }
        }
        return ['allowed' => true, 'reason' => '', 'used' => $used, 'quota' => $quota];
    }
}
