<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\FrontendArticleModel;
use App\Models\CategoryModel;
use App\Models\LocationModel;
use App\Models\SettingModel;

class HomeController extends Controller
{
    public function index(): void
    {
        $articles   = new FrontendArticleModel();
        $categories = new CategoryModel();
        $settings   = new SettingModel();

        // Core homepage data
        $breaking    = $articles->breaking(10);
        $hero        = $articles->featured(1);
        $heroSide    = $articles->topStories(2);   // 2 cards beside hero
        $topStories  = $articles->topStories(3);   // 3-col row below hero
        $trending    = $articles->trending(5);
        $editorsPick = $articles->editorsPicks(3);
        $videos      = $articles->videos(6);

        // Category blocks
        $tamilNadu  = $articles->categoryBlock('tamil-nadu', 4);
        $cinema     = $articles->categoryBlock('cinema', 3);
        $sports     = $articles->categoryBlock('sports', 4);
        $india      = $articles->categoryBlock('india', 3);
        $technology = $articles->categoryBlock('technology', 3);

        // Nav categories
        $navCategories = $categories->allWithParent();
        $cities        = (new LocationModel())->allCities();

        // Site settings
        $siteName = $settings->getValue('site_name', 'தமிழ் செய்தி');
        $siteUrl  = $settings->getValue('site_url', '/');

        // Active live blogs
        $liveBlogs = [];
        try {
            $liveBlogs = (new \App\Models\LiveBlogModel())->activeBlogs();
        } catch (\Exception $e) {}

        // Ad slots
        $ads = $this->getAdSlots($settings);

        $this->view('frontend.home.index', compact(
            'breaking', 'hero', 'heroSide', 'topStories', 'trending',
            'editorsPick', 'videos', 'tamilNadu', 'cinema', 'sports',
            'india', 'technology', 'navCategories', 'cities',
            'siteName', 'siteUrl', 'ads', 'liveBlogs'
        ), 'frontend');
    }

    private function getAdSlots(SettingModel $settings): array
    {
        $db   = \App\Core\Database::getInstance();
        $rows = $db->query("SELECT * FROM tn_ad_slots WHERE is_active = 1")->fetchAll(\PDO::FETCH_ASSOC);
        $slots = [];
        foreach ($rows as $row) {
            $slots[$row['position']] = $row;
        }
        return $slots;
    }
}
