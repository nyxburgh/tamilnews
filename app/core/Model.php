<?php
namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /* ── Query helpers ── */

    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    protected function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    protected function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    public function find(int $id): array|false
    {
        return $this->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    public function all(string $orderBy = 'id', string $dir = 'DESC'): array
    {
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        return $this->fetchAll("SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$dir}");
    }

    public function insert(array $data): int
    {
        $cols   = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $places = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO `{$this->table}` ({$cols}) VALUES ({$places})", array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set    = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        $values = array_values($data);
        $values[] = $id;
        $this->query("UPDATE `{$this->table}` SET {$set} WHERE `{$this->primaryKey}` = ?", $values);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->query("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?", [$id]);
        return true;
    }

    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        return (int)$this->fetchColumn($sql, $params);
    }

    /* ── Pagination ── */
    public function paginate(int $page, int $perPage, string $where = '', array $params = [], string $orderBy = 'id', string $dir = 'DESC'): array
    {
        $dir    = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT * FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        $sql .= " ORDER BY `{$orderBy}` {$dir} LIMIT {$perPage} OFFSET {$offset}";
        return [
            'data'  => $this->fetchAll($sql, $params),
            'total' => $this->count($where, $params),
            'page'  => $page,
            'per_page' => $perPage,
        ];
    }
}
