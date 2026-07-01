<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, Auth, CSRF, Helper, Database};
use App\Services\PushService;

class PushController extends Controller
{
    public function __construct()
    {
        $this->requireRole('admin','chief_editor');
    }

    protected function layout(): string
    {
        return Auth::role() === 'admin' ? 'admin' : 'portal';
    }

    // GET /admin/push
    public function index(): void
    {
        $svc = new PushService();
        $db  = Database::getInstance();

        $logs = $db->query(
            "SELECT l.*, u.name AS sent_by_name FROM tn_push_logs l
             LEFT JOIN tn_users u ON u.id = l.sent_by
             ORDER BY l.created_at DESC LIMIT 30"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $districts = $db->query("SELECT id, name FROM tn_districts ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('admin.push.compose', [
            'pageTitle'       => 'Push Notifications',
            'logs'            => $logs,
            'districts'       => $districts,
            'totalSubscribers'=> $svc->subscriberCount(),
        ], $this->layout());
    }

    // POST /admin/push/send
    public function send(): void
    {
        CSRF::validate();
        $title      = Helper::sanitize($this->post('title', ''));
        $body       = Helper::sanitize($this->post('body', ''));
        $clickUrl   = $this->post('click_url', '');
        $districtIds= array_filter(array_map('intval', (array)($_POST['district_ids'] ?? [])));

        if (!$title || !$body) {
            $this->flash('danger', 'Title and body are required.');
            $this->redirect('/admin/push');
        }

        $result = (new PushService())->sendManual($title, $body, $clickUrl ?: null, $districtIds, Auth::id());

        if ($result['success']) {
            $this->flash('success', "Push sent to {$result['sent']} subscribers.");
        } else {
            $this->flash('warning', $result['reason'] ?? 'Push failed.');
        }
        $this->redirect('/admin/push');
    }
    // POST /admin/push/send-ad/{id}
    public function sendAd(string $adId): void
    {
        CSRF::validate();
        $db = Database::getInstance();
        $ad = $db->prepare(
            "SELECT b.*, GROUP_CONCAT(DISTINCT i.filepath ORDER BY i.id LIMIT 1) AS img
             FROM tn_business_ads b LEFT JOIN tn_ad_images i ON i.ad_id = b.id
             WHERE b.id=? GROUP BY b.id"
        );
        $ad->execute([(int)$adId]);
        $adRow = $ad->fetch(\PDO::FETCH_ASSOC);

        if (!$adRow) { $this->flash('danger','Ad not found.'); $this->redirect('/admin/business-ads'); }

        $districtId  = (int)$this->post('push_district', 0) ?: null;
        $districtIds = $districtId ? [$districtId] : [];

        // Build ad array for PushService
        $adData = $adRow;
        if ($adRow['img']) {
            $adData['images'] = [['filepath' => $adRow['img']]];
        }

        $result = (new \App\Services\PushService())->sendAd($adData, $districtIds);

        if ($result['success']) {
            $this->flash('success', "Push sent to {$result['sent']} subscribers.");
        } else {
            $this->flash('warning', $result['reason'] ?? 'Push failed.');
        }

        $base = \App\Core\Auth::role() === 'admin' ? '/admin/business-ads' : '/portal/ads';
        $this->redirect($base . '/show/' . $adId);
    }
}
