<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Helper};
use App\Models\TagModel;

class TagController extends Controller
{
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    private TagModel $tags;
    public function middleware(): void {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, '/tags/suggest') || str_contains($uri, '/tags/quick-create')) {
            $this->requireAuth(); return;
        }
        $this->requireRole('admin');
    }
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

    public function quickCreate(): void
    {
        header('Content-Type: application/json');
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CSRF::verify($token)) {
            echo json_encode(['error' => 'Session expired. Please refresh the page.']); exit;
        }
        $name  = Helper::sanitize($this->post('name',''));
        $tamil = Helper::sanitize($this->post('name_tamil',''));
        if (!$name && !$tamil) { $this->json(['error'=>'Name required']); return; }
        $label = $name ?: $tamil;
        $slug  = Helper::uniqueSlug('tn_tags', Helper::slug($label));
        $id = $this->tags->insert(['name'=>$name ?: $tamil,'name_tamil'=>$tamil ?: null,'slug'=>$slug]);
        $this->json(['success'=>true,'id'=>$id,'name'=>$name ?: $tamil,'name_tamil'=>$tamil]);
    }

    public function suggest(): void
    {
        $q    = $this->get('q','');
        $tags = $this->tags->suggest($q);
        $this->json($tags);
    }
}
