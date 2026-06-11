<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth};
use App\Models\{ReporterPerformanceModel, UserModel};

class PerformanceController extends Controller
{
    public function middleware(): void { $this->requireCan('view_analytics'); }
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    public function index(): void
    {
        $month = $this->get('month', date('Y-m-01'));
        $model = new ReporterPerformanceModel();

        $this->view('admin.performance.index', [
            'pageTitle'   => 'Reporter Performance',
            'leaderboard' => $model->leaderboard($month),
            'month'       => $month,
        ], $this->layout());
    }

    public function recalculate(): void
    {
        $month   = $this->post('month', date('Y-m-01'));
        $model   = new ReporterPerformanceModel();
        $umodel  = new UserModel();
        $reporters = $umodel->allReporters();
        foreach ($reporters as $r) {
            $model->recalculate($r['id'], $month);
        }
        $this->flash('success', 'Performance data recalculated for ' . count($reporters) . ' reporters.');
        $this->redirect('/admin/performance?month=' . $month);
    }

    public function user(string $id): void
    {
        $model = new ReporterPerformanceModel();
        $umodel = new UserModel();
        $user = $umodel->findWithRole((int)$id);
        if (!$user) { $this->flash('danger','User not found.'); $this->redirect('/admin/performance'); }

        $this->view('admin.performance.user', [
            'pageTitle'   => 'Performance: ' . $user['name'],
            'user'        => $user,
            'performance' => $model->forUser((int)$id, 12),
        ], $this->layout());
    }
}
