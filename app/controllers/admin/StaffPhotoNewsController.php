<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, Auth, CSRF, Helper};
use App\Models\{PhotoNewsModel, TagModel};

class StaffPhotoNewsController extends Controller
{
    private PhotoNewsModel $model;
    private TagModel $tags;
    private string $base;
    private string $layout;

    public function __construct()
    {
        $this->requireRole('admin','chief_editor','editor','reporter');
        $this->model  = new PhotoNewsModel();
        $this->tags   = new TagModel();
        $role         = Auth::role();
        $this->layout = $role === 'admin' ? 'admin' : 'portal';
        $this->base   = $role === 'admin' ? '/admin/photo-news' : '/portal/photo-news';
    }

    // GET list
    public function index(): void
    {
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;

        // Reporters see only their own submissions
        $userId = (Auth::role() === 'reporter') ? Auth::id() : null;

        $items = $this->model->all($limit, ($page - 1) * $limit, $userId);
        $total = $this->model->count($userId);

        foreach ($items as &$item) {
            $item['tags'] = $this->model->tags($item['id']);
        }

        $this->view('admin.staff_photo_news.index', [
            'pageTitle' => 'Photo News — பட செய்திகள்',
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'limit'     => $limit,
            'base'      => $this->base,
        ], $this->layout);
    }

    // GET create form
    public function create(): void
    {
        $item = [];
        $tags = [];

        // Prefill from article if article_id provided
        $articleId = (int)($_GET['article_id'] ?? 0);
        if ($articleId) {
            $articleModel = new \App\Models\ArticleModel();
            $article = $articleModel->find($articleId);
            if ($article) {
                $item = [
                    'title' => $article['title'],
                    'slug'  => $article['slug'],
                ];
                $tagModel = new \App\Models\TagModel();
                $tags = $tagModel->forArticle($articleId);
            }
        }

        $this->view('admin.staff_photo_news.form', [
            'pageTitle'  => 'Add Photo News',
            'item'       => $item,
            'tags'       => $tags,
            'allTags'    => $this->tags->all('name_tamil'),
            'base'       => $this->base,
            'isEdit'     => false,
            'articleId'  => $articleId,
        ], $this->layout);
    }

    // POST store
    public function store(): void
    {
        CSRF::validate();
        $title = trim($_POST['title'] ?? '');
        if (!$title) { $this->flash('danger','Title required.'); $this->redirect($this->base.'/create'); }

        $slug = $this->model->uniqueSlug(
            $_POST['slug'] ?: Helper::slug($title)
        );

        $role     = Auth::role();
        $approved = in_array($role, ['admin','chief_editor','editor']);

        // Reporters/contributors can never self-publish — force draft + pending
        $status = $approved ? ($_POST['status'] ?? 'published') : 'draft';

        $id = $this->model->store([
            'title'           => $title,
            'slug'            => $slug,
            'status'          => $status,
            'approval_status' => $approved ? 'approved' : 'pending',
            'created_by'      => Auth::id(),
        ]);

        if (!$approved) {
            (new \App\Models\NotificationModel())->notifyChiefEditors(
                'photo_news_submit',
                Auth::user()['name'] . ' submitted a photo news for review: "' . $title . '"',
                $id,
                Auth::id()
            );
        }

        $this->handleImage($id);

        if (!empty($_POST['tag_ids'])) {
            $this->model->syncTags($id, array_map('intval', (array)$_POST['tag_ids']));
        }

        // Link back to article if created from article side
        $articleId = (int)($_POST['article_id'] ?? 0);
        if ($articleId) {
            $this->model->linkArticle($id, $articleId);
        }

        $this->flash('success','Photo news created.');
        $this->redirect($this->base);
    }

    // GET edit
    public function edit(string $id): void
    {
        $item = $this->model->find((int)$id);
        if (!$item) { $this->redirect($this->base); }

        $this->view('admin.staff_photo_news.form', [
            'pageTitle' => 'Edit Photo News',
            'item'      => $item,
            'tags'      => $this->model->tags((int)$id),
            'allTags'   => $this->tags->all('name_tamil'),
            'base'      => $this->base,
            'isEdit'    => true,
        ], $this->layout);
    }

