<?php
namespace App\Models;
use App\Core\Model;

class AdModel extends Model
{
    protected string $table = 'tn_ad_slots';
    public function allSlots(): array { return $this->fetchAll("SELECT * FROM tn_ad_slots ORDER BY id"); }
    public function updateSlot(int $id, array $data): bool { return $this->update($id, $data); }
}
