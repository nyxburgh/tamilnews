<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth, CSRF, Helper};
use App\Models\AdPackageModel;

class PackageController extends Controller
{
    private AdPackageModel $model;
    public function middleware(): void { $this->requireCan('manage_packages'); }
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    public function __construct() { $this->model = new AdPackageModel(); }

    public function index(): void
    {
        $this->view('admin.packages.index', [
            'pageTitle' => 'Ad Packages',
            'packages'  => $this->model->allPackages(),
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $data = [
            'name'          => Helper::sanitize($this->post('name','')),
            'name_tamil'    => Helper::sanitize($this->post('name_tamil','')),
            'type'          => $this->post('type','paid_ad'),
            'description'   => Helper::sanitize($this->post('description','')),
            'price_inr'     => (float)$this->post('price_inr',0),
            'duration_days' => (int)$this->post('duration_days',30),
            'max_images'    => (int)$this->post('max_images',5),
            'includes_news' => (int)$this->post('includes_news',0),
            'includes_video'=> (int)$this->post('includes_video',0),
            'is_active'     => 1,
            'sort_order'    => (int)$this->post('sort_order',99),
        ];
        $qr = $this->handleQrUpload();
        if ($qr) $data['qr_code_path'] = $qr;
        $this->model->insert($data);
        $this->flash('success','Package created.');
        $this->redirect('/admin/packages');
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $data = [
            'name'          => Helper::sanitize($this->post('name','')),
            'name_tamil'    => Helper::sanitize($this->post('name_tamil','')),
            'price_inr'     => (float)$this->post('price_inr',0),
            'duration_days' => (int)$this->post('duration_days',30),
            'max_images'    => (int)$this->post('max_images',5),
            'is_active'     => (int)$this->post('is_active',1),
        ];
        $qr = $this->handleQrUpload();
        if ($qr) $data['qr_code_path'] = $qr;
        $this->model->update((int)$id, $data);
        $this->flash('success','Package updated.');
        $this->redirect('/admin/packages');
    }

    /** Handle optional QR code image upload, returns relative path or null */
    private function handleQrUpload(): ?string
    {
        if (empty($_FILES['qr_code']['name'])) return null;
        $ext = strtolower(pathinfo($_FILES['qr_code']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) return null;
        $dir = dirname(__DIR__, 3) . '/public/uploads/qr/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $filename = 'qr_' . uniqid() . '.' . $ext;
        if (!move_uploaded_file($_FILES['qr_code']['tmp_name'], $dir . $filename)) return null;
        return '/uploads/qr/' . $filename;
    }
}
