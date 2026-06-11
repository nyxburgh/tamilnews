<?php
namespace App\Models;
use App\Core\Model;

class YoutubeModel extends Model
{
    protected string $table = 'tn_youtube_channels';
    public function allChannels(): array
    {
        return $this->fetchAll(
            "SELECT y.*, c.name AS category_name FROM tn_youtube_channels y
             LEFT JOIN tn_categories c ON c.id = y.category_id ORDER BY y.id DESC"
        );
    }
    public function keywords(int $channelId): array
    {
        return $this->fetchAll(
            "SELECT k.*, c.name AS category_name FROM tn_youtube_keyword_map k
             JOIN tn_categories c ON c.id = k.category_id WHERE k.channel_id = ?", [$channelId]
        );
    }
    public function addKeyword(array $data): int
    {
        $this->db->prepare("INSERT INTO tn_youtube_keyword_map (channel_id, keyword, category_id) VALUES (?,?,?)")
                 ->execute([$data['channel_id'], $data['keyword'], $data['category_id']]);
        return (int)$this->db->lastInsertId();
    }
    public function deleteKeyword(int $id): void
    {
        $this->db->prepare("DELETE FROM tn_youtube_keyword_map WHERE id=?")->execute([$id]);
    }
    public function imports(int $page = 1, int $perPage = 20, string $status = ''): array
    {
        $where  = $status ? "WHERE yi.status = ?" : '';
        $offset = ($page - 1) * $perPage;
        $params = $status ? [$status] : [];
        $data   = $this->fetchAll(
            "SELECT yi.*, yc.channel_name FROM tn_youtube_imports /* table may not exist in some installs */ yi
             JOIN tn_youtube_channels yc ON yc.id = yi.channel_id
             {$where} ORDER BY yi.created_at DESC LIMIT {$perPage} OFFSET {$offset}", $params
        );
        $total  = (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_youtube_imports /* table may not exist in some installs */ {$where}", $params);
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }
    public function pendingCount(): int { try { return (int)$this->fetchColumn("SELECT COUNT(*) FROM tn_youtube_imports /* table may not exist in some installs */ WHERE status='pending'"); } catch (\Exception $e) { return 0; } }
}
