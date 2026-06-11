<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, CSRF, Helper, Auth};
use App\Models\{LiveBlogModel, ArticleModel};

class LiveBlogController extends Controller
{
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    private LiveBlogModel $model;
    public function middleware(): void { $this->requireRole('admin', 'editor'); }
    public function __construct() { $this->model = new LiveBlogModel(); }

    public function index(): void
    {
        $this->view('admin.live_blog.index', [
            'pageTitle' => 'Live Blog',
            'blogs'     => $this->model->allWithStats(),
        ], $this->layout());
    }

    public function create(): void
    {
        $this->view('admin.live_blog.form', [
            'pageTitle' => 'Create Live Blog',
            'blog'      => [],
            'entries'   => [],
            'isEdit'    => false,
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $title = Helper::sanitize($this->post('title', ''));
        $slug  = Helper::uniqueSlug('tn_live_blogs', $this->post('slug', '') ?: Helper::slug($title));
        $id    = $this->model->insert([
            'user_id'     => Auth::id(),
            'article_id'  => (int)$this->post('article_id', 0) ?: null,
            'title'       => $title,
            'slug'        => $slug,
            'description' => $this->post('description', '') ?: null,
            'type'        => $this->post('type', 'general'),
            'team_home'   => $this->post('team_home', '') ?: null,
            'team_away'   => $this->post('team_away', '') ?: null,
            'status'      => 'active',
        ]);
        $this->flash('success', 'Live blog created and is now ACTIVE.');
        $this->redirect('/admin/live-blog/manage/' . $id);
    }

    public function manage(string $id): void
    {
        $blog = $this->model->findWithEntries((int)$id);
        if (!$blog) { $this->flash('danger', 'Not found.'); $this->redirect('/admin/live-blog'); }
        $this->view('admin.live_blog.manage', [
            'pageTitle' => '🔴 LIVE: ' . $blog['title'],
            'blog'      => $blog,
            'entries'   => $blog['entries'],
        ], $this->layout());
    }

    public function postEntry(string $id): void
    {
        CSRF::validate();
        $content = trim($this->post('content', ''));
        if (!$content) { $this->flash('danger', 'Entry cannot be empty.'); $this->redirect('/admin/live-blog/manage/' . $id); }

        $this->model->addEntry([
            'live_blog_id' => (int)$id,
            'user_id'      => Auth::id(),
            'content'      => $content,
            'label'        => $this->post('label', '') ?: null,
            'label_color'  => $this->post('label_color', '#C0001A'),
            'score_home'   => $this->post('score_home', '') ?: null,
            'score_away'   => $this->post('score_away', '') ?: null,
            'is_pinned'    => (int)(bool)$this->post('is_pinned', 0),
        ]);

        if (Helper::isAjax()) {
            $entries = $this->model->entries((int)$id, 0, 1);
            $this->json(['success' => true, 'entry' => $entries[0] ?? null]);
        }
        $this->redirect('/admin/live-blog/manage/' . $id);
    }

    public function deleteEntry(string $id): void
    {
        CSRF::validate();
        $entryId = (int)$this->post('entry_id', 0);
        $this->model->deleteEntry($entryId);
        if (Helper::isAjax()) { $this->json(['success' => true]); }
        $this->redirect('/admin/live-blog/manage/' . $id);
    }

    public function end(string $id): void
    {
        CSRF::validate();
        $this->model->setStatus((int)$id, 'ended');
        $this->flash('success', 'Live blog ended.');
        $this->redirect('/admin/live-blog');
    }

    public function reactivate(string $id): void
    {
        CSRF::validate();
        $this->model->setStatus((int)$id, 'active');
        $this->flash('success', 'Live blog reactivated.');
        $this->redirect('/admin/live-blog/manage/' . $id);
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->model->delete((int)$id);
        $this->flash('success', 'Live blog deleted.');
        $this->redirect('/admin/live-blog');
    }

    // AJAX polling endpoint — returns new entries after given ID
    public function poll(string $id): void
    {
        $afterId = (int)($_GET['after'] ?? 0);
        $entries = $this->model->entries((int)$id, $afterId, 50);
        $blog    = $this->model->find((int)$id);
        $this->json([
            'entries' => $entries,
            'status'  => $blog['status'] ?? 'ended',
            'latest'  => $this->model->latestEntryId((int)$id),
        ]);
    }

    // External API post — for cricket/election integrations
    public function apiPost(string $id): void
    {
        // Simple API key check from settings
        $apiKey  = (new \App\Models\SettingModel())->getValue('live_blog_api_key', '');
        $reqKey  = $_SERVER['HTTP_X_API_KEY'] ?? $_POST['api_key'] ?? '';

        if ($apiKey && $reqKey !== $apiKey) {
            $this->json(['error' => 'Invalid API key'], 401);
        }

        $content = trim($_POST['content'] ?? '');
        if (!$content) { $this->json(['error' => 'content required'], 422); }

        $entryId = $this->model->addEntry([
            'live_blog_id' => (int)$id,
            'user_id'      => 1, // system
            'content'      => $content,
            'label'        => $_POST['label']       ?? null,
            'label_color'  => $_POST['label_color'] ?? '#C0001A',
            'score_home'   => $_POST['score_home']  ?? null,
            'score_away'   => $_POST['score_away']  ?? null,
            'image_url'    => $_POST['image_url']   ?? null,
            'youtube_url'  => $_POST['youtube_url'] ?? null,
            'is_pinned'    => (int)(bool)($_POST['is_pinned'] ?? 0),
        ]);

        $this->json(['success' => true, 'entry_id' => $entryId]);
    }

}