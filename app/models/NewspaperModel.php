<?php
namespace App\Models;

use App\Core\{Model, Database};

class NewspaperModel extends Model
{
    protected string $table = 'tn_newspapers';

    public function allPaginated(int $page = 1, int $perPage = 12, string $year = ''): array
    {
        $where  = ['n.is_active = 1'];
        $params = [];
        if ($year) { $where[] = 'YEAR(n.edition_date) = ?'; $params[] = $year; }
        $whereSQL = 'WHERE ' . implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $data = $this->fetchAll(
            "SELECT n.*, u.name AS uploaded_by_name
             FROM tn_newspapers n
             LEFT JOIN tn_users u ON u.id = n.user_id
             {$whereSQL}
             ORDER BY n.edition_date DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM tn_newspapers n {$whereSQL}", $params
        );

        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function allForAdmin(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $data = $this->fetchAll(
            "SELECT n.*, u.name AS uploaded_by_name
             FROM tn_newspapers n
             LEFT JOIN tn_users u ON u.id = n.user_id
             ORDER BY n.edition_date DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        $total = (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_newspapers");
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function latest(int $limit = 6): array
    {
        return $this->fetchAll(
            "SELECT * FROM tn_newspapers WHERE is_active = 1
             ORDER BY edition_date DESC LIMIT ?",
            [$limit]
        );
    }

    public function byDate(string $date): array|false
    {
        return $this->fetchOne(
            "SELECT n.*, u.name AS uploaded_by_name
             FROM tn_newspapers n
             LEFT JOIN tn_users u ON u.id = n.user_id
             WHERE n.edition_date = ? AND n.is_active = 1",
            [$date]
        );
    }

    public function availableYears(): array
    {
        return $this->fetchAll(
            "SELECT YEAR(edition_date) AS year, COUNT(*) AS count
             FROM tn_newspapers WHERE is_active = 1
             GROUP BY YEAR(edition_date) ORDER BY year DESC"
        );
    }

    public function incrementDownload(int $id): void
    {
        $this->query(
            "UPDATE tn_newspapers SET download_count = download_count + 1 WHERE id = ?",
            [$id]
        );
    }

    public function upload(array $file, string $title, string $titleTamil, string $date,
                           string $type, int $userId): int|false
    {
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') return false;

        $filename  = 'paper_' . $date . '_' . uniqid() . '.pdf';
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/newspapers/';
        $destPath  = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) return false;

        $id = $this->insert([
            'title'        => $title,
            'title_tamil'  => $titleTamil ?: null,
            'edition_date' => $date,
            'edition_type' => $type,
            'pdf_path'     => '/uploads/newspapers/' . $filename,
            'file_size'    => filesize($destPath),
            'is_active'    => 1,
            'uploaded_by'  => $userId,
        ]);

        return $id;
    }

    public function deleteNewspaper(int $id): void
    {
        $paper = $this->find($id);
        if ($paper) {
            $filePath = dirname(__DIR__, 2) . '/public' . $paper['pdf_path'];
            if (file_exists($filePath)) unlink($filePath);
            $this->delete($id);
        }
    }
}
