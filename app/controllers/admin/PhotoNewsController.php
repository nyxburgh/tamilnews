<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, Auth, CSRF, Database, Helper};
use App\Models\ArticleModel;

class PhotoNewsController extends Controller
{
    public function __construct()
    {
        $this->requireRole('admin','chief_editor','editor','reporter');
    }

    protected function layout(): string
    {
        return Auth::role() === 'admin' ? 'admin' : 'portal';
    }

    private function base(): string
    {
        return Auth::role() === 'admin' ? '/admin/photo-news' : '/portal/photo-news';
    }

    // GET /portal/photo-news  or  /admin/photo-news
    public function index(): void
    {
        $db   = Database::getInstance();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = 20;
        $off  = ($page - 1) * $per;

        $total = (int)$db->query(
            "SELECT COUNT(*) FROM tn_articles WHERE status='published'"
        )->fetchColumn();

        $stmt = $db->prepare(
            "SELECT a.id, a.title, a.slug, a.image_url, a.news_card_image,
                    a.published_at, c.name_tamil AS cat
             FROM tn_articles a
             LEFT JOIN tn_categories c ON c.id = a.category_id
             WHERE a.status = 'published'
             ORDER BY a.published_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$per, $off]);
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('admin.photo_news.index', [
            'pageTitle' => 'Photo News — பட செய்திகள்',
            'articles'  => $articles,
            'total'     => $total,
            'page'      => $page,
            'per'       => $per,
            'base'      => $this->base(),
        ], $this->layout());
    }

    // POST /portal/photo-news/upload/{id}
    public function upload(string $id): void
    {
        CSRF::validate();
        $articleId = (int)$id;
        $db = Database::getInstance();

        $file = $_FILES['card_image'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('danger', 'No file uploaded or upload error.');
            $this->redirect($this->base());
        }

        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $this->flash('danger', 'Invalid file type. Use JPG, PNG or WebP.');
            $this->redirect($this->base());
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->flash('danger', 'File too large. Max 5MB.');
            $this->redirect($this->base());
        }

        $dir  = dirname(__DIR__, 3) . '/public/uploads/newscards/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $name = 'card_' . $articleId . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            $this->flash('danger', 'File save failed. Check folder permissions.');
            $this->redirect($this->base());
        }

        // Remove old card if exists
        $existing = $db->prepare("SELECT news_card_image FROM tn_articles WHERE id=? LIMIT 1");
        $existing->execute([$articleId]);
        $old = $existing->fetchColumn();
        if ($old) {
            @unlink(dirname(__DIR__, 3) . '/public' . $old);
        }

        $db->prepare("UPDATE tn_articles SET news_card_image = ? WHERE id = ?")
           ->execute(['/uploads/newscards/' . $name, $articleId]);

        $this->flash('success', 'News card image uploaded successfully.');
        $this->redirect($this->base());
    }

    // POST /portal/photo-news/remove/{id}
    public function remove(string $id): void
    {
        CSRF::validate();
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT news_card_image FROM tn_articles WHERE id=? LIMIT 1");
        $stmt->execute([(int)$id]);
        $path = $stmt->fetchColumn();
        if ($path) @unlink(dirname(__DIR__, 3) . '/public' . $path);
        $db->prepare("UPDATE tn_articles SET news_card_image = NULL WHERE id = ?")
           ->execute([(int)$id]);
        $this->flash('success', 'Card image removed.');
        $this->redirect($this->base());
    }
}
