<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Helper};
use App\Models\TagModel;

class TagController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private TagModel $tags;
    public function middleware(): void { $this->requireRole('admin'); }
    public function __construct() { $this->tags = new TagModel(); }

    public function index(): void
    {
        $page   = max(1, (int)$this->get('page', 1));
        $result = $this->tags->paginate($page, 30, '', [], 'usage_count', 'DESC');
        $this->view('admin.tags.index', ['pageTitle'=>'Tags','tags'=>$result['data'],'total'=>$result['total'],'page'=>$result['page'],'per_page'=>$result['per_page']], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $name = Helper::sanitize($this->post('name',''));
        $slug = Helper::uniqueSlug('tn_tags', Helper::slug($name));
        $this->tags->insert(['name'=>$name,'name_tamil'=>$this->post('name_tamil','')?: null,'slug'=>$slug]);
        $this->flash('success','Tag created.');
        $this->redirect('/admin/tags');
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $name = Helper::sanitize($this->post('name',''));
        $slug = Helper::uniqueSlug('tn_tags', Helper::slug($name), (int)$id);
        $this->tags->update((int)$id, ['name'=>$name,'name_tamil'=>$this->post('name_tamil','')?: null,'slug'=>$slug]);
        $this->flash('success','Tag updated.');
        $this->redirect('/admin/tags');
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->tags->delete((int)$id);
        $this->flash('success','Tag deleted.');
        $this->redirect('/admin/tags');
    }

    public function suggest(): void
    {
        $q    = $this->get('q','');
        $tags = $this->tags->suggest($q);
        $this->json($tags);
    }
}
