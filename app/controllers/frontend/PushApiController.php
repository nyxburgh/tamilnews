<?php
namespace App\Controllers\Frontend;

use App\Core\{Controller, Auth, CSRF};
use App\Services\PushService;

class PushApiController extends Controller
{
    // POST /api/push/subscribe
    public function subscribe(): void
    {
        header('Content-Type: application/json');
        $token      = trim($_POST['token'] ?? '');
        $districtId = (int)($_POST['district_id'] ?? $_COOKIE['tn_district_id'] ?? 0) ?: null;
        $userId     = Auth::id() ?: null;

        if (empty($token)) { echo json_encode(['error' => 'No token']); return; }

        try {
            (new PushService())->subscribe($token, $userId, $districtId);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['error' => 'DB error']);
        }
    }

    // POST /api/push/unsubscribe
    public function unsubscribe(): void
    {
        header('Content-Type: application/json');
        $token = trim($_POST['token'] ?? '');
        if (empty($token)) { echo json_encode(['error' => 'No token']); return; }
        (new PushService())->unsubscribe($token);
        echo json_encode(['success' => true]);
    }
}
