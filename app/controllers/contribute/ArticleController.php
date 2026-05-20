<?php
namespace App\Controllers\Contribute;

use App\Core\{Controller, Session, Helper, CSRF};
use App\Models\{ArticleModel, ContributorModel, TagModel};

class ArticleController extends Controller
{
    private ArticleModel    $articles;
    private int             $contributorId;
    private array           $assignedCatIds;

    public function middleware(): void
    {
        if (!Session::get('contributor_id')) {
            Helper::redirect('/contribute/login');
        }
        $this->contributorId  = Session::get('contributor_id');
        $this->articles       = new ArticleModel();
        $this->assignedCatIds = Session::get('contributor_cats', []);
    }

    public function index(): void
    {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $result = $this->articles->byContributor($this->contributorId, $page, 15, $status);

        $this->view('contribute.articles.index', [
            'pageTitle' => 'My Articles',
            'articles'  => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'per_page'  => $result['per_page'],
            'status'    => $status,
        ], 'contributor');
    }

    public function create(): void
    {
        $categories = $this->allowedCategories();
        $this->view('contribute.articles.form', [
            'pageTitle'  => 'Submit Article',
            'article'    => [],
            'categories' => $categories,
            'tags'       => [],
            'isEdit'     => false,
        ], 'contributor');
    }

    public function store(): void
    {
        CSRF::validate();
        $data = $this->buildData();
        $id   = $this->articles->store($data);

        if (!empty($_POST['tag_ids'])) {
            (new TagModel())->syncArticleTags($id, array_map('intval', $_POST['tag_ids']));
        }

        $this->flash('success', 'Article submitted for review.');
        Helper::redirect('/contribute/articles');
    }

    public function edit(string $id): void
    {
        $article = $this->ownerCheck((int)$id);
        if (in_array($article['status'], ['published', 'review'])) {
            $this->flash('danger', 'Published or under-review articles cannot be edited.');
            Helper::redirect('/contribute/articles');
        }

        $this->view('contribute.articles.form', [
            'pageTitle'  => 'Edit Article',
            'article'    => $article,
            'categories' => $this->allowedCategories(),
            'tags'       => (new TagModel())->forArticle((int)$id),
            'isEdit'     => true,
        ], 'contributor');
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $article = $this->ownerCheck((int)$id);
        if (in_array($article['status'], ['published', 'review'])) {
            $this->flash('danger', 'Cannot edit at this stage.');
            Helper::redirect('/contribute/articles');
        }

        $data = $this->buildData();
        $this->articles->updateArticle((int)$id, $data);

        if (isset($_POST['tag_ids'])) {
            (new TagModel())->syncArticleTags((int)$id, array_map('intval', $_POST['tag_ids']));
        }

        $this->flash('success', 'Article updated.');
        Helper::redirect('/contribute/articles');
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $article = $this->ownerCheck((int)$id);
        if ($article['status'] === 'published') {
            $this->flash('danger', 'Published articles cannot be deleted.');
            Helper::redirect('/contribute/articles');
        }
        $this->articles->delete((int)$id);
        $this->flash('success', 'Article deleted.');
        Helper::redirect('/contribute/articles');
    }

    // ── Private ──────────────────────────────────────

    private function ownerCheck(int $id): array
    {
        $article = $this->articles->find($id);
        if (!$article || (int)$article['contributor_id'] !== $this->contributorId) {
            $this->flash('danger', 'Article not found.');
            Helper::redirect('/contribute/articles');
        }
        return $article;
    }

    private function allowedCategories(): array
    {
        if (empty($this->assignedCatIds)) return [];
        $placeholders = implode(',', array_fill(0, count($this->assignedCatIds), '?'));
        return \App\Core\Database::getInstance()
            ->prepare("SELECT * FROM tn_categories WHERE id IN ({$placeholders}) AND is_active = 1")
            ->execute($this->assignedCatIds)
            ? \App\Core\Database::getInstance()
                ->query("SELECT * FROM tn_categories WHERE id IN ({$placeholders}) AND is_active = 1")
                ->fetchAll(\PDO::FETCH_ASSOC)
            : (new \App\Models\CategoryModel())->fetchAll(
                "SELECT * FROM tn_categories WHERE id IN ({$placeholders}) AND is_active = 1",
                $this->assignedCatIds
            );
    }

    private function buildData(): array
    {
        $title   = Helper::sanitize($_POST['title'] ?? '');
        $slug    = Helper::uniqueSlug('tn_articles', $_POST['slug'] ?? '' ?: Helper::slug($title));
        $content = $_POST['content'] ?? '';

        return [
            'contributor_id' => $this->contributorId,
            'user_id'        => 1, // system user
            'category_id'    => (int)($_POST['category_id'] ?? $this->assignedCatIds[0] ?? 1),
            'title'          => $title,
            'slug'           => $slug,
            'excerpt'        => $_POST['excerpt'] ?? '' ?: Helper::excerpt($content),
            'content'        => $content,
            'content_type'   => 'news',
            'youtube_url'    => $_POST['youtube_url'] ?? '' ?: null,
            'youtube_video_id' => $_POST['youtube_url'] ? Helper::youtubeId($_POST['youtube_url']) : null,
            'status'         => 'review', // always goes to review
            'is_auto_imported' => 0,
            'import_source'  => 'contributor',
            'read_time'      => Helper::readTime($content),
            'meta_title'     => $_POST['meta_title'] ?? '' ?: null,
            'meta_desc'      => $_POST['meta_desc']  ?? '' ?: null,
        ];
    }
}
