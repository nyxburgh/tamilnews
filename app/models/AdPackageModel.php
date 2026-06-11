<?php
namespace App\Models;
use App\Core\Model;

class AdPackageModel extends Model
{
    protected string $table = 'tn_ad_packages';

    public function active(): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_ad_packages WHERE is_active=1 ORDER BY sort_order"
        );
    }

    public function allPackages(): array
    {
        return $this->fetchAll(
            "SELECT p.*, COUNT(b.id) AS ad_count
             FROM tn_ad_packages p
             LEFT JOIN tn_business_ads b ON b.package_id = p.id
             GROUP BY p.id ORDER BY sort_order"
        );
    }
}
