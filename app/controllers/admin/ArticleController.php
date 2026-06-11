<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Helper;
use App\Core\CSRF;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Models\LocationModel;
use App\Models\MediaModel;
use App\Models\SettingModel;

class ArticleController extends Controller
{
    // Use portal layout for editor/reporter, admin layout for admin
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    private ArticleModel   $articles;
    private CategoryModel  $categories;
    private TagModel       $tags;
    private LocationModel  $locations;

    public function middleware(): void { $this->requireAuth(); }

    public function __construct()
    {
        $this->articles   = new ArticleModel();
        $this->categories = new CategoryModel();
        $this->tags       = new TagModel();
        $this->locations  = new LocationModel();
    }

    public function index(): void
    {
        $filters = [
            'status'       => $this->get('status', ''),
            'category_id'  => (int)$this->get('category_id', 0) ?: null,
            'content_type' => $this->get('content_type', ''),
            'search'       => $this->get('search', ''),
            'date'         => $this->get('date', ''),
        ];

        // Reporter sees only own articles
        if (Auth::role() === 'reporter') {
            $filters['user_id'] = Auth::id();
        }

        $page    = max(1, (int)$this->get('page', 1));
        $result  = $this->articles->listPaginated(array_filter($filters), $page, 10);

        $this->view('admin.articles.index', [
            'pageTitle'  => 'Articles',
            'articles'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'per_page'   => $result['per_page'],
            'filters'    => $filters,
            'categories' => $this->categories->allWithParent(),
        ], $this->layout());
    }

