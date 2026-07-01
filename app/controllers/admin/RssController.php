<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Helper};
use App\Models\{RssModel, CategoryModel};

class RssController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private RssModel      $rss;
    private CategoryModel $cats;
    public function middleware(): void { $this->requireCan('manage_rss'); }
    public function __construct() { $this->rss = new RssModel(); $this->cats = new CategoryModel(); }

    public function index(): void
    {
        $this->view('admin.rss.index', ['pageTitle'=>'RSS Feeds','feeds'=>$this->rss->allFeeds(),'categories'=>$this->cats->topLevel()], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $this->rss->insert(['name'=>Helper::sanitize($this->post('name','')),'url'=>trim($this->post('url','')),'category_id'=>(int)$this->post('category_id',1),'fetch_interval'=>(int)$this->post('fetch_interval',30),'is_active'=>1]);
        $this->flash('success','RSS feed added.'); $this->redirect('/admin/rss');
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $this->rss->update((int)$id, ['name'=>Helper::sanitize($this->post('name','')),'url'=>trim($this->post('url','')),'category_id'=>(int)$this->post('category_id',1),'fetch_interval'=>(int)$this->post('fetch_interval',30),'is_active'=>(int)(bool)$this->post('is_active',1)]);
        $this->flash('success','Feed updated.'); $this->redirect('/admin/rss');
    }

    public function delete(string $id): void
    {
        CSRF::validate(); $this->rss->delete((int)$id);
        $this->flash('success','Feed deleted.'); $this->redirect('/admin/rss');
    }

    public function imports(): void
    {
        $page   = max(1,(int)$this->get('page',1));
        $status = $this->get('status','pending');
        $result = $this->rss->imports($page, 20, $status);
        $this->view('admin.rss.imports', ['pageTitle'=>'RSS Queue','imports'=>$result['data'],'total'=>$result['total'],'page'=>$result['page'],'per_page'=>$result['per_page'],'status'=>$status], $this->layout());
    }

    public function publish(string $id): void
    {
        CSRF::validate();
        $import = $this->rss->fetchOne("SELECT * FROM tn_rss_imports WHERE id=?",[(int)$id]);
        if ($import) { $this->rss->updateImportStatus((int)$id,'imported'); }
        $this->flash('success','Marked for publishing.'); $this->redirect('/admin/rss/imports');
    }

    public function skip(string $id): void
    {
        CSRF::validate();
        $this->rss->updateImportStatus((int)$id,'skipped');
        $this->flash('success','Entry skipped.'); $this->redirect('/admin/rss/imports');
    }
}
