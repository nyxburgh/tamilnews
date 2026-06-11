<?php
namespace App\Models;
use App\Core\Model;

class WidgetModel extends Model
{
    protected string $table = 'tn_widgets';

    public function active(string $position = 'sidebar'): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_widgets WHERE is_active=1 AND position=?
             ORDER BY sort_order ASC",
            [$position]
        );
    }

    public function allWidgets(): array
    {
        return $this->fetchAll("SELECT * FROM tn_widgets ORDER BY position, sort_order");
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $i => $id) {
            $this->query(
                "UPDATE tn_widgets SET sort_order=? WHERE id=?",
                [$i + 1, (int)$id]
            );
        }
    }

    public function toggle(int $id): void
    {
        $this->query(
            "UPDATE tn_widgets SET is_active = 1 - is_active WHERE id=?", [$id]
        );
    }

    public function updateConfig(int $id, array $config): void
    {
        $this->query(
            "UPDATE tn_widgets SET config=?, title=?, title_tamil=?, show_mobile=? WHERE id=?",
            [json_encode($config), $config['title'] ?? null, $config['title_tamil'] ?? null,
             (int)($config['show_mobile'] ?? 0), $id]
        );
    }
}
