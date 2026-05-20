<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, CSRF, Auth, Helper};
use App\Models\NewspaperModel;

class NewspaperController extends Controller
{
    private NewspaperModel $model;

    public function middleware(): void
    {
        // Only chief_editor and admin can manage newspapers
        $this->requireRole('admin', 'chief_editor');
    }

    public function __construct()
    {
        $this->model = new NewspaperModel();
    }

    protected function layout(): string
    {
        return match(Auth::role()) {
            'admin'        => 'admin',
            'chief_editor' => 'editor_portal',
            default        => 'portal',
        };
    }

    public function index(): void
    {
        $page   = max(1, (int)$this->get('page', 1));
        $result = $this->model->allForAdmin($page, 15);

        $this->view('admin.newspaper.index', [
            'pageTitle' => 'Newspaper Archive',
            'papers'    => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'per_page'  => $result['per_page'],
        ], $this->layout());
    }

    public function upload(): void
    {
        CSRF::validate();

        if (empty($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('danger', 'Please select a valid PDF file.');
            $this->redirect('/admin/newspaper');
        }

        if ($_FILES['pdf']['size'] > 50 * 1024 * 1024) {
            $this->flash('danger', 'PDF too large. Maximum 50MB allowed.');
            $this->redirect('/admin/newspaper');
        }

        $date  = $this->post('edition_date', date('Y-m-d'));
        $title = Helper::sanitize($this->post('title', ''));
        if (!$title) {
            $title = 'Tamil News — ' . date('d M Y', strtotime($date));
        }

        $id = $this->model->upload(
            $_FILES['pdf'],
            $title,
            $this->post('title_tamil', ''),
            $date,
            $this->post('edition_type', 'daily'),
            Auth::id()
        );

        if (!$id) {
            $this->flash('danger', 'Upload failed. Ensure file is a valid PDF.');
            $this->redirect('/admin/newspaper');
        }

        $this->flash('success', 'Newspaper uploaded successfully.');
        $this->redirect('/admin/newspaper');
    }

    public function toggle(string $id): void
    {
        CSRF::validate();
        $paper = $this->model->find((int)$id);
        if ($paper) {
            $this->model->update((int)$id, ['is_active' => $paper['is_active'] ? 0 : 1]);
            $this->flash('success', $paper['is_active'] ? 'Paper hidden from public.' : 'Paper made public.');
        }
        $this->redirect('/admin/newspaper');
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->model->deleteNewspaper((int)$id);
        $this->flash('success', 'Newspaper deleted.');
        $this->redirect('/admin/newspaper');
    }
}
