<?php
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Models\ArticleModel;

class AnalyticsController extends Controller
{
    protected function layout(): string
    {
        return match(\App\Core\Auth::role()) { 'admin' => 'admin', 'chief_editor' => 'editor_portal', default => 'portal' };
    }

    private ArticleModel $articles;
    public function middleware(): void { $this->requireRole('admin','editor'); }
    public function __construct() { $this->articles = new ArticleModel(); }

    public function index(): void
    {
        $period = $this->get('period','today');
        $this->view('admin.analytics.index', [
            'pageTitle'   => 'Analytics',
            'topArticles' => $this->articles->topByViews(20, $period),
            'viewTrend'   => $this->articles->viewTrend(30),
            'viewsToday'  => $this->articles->viewsToday(),
            'period'      => $period,
        ], $this->layout());
    }

    public function articles(): void { $this->index(); }
}
