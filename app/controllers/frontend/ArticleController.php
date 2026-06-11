<?php
namespace App\Controllers\Frontend;

use App\Core\{Controller, Session, Helper};
use App\Models\{FrontendArticleModel, CategoryModel, RatingModel, SettingModel};

class ArticleController extends Controller
{
    public function show(string $slug): void
    {
        $model    = new FrontendArticleModel();
        $article  = $model->bySlug($slug);

        if (!$article) {
            http_response_code(404);
            require VIEW_PATH . '/errors/404.php';
            return;
        }

        // Premium gate — check if reader has access
        $isPremiumLocked = false;
        if (!empty($article['is_premium'])) {
            $readerId = \App\Core\Session::get('reader_id', 0);
            // Also allow logged-in admin/editor/reporter
            $staffLoggedIn = \App\Core\Auth::check();
            if (!$staffLoggedIn && !$readerId) {
                $isPremiumLocked = true;
            } elseif (!$staffLoggedIn && $readerId) {
                $premiumModel = new \App\Models\PremiumModel();
                $isPremiumLocked = !$premiumModel->hasAccess($readerId);
            }
        }

        // Increment view count
        $model->incrementView($article['id']);

        // Related articles
        $related = $model->related($article['id'], $article['category_id'] ?? 0, 4);

        // Ratings
        $ratingModel = new RatingModel();
        $ratingStats = $ratingModel->forArticle($article['id']);
        $reviews     = $ratingModel->recentReviews($article['id'], 10);
        $readerId    = Session::get('reader_id', 0);
        $userRating  = $readerId ? ($ratingModel->readerRating($article['id'], $readerId)['rating'] ?? 0) : 0;

        // Nav
        $categories    = (new CategoryModel())->allWithParent();
        $settings      = new SettingModel();
        $siteName      = $settings->getValue('site_name', 'தமிழ் செய்தி');

        // Trending sidebar
        $trending = $model->trending(5);

        // Ad slots
        $db   = \App\Core\Database::getInstance();
        $rows = $db->query("SELECT * FROM tn_ad_slots WHERE is_active = 1")->fetchAll(\PDO::FETCH_ASSOC);
        $ads  = [];
        foreach ($rows as $row) { $ads[$row['type']] = $row; }

        // SEO
        $siteUrl   = rtrim($settings->getValue('site_url', BASE_URL . '/public'), '/');
        $metaTitle = $article['meta_title'] ?: $article['title'] . ' | ' . $siteName;
        $rawDesc   = $article['meta_desc']  ?: strip_tags($article['excerpt'] ?? '');
        $metaDesc  = mb_substr($rawDesc, 0, 160);
        $canonical = $siteUrl . '/article/' . $article['slug'];

        // OG image — use article thumbnail or fallback to site default
        if (!empty($article['image_url'])) {
            $ogImage = strpos($article['image_url'], 'http') === 0
                ? $article['image_url']
                : $siteUrl . '/' . ltrim($article['image_url'], '/');
        } else {
            $ogImage = BASE_URL . '/public/uploads/vaqua.jpeg'; // fallback
        }

        $isPremiumLocked = $isPremiumLocked ?? false;
        $noSidebar   = true;
        $categoryId  = (int)($article['category_id'] ?? 0);
        $csrf = \App\Core\CSRF::token();

        $this->view('frontend.article.show', compact(
            'article', 'related', 'ratingStats', 'reviews',
            'readerId', 'userRating', 'categories', 'siteName',
            'trending', 'ads', 'metaTitle', 'metaDesc', 'canonical', 'ogImage', 'csrf',
            'categoryId',
            'noSidebar'
        ), 'frontend');
    }
}
