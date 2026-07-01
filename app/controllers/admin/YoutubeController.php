<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Helper};
use App\Models\{YoutubeModel, CategoryModel};

class YoutubeController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private YoutubeModel  $yt;
    private CategoryModel $cats;
    public function middleware(): void { $this->requireCan('manage_youtube'); }
    public function __construct() { $this->yt = new YoutubeModel(); $this->cats = new CategoryModel(); }

    public function index(): void
    {
        $this->view('admin.youtube.index', [
            'pageTitle'  => 'YouTube Automation',
            'channels'   => $this->yt->allChannels(),
            'categories' => $this->cats->topLevel(),
        ], $this->layout());
    }

    public function storeChannel(): void
    {
        CSRF::validate();
        $this->yt->insert([
            'channel_id'     => trim($this->post('channel_id','')),
            'channel_name'   => Helper::sanitize($this->post('channel_name','')),
            'playlist_id'    => trim($this->post('playlist_id','')) ?: null,
            'category_id'    => (int)$this->post('category_id',1),
            'auto_publish'   => (int)(bool)$this->post('auto_publish',0),
            'fetch_interval' => $this->post('fetch_interval','hourly'),
            'is_active'      => 1,
        ]);
        $this->flash('success','Channel added.'); $this->redirect('/admin/youtube');
    }

    public function updateChannel(string $id): void
    {
        CSRF::validate();
        $this->yt->update((int)$id, [
            'channel_name'   => Helper::sanitize($this->post('channel_name','')),
            'playlist_id'    => trim($this->post('playlist_id','')) ?: null,
            'category_id'    => (int)$this->post('category_id',1),
            'auto_publish'   => (int)(bool)$this->post('auto_publish',0),
            'fetch_interval' => $this->post('fetch_interval','hourly'),
            'is_active'      => (int)(bool)$this->post('is_active',1),
        ]);
        $this->flash('success','Channel updated.'); $this->redirect('/admin/youtube');
    }

    public function deleteChannel(string $id): void
    {
        CSRF::validate(); $this->yt->delete((int)$id);
        $this->flash('success','Channel deleted.'); $this->redirect('/admin/youtube');
    }

    public function storeKeyword(): void
    {
        CSRF::validate();
        $this->yt->addKeyword(['channel_id'=>(int)$this->post('channel_id'),'keyword'=>trim($this->post('keyword','')),'category_id'=>(int)$this->post('category_id')]);
        $this->flash('success','Keyword mapping added.'); $this->redirect('/admin/youtube');
    }

    public function deleteKeyword(string $id): void
    {
        CSRF::validate(); $this->yt->deleteKeyword((int)$id);
        $this->flash('success','Keyword deleted.'); $this->redirect('/admin/youtube');
    }

    public function imports(): void
    {
        $page   = max(1,(int)$this->get('page',1));
        $status = $this->get('status','');
        $result = $this->yt->imports($page, 20, $status);
        $this->view('admin.youtube.imports', ['pageTitle'=>'YouTube Imports','imports'=>$result['data'],'total'=>$result['total'],'page'=>$result['page'],'per_page'=>$result['per_page'],'status'=>$status], $this->layout());
    }

    public function publishImport(string $id): void
    {
        CSRF::validate();
        // Logic handled by cron — manually mark as imported
        $this->yt->query("UPDATE tn_youtube_imports SET status='imported', imported_at=NOW() WHERE id=?", [(int)$id]);
        $this->flash('success','Marked as imported.'); $this->redirect('/admin/youtube/imports');
    }
}
