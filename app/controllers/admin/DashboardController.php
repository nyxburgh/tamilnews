<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\{ArticleModel, UserModel, YoutubeModel, RssModel, ContributorModel, LiveBlogModel};

class DashboardController extends Controller
{
    protected function layout(): string
    {
        return match(\App\Core\Auth::role()) { 'admin' => 'admin', 'chief_editor' => 'editor_portal', default => 'portal' };
    }

    public function middleware(): void { $this->requireAuth(); }

    public function index(): void
    {
        $articles    = new ArticleModel();
        $users       = new UserModel();
        $youtube     = new YoutubeModel();
        $rss         = new RssModel();
        $contributors= new ContributorModel();

        $stats = [
            'published'            => $articles->countByStatus('published'),
            'draft'                => $articles->countByStatus('draft'),
            'review'               => $articles->countByStatus('review'),
            'scheduled'            => $articles->countByStatus('scheduled'),
            'views_today'          => $articles->viewsToday(),
            'total_users'          => $users->count(),
            'yt_pending'           => $youtube->pendingCount(),
            'rss_pending'          => $rss->pendingCount(),
            'pending_contributors' => $contributors->pendingApprovalCount(),
        ];

        // Review queue — articles needing action
        $reviewQueue = $articles->listPaginated(['status' => 'review'], 1, 8);

        $recentArticles = $articles->recentPublished(8);
        $topArticles    = $articles->topByViews(5, 'today');
        $scheduledPosts = $articles->scheduled();
        $viewTrend      = $articles->viewTrend(7);

        // Active live blogs
        $liveBlogs = [];
        try {
            $liveModel = new LiveBlogModel();
            $liveBlogs = $liveModel->activeBlogs();
        } catch (\Exception $e) {}

        $this->view('admin.dashboard.index', [
            'pageTitle'      => 'Dashboard',
            'stats'          => $stats,
            'reviewQueue'    => $reviewQueue['data'],
            'recentArticles' => $recentArticles,
            'topArticles'    => $topArticles,
            'scheduledPosts' => $scheduledPosts,
            'viewTrend'      => $viewTrend,
            'liveBlogs'      => $liveBlogs,
        ], $this->layout());
    }
}
