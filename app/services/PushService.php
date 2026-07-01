<?php
namespace App\Services;

use App\Core\Database;

class PushService
{
    private \PDO    $db;
    private string  $serverKey;
    private bool    $configured;

    public function __construct()
    {
        $this->db        = Database::getInstance();
        $this->serverKey = $_ENV['FCM_SERVER_KEY'] ?? getenv('FCM_SERVER_KEY') ?? '';
        $this->configured = !empty($this->serverKey) && $this->serverKey !== 'REPLACE_WITH_SERVER_KEY';
    }

    // ── Send article push ─────────────────────────────────────

    public function sendArticle(array $article, array $districtIds = []): array
    {
        $title    = $article['title'] ?? '';
        $excerpt  = mb_substr(strip_tags($article['excerpt'] ?? $article['content'] ?? ''), 0, 120);
        $imageUrl = !empty($article['image_url']) ? $article['image_url'] : null;
        $slug     = $article['slug'] ?? '';
        $clickUrl = (BASE_URL ?? '') . '/public/article/' . $slug;

        $isBreaking = !empty($article['is_breaking']);

        return $this->dispatch(
            type:       'article',
            refId:      (int)$article['id'],
            title:      ($isBreaking ? '⚡ Breaking: ' : '') . $title,
            body:       $excerpt ?: 'Read the full story →',
            imageUrl:   $imageUrl,
            clickUrl:   $clickUrl,
            districtIds:$districtIds
        );
    }

    // ── Send ad push ─────────────────────────────────────────

    public function sendAd(array $ad, array $districtIds = []): array
    {
        $title    = '📢 ' . ($ad['business_name'] ?? 'New Ad');
        $body     = $ad['notes'] ?? 'Check out this advertisement';
        $imageUrl = !empty($ad['images'][0]['filepath'])
                  ? (rtrim(ASSET_URL ?? '', '/') . $ad['images'][0]['filepath'])
                  : null;
        $clickUrl = (BASE_URL ?? '') . '/public/';

        // District from ad if not passed
        if (empty($districtIds) && !empty($ad['district_id'])) {
            $districtIds = [(int)$ad['district_id']];
        }

        return $this->dispatch(
            type:       'ad',
            refId:      (int)$ad['id'],
            title:      $title,
            body:       $body,
            imageUrl:   $imageUrl,
            clickUrl:   $clickUrl,
            districtIds:$districtIds
        );
    }

    // ── Manual push ──────────────────────────────────────────

    public function sendManual(string $title, string $body, ?string $clickUrl = null, array $districtIds = [], ?int $byUserId = null): array
    {
        return $this->dispatch(
            type:       'manual',
            refId:      null,
            title:      $title,
            body:       $body,
            imageUrl:   null,
            clickUrl:   $clickUrl ?? ((BASE_URL ?? '') . '/public/'),
            districtIds:$districtIds,
            byUserId:   $byUserId
        );
    }

    // ── Core dispatch ─────────────────────────────────────────

    private function dispatch(
        string  $type,
        ?int    $refId,
        string  $title,
        string  $body,
        ?string $imageUrl,
        string  $clickUrl,
        array   $districtIds = [],
        ?int    $byUserId = null
    ): array {
        // Log the push
        $logStmt = $this->db->prepare(
            "INSERT INTO tn_push_logs (type, ref_id, title, body, image_url, click_url, districts, status, sent_by)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $logStmt->execute([
            $type, $refId, $title, $body, $imageUrl, $clickUrl,
            !empty($districtIds) ? json_encode($districtIds) : null,
            'pending', $byUserId
        ]);
        $logId = (int)$this->db->lastInsertId();

        if (!$this->configured) {
            $this->db->prepare("UPDATE tn_push_logs SET status='failed' WHERE id=?")->execute([$logId]);
            return ['success' => false, 'reason' => 'FCM not configured. Add FCM_SERVER_KEY to .env', 'log_id' => $logId];
        }

        // Get subscriber tokens
        $tokens = $this->getTokens($districtIds);
        if (empty($tokens)) {
            $this->db->prepare("UPDATE tn_push_logs SET status='sent', sent_count=0 WHERE id=?")->execute([$logId]);
            return ['success' => true, 'sent' => 0, 'log_id' => $logId];
        }

        // Send in batches of 500 (FCM limit)
        $sent = 0; $failed = 0;
        foreach (array_chunk($tokens, 500) as $batch) {
            $result = $this->fcmSend($title, $body, $clickUrl, $imageUrl, $batch);
            $sent   += $result['success'] ?? 0;
            $failed += $result['failure'] ?? 0;
        }

        $this->db->prepare(
            "UPDATE tn_push_logs SET status='sent', sent_count=?, fail_count=? WHERE id=?"
        )->execute([$sent, $failed, $logId]);

        return ['success' => true, 'sent' => $sent, 'failed' => $failed, 'log_id' => $logId];
    }

    // ── FCM HTTP Legacy API ──────────────────────────────────

    private function fcmSend(string $title, string $body, string $clickUrl, ?string $imageUrl, array $tokens): array
    {
        $payload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body'  => $body,
                'icon'  => '/public/assets/img/logo-192.png',
                'click_action' => $clickUrl,
            ],
            'data' => [
                'click_url' => $clickUrl,
                'title'     => $title,
                'body'      => $body,
            ],
            'webpush' => [
                'fcm_options' => ['link' => $clickUrl],
            ],
        ];
        if ($imageUrl) {
            $payload['notification']['image'] = $imageUrl;
        }

        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: key=' . $this->serverKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err || !$response) return ['success' => 0, 'failure' => count($tokens)];
        $data = json_decode($response, true);
        return [
            'success' => $data['success'] ?? 0,
            'failure' => $data['failure'] ?? count($tokens),
        ];
    }

    // ── Token retrieval ──────────────────────────────────────

    private function getTokens(array $districtIds): array
    {
        if (!empty($districtIds)) {
            $placeholders = implode(',', array_fill(0, count($districtIds), '?'));
            $stmt = $this->db->prepare(
                "SELECT DISTINCT fcm_token FROM tn_push_subscribers
                 WHERE is_active = 1 AND (district_id IN ($placeholders) OR district_id IS NULL)"
            );
            $stmt->execute($districtIds);
        } else {
            $stmt = $this->db->query("SELECT DISTINCT fcm_token FROM tn_push_subscribers WHERE is_active = 1");
        }
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'fcm_token');
    }

    // ── Subscribe (called from API) ──────────────────────────

    public function subscribe(string $token, ?int $userId = null, ?int $districtId = null, string $platform = 'web'): void
    {
        // Upsert: update existing token or insert new
        $this->db->prepare(
            "INSERT INTO tn_push_subscribers (fcm_token, user_id, district_id, platform, is_active)
             VALUES (?,?,?,?,1)
             ON DUPLICATE KEY UPDATE user_id=VALUES(user_id), district_id=VALUES(district_id), is_active=1, updated_at=NOW()"
        )->execute([$token, $userId, $districtId, $platform]);
    }

    public function unsubscribe(string $token): void
    {
        $this->db->prepare("UPDATE tn_push_subscribers SET is_active=0 WHERE fcm_token=?")->execute([$token]);
    }

    public function subscriberCount(?int $districtId = null): int
    {
        if ($districtId) {
            return (int)$this->db->query("SELECT COUNT(*) FROM tn_push_subscribers WHERE is_active=1 AND district_id=$districtId")->fetchColumn();
        }
        return (int)$this->db->query("SELECT COUNT(*) FROM tn_push_subscribers WHERE is_active=1")->fetchColumn();
    }
}
