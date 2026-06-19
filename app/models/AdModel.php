<?php
namespace App\Models;
use App\Core\Model;

class AdModel extends Model
{
    protected string $table = 'tn_ad_slots';

    public function allSlots(): array
    {
        return $this->fetchAll("SELECT * FROM tn_ad_slots ORDER BY id");
    }

    public function updateSlot(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /** Get the type ('square'/'horizontal'/'vertical') for a given slot id */
    public function slotType(int $slotId): string
    {
        if (!$slotId) return '';
        $type = $this->fetchColumn("SELECT type FROM tn_ad_slots WHERE id = ?", [$slotId]);
        return $type ?: '';
    }

    /**
     * Get active ad for a slot type: 'square' or 'horizontal'
     * Tries business ads first, falls back to manual ad_code
     */
    public function getSlot(string $position): array|false
    {
        // 1. Try active business ad first
        try {
            $bizAd = (new \App\Models\BusinessAdModel())->getActiveForSlot($position);
            if ($bizAd && !empty($bizAd['primary_image'])) {
                $imgUrl   = ASSET_URL . htmlspecialchars($bizAd['primary_image']);
                $clickUrl = htmlspecialchars($bizAd['click_url'] ?? '#');
                $bname    = htmlspecialchars($bizAd['business_name']);
                $adId     = (int)$bizAd['id'];
                return [
                    'ad_code' => "<a href=\"{$clickUrl}\" target=\"_blank\" rel=\"noopener\" data-ad-id=\"{$adId}\"><img src=\"{$imgUrl}\" alt=\"{$bname}\" style=\"width:100%;height:100%;object-fit:cover\"></a>",
                    'source'  => 'business',
                    'ad_id'   => $adId,
                ];
            }
        } catch (\Exception $e) {}

        // 2. Fall back to manual ad_code in tn_ad_slots
        try {
            return $this->fetchOne(
                "SELECT * FROM tn_ad_slots WHERE position = ? AND is_active = 1 LIMIT 1",
                [$position]
            );
        } catch (\Exception $e) {}

        return false;
    }

    // Convenience helpers
    public function getSquareAd(): array|false      { return $this->getSlot('square'); }
    public function getHorizontalAd(): array|false  { return $this->getSlot('horizontal'); }
}
