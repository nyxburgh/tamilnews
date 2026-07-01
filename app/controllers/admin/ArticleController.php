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
    private MediaModel     $media;


    private function portalBase(): string
    {
        $role = \App\Core\Auth::role();
        return $role === 'admin' ? '/admin/articles' : '/portal/all-articles';
    }
    public function middleware(): void { $this->requireAuth(); }

    public function __construct()
    {
        $this->articles   = new ArticleModel();
        $this->categories = new CategoryModel();
        $this->tags       = new TagModel();
        $this->locations  = new LocationModel();
        $this->media      = new MediaModel();
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

        // Build photo news lookup [article_id => photo_news_id]
        $pnModel  = new \App\Models\PhotoNewsModel();
        $pnLinked = $pnModel->articleLookup(
            array_column($result['data'], 'id')
        );

        $this->view('admin.articles.index', [
            'pageTitle'  => 'Articles',
            'articles'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'per_page'   => $result['per_page'],
            'filters'    => $filters,
            'categories' => $this->categories->allWithParent(),
            'pnLinked'   => $pnLinked,
        ], $this->layout());
    }

    public function create(): void
    {
        // Prefill from photo news if pn_id provided
        $prefill = [];
        if (!empty($_GET['pn_id'])) {
            $pnModel = new \App\Models\PhotoNewsModel();
            $pn = $pnModel->find((int)$_GET['pn_id']);
            if ($pn) {
                $prefill = [
                    'title'  => $pn['title'],
                    'slug'   => $pn['slug'],
                    '_pn_tags' => $pnModel->tags((int)$pn['id']),
                    '_pn_image' => $pn['image_path'],
                ];
            }
        }

        $this->view('admin.articles.form', [
            '_prefill' => $prefill,
            'pageTitle'  => 'Create Article',
            'article'    => [],
            'categories' => $this->categories->allWithParent(),
            'districts'  => $this->locations->allDistricts(),
            'tags'       => [],
            'isEdit'     => false,
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $data = $this->buildArticleData();
        if (empty($data['category_id'])) { $this->flash('danger','Please select a category.'); $this->redirect($this->portalBase() . '/create'); }
        $id = $this->articles->store($data);

        // Sync tags
        if (!empty($_POST['tag_ids'])) {
            $this->tags->syncArticleTags($id, array_map('intval', $_POST['tag_ids']));
        }

        // Send notification if submitted for review
        if (($data['status'] ?? '') === 'review') {
            (new \App\Core\ApprovalService())->onSubmit($id, Auth::id());
            $this->flash('success', 'Article submitted for review.');
        } else {
            $this->flash('success', 'Article created successfully.');
        }

        // If created from photo news, link them
        if (!empty($_GET['pn_id'])) {
            (new \App\Models\PhotoNewsModel())->linkArticle((int)$_GET['pn_id'], (int)$id);
        }

        $this->redirect($this->portalBase().'/edit/' . $id);
    }

    public function edit(string $id): void
    {
        $article = $this->articles->findFull((int)$id);
        if (!$article) { $this->flash('danger', 'Article not found.'); $this->redirect($this->portalBase()); }

        // Reporter can only edit own
        if (Auth::role() === 'reporter' && $article['user_id'] !== Auth::id()) {
            $this->flash('danger', 'Access denied.'); $this->redirect($this->portalBase());
        }

        $this->view('admin.articles.form', [
            'pageTitle'  => 'Edit Article',
            'article'    => $article,
            'categories' => $this->categories->allWithParent(),
            'districts'  => $this->locations->allDistricts(),
            'tags'       => $this->tags->forArticle((int)$id),
            'isEdit'     => true,
        ], $this->layout());
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $article = $this->articles->find((int)$id);
        if (!$article) { $this->redirect($this->portalBase()); }

        if (Auth::role() === 'reporter' && $article['user_id'] !== Auth::id()) {
            $this->flash('danger', 'Access denied.'); $this->redirect($this->portalBase());
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

        // Push notification if requested + article is published
        if ($this->post('send_push') && Auth::can('publish_articles') && ($data['status'] ?? '') === 'published') {
            try {
                $article = $this->articles->findFull((int)$id);
                if ($article) {
                    $districtId  = (int)$this->post('push_district_id', 0) ?: null;
                    $districtIds = $districtId ? [$districtId] : [];
                    (new \App\Services\PushService())->sendArticle($article, $districtIds);
                }
            } catch (\Exception $e) {}
        }

        $this->articles->updateArticle((int)$id, $data);

        if (isset($_POST['tags_managed'])) {
            $tagIds = isset($_POST['tag_ids']) ? array_map('intval', $_POST['tag_ids']) : [];
            $this->tags->syncArticleTags((int)$id, $tagIds);
        }

        $this->redirect($this->portalBase().'/edit/' . $id);
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->articles->delete((int)$id);
        $this->flash('success', 'Article deleted.');
        $this->redirect($this->portalBase());
    }

    public function bulk(): void
    {
        CSRF::validate();
        $ids    = array_map('intval', $_POST['ids'] ?? []);
        $action = $this->post('action', '');
        if ($ids && $action) {
            if ($action === 'publish' && !Auth::can('publish_articles')) {
                $this->flash('danger', 'No permission to publish.'); $this->redirect($this->portalBase());
            }
            $this->articles->bulkAction($ids, $action);
            $this->flash('success', 'Bulk action applied.');
        }
        $this->redirect($this->portalBase());
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

    // GET — lightweight article search for the photo-news "connect existing" picker
    public function suggest(): void
    {
        $q = trim($this->get('q', ''));
        if (mb_strlen($q) < 1) { $this->json([]); return; }

        $stmt = \App\Core\Database::getInstance()->prepare(
            "SELECT id, title, slug, status, published_at
             FROM tn_articles
             WHERE title LIKE ?
             ORDER BY published_at DESC
             LIMIT 10"
        );
        $stmt->execute(['%' . $q . '%']);
        $this->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    private function buildArticleData(array $existing = []): array
    {
        $title       = Helper::sanitize($this->post('title', ''));
        $slug        = $this->post('slug', '') ?: Helper::slug($title);
        $slug        = Helper::uniqueSlug('tn_articles', $slug, (int)($existing['id'] ?? 0));
        $content     = $this->post('content', '');
        $youtubeUrl  = $this->post('youtube_url', '');
        $youtubeId   = $youtubeUrl ? Helper::youtubeId($youtubeUrl) : null;

        // Resolve media_id (from upload / media library picker) into the
        // actual image URL the rest of the site reads. Without this,
        // tn_articles.image_url never gets written and the featured image
        // disappears — both in the edit form and on the live article.
        $mediaId    = (int)$this->post('media_id', 0);
        $oldMediaId = (int)($existing['media_id'] ?? 0);
        $imageUrl   = $existing['image_url'] ?? null;
        if ($mediaId) {
            $mediaRow = $this->media->find($mediaId);
            if ($mediaRow && !empty($mediaRow['filepath'])) {
                $imageUrl = $mediaRow['filepath'];
            }
        } elseif ($mediaId === 0 && isset($_POST['media_id']) && $_POST['media_id'] === '') {
            $imageUrl = null;
        }
        // Image changed: delete the OLD file only if no other article still uses it
        // (safe for both direct-upload and media-library sourced images)
        if ($oldMediaId && $oldMediaId !== $mediaId) {
            $stillUsed = $this->articles->mediaStillUsed($oldMediaId, (int)($existing['id'] ?? 0));
            if (!$stillUsed) $this->media->deleteFile($oldMediaId);
        }

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
            'city_id'          => null,
            'district_id'      => (int)$this->post('district_id', 0) ?: null,
            'city_text'        => Helper::sanitize($this->post('city_name', '')) ?: null,
            'media_id'         => $mediaId ?: null,
            'image_url'        => $imageUrl,
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
            'meta_title'       => $this->post('meta_title','') ?: ($this->post('title','') ?: null),
            'meta_desc'        => $this->post('meta_desc','')
                                  ?: mb_substr(strip_tags($this->post('excerpt','') ?: $this->post('content','')), 0, 160) ?: null,
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
                $this->redirect($this->portalBase().'?status=review');
            }
            $service->chiefApprove((int)$id, Auth::id());
            $this->flash('success','Escalated article approved and published.');
        } elseif (Auth::can('approve_articles')) {
            $service->editorApprove((int)$id, Auth::id());
            $this->flash('success','Article approved and published.');
        } else {
            $this->flash('danger','Access denied.');
        }
        $this->redirect($this->portalBase().'?status=review');
    }

    
    public function reject(string $id): void
    {
        CSRF::validate();
        $reason = trim($this->post('reason',''));
        (new \App\Core\ApprovalService())->reject((int)$id, Auth::id(), $reason);
        $this->flash('success', 'Article rejected. Reporter notified.');
        $this->redirect($this->portalBase().'?status=review');
    }


    public function escalate(string $id): void
    {
        CSRF::validate();
        $note = trim($this->post('note',''));
        (new \App\Core\ApprovalService())->escalateToChief((int)$id, Auth::id(), $note);
        $this->flash('success','Article escalated to Chief Editor.');
        $this->redirect($this->portalBase().'?status=review');
    }

    private function handleNewsCardUpload(int $articleId): void
    {
        // Remove existing card if checkbox ticked
        if (!empty($_POST['remove_news_card'])) {
            $existing = $this->articles->find($articleId);
            if (!empty($existing['news_card_image'])) {
                @unlink(PUBLIC_PATH . $existing['news_card_image']);
            }
            $this->articles->updateField($articleId, 'news_card_image', null);
            return;
        }

        $file = $_FILES['news_card_image'] ?? null;
        if (!$file || empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return;

        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) return;

        $dir  = PUBLIC_PATH . '/uploads/newscards/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $name = 'card_' . $articleId . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
            $this->articles->updateField($articleId, 'news_card_image', '/uploads/newscards/' . $name);
        }
    }

}