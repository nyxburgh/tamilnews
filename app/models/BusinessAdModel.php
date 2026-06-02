<?php
namespace App\Models;

use App\Core\{Model, Database};

class BusinessAdModel extends Model
{
    protected string $table = 'tn_business_ads';

    // ── Submit new ad ────────────────────────────────────────

    public function submit(array $data, int $userId): int|false
    {
        return $this->insert(array_merge($data, [
            'submitted_by' => $userId,
            'status'       => 'pending',
        ]));
    }

    // ── Upload images (max 5) ────────────────────────────────

    public function uploadImage(int $adId, array $file, string $linkUrl = '', string $altText = ''): bool
    {
        // Check existing count
        $count = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_ad_images WHERE ad_id = ?", [$adId]
        );
        if ($count >= 5) return false;

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) return false;

        $filename = 'ad_' . $adId . '_' . uniqid() . '.' . $ext;
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/ads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) return false;

        $this->db->prepare(
            "INSERT INTO tn_ad_images (ad_id, filepath, link_url, alt_text, sort_order)
             VALUES (?, ?, ?, ?, (SELECT COALESCE(MAX(sort_order),0)+1 FROM tn_ad_images ai2 WHERE ad_id=?))"
        )->execute([$adId, '/uploads/ads/' . $filename, $linkUrl, $altText, $adId]);

        return true;
    }

    public function images(int $adId): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_ad_images WHERE ad_id = ? ORDER BY sort_order ASC",
            [$adId]
        );
    }

    public function deleteImage(int $imageId, int $adId): void
    {
        $img = $this->fetchOne("SELECT * FROM tn_ad_images WHERE id=? AND ad_id=?", [$imageId, $adId]);
        if ($img) {
            $path = dirname(__DIR__, 2) . '/public' . $img['filepath'];
            if (file_exists($path)) unlink($path);
            $this->db->prepare("DELETE FROM tn_ad_images WHERE id=?")->execute([$imageId]);
        }
    }

    // ── List with filters ────────────────────────────────────

    public function listPaginated(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status']))      { $where[] = 'b.status = ?';           $params[] = $filters['status']; }
        if (!empty($filters['submitted_by'])) { $where[] = 'b.submitted_by = ?';     $params[] = $filters['submitted_by']; }
        if (!empty($filters['district_id'])) { $where[] = 'b.district_id = ?';      $params[] = $filters['district_id']; }

        $whereSQL = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $data = $this->fetchAll(
            "SELECT b.*,
                    u.name AS submitted_by_name,
                    s.name AS slot_name, s.position,
                    d.name AS district_name,
                    ci.name AS city_name,
                    c.name AS category_name,
                    c.name_tamil AS category_tamil,
                    (SELECT COUNT(*) FROM tn_ad_images WHERE ad_id=b.id) AS image_count
             FROM tn_business_ads b
             LEFT JOIN tn_users u ON u.id = b.submitted_by
             LEFT JOIN tn_ad_slots s ON s.id = b.slot_id
             LEFT JOIN tn_districts d ON d.id = b.district_id
             LEFT JOIN tn_cities ci ON ci.id = b.city_id
             LEFT JOIN tn_categories c ON c.id = b.category_id
             WHERE {$whereSQL}
             ORDER BY b.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_business_ads b WHERE {$whereSQL}", $params
        );

        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function findWithDetails(int $id): array|false
    {
        $ad = $this->fetchOne(
            "SELECT b.*,
                    u.name AS submitted_by_name,
                    s.name AS slot_name, s.position, s.desktop_size,
                    d.name AS district_name,
                    ci.name AS city_name,
                    c.name AS category_name
             FROM tn_business_ads b
             LEFT JOIN tn_users u ON u.id = b.submitted_by
             LEFT JOIN tn_ad_slots s ON s.id = b.slot_id
             LEFT JOIN tn_districts d ON d.id = b.district_id
             LEFT JOIN tn_cities ci ON ci.id = b.city_id
             LEFT JOIN tn_categories c ON c.id = b.category_id
             WHERE b.id = ?",
            [$id]
        );
        if (!$ad) return false;
        $ad['images'] = $this->images($id);
        return $ad;
    }

    // ── Approval / rejection ─────────────────────────────────

    public function approve(int $id, int $byUserId): void
    {
        $this->query(
            "UPDATE tn_business_ads
             SET status='approved', approved_by=?, approved_at=NOW()
             WHERE id=?",
            [$byUserId, $id]
        );
        // Auto-activate if payment confirmed and validity started
        $this->activateIfReady($id);
    }

    public function reject(int $id, int $byUserId, string $reason = ''): void
    {
        $this->query(
            "UPDATE tn_business_ads
             SET status='rejected', approved_by=?, approved_at=NOW(), rejection_reason=?
             WHERE id=?",
            [$byUserId, $reason, $id]
        );
    }

    public function confirmPayment(int $id, int $byUserId, string $note = ''): void
    {
        $this->query(
            "UPDATE tn_business_ads
             SET payment_status='confirmed', payment_confirmed_by=?,
                 payment_confirmed_at=NOW(), payment_note=?
             WHERE id=?",
            [$byUserId, $note, $id]
        );
        $this->activateIfReady($id);
    }

    private function activateIfReady(int $id): void
    {
        $this->query(
            "UPDATE tn_business_ads
             SET status='active'
             WHERE id=?
             AND status='approved'
             AND payment_status='confirmed'
             AND valid_from <= CURDATE()
             AND valid_until >= CURDATE()",
            [$id]
        );
    }

    // ── Display: get active ad for a slot ────────────────────
    // Called by frontend to get the right ad based on context

    /**
     * Get best active ad for slot type ('square' or 'horizontal')
     * Priority: location(3) > category(2) > global(1)
     */
    public function getActiveForSlot(
        string $position,
        ?int   $districtId  = null,
        ?int   $categoryId  = null
    ): array|false {
        // Priority: location-specific > category-specific > global
        $today = date('Y-m-d');

        // Build candidates query
        $params = [$position, $today, $today];

        $districtCond  = $districtId  ? "OR (b.display_type='location' AND b.district_id=?)" : '';
        $categoryCond  = $categoryId  ? "OR (b.display_type='category' AND b.category_id=?)"  : '';
        if ($districtId)  $params[] = $districtId;
        if ($categoryId)  $params[] = $categoryId;
        // Append global — always last priority
        $params[] = $position;
        $params[] = $today;
        $params[] = $today;

        // Score: location=3, category=2, global=1 — pick highest, then random within same score
        $ad = $this->fetchOne(
            "SELECT b.*, s.position, s.desktop_size, s.mobile_size,
                    (SELECT filepath FROM tn_ad_images WHERE ad_id=b.id AND is_active=1 ORDER BY sort_order LIMIT 1) AS primary_image,
                    (SELECT link_url FROM tn_ad_images WHERE ad_id=b.id AND is_active=1 ORDER BY sort_order LIMIT 1) AS click_url,
                    CASE b.display_type WHEN 'location' THEN 3 WHEN 'category' THEN 2 ELSE 1 END AS priority
             FROM tn_business_ads b
             JOIN tn_ad_slots s ON s.id = b.slot_id
             WHERE s.position = ?
             AND b.status = 'active'
             AND b.valid_from <= ? AND b.valid_until >= ?
             AND (
               b.display_type = 'global'
               {$districtCond}
               {$categoryCond}
             )
             ORDER BY priority DESC, RAND()
             LIMIT 1",
            $params
        );

        return $ad ?: false;
    }

    // ── Stats: increment impressions ─────────────────────────

    public function trackImpression(int $adId): void
    {
        try {
            $this->query(
                "UPDATE tn_business_ads SET impression_count=impression_count+1 WHERE id=?",
                [$adId]
            );
        } catch (\Exception $e) {}
    }

    public function trackClick(int $adId, ?int $imageId = null): void
    {
        try {
            $this->query(
                "UPDATE tn_business_ads SET click_count=click_count+1 WHERE id=?",
                [$adId]
            );
            $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
            $this->db->prepare(
                "INSERT INTO tn_ad_clicks (ad_id, image_id, ip_hash) VALUES (?,?,?)"
            )->execute([$adId, $imageId, $ipHash]);
        } catch (\Exception $e) {}
    }

    // ── Cron: expire outdated ads ─────────────────────────────

    public function expireOldAds(): int
    {
        $stmt = $this->db->prepare(
            "UPDATE tn_business_ads SET status='expired'
             WHERE status='active' AND valid_until < CURDATE()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function pendingCount(): int
    {
        try {
            return (int)$this->fetchColumn(
                "SELECT COUNT(*) FROM tn_business_ads WHERE status='pending'"
            );
        } catch (\Exception $e) {
            return 0;
        }
    }
}
