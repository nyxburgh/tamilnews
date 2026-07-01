<?php
namespace App\Models;
use App\Core\Model;

class SettingModel extends Model
{
    protected string $table = 'tn_settings';

    public function all(string $orderBy = 'id', string $dir = 'DESC'): array
    {
        $rows   = $this->fetchAll("SELECT * FROM tn_settings ORDER BY `group`, id");
        $result = [];
        foreach ($rows as $row) { $result[$row['group']][$row['key']] = $row; }
        return $result;
    }
    public function getValue(string $key, mixed $default = null): mixed
    {
        $row = $this->fetchOne("SELECT value FROM tn_settings WHERE `key` = ?", [$key]);
        return $row ? $row['value'] : $default;
    }
    public function updateKey(string $key, mixed $value): void
    {
        $this->query("UPDATE tn_settings SET value = ? WHERE `key` = ?", [$value, $key]);
    }
    public function updateGroup(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            $this->query("UPDATE tn_settings SET value = ? WHERE `group` = ? AND `key` = ?", [$value, $group, $key]);
        }
    }
    public function byGroup(string $group): array
    {
        return $this->fetchAll("SELECT * FROM tn_settings WHERE `group` = ? ORDER BY id", [$group]);
    }
}
