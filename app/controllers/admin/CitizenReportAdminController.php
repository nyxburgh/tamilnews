<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, CSRF, Auth, Database};
use App\Models\{CitizenReportModel, ArticleModel, CategoryModel, NotificationModel};
use App\Core\Helper;

class CitizenReportAdminController extends Controller
{
    protected function layout(): string
    {
        $role = Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    private function base(): string
    {
        return \App\Core\Auth::role() === 'admin' ? '/admin/citizen-reports' : '/portal/citizen-reports';
    }

    public function middleware(): void { $this->requireCan('manage_articles'); }

    private CitizenReportModel $model;
    private \PDO $db;

    public function __construct()
    {
        $this->model = new CitizenReportModel();
        $this->db    = Database::getInstance();
    }

    public function index(): void
    {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $result = $this->model->all($page, 20);
        $this->view('admin.citizen_reports.index', [
            'pageTitle' => 'Citizen Reports',
            'reports'   => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'per_page'  => $result['per_page'],
            'pending'   => $this->model->pendingCount(),
        ], $this->layout());
    }

    public function show(string $id): void
    {
        $report = $this->model->find((int)$id);
        if (!$report) { $this->flash('danger','Not found.'); $this->redirect($this->base()); }
        $this->view('admin.citizen_reports.show', [
            'pageTitle' => 'Review Report — ' . $report['title'],
            'report'    => $report,
            'categories'=> (new CategoryModel())->all(),
        ], $this->layout());
    }

    public function approve(string $id): void
    {
        CSRF::validate();
        $report = $this->model->find((int)$id);
        if (!$report) { $this->flash('danger','Not found.'); $this->redirect($this->base()); }

        $catId   = (int)$this->post('category_id', 1);
        $title   = Helper::sanitize($this->post('title', $report['title']));
        $content = Helper::sanitize($this->post('content', $report['content']));
        $slug    = Helper::slug($title);

        $existing = $this->db->prepare("SELECT id FROM tn_articles WHERE slug=? LIMIT 1");
        $existing->execute([$slug]);
        if ($existing->fetch()) $slug .= '-' . time();

        $stmt = $this->db->prepare(
            "INSERT INTO tn_articles
             (user_id, category_id, title, slug, content, district_id,
              content_type, status, published_at, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,NOW(),NOW(),NOW())"
        );
        $stmt->execute([
            Auth::id(),
            $catId,
            $title,
            $slug,
            $content,
            $report['district_id'] ?: null,
            'news',
            'published',
        ]);
        $articleId = (int)$this->db->lastInsertId();

        // Copy citizen image to article if exists
        if (!empty($report['image_path'])) {
            $this->db->prepare("UPDATE tn_articles SET image_url=? WHERE id=?")
                     ->execute([$report['image_path'], $articleId]);
        }

        $this->model->approve((int)$id, Auth::id(), $articleId);
        $this->flash('success', 'Citizen report approved and published as article #' . $articleId . '.');
        $this->redirect($this->base());
    }

    public function reject(string $id): void
    {
        CSRF::validate();
        $reason = Helper::sanitize($this->post('reason', ''));
        $this->model->reject((int)$id, Auth::id(), $reason);
        $this->flash('info', 'Report rejected.');
        $this->redirect($this->base());
    }
}