    // POST update
    public function update(string $id): void
    {
        CSRF::validate();
        $item = $this->model->find((int)$id);
        if (!$item) { $this->redirect($this->base); }

        $role     = Auth::role();
        $approved = in_array($role, ['admin','chief_editor','editor']);

        $title = trim($_POST['title'] ?? '');
        $slug  = $this->model->uniqueSlug(
            $_POST['slug'] ?: Helper::slug($title), (int)$id
        );

        $status = $approved ? ($_POST['status'] ?? 'published') : 'draft';

        $this->model->update((int)$id, [
            'title'           => $title,
            'slug'            => $slug,
            'status'          => $status,
            'approval_status' => $approved ? 'approved' : 'pending',
        ]);

        $this->handleImage((int)$id);

        $tagIds = !empty($_POST['tag_ids']) ? array_map('intval', (array)$_POST['tag_ids']) : [];
        $this->model->syncTags((int)$id, $tagIds);

        if (!$approved) {
            (new \App\Models\NotificationModel())->notifyChiefEditors(
                'photo_news_resubmit',
                Auth::user()['name'] . ' resubmitted a photo news for review: "' . $title . '"',
                (int)$id,
                Auth::id()
            );
        }

        $this->flash('success','Updated.');
        $this->redirect($this->base);
    }

    // POST delete
    public function delete(string $id): void
    {
        CSRF::validate();
        $item = $this->model->find((int)$id);
        if ($item && $item['image_path']) {
            @unlink(dirname(__DIR__,3).'/public'.$item['image_path']);
        }
        $this->model->delete((int)$id);
        $this->flash('success','Deleted.');
        $this->redirect($this->base);
    }

    // GET — prefill article form from photo news
    public function toArticle(string $id): void
    {
        $item = $this->model->find((int)$id);
        if (!$item) { $this->redirect($this->base); }
        $writeUrl = Auth::role() === 'admin' ? '/admin/articles/create' : '/portal/write';
        $this->redirect($writeUrl . '?pn_id=' . (int)$id);
    }

    // GET — picker to connect this photo news to an already-existing article
    public function connect(string $id): void
    {
        $item = $this->model->find((int)$id);
        if (!$item) { $this->redirect($this->base); }

        $this->view('admin.staff_photo_news.connect', [
            'pageTitle' => 'Connect Article — ' . $item['title'],
            'item'      => $item,
            'base'      => $this->base,
        ], $this->layout);
    }

    // POST — save the connection
    public function connectSubmit(string $id): void
    {
        CSRF::validate();
        $articleId = (int)$this->post('article_id', 0);
        if (!$articleId) {
            $this->flash('danger', 'Please select an article.');
            $this->redirect($this->base . '/connect/' . $id);
        }
        $this->model->linkArticle((int)$id, $articleId);
        $this->flash('success', 'Connected to article.');
        $this->redirect($this->base);
    }

    // GET — search unlinked photo news (AJAX, used by article-list connect picker)
    public function suggestUnlinked(): void
    {
        $q = trim($this->get('q', ''));
        if (mb_strlen($q) < 1) { $this->json([]); return; }
        $this->json($this->model->suggestUnlinked($q));
    }

    // GET — picker shown from the article list to connect an existing photo news
    public function connectFromArticle(string $articleId): void
    {
        $this->view('admin.staff_photo_news.connect_from_article', [
            'pageTitle' => 'Connect Photo News',
            'articleId' => (int)$articleId,
            'base'      => $this->base,
        ], $this->layout);
    }

    // POST — save the connection from the article side
    public function connectFromArticleSubmit(string $articleId): void
    {
        CSRF::validate();
        $pnId = (int)$this->post('photo_news_id', 0);
        if (!$pnId) {
            $this->flash('danger', 'Please select a photo news item.');
            $this->redirect($this->base . '/connect-from-article/' . $articleId);
        }
        $this->model->linkArticle($pnId, (int)$articleId);
        $this->flash('success', 'Connected to photo news.');
        $artBase = Auth::role() === 'admin' ? '/admin/articles' : '/portal/all-articles';
        $this->redirect($artBase);
    }

