<?php
namespace App\Models;

use App\Core\{Model, Database};

class BusinessAdModel extends Model
{
    protected string $table = 'tn_business_ads';

    // ── Submit new ad ────────────────────────────────────────

    public function submit(array $data, int $userId): int|false
    {
        $data['submitted_by'] = $userId;
        $data['status']       = 'pending';
        // package_id is optional — skip if column not yet in DB
        if (!isset($data['package_id'])) $data['package_id'] = 2;
        return $this->insert($data);
    }

    // ── Upload images (max 5) ────────────────────────────────

    public function uploadImage(int $adId, array $file, string $linkUrl = '', string $altText = '', string $slotType = ''): bool
    {
        // Check existing count
        $count = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_ad_images WHERE ad_id = ?", [$adId]
        );
        if ($count >= 5) return false;

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) return false;

        $tmpName   = 'ad_' . $adId . '_' . uniqid() . '.' . $ext;
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/ads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $tmpPath = $uploadDir . $tmpName;

        if (!move_uploaded_file($file['tmp_name'], $tmpPath)) return false;

        // Normalize "square" slot ads to a fixed 2:1 preset (300x150 / 600x300 / 900x450)
        if ($slotType === 'square') {
            $this->resizeToAdPreset($tmpPath);
        }

        // Convert to WebP to save storage (skip if already webp)
        $finalName = $this->convertToWebp($tmpPath, $uploadDir, $tmpName);

        $this->db->prepare(
            "INSERT INTO tn_ad_images (ad_id, filepath, link_url, alt_text, sort_order)
             VALUES (?, ?, ?, ?, (SELECT COALESCE(MAX(sort_order),0)+1 FROM tn_ad_images ai2 WHERE ad_id=?))"
        )->execute([$adId, '/uploads/ads/' . $finalName, $linkUrl, $altText, $adId]);

        return true;
    }

    /**
     * Resize + center-crop an ad image to the nearest fixed preset size:
     * 300×150, 600×300, or 900×450 (all 2:1 ratio). Picks the preset
     * closest to the source width, capped at 900×450 max. Overwrites
     * the file in place.
     */
    private function resizeToAdPreset(string $path): void
    {
        $info = @getimagesize($path);
        if (!$info) return;
        [$srcW, $srcH, $type] = $info;

        // Choose nearest preset based on source width
        if ($srcW <= 450)      { $tgtW = 300; $tgtH = 150; }
        elseif ($srcW <= 750)  { $tgtW = 600; $tgtH = 300; }
        else                   { $tgtW = 900; $tgtH = 450; }

        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default        => null,
        };
        if (!$src) return;

        // Cover-fit: scale to fill target, then crop center
        $srcRatio = $srcW / $srcH;
        $tgtRatio = $tgtW / $tgtH;

        if ($srcRatio > $tgtRatio) {
            // source wider than target → scale by height, crop width
            $scaleH = $tgtH;
            $scaleW = (int)round($srcW * ($tgtH / $srcH));
        } else {
            // source taller/narrower → scale by width, crop height
            $scaleW = $tgtW;
            $scaleH = (int)round($srcH * ($tgtW / $srcW));
        }

        $scaled = imagecreatetruecolor($scaleW, $scaleH);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        imagecopyresampled($scaled, $src, 0, 0, 0, 0, $scaleW, $scaleH, $srcW, $srcH);

        $cropX = (int)round(($scaleW - $tgtW) / 2);
        $cropY = (int)round(($scaleH - $tgtH) / 2);

        $final = imagecreatetruecolor($tgtW, $tgtH);
        imagealphablending($final, false);
        imagesavealpha($final, true);
        imagecopy($final, $scaled, 0, 0, $cropX, $cropY, $tgtW, $tgtH);

        match ($type) {
            IMAGETYPE_PNG => imagepng($final, $path, 8),
            IMAGETYPE_GIF => imagegif($final, $path),
            default       => imagejpeg($final, $path, 90),
        };

        imagedestroy($src);
        imagedestroy($scaled);
        imagedestroy($final);
    }

    /** Attach images already in the media library — copies the path reference only */
    public function attachExistingImages(int $adId, array $paths): int
    {
        $added = 0;
        foreach ($paths as $p) {
            $count = (int)$this->fetchColumn(
                "SELECT COUNT(*) FROM tn_ad_images WHERE ad_id = ?", [$adId]
            );
            if ($count >= 5) break;
            $filepath = is_array($p) ? ($p['filepath'] ?? '') : (string)$p;
            $alt      = is_array($p) ? ($p['alt'] ?? '')      : '';
            if (!$filepath) continue;
            $this->db->prepare(
                "INSERT INTO tn_ad_images (ad_id, filepath, link_url, alt_text, sort_order)
                 VALUES (?, ?, '', ?, (SELECT COALESCE(MAX(sort_order),0)+1 FROM tn_ad_images ai2 WHERE ad_id=?))"
            )->execute([$adId, $filepath, $alt, $adId]);
            $added++;
        }
        return $added;
    }

    /** Convert an uploaded image file to WebP format, delete original, return new filename */
    private function convertToWebp(string $srcPath, string $dir, string $originalName): string
    {
        if (!function_exists('imagewebp')) return $originalName; // GD WebP not available

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($ext === 'webp') return $originalName; // already webp

        $info = @getimagesize($srcPath);
        if (!$info) return $originalName;

        $img = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($srcPath),
            IMAGETYPE_PNG  => @imagecreatefrompng($srcPath),
            IMAGETYPE_GIF  => @imagecreatefromgif($srcPath),
            default        => null,
        };
        if (!$img) return $originalName;

        // Preserve transparency for PNG/GIF
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        $webpName = pathinfo($originalName, PATHINFO_FILENAME) . '.webp';
        $webpPath = $dir . $webpName;

        if (imagewebp($img, $webpPath, 82)) {
            imagedestroy($img);
            @unlink($srcPath); // remove original, keep only webp
            return $webpName;
        }
        imagedestroy($img);
        return $originalName;
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
            self::deleteFile($img['filepath']);
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
                    s.name AS slot_name, s.type AS slot_type,
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
                    s.name AS slot_name, s.type AS slot_type, s.desktop_size,
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
            "SELECT b.*, s.type AS slot_type, s.desktop_size, s.mobile_size,
                    (SELECT filepath FROM tn_ad_images WHERE ad_id=b.id AND is_active=1 ORDER BY sort_order LIMIT 1) AS primary_image,
                    (SELECT link_url FROM tn_ad_images WHERE ad_id=b.id AND is_active=1 ORDER BY sort_order LIMIT 1) AS click_url,
                    CASE b.display_type WHEN 'location' THEN 3 WHEN 'category' THEN 2 ELSE 1 END AS priority
             FROM tn_business_ads b
             JOIN tn_ad_slots s ON s.id = b.slot_id
             WHERE s.position = ?
             AND b.status IN ('active','approved')
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

    /** Get default fallback image path for a slot type */
    public function getDefaultImage(string $type): string
    {
        $defaults = ['square'=>'/uploads/vaqua.jpeg','horizontal'=>'/uploads/vah.png','vertical'=>'/uploads/vaqua.jpeg'];
        try {
            $row = $this->fetchOne("SELECT ad_code FROM tn_ad_slots WHERE type = ? LIMIT 1", [$type]);
            if (!empty($row['ad_code'])) {
                $v = trim($row['ad_code']);
                if ($v[0] === '/' || str_starts_with($v, 'http')) return $v;
            }
        } catch (\Exception $e) {}
        return $defaults[$type] ?? '/uploads/vaqua.jpeg';
    }

    /** Get active approved ads for rotation — up to 5 images per ad */
    public function activeForRotation(string $slotType, ?int $categoryId = null): array
    {
        try {
            $rows = $this->fetchAll(
                "SELECT b.id, b.business_name, b.website_url, b.contact_phone, b.contact_email,
                        d.name AS district_name,
                        ai.filepath, ai.alt_text, ai.link_url, ai.sort_order
                 FROM tn_business_ads b
                 JOIN tn_ad_slots s ON s.id = b.slot_id AND s.type = ?
                 LEFT JOIN tn_districts d ON d.id = b.district_id
                 LEFT JOIN tn_ad_images ai ON ai.ad_id = b.id AND ai.is_active = 1
                 WHERE b.status IN ('active','approved')
                   AND (b.valid_from IS NULL OR b.valid_from <= CURDATE())
                   AND (b.valid_until IS NULL OR b.valid_until >= CURDATE())
                   AND (b.display_type = 'global' OR (b.display_type = 'category' AND b.category_id = ?))
                 ORDER BY b.id ASC, ai.sort_order ASC",
                [$slotType, $categoryId ?? 0]
            );
        } catch (\Exception $e) { return []; }

        // Group images by ad (max 5 each)
        $ads = [];
        foreach ($rows as $row) {
            $id = $row['id'];
            if (!isset($ads[$id])) {
                $ads[$id] = ['ad_id'=>$id, 'business_name'=>$row['business_name'],
                             'website_url'=>$row['website_url'] ?? '#', 'images'=>[]];
            }
            if ($row['filepath'] && count($ads[$id]['images']) < 5) {
                $ads[$id]['images'][] = [
                    'src'      => $row['filepath'],
                    'alt'      => $row['alt_text'] ?? '',
                    'link'     => $row['link_url'] ?: ($row['website_url'] ?? '#'),
                    'name'     => $row['business_name'] ?? '',
                    'phone'    => $row['contact_phone'] ?? '',
                    'email'    => $row['contact_email'] ?? '',
                    'district' => $row['district_name'] ?? '',
                ];
            }
        }

        // If no active DB ads → return default image
        if (empty($ads)) {
            $default = $this->getDefaultImage($slotType);
            return [['ad_id'=>0, 'business_name'=>'Advertisement', 'website_url'=>'#',
                     'images'=>[['src'=>$default, 'alt'=>'Advertisement', 'link'=>'#']]]];
        }

        return array_values($ads);
    }
    public function deleteWithFiles(int $adId): void
    {
        $images = $this->fetchAll("SELECT filepath FROM tn_ad_images WHERE ad_id = ?", [$adId]);
        foreach ($images as $img) {
            self::deleteFile($img['filepath']);
        }
        $this->db->prepare("DELETE FROM tn_ad_images WHERE ad_id = ?")->execute([$adId]);
        $this->db->prepare("DELETE FROM tn_business_ads WHERE id = ?")->execute([$adId]);
    }

    /**
     * Delete all images for an ad (when replacing/editing)
     */
    public function deleteAdImages(int $adId): void
    {
        $images = $this->fetchAll("SELECT filepath FROM tn_ad_images WHERE ad_id = ?", [$adId]);
        foreach ($images as $img) {
            self::deleteFile($img['filepath']);
        }
        $this->db->prepare("DELETE FROM tn_ad_images WHERE ad_id = ?")->execute([$adId]);
    }

    /**
     * Static helper: delete a file given its relative path (e.g. /uploads/ads/image.jpg)
     */
    public static function deleteFile(string $filepath): void
    {
        if (empty($filepath)) return;
        // Try ROOT_PATH/public + filepath
        $abs = ROOT_PATH . '/public' . '/' . ltrim($filepath, '/');
        if (file_exists($abs)) { unlink($abs); return; }
        // Try ROOT_PATH + filepath
        $abs2 = ROOT_PATH . '/' . ltrim($filepath, '/');
        if (file_exists($abs2)) { unlink($abs2); }
    }

}
