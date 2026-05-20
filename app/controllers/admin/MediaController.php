<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Auth};
use App\Models\MediaModel;

class MediaController extends Controller
{
    protected function layout(): string
    {
        return match(\App\Core\Auth::role()) { 'admin' => 'admin', 'chief_editor' => 'editor_portal', default => 'portal' };
    }

    private MediaModel $media;
    public function middleware(): void { $this->requireAuth(); }
    public function __construct() { $this->media = new MediaModel(); }

    public function index(): void
    {
        $page   = max(1,(int)$this->get('page',1));
        $search  = $this->get('search','');
        $folder  = $this->get('folder','');
        $folders = [];
        try { $folders = $this->media->allFolders(); } catch (\Exception $e) {}
        $result  = $this->media->allPaginated($page, 24, $search);
        $this->view('admin.media.index', ['pageTitle'=>'Media Library','media'=>$result['data'],'total'=>$result['total'],'page'=>$result['page'],'per_page'=>$result['per_page'],'search'=>$search, 'folder'=>$folder, 'folders'=>$folders], $this->layout());
    }

    public function upload(): void
    {
        CSRF::validate();
        if (empty($_FILES['file'])) { $this->json(['error'=>'No file uploaded'],400); }
        $id = $this->media->upload($_FILES['file'], Auth::id());
        if (!$id) { $this->json(['error'=>'Upload failed — invalid type or size exceeded'],422); }
        $file = $this->media->find($id);
        $this->json(['success'=>true,'media'=>$file]);
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->media->deleteFile((int)$id);
        if (\App\Core\Helper::isAjax()) { $this->json(['success'=>true]); }
        $this->flash('success','File deleted.'); $this->redirect('/admin/media');
    }

    public function modal(): void
    {
        $page   = max(1,(int)$this->get('page',1));
        $search = $this->get('search','');
        $result = $this->media->allPaginated($page, 20, $search);
        $this->view('admin.media.modal', ['media'=>$result['data'],'total'=>$result['total'],'page'=>$result['page'],'per_page'=>$result['per_page'],'search'=>$search], '');
    }

    public function moveFolder(): void
    {
        \App\Core\CSRF::validate();
        $id     = (int)$this->post('id',0);
        $folder = $this->post('folder','general');
        if ($id) $this->media->moveToFolder($id, $folder);
        $this->json(['success'=>true]);
    }

}