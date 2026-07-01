<?php
namespace App\Models;
use App\Core\Model;

class SiteCounterModel extends Model
{
    protected string $table = 'tn_site_counter';

    public function increment(): void
    {
        $this->query(
            "INSERT INTO tn_site_counter (id, total_views) VALUES (1, 1)
             ON DUPLICATE KEY UPDATE total_views = total_views + 1"
        );
    }

    public function get(): int
    {
        return (int)($this->fetchColumn("SELECT total_views FROM tn_site_counter WHERE id = 1") ?? 0);
    }
}
