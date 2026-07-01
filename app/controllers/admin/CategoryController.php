<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Helper, Auth};
use App\Models\CategoryModel;

class CategoryController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private CategoryModel $cats;
    public function middleware(): void { $this->requireCan('manage_categories'); }
    public function __construct() { $this->cats = new CategoryModel(); }

    public function index(): void
    {
        $this->view('admin.categories.index', [
            'pageTitle'  => 'Categories',
            'categories' => $this->cats->allWithParent(),
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $name  = Helper::sanitize($this->post('name', ''));
        $slug  = Helper::uniqueSlug('tn_categories', $this->post('slug') ?: Helper::slug($name));
        $this->cats->insert([
            'parent_id'  => (int)$this->post('parent_id', 0) ?: null,
            'name'       => $name,
            'name_tamil' => $this->post('name_tamil', '') ?: null,
            'slug'       => $slug,
            'description'=> $this->post('description', '') ?: null,
            'is_active'  => 1,
        ]);
        $this->flash('success', 'Category created.');
        $this->redirect('/admin/categories');
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $name = Helper::sanitize($this->post('name', ''));
        $slug = Helper::uniqueSlug('tn_categories', $this->post('slug') ?: Helper::slug($name), (int)$id);
        $this->cats->update((int)$id, [
            'parent_id'  => (int)$this->post('parent_id', 0) ?: null,
            'name'       => $name,
            'name_tamil' => $this->post('name_tamil', '') ?: null,
            'slug'       => $slug,
            'description'=> $this->post('description', '') ?: null,
            'is_active'  => (int)(bool)$this->post('is_active', 1),
        ]);
        $this->flash('success', 'Category updated.');
        $this->redirect('/admin/categories');
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        try {
            $this->cats->delete((int)$id);
            $this->flash('success', 'Category deleted.');
        } catch (\PDOException $e) {
            $this->flash('danger', 'Cannot delete — this category still has articles linked to it. Move or delete those articles first.');
        }
        $this->redirect('/admin/categories');
    }

    public function toggleActive(string $id): void
    {
        CSRF::validate();
        $cat = $this->cats->find((int)$id);
        if ($cat) {
            $this->cats->update((int)$id, ['is_active' => $cat['is_active'] ? 0 : 1]);
        }
        $this->flash('success', 'Status updated.');
        $this->redirect('/admin/categories');
    }

    public function sort(): void
    {
        CSRF::validate();
        $ids = array_map('intval', $this->post('ids', []));
        $this->cats->updateSort($ids);
        $this->json(['success' => true]);
    }
}
