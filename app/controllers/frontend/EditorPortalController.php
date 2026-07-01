<?php
namespace App\Controllers\Frontend;

use App\Core\{Controller, Auth, Session, Helper, CSRF};
use App\Models\{ArticleModel, CategoryModel, UserModel, ContributorModel};

class EditorPortalController extends Controller
{
    private ArticleModel $articles;
    private string $role;
    private int $userId;
    private bool $isContributor = false;

    public function middleware(): void
    {
        // Check if contributor session
        if (Session::get('contributor_id')) {
            $this->isContributor = true;
            $this->userId        = Session::get('contributor_id');
            $this->role          = 'contributor';
        } elseif (Auth::check()) {
            $this->userId = Auth::id();
            $this->role   = Auth::role();
            if ($this->role === 'ad_owner') {
                Helper::redirect('/portal/my-ads');
            }
            if (!in_array($this->role, ['admin','chief_editor','editor','district_editor','category_editor','senior_reporter','reporter','ads_manager'])) {
                http_response_code(403); exit;
            }
        } else {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            Helper::redirect(strpos($uri, '/admin/') !== false ? '/admin/login' : '/login');
        }
        $this->articles = new ArticleModel();
    }

    public function dashboard(): void
    {
        $filters = $this->roleFilters();

        $stats = [
            'total'     => $this->countArticles($filters),
            'published' => $this->countArticles($filters + ['status' => 'published']),
            'review'    => $this->countArticles($filters + ['status' => 'review']),
            'draft'     => $this->countArticles($filters + ['status' => 'draft']),
        ];

        $recent = $this->articles->listPaginated($filters, 1, 8);

        $reviewQueue = [];
        if (in_array($this->role, ['admin','chief_editor','editor','district_editor','category_editor'])) {
            // Chief editor/admin sees all
            $q = $this->articles->listPaginated(['status' => 'review'], 1, 5);
            $reviewQueue = $q['data'];
        } elseif (in_array($this->role, ['district_editor','category_editor'])) {
            // Scoped editor sees only their district/category
            $scope = (new \App\Models\UserModel())->getEditorScope($this->userId);
            $q     = $this->articles->reviewQueueForEditor($scope, 1, 5);
            $reviewQueue = $q['data'];
        }

        $categories   = (new CategoryModel())->topLevel();
        $userBadges   = [];

        if (!$this->isContributor) {
            $userBadges = (new UserModel())->userBadges($this->userId);
        }

        $this->view('frontend.editor.dashboard', [
            'pageTitle'     => 'My Dashboard',
            'stats'         => $stats,
            'recent'        => $recent['data'],
            'reviewQueue'   => $reviewQueue,
            'categories'    => $categories,
            'role'          => $this->role,
            'userId'        => $this->userId,
            'isContributor' => $this->isContributor,
            'userBadges'    => $userBadges,
        ], 'portal');
    }

    public function myArticles(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $status  = $_GET['status'] ?? '';
        $filters = $this->roleFilters();
        if ($status) $filters['status'] = $status;

        $result = $this->articles->listPaginated($filters, $page, 15);

        $this->view('frontend.editor.articles', [
            'pageTitle'     => 'My Articles',
            'articles'      => $result['data'],
            'total'         => $result['total'],
            'page'          => $result['page'],
            'per_page'      => $result['per_page'],
            'status'        => $status,
            'role'          => $this->role,
            'isContributor' => $this->isContributor,
        ], 'portal');
    }

    public function profile(): void
    {
        if ($this->isContributor) {
            $user = Session::get('contributor');
        } else {
            $user = (new UserModel())->findWithRole($this->userId);
        }

        $badges = $this->isContributor ? [] : (new UserModel())->userBadges($this->userId);

        $this->view('frontend.profile.index', [
            'pageTitle'     => 'My Profile',
            'user'          => $user,
            'badges'        => $badges,
            'isContributor' => $this->isContributor,
            'role'          => $this->role,
        ], 'portal');
    }

    public function updateProfile(): void
    {
        CSRF::validate();

        if ($this->isContributor) {
            // Update contributor name
            $model = new ContributorModel();
            $model->update($this->userId, ['name' => Helper::sanitize($_POST['name'] ?? '')]);
            $updated = $model->findFull($this->userId);
            Session::set('contributor', $updated);
        } else {
            $model = new UserModel();
            $data  = ['name' => Helper::sanitize($_POST['name'] ?? '')];
            if (!empty($_POST['password']) && strlen($_POST['password']) >= 8) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            }
            $model->update($this->userId, $data);
            $updated = $model->findWithRole($this->userId);
            Auth::login($updated);
        }

        $this->flash('success', 'Profile updated.');
        Helper::redirect('/portal/profile');
    }

    // Submit edit for approval (non-admins)
    public function submitEdit(string $id): void
    {
        CSRF::validate();
        $article = $this->articles->find((int)$id);
        if (!$article) { $this->flash('danger','Not found.'); Helper::redirect('/portal/articles'); }

        // Ownership check
        $ownerId = $this->isContributor ? ($article['contributor_id'] ?? 0) : ($article['user_id'] ?? 0);
        if ((int)$ownerId !== $this->userId) {
            $this->flash('danger','Access denied.'); Helper::redirect('/portal/articles');
        }

        $title   = Helper::sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';

        $editData = [
            'title'   => $title,
            'excerpt' => $_POST['excerpt'] ?? '' ?: Helper::excerpt($content),
            'content' => $content,
        ];

        if ($this->role === 'admin') {
            // Admin edits apply directly
            $this->articles->updateArticle((int)$id, $editData);
            $this->flash('success','Article updated.');
        } else {
            // Others: submit for approval
            $this->articles->submitEdit((int)$id, $editData, $this->userId);
            $this->flash('success','Edit submitted for admin approval.');
        }
        Helper::redirect('/portal/articles');
    }

    // ── Private helpers ──────────────────────────────────────

    private function roleFilters(): array
    {
        // Reporter → own articles only
        if ($this->role === 'reporter') {
            return ['user_id' => $this->userId];
        }
        // Contributor → own submissions
        if ($this->isContributor) {
            return ['contributor_id' => $this->userId];
        }
        // District/Category editors → scoped by permissions
        if (in_array($this->role, ['district_editor','category_editor'])) {
            // Still show own + permitted articles
            // For "My Articles" tab show own; for review queue show scoped
            return ['user_id' => $this->userId]; // own articles in "My Articles"
        }
        // chief_editor + editor → see all
        return [];
    }

    private function countArticles(array $filters): int
    {
        $result = $this->articles->listPaginated($filters, 1, 1);
        return $result['total'];
    }

    public function notifications(): void
    {
        $model = new \App\Models\NotificationModel();
        $model->markRead($this->userId);
        $notifs = $model->forUser($this->userId, 50);

        $this->view('frontend.editor.notifications', [
            'pageTitle'     => 'Notifications',
            'notifications' => $notifs,
            'role'          => $this->role,
            'isContributor' => $this->isContributor,
        ], 'portal');
    }

    public function markRead(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        (new \App\Models\NotificationModel())->markRead($this->userId, $id ?: null);
        $this->json(['success' => true]);
    }

}