    public function create(): void
    {
        $this->view('admin.articles.form', [
            'pageTitle'  => 'Create Article',
            'article'    => [],
            'categories' => $this->categories->allWithParent(),
            'cities'     => $this->locations->allCities(),
            'tags'       => [],
            'isEdit'     => false,
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $data = $this->buildArticleData();
        if (empty($data['category_id'])) { $this->flash('danger','Please select a category.'); $this->redirect('/admin/articles/create'); }
        $id = $this->articles->store($data);

        // Sync tags
        if (!empty($_POST['tag_ids'])) {
            $this->tags->syncArticleTags($id, array_map('intval', $_POST['tag_ids']));
        }

        $this->flash('success', 'Article created successfully.');
        $this->redirect('/admin/articles/edit/' . $id);
    }

    public function edit(string $id): void
    {
        $article = $this->articles->findFull((int)$id);
        if (!$article) { $this->flash('danger', 'Article not found.'); $this->redirect('/admin/articles'); }

        // Reporter can only edit own
        if (Auth::role() === 'reporter' && $article['user_id'] !== Auth::id()) {
            $this->flash('danger', 'Access denied.'); $this->redirect('/admin/articles');
        }

        $this->view('admin.articles.form', [
            'pageTitle'  => 'Edit Article',
            'article'    => $article,
            'categories' => $this->categories->allWithParent(),
            'cities'     => $this->locations->allCities(),
            'tags'       => $this->tags->forArticle((int)$id),
            'isEdit'     => true,
        ], $this->layout());
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $article = $this->articles->find((int)$id);
        if (!$article) { $this->redirect('/admin/articles'); }

        if (Auth::role() === 'reporter' && $article['user_id'] !== Auth::id()) {
            $this->flash('danger', 'Access denied.'); $this->redirect('/admin/articles');
        }

        // Non-chief editors editing a published article → pending edit
        if ($article['status'] === 'published'
            && !in_array(Auth::role(), ['admin','chief_editor'])) {
            $editData = [
                'title'   => Helper::sanitize($this->post('title', '')),
                'excerpt' => $this->post('excerpt', ''),
                'content' => $this->post('content', ''),
            ];
            $this->articles->submitEdit((int)$id, $editData, Auth::id());
            (new \App\Models\NotificationModel())->notifyChiefEditors(
                'edit_submitted',
                Auth::user()['name'] . ' submitted an edit for: "' . $article['title'] . '"',
                (int)$id, Auth::id()
            );
            $this->flash('success', 'Edit submitted to Chief Editor for approval.');
            $this->redirect('/portal/articles');
        }

        $data = $this->buildArticleData($article);

        // Handle status/approval
        $status = $data['status'];
        if ($status === 'published' && !Auth::can('publish_articles')) {
            $data['status'] = 'review';
            (new \App\Core\ApprovalService())->onSubmit((int)$id, Auth::id());
            $this->flash('success', 'Article submitted for review.');
        } elseif ($status === 'review') {
            (new \App\Core\ApprovalService())->onSubmit((int)$id, Auth::id());
            $this->flash('success', 'Article submitted for review.');
        } else {
            $this->flash('success', 'Article updated.');
        }

        $this->articles->updateArticle((int)$id, $data);

        if (isset($_POST['tag_ids'])) {
            $this->tags->syncArticleTags((int)$id, array_map('intval', $_POST['tag_ids']));
        }

        $this->redirect('/admin/articles/edit/' . $id);
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->articles->delete((int)$id);
        $this->flash('success', 'Article deleted.');
        $this->redirect('/admin/articles');
    }

    public function bulk(): void
    {
        CSRF::validate();
        $ids    = array_map('intval', $_POST['ids'] ?? []);
        $action = $this->post('action', '');
        if ($ids && $action) {
            if ($action === 'publish' && !Auth::can('publish_articles')) {
                $this->flash('danger', 'No permission to publish.'); $this->redirect('/admin/articles');
            }
            $this->articles->bulkAction($ids, $action);
            $this->flash('success', 'Bulk action applied.');
        }
        $this->redirect('/admin/articles');
    }

    public function toggleBreaking(string $id): void
    {
        CSRF::validate();
        $settings    = new SettingModel();
        $expiryHours = (int)$settings->getValue('breaking_expiry_hours', 6);
        $this->articles->toggleBreaking((int)$id, $expiryHours);
        if (Helper::isAjax()) {
            $article = $this->articles->find((int)$id);
            $this->json(['is_breaking' => $article['is_breaking']]);
        }
        $this->back();
    }

    /* ── Private ── */

    private function buildArticleData(array $existing = []): array
    {
        $title       = Helper::sanitize($this->post('title', ''));
        $slug        = $this->post('slug', '') ?: Helper::slug($title);
        $slug        = Helper::uniqueSlug('tn_articles', $slug, (int)($existing['id'] ?? 0));
        $content     = $this->post('content', '');
        $youtubeUrl  = $this->post('youtube_url', '');
        $youtubeId   = $youtubeUrl ? Helper::youtubeId($youtubeUrl) : null;

        $status      = $this->post('status', 'draft');
        $publishedAt = null;
        if ($status === 'published' && empty($existing['published_at'])) {
            $publishedAt = Helper::now();
        } elseif (!empty($existing['published_at'])) {
            $publishedAt = $existing['published_at'];
        }

        $scheduledAt = $status === 'scheduled' ? $this->post('scheduled_at') : null;

        return [
            'user_id'          => $existing['user_id'] ?? Auth::id(),
            'category_id'      => (int)$this->post('category_id', 1),
            'city_id'          => (int)$this->post('city_id', 0) ?: (
                // Auto-assign reporter's district first city if no city selected
                !empty(\App\Core\Auth::user()['assigned_district_id'])
                    ? $this->locations->firstCityByDistrict((int)\App\Core\Auth::user()['assigned_district_id'])
                    : null
            ),
            'media_id'         => (int)$this->post('media_id', 0) ?: null,
            'title'            => $title,
            'slug'             => $slug,
            'excerpt'          => $this->post('excerpt') ?: Helper::excerpt($content),
            'content'          => $content,
            'content_type'     => $this->post('content_type', 'news'),
            'youtube_url'      => $youtubeUrl ?: null,
            'youtube_video_id' => $youtubeId,
            'status'           => $status,
            'is_breaking'      => (int)(bool)$this->post('is_breaking', 0),
            'is_editors_pick'  => (int)(bool)$this->post('is_editors_pick', 0),
            'is_featured'      => (int)(bool)$this->post('is_featured', 0),
            'read_time'        => Helper::readTime($content),
            'meta_title'       => $this->post('meta_title', '') ?: null,
            'meta_desc'        => $this->post('meta_desc', '') ?: null,
            'schema_type'      => $youtubeId ? 'VideoObject' : 'NewsArticle',
            'published_at'     => $publishedAt,
            'scheduled_at'     => $scheduledAt,
        ];
    }

    // ── APPROVAL ACTIONS ─────────────────────────────────────

    public function pendingEdits(): void
    {
        $edits = (new \App\Models\ArticleModel())->pendingEdits();
        $this->view('admin.articles.pending_edits', [
            'pageTitle' => 'Pending Edits',
            'edits'     => $edits,
        ], $this->layout());
    }

    public function approve(string $id): void
    {
        CSRF::validate();
        $role    = Auth::role();
        $service = new \App\Core\ApprovalService();
        $article = $this->articles->find((int)$id);

        if ($role === 'chief_editor') {
            if ($article && $article['approval_stage'] !== 'chief_editor') {
                $this->flash('danger','You can only approve escalated articles.');
                $this->redirect('/admin/articles?status=review');
            }
            $service->chiefApprove((int)$id, Auth::id());
            $this->flash('success','Escalated article approved and published.');
        } elseif (Auth::can('approve_articles')) {
            $service->editorApprove((int)$id, Auth::id());
            $this->flash('success','Article approved and published.');
        } else {
            $this->flash('danger','Access denied.');
        }
        $this->redirect('/admin/articles?status=review');
    }

    
    public function reject(string $id): void
    {
        CSRF::validate();
        $reason = trim($this->post('reason',''));
        (new \App\Core\ApprovalService())->reject((int)$id, Auth::id(), $reason);
        $this->flash('success', 'Article rejected. Reporter notified.');
        $this->redirect('/admin/articles?status=review');
    }


    public function escalate(string $id): void
    {
        CSRF::validate();
        $note = trim($this->post('note',''));
        (new \App\Core\ApprovalService())->escalateToChief((int)$id, Auth::id(), $note);
        $this->flash('success','Article escalated to Chief Editor.');
        $this->redirect('/admin/articles?status=review');
    }
}