    // POST approve — admin / chief_editor / editor only
    public function approve(string $id): void
    {
        CSRF::validate();
        if (!in_array(Auth::role(), ['admin','chief_editor','editor'])) {
            $this->flash('danger','Permission denied.'); $this->redirect($this->base);
        }
        $item = $this->model->find((int)$id);
        if (!$item) { $this->redirect($this->base); }

        $this->model->update((int)$id, [
            'title'           => $item['title'],
            'slug'            => $item['slug'],
            'status'          => 'published',
            'approval_status' => 'approved',
        ]);

        if (!empty($item['created_by'])) {
            (new \App\Models\NotificationModel())->send(
                (int)$item['created_by'],
                'photo_news_approved',
                'Your photo news "'.$item['title'].'" was approved and published.',
                (int)$id,
                Auth::id()
            );
        }

        $this->flash('success','Photo news approved and published.');
        $this->redirect($this->base);
    }

    // POST reject — admin / chief_editor / editor only
    public function reject(string $id): void
    {
        CSRF::validate();
        if (!in_array(Auth::role(), ['admin','chief_editor','editor'])) {
            $this->flash('danger','Permission denied.'); $this->redirect($this->base);
        }
        $item = $this->model->find((int)$id);
        if (!$item) { $this->redirect($this->base); }

        $this->model->update((int)$id, [
            'title'           => $item['title'],
            'slug'            => $item['slug'],
            'status'          => 'draft',
            'approval_status' => 'rejected',
        ]);

        if (!empty($item['created_by'])) {
            (new \App\Models\NotificationModel())->send(
                (int)$item['created_by'],
                'photo_news_rejected',
                'Your photo news "'.$item['title'].'" was rejected. Please edit and resubmit.',
                (int)$id,
                Auth::id()
            );
        }

        $this->flash('success','Photo news rejected.');
        $this->redirect($this->base);
    }

    private function handleImage(int $id): void
    {
        $file = $_FILES['image'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || empty($file['name'])) return;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) return;
        if ($file['size'] > 5 * 1024 * 1024) return;

        $dir = dirname(__DIR__,3).'/public/uploads/photo-news/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        // GD not available — save original file as-is, skip compression
        if (!function_exists('imagecreatetruecolor')) {
            $name = 'pn_'.$id.'_'.time().'.'.$ext;
            if (move_uploaded_file($file['tmp_name'], $dir.$name)) {
                $old = $this->model->find($id);
                if (!empty($old['image_path'])) {
                    @unlink(dirname(__DIR__,3).'/public'.$old['image_path']);
                }
                $this->model->updateImage($id, '/uploads/photo-news/'.$name);
            }
            return;
        }

        $name    = 'pn_'.$id.'_'.time().'.png';
        $outPath = $dir.$name;

        // Load source image via GD
        $src = match($ext) {
            'jpg','jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'png'        => @imagecreatefrompng($file['tmp_name']),
            'webp'       => @imagecreatefromwebp($file['tmp_name']),
            'gif'        => @imagecreatefromgif($file['tmp_name']),
            default      => false
        };
        if (!$src) return;

        // Resize if wider than 900px
        $w = imagesx($src); $h = imagesy($src);
        if ($w > 900) {
            $nh  = (int)round($h * 900 / $w);
            $dst = imagecreatetruecolor(900, $nh);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, 900, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }

        // Save as PNG (compressed level 6)
        imagepng($src, $outPath, 6);
        imagedestroy($src);

        if (file_exists($outPath)) {
            // Remove old image
            $old = $this->model->find($id);
            if (!empty($old['image_path'])) {
                @unlink(dirname(__DIR__,3).'/public'.$old['image_path']);
            }
            $this->model->updateImage($id, '/uploads/photo-news/'.$name);
        }
    }
}
