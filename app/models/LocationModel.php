<?php
namespace App\Models;

use App\Core\Model;

class LocationModel extends Model
{
    protected string $table = 'tn_states';

    public function allStates(): array
    {
        return $this->fetchAll("SELECT * FROM tn_states ORDER BY name");
    }

    public function allDistricts(int $stateId = 0): array
    {
        if ($stateId) {
            return $this->fetchAll(
                "SELECT d.*, s.name AS state_name FROM tn_districts d JOIN tn_states s ON s.id = d.state_id WHERE d.state_id = ? ORDER BY d.name",
                [$stateId]
            );
        }
        return $this->fetchAll(
            "SELECT d.*, s.name AS state_name FROM tn_districts d JOIN tn_states s ON s.id = d.state_id ORDER BY d.name"
        );
    }

    public function allCities(int $districtId = 0): array
    {
        if ($districtId) {
            return $this->fetchAll(
                "SELECT c.*, d.name AS district_name FROM tn_cities c JOIN tn_districts d ON d.id = c.district_id WHERE c.district_id = ? ORDER BY c.name",
                [$districtId]
            );
        }
        return $this->fetchAll(
            "SELECT c.*, d.name AS district_name, s.name AS state_name
             FROM tn_cities c
             JOIN tn_districts d ON d.id = c.district_id
             JOIN tn_states s ON s.id = d.state_id
             ORDER BY c.name"
        );
    }

    public function addState(array $data): int
    {
        $stmt = Database::getInstance()->prepare("INSERT INTO tn_states (name, slug) VALUES (?,?)");
        $stmt->execute([$data['name'], $data['slug']]);
        return (int)Database::getInstance()->lastInsertId();
    }

    public function addDistrict(array $data): int
    {
        $stmt = Database::getInstance()->prepare("INSERT INTO tn_districts (state_id, name, slug) VALUES (?,?,?)");
        $stmt->execute([$data['state_id'], $data['name'], $data['slug']]);
        return (int)Database::getInstance()->lastInsertId();
    }

    public function addCity(array $data): int
    {
        $stmt = Database::getInstance()->prepare("INSERT INTO tn_cities (district_id, name, slug) VALUES (?,?,?)");
        $stmt->execute([$data['district_id'], $data['name'], $data['slug']]);
        return (int)Database::getInstance()->lastInsertId();
    }

    public function deleteState(int $id): void
    {
        Database::getInstance()->prepare("DELETE FROM tn_states WHERE id = ?")->execute([$id]);
    }

    public function deleteDistrict(int $id): void
    {
        Database::getInstance()->prepare("DELETE FROM tn_districts WHERE id = ?")->execute([$id]);
    }

    public function deleteCity(int $id): void
    {
        Database::getInstance()->prepare("DELETE FROM tn_cities WHERE id = ?")->execute([$id]);
    }

    public function firstCityByDistrict(int $districtId): ?int
    {
        $row = $this->fetchOne(
            "SELECT id FROM tn_cities WHERE district_id = ? ORDER BY id ASC LIMIT 1",
            [$districtId]
        );
        return $row ? (int)$row['id'] : null;
    }

    public function findCityByName(string $name, int $districtId = 0): ?int
    {
        if (!trim($name)) return null;
        if ($districtId) {
            $row = $this->fetchOne(
                "SELECT id FROM tn_cities WHERE name LIKE ? AND district_id=? LIMIT 1",
                ['%' . trim($name) . '%', $districtId]
            );
            if ($row) return (int)$row['id'];
        }
        $row = $this->fetchOne(
            "SELECT id FROM tn_cities WHERE name LIKE ? LIMIT 1",
            ['%' . trim($name) . '%']
        );
        return $row ? (int)$row['id'] : null;
    }

}