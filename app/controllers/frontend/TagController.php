<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{FrontendArticleModel, CategoryModel, SettingModel, TagModel};

class TagController extends Controller
{
    public function show(string $slug): void
    {
        $tagModel = new TagModel();
        $tag      = $tagModel->findBySlug($slug);

        if (!$tag) {
            http_response_code(404);
            require VIEW_PATH . '/errors/404.php';
            return;
        }

        $page       = max(1, (int)($_GET['page'] ?? 1));
        $articleModel = new FrontendArticleModel();
        $result     = $articleModel->byTag($slug, $page, 6);
        $settings   = new SettingModel();
        $siteName   = $settings->getValue('site_name', 'வேள் சுடர்');

        // Sidebar data
        try { $trending = $articleModel->trending(6); } catch (\Exception $e) { $trending = []; }

        $this->view('frontend.tag.show', [
            'tag'           => $tag,
            'articles'      => $result['data'],
            'total'         => $result['total'],
            'page'          => $result['page'],
            'per_page'      => $result['per_page'],
            'navCategories' => (new CategoryModel())->allWithParent(),
            'siteName'      => $siteName,
            'metaTitle'     => $tag['name'] . ' | ' . $siteName,
            // Thin content (fewer than 3 articles) shouldn't be indexed —
            // avoids diluting crawl budget / duplicate-content signals
            'robotsContent' => $result['total'] < 3 ? 'noindex, follow' : 'index, follow',
            'breaking'      => [],
        ], 'frontend');
    }
}
