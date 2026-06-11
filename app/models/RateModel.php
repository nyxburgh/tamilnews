<?php
namespace App\Models;
use App\Core\Model;

class RateModel extends Model
{
    protected string $table = 'tn_rates';

    public function allRates(): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_rates ORDER BY FIELD(type,
             'gold','silver','petrol','diesel','currency_usd','currency_gbp','currency_eur'),
             city ASC"
        );
    }

    public function get(string $type, ?string $city = null): array|false
    {
        if ($city) {
            return $this->fetchOne(
                "SELECT * FROM tn_rates WHERE type=? AND city=? LIMIT 1",
                [$type, $city]
            );
        }
        return $this->fetchOne(
            "SELECT * FROM tn_rates WHERE type=? ORDER BY updated_at DESC LIMIT 1",
            [$type]
        );
    }

    public function allForWidget(): array
    {
        // Latest of each type for floating mobile icons
        return $this->fetchAll(
            "SELECT r.* FROM tn_rates r
             INNER JOIN (
               SELECT type, MAX(updated_at) AS max_updated
               FROM tn_rates GROUP BY type
             ) latest ON r.type = latest.type AND r.updated_at = latest.max_updated
             ORDER BY FIELD(r.type,'gold','silver','petrol','diesel','currency_usd')"
        );
    }

    public function upsert(string $type, float $value, ?string $city, ?float $change): void
    {
        // Check existing
        $existing = $city
            ? $this->fetchOne("SELECT id FROM tn_rates WHERE type=? AND city=?", [$type, $city])
            : $this->fetchOne("SELECT id FROM tn_rates WHERE type=? AND city IS NULL", [$type]);

        $pct = ($change && $value > 0) ? round(($change / ($value - $change)) * 100, 2) : null;

        if ($existing) {
            $this->query(
                "UPDATE tn_rates SET value=?, change_val=?, change_pct=?, updated_at=NOW() WHERE id=?",
                [$value, $change, $pct, $existing['id']]
            );
        } else {
            $this->db->prepare(
                "INSERT INTO tn_rates (type,value,city,change_val,change_pct) VALUES (?,?,?,?,?)"
            )->execute([$type, $value, $city, $change, $pct]);
        }
    }
}
