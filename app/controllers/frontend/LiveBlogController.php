<?php
namespace App\Controllers\Frontend;

use App\Core\{Controller, Helper};
use App\Models\{LiveBlogModel, SettingModel, CategoryModel};

class LiveBlogController extends Controller
{
    public function show(string $slug): void
    {
        $model = new LiveBlogModel();
        $blog  = $model->findBySlug($slug);

        if (!$blog) {
            http_response_code(404);
            require VIEW_PATH . '/errors/404.php';
            return;
        }

        $entries       = $model->entries($blog['id'], 0, 100);
        $latestId      = $model->latestEntryId($blog['id']);
        $settings      = new SettingModel();
        $siteName      = $settings->getValue('site_name', 'Tamil News');
        $navCategories = (new CategoryModel())->allWithParent();

        $this->view('frontend.live.show', [
            'pageTitle'     => '🔴 LIVE: ' . $blog['title'],
            'metaTitle'     => 'LIVE: ' . $blog['title'] . ' | ' . $siteName,
            'metaDesc'      => $blog['description'] ?? 'Live updates from Tamil News Portal',
            'blog'          => $blog,
            'entries'       => $entries,
            'latestId'      => $latestId,
            'siteName'      => $siteName,
            'navCategories' => $navCategories,
            'breaking'      => [],
        ], 'frontend');
    }

    // AJAX poll — called by frontend JS every 15s
    public function poll(string $id): void
    {
        $model   = new LiveBlogModel();
        $afterId = (int)($_GET['after'] ?? 0);
        $entries = $model->entries((int)$id, $afterId, 50);
        $blog    = $model->find((int)$id);

        // Format entries for JSON
        $formatted = array_map(fn($e) => [
            'id'          => $e['id'],
            'content'     => $e['content'],
            'label'       => $e['label'],
            'label_color' => $e['label_color'],
            'score_home'  => $e['score_home'],
            'score_away'  => $e['score_away'],
            'is_pinned'   => $e['is_pinned'],
            'author_name' => $e['author_name'],
            'created_at'  => $e['created_at'],
            'time_ago'    => Helper::timeAgo($e['created_at']),
            'time_fmt'    => date('h:i A', strtotime($e['created_at'])),
        ], $entries);

        Helper::json([
            'entries' => $formatted,
            'status'  => $blog['status'] ?? 'ended',
            'latest'  => $model->latestEntryId((int)$id),
        ]);
    }
}
