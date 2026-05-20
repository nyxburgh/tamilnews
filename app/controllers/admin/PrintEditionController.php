<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, Auth, CSRF, Helper};
use App\Models\{PrintEditionModel, ArticleModel, CategoryModel};

class PrintEditionController extends Controller
{
    private PrintEditionModel $model;

    public function middleware(): void
    {
        $this->requireRole('admin', 'chief_editor', 'editor');
    }

    protected function layout(): string
    {
        return match(Auth::role()) {
            'admin'        => 'admin',
            'chief_editor' => 'editor_portal',
            default        => 'portal',
        };
    }

    public function __construct()
    {
        $this->model = new PrintEditionModel();
    }

    // List all editions
    public function index(): void
    {
        $this->view('admin.print.index', [
            'pageTitle' => 'Print Editions',
            'editions'  => $this->model->allWithCount(),
        ], $this->layout());
    }

    // Create new edition
    public function create(): void
    {
        $this->view('admin.print.create', [
            'pageTitle' => 'New Print Edition',
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $date  = $this->post('edition_date', date('Y-m-d'));
        $title = Helper::sanitize($this->post('title', ''));
        if (!$title) $title = 'வேள் சுடர் — ' . date('d M Y', strtotime($date));

        $id = $this->model->insert([
            'title'        => $title,
            'edition_date' => $date,
            'status'       => 'draft',
            'notes'        => $this->post('notes', '') ?: null,
            'created_by'   => Auth::id(),
        ]);

        $this->flash('success', 'Edition created. Now select articles.');
        $this->redirect('/admin/print/select/' . $id);
    }

    // Article selection page
    public function select(string $id): void
    {
        $edition = $this->model->findWithArticles((int)$id);
        if (!$edition) { $this->flash('danger', 'Edition not found.'); $this->redirect('/admin/print'); }

        // Already selected article IDs
        $selectedIds = array_column($edition['articles'], 'id');

        // Published articles for selection — with filters
        $filters = ['status' => 'published'];
        if ($cat = $this->get('category_id')) $filters['category_id'] = $cat;
        if ($date = $this->get('from_date'))  $filters['from_date']   = $date;
        if ($q    = $this->get('q'))          $filters['q']           = $q;

        $page   = max(1, (int)$this->get('page', 1));
        $result = (new ArticleModel())->listPaginated($filters, $page, 15);

        $this->view('admin.print.select', [
            'pageTitle'   => 'Select Articles — ' . $edition['title'],
            'edition'     => $edition,
            'selectedIds' => $selectedIds,
            'articles'    => $result['data'],
            'total'       => $result['total'],
            'page'        => $result['page'],
            'per_page'    => $result['per_page'],
            'categories'  => (new CategoryModel())->topLevel(),
            'filters'     => $filters,
        ], $this->layout());
    }

    // AJAX: toggle article in/out of edition
    public function toggleArticle(string $id): void
    {
        CSRF::validate();
        $articleId = (int)$this->post('article_id', 0);
        $action    = $this->post('action', 'add'); // add | remove

        if ($action === 'remove') {
            $this->model->removeArticle((int)$id, $articleId);
            $this->json(['success' => true, 'action' => 'removed']);
        } else {
            $this->model->addArticle((int)$id, $articleId);
            $this->json(['success' => true, 'action' => 'added']);
        }
    }

    // Update sort order (drag & drop)
    public function updateSort(string $id): void
    {
        CSRF::validate();
        $order = $this->post('order', '');
        $ids   = array_filter(array_map('intval', explode(',', $order)));
        if ($ids) $this->model->updateSort((int)$id, $ids);
        $this->json(['success' => true]);
    }

    // View/manage selected articles
    public function manage(string $id): void
    {
        $edition = $this->model->findWithArticles((int)$id);
        if (!$edition) { $this->flash('danger', 'Not found.'); $this->redirect('/admin/print'); }

        $this->view('admin.print.manage', [
            'pageTitle' => 'Manage Edition — ' . $edition['title'],
            'edition'   => $edition,
            'articles'  => $edition['articles'],
        ], $this->layout());
    }

    public function updateStatus(string $id): void
    {
        CSRF::validate();
        $status = $this->post('status', 'draft');
        $this->model->updateStatus((int)$id, $status);
        $this->flash('success', 'Status updated.');
        $this->redirect('/admin/print/manage/' . $id);
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->model->delete((int)$id);
        $this->flash('success', 'Edition deleted.');
        $this->redirect('/admin/print');
    }
}
