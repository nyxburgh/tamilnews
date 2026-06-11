<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, CSRF, Helper};
use App\Models\{SpecialCategoryModel, CategoryModel, FrontendArticleModel};

class SpecialCategoryController extends Controller
{
    protected function layout(): string
    {
        return match(\App\Core\Auth::role()) { 'admin' => 'admin', 'chief_editor' => 'editor_portal', default => 'portal' };
    }

    private SpecialCategoryModel $model;
    public function middleware(): void { $this->requireRole('admin','editor'); }
    public function __construct() { $this->model = new SpecialCategoryModel(); }

    public function index(): void
    {
        $this->view('admin.special_categories.index', [
            'pageTitle'   => 'Special Categories',
            'specials'    => $this->model->allSpecial(),
            'categories'  => (new CategoryModel())->topLevel(),
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $name  = Helper::sanitize($this->post('name',''));
        $slug  = Helper::uniqueSlug('tn_special_categories', $this->post('slug','') ?: Helper::slug($name));
        $this->model->insert([
            'name'         => $name,
            'name_tamil'   => $this->post('name_tamil','') ?: null,
            'slug'         => $slug,
            'type'         => $this->post('type','event'),
            'description'  => $this->post('description','') ?: null,
            'banner_color' => $this->post('banner_color','#C0001A'),
            'banner_icon'  => $this->post('banner_icon','🔴') ?: null,
            'category_id'  => (int)$this->post('category_id',0) ?: null,
            'is_active'    => (int)(bool)$this->post('is_active',1),
            'starts_at'    => $this->post('starts_at','') ?: null,
            'ends_at'      => $this->post('ends_at','') ?: null,
        ]);
        $this->flash('success','Special category created.');
        $this->redirect('/admin/special-categories');
    }

    public function edit(string $id): void
    {
        $special = $this->model->find((int)$id);
        if (!$special) { $this->flash('danger','Not found.'); $this->redirect('/admin/special-categories'); }
        $page    = max(1,(int)$this->get('page',1));
        $result  = $this->model->articlesForSpecial((int)$id, $page, 15);
        $this->view('admin.special_categories.edit', [
            'pageTitle'  => 'Edit: ' . $special['name'],
            'special'    => $special,
            'articles'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'per_page'   => $result['per_page'],
            'categories' => (new CategoryModel())->topLevel(),
        ], $this->layout());
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $name = Helper::sanitize($this->post('name',''));
        $this->model->update((int)$id, [
            'name'         => $name,
            'name_tamil'   => $this->post('name_tamil','') ?: null,
            'type'         => $this->post('type','event'),
            'description'  => $this->post('description','') ?: null,
            'banner_color' => $this->post('banner_color','#C0001A'),
            'banner_icon'  => $this->post('banner_icon','') ?: null,
            'category_id'  => (int)$this->post('category_id',0) ?: null,
            'is_active'    => (int)(bool)$this->post('is_active',0),
            'starts_at'    => $this->post('starts_at','') ?: null,
            'ends_at'      => $this->post('ends_at','') ?: null,
        ]);
        $this->flash('success','Updated.');
        $this->redirect('/admin/special-categories/edit/'.$id);
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->model->delete((int)$id);
        $this->flash('success','Deleted.');
        $this->redirect('/admin/special-categories');
    }

    public function addArticle(string $id): void
    {
        CSRF::validate();
        $articleId = (int)$this->post('article_id',0);
        if ($articleId) {
            $this->model->syncArticle((int)$id, $articleId);
            $this->flash('success','Article added to special category.');
        }
        $this->redirect('/admin/special-categories/edit/'.$id);
    }

    public function removeArticle(string $id): void
    {
        CSRF::validate();
        $articleId = (int)$this->post('article_id',0);
        $this->model->removeArticle((int)$id, $articleId);
        if (Helper::isAjax()) { $this->json(['success'=>true]); }
        $this->redirect('/admin/special-categories/edit/'.$id);
    }
}
