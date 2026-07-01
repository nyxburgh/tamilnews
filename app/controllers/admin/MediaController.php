<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Auth};
use App\Models\MediaModel;

class MediaController extends Controller
{
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
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
        header('Content-Type: application/json');
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!\App\Core\CSRF::verify($token)) {
            echo json_encode(['success'=>false,'error'=>'Session expired. Refresh the page.']);
            exit;
        }
        if (empty($_FILES['file'])) { echo json_encode(['success'=>false,'error'=>'No file uploaded']); exit; }
        $id = $this->media->upload($_FILES['file'], Auth::id());
        if (!$id) { echo json_encode(['success'=>false,'error'=>'Upload failed — invalid type or size exceeded']); exit; }
        $file = $this->media->find($id);
        echo json_encode(['success'=>true,'media'=>$file]);
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

    /** POST /admin/media/update/{id} — edit alt text / folder */
    public function update(string $id): void
    {
        \App\Core\CSRF::validate();
        $mediaId = (int)$id;
        $file    = $this->media->find($mediaId);
        if (!$file) {
            if (\App\Core\Helper::isAjax()) { $this->json(['success'=>false,'error'=>'Not found']); return; }
            $this->flash('danger','File not found.'); $this->redirect('/admin/media');
        }

        $data = [
            'alt_text' => \App\Core\Helper::sanitize($this->post('alt_text','')),
        ];
        $folder = $this->post('folder', '');
        if ($folder !== '') $data['folder'] = \App\Core\Helper::sanitize($folder);

        $this->media->update($mediaId, $data);

        if (\App\Core\Helper::isAjax()) {
            $this->json(['success'=>true, 'media'=>$this->media->find($mediaId)]);
            return;
        }
        $this->flash('success','Media updated.');
        $this->redirect('/admin/media');
    }


    /** POST /admin/media/upload-ajax — returns JSON with url for article form */
    public function uploadAjax(): void
    {
        header('Content-Type: application/json');
        // Verify CSRF token from POST or header
        // Accept _token (standard field) or csrf_token (JS sends this)
        $token = $_POST['_token'] ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!\App\Core\CSRF::verify($token)) {
            echo json_encode(['success'=>false,'error'=>'Session expired. Refresh the page and try again.']);
            exit;
        }
        try {
            $fileKey = !empty($_FILES['image']) ? 'image' : (!empty($_FILES['file']) ? 'file' : null);
            if (!$fileKey) { echo json_encode(['success'=>false,'error'=>'No file received.']); exit; }
            $id = $this->media->upload($_FILES[$fileKey], Auth::id());
            if (!$id) { echo json_encode(['success'=>false,'error'=>'Upload failed. Check file type (JPG/PNG/WebP) and size (max 5MB).']); exit; }
            $file = $this->media->find($id);
            $fp   = ltrim($file['filepath'] ?? '', '/');
            $url  = rtrim(ASSET_URL, '/') . '/public/' . $fp;
            echo json_encode(['success'=>true,'media_id'=>$id,'url'=>$url]);
        } catch (\Exception $e) {
            error_log('Upload error: '.$e->getMessage());
            echo json_encode(['success'=>false,'error'=>'Server error: '.$e->getMessage()]);
        }
        exit;
    }

}