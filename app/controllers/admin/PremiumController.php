<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, CSRF, Helper, Auth};
use App\Models\PremiumModel;

class PremiumController extends Controller
{
    protected function layout(): string
    {
        return match(\App\Core\Auth::role()) { 'admin' => 'admin', 'chief_editor' => 'editor_portal', default => 'portal' };
    }

    private PremiumModel $model;
    public function middleware(): void { $this->requireRole('admin', 'editor'); }
    public function __construct() { $this->model = new PremiumModel(); }

    public function index(): void
    {
        $page   = max(1, (int)$this->get('page', 1));
        $result = $this->model->premiumArticles($page, 20);
        $this->view('admin.premium.index', [
            'pageTitle' => 'Premium Articles',
            'articles'  => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'per_page'  => $result['per_page'],
            'stats'     => $this->model->stats(),
            'plans'     => $this->model->activePlans(),
        ], $this->layout());
    }

    public function toggle(string $id): void
    {
        CSRF::validate();
        $isPremium = $this->model->toggleArticlePremium((int)$id, Auth::id());
        if (Helper::isAjax()) {
            $this->json(['is_premium' => $isPremium]);
        }
        $this->flash('success', $isPremium ? 'Article marked as Premium 🔒' : 'Premium removed.');
        $this->back();
    }

    public function plans(): void
    {
        $this->view('admin.premium.plans', [
            'pageTitle' => 'Premium Plans',
            'plans'     => $this->model->allPlans(),
        ], $this->layout());
    }

    public function storePlan(): void
    {
        CSRF::validate();
        $this->model->insert([
            'name'          => Helper::sanitize($this->post('name', '')),
            'name_tamil'    => $this->post('name_tamil', '') ?: null,
            'price_inr'     => (float)$this->post('price_inr', 0),
            'duration_days' => (int)$this->post('duration_days', 30),
            'is_active'     => (int)(bool)$this->post('is_active', 0),
        ]);
        $this->flash('success', 'Plan created.');
        $this->redirect('/admin/premium/plans');
    }

    public function updatePlan(string $id): void
    {
        CSRF::validate();
        $this->model->update((int)$id, [
            'name'          => Helper::sanitize($this->post('name', '')),
            'name_tamil'    => $this->post('name_tamil', '') ?: null,
            'price_inr'     => (float)$this->post('price_inr', 0),
            'duration_days' => (int)$this->post('duration_days', 30),
            'is_active'     => (int)(bool)$this->post('is_active', 0),
        ]);
        $this->flash('success', 'Plan updated.');
        $this->redirect('/admin/premium/plans');
    }

    public function subscribers(): void
    {
        $data = $this->model->allSubscribers(200);
        $this->view('admin.premium.subscribers', [
            'pageTitle'   => 'Premium Subscribers',
            'subscribers' => $data,
            'stats'       => $this->model->stats(),
        ], $this->layout());
    }
}
