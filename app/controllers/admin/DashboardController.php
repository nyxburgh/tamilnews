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
        try { $youtube     = new YoutubeModel(); } catch(\Exception $e) { }
        try { $rss         = new RssModel(); } catch(\Exception $e) { }
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

        // Ad pending count
        try {
            $badModel = new \App\Models\BusinessAdModel();
            $pendingAds = $badModel->pendingCount();
        } catch (\Exception $e) { $pendingAds = 0; }

        // Poll count
        try {
            $pollModel = new \App\Models\PollModel();
            $activePollCount = count($pollModel->active());
        } catch (\Exception $e) { $activePollCount = 0; }

        $this->view('admin.dashboard.index', [
            'pageTitle'      => 'Dashboard',
            'stats'          => $stats,
            'reviewQueue'    => $reviewQueue['data'],
            'recentArticles' => $recentArticles,
            'topArticles'    => $topArticles,
            'scheduledPosts' => $scheduledPosts,
            'viewTrend'      => $viewTrend,
            'liveBlogs'      => $liveBlogs,
            'pendingAds'     => $pendingAds ?? 0,
            'activePollCount'=> $activePollCount ?? 0,
        ], $this->layout());
    }
}
