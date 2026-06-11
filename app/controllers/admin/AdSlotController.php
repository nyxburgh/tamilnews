<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, Auth, CSRF, Helper};
use App\Models\BusinessAdModel;

class AdSlotController extends Controller
{
    public function middleware(): void
    {
        // /api/ads/* is public — no auth required
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, '/api/ads/')) return;
        $this->requireCan('manage_ads');
    }

    /** GET /admin/ad-defaults — manage default images per slot type */
    public function defaults(): void
    {
        $ads = new BusinessAdModel();
        $this->view('admin.ad_slots.defaults', [
            'pageTitle'        => 'Ad Default Images',
            'squareDefault'     => $ads->getDefaultImage('square'),
            'horizontalDefault' => $ads->getDefaultImage('horizontal'),
            'verticalDefault'   => $ads->getDefaultImage('vertical'),
        ], Auth::role() === 'admin' ? 'admin' : 'editor_portal');
    }

    /** POST /admin/ad-defaults/upload */
    public function uploadDefault(): void
    {
        CSRF::validate();
        $type = $this->post('slot_type', 'square');
        if (!in_array($type, ['square','horizontal','vertical'])) {
            $this->flash('danger','Invalid slot type.'); $this->redirect('/admin/ad-defaults');
        }
        if (empty($_FILES['default_image']['tmp_name'])) {
            $this->flash('danger','No file selected.'); $this->redirect('/admin/ad-defaults');
        }
        $file  = $_FILES['default_image'];
        $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $this->flash('danger','Invalid file type.'); $this->redirect('/admin/ad-defaults');
        }
        $dir  = ROOT_PATH . '/public/uploads/ads/defaults/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fname = $type . '_default.' . $ext;
        move_uploaded_file($file['tmp_name'], $dir . $fname);

        // Save to tn_ad_slots ad_code field as default image path
        $db = \App\Core\Database::getInstance();
        $db->prepare("UPDATE tn_ad_slots SET ad_code = ? WHERE type = ?")
           ->execute(['/uploads/ads/defaults/' . $fname, $type]);

        $this->flash('success', ucfirst($type) . ' default image updated.');
        $this->redirect('/admin/ad-defaults');
    }

    /** GET /api/ads/{type}?category_id=X — return active ads for rotation */
    public function serve(string $type = 'square'): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache');
        header('Access-Control-Allow-Origin: *');
        $ads        = new BusinessAdModel();
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        try {
            $data = $ads->activeForRotation($type, $categoryId);
        } catch (\Exception $e) {
            $data = [];
            error_log('Ad serve error: ' . $e->getMessage());
        }
        echo json_encode(['success' => true, 'ads' => $data]);
        exit;
    }
}
