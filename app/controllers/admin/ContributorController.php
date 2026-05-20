<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, CSRF, Helper};
use App\Models\{ContributorModel, ArticleModel, CategoryModel};

class ContributorController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private ContributorModel $contributors;

    public function middleware(): void { $this->requireRole('admin'); }

    public function __construct()
    {
        $this->contributors = new ContributorModel();
    }

    // List all contributors
    public function index(): void
    {
        $page   = max(1, (int)($this->get('page', 1)));
        $result = $this->contributors->allWithStats($page, 20);

        $this->view('admin.contributors.index', [
            'pageTitle'    => 'Contributors',
            'contributors' => $result['data'],
            'total'        => $result['total'],
            'page'         => $result['page'],
            'per_page'     => $result['per_page'],
            'pending'      => $this->contributors->pendingApprovalCount(),
        ], $this->layout());
    }

    // View one contributor + their articles
    public function show(string $id): void
    {
        $contributor = $this->contributors->findFull((int)$id);
        if (!$contributor) {
            $this->flash('danger', 'Contributor not found.');
            $this->redirect('/admin/contributors');
        }

        $articles    = new ArticleModel();
        $page        = max(1, (int)($this->get('page', 1)));
        $status      = $this->get('status', '');
        $result      = $articles->byContributor((int)$id, $page, 15, $status);
        $categories  = $this->contributors->assignedCategories((int)$id);

        $this->view('admin.contributors.show', [
            'pageTitle'   => $contributor['name'] . ' — Contributor',
            'contributor' => $contributor,
            'articles'    => $result['data'],
            'total'       => $result['total'],
            'page'        => $result['page'],
            'per_page'    => $result['per_page'],
            'status'      => $status,
            'categories'  => $categories,
        ], $this->layout());
    }

    // Approve / reject / toggle active
    public function approve(string $id): void
    {
        CSRF::validate();
        $this->contributors->update((int)$id, ['is_active' => 1]);
        $this->flash('success', 'Contributor approved.');
        $this->redirect('/admin/contributors');
    }

    public function reject(string $id): void
    {
        CSRF::validate();
        $this->contributors->update((int)$id, ['is_active' => 0]);
        $this->flash('success', 'Contributor deactivated.');
        $this->redirect('/admin/contributors');
    }

    // Update assigned categories
    public function updateCategories(string $id): void
    {
        CSRF::validate();
        $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);
        $this->contributors->syncCategories((int)$id, $categoryIds);
        $this->flash('success', 'Categories updated.');
        $this->redirect('/admin/contributors/show/' . $id);
    }

    // Delete contributor
    public function delete(string $id): void
    {
        CSRF::validate();
        $this->contributors->delete((int)$id);
        $this->flash('success', 'Contributor removed.');
        $this->redirect('/admin/contributors');
    }

    // Add contributor manually (by admin, before Google login)
    public function store(): void
    {
        CSRF::validate();
        $email = trim($this->post('email', ''));
        $existing = $this->contributors->findByEmail($email);
        if ($existing) {
            $this->flash('danger', 'Email already registered.');
            $this->redirect('/admin/contributors');
        }

        $id = $this->contributors->insert([
            'google_id'  => null,
            'name'       => Helper::sanitize($this->post('name', '')),
            'email'      => $email,
            'bio'        => $this->post('bio', '') ?: null,
            'is_active'  => 1,
        ]);

        $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);
        if ($categoryIds) {
            $this->contributors->syncCategories($id, $categoryIds);
        }

        $this->flash('success', 'Contributor added. They can now sign in with their Google account.');
        $this->redirect('/admin/contributors');
    }
}
