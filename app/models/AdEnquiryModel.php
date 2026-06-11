<?php
namespace App\Models;
use App\Core\Model;

class AdEnquiryModel extends Model
{
    protected string $table = 'tn_ad_enquiries';

    public function pending(): array
    {
        return $this->fetchAll(
            "SELECT e.*, p.name AS package_name
             FROM tn_ad_enquiries e
             LEFT JOIN tn_ad_packages p ON p.id = e.package_id
             WHERE e.status = 'new'
             ORDER BY e.created_at DESC"
        );
    }

    public function allPaginated(int $page=1, int $per=20): array
    {
        $offset = ($page-1)*$per;
        $data = $this->fetchAll(
            "SELECT e.*, p.name AS package_name
             FROM tn_ad_enquiries e
             LEFT JOIN tn_ad_packages p ON p.id = e.package_id
             ORDER BY e.created_at DESC LIMIT ? OFFSET ?",
            [$per, $offset]
        );
        $total = (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_ad_enquiries");
        return ['data'=>$data,'total'=>$total,'page'=>$page,'per_page'=>$per];
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->query("UPDATE tn_ad_enquiries SET status=? WHERE id=?", [$status, $id]);
    }

    public function submit(array $data): int|false
    {
        return $this->insert($data);
    }
}
