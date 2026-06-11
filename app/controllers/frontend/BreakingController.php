<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{FrontendArticleModel, CategoryModel, SettingModel};

class BreakingController extends Controller
{
    public function index(): void
    {
        $articles  = new FrontendArticleModel();
        $catModel  = new CategoryModel();
        $settings  = new SettingModel();

        $data      = $articles->breaking(20);
        $siteName  = $settings->getValue('site_name', 'தினத்துளிர்');

        $this->view('frontend.category.index', [
            'category'      => ['name'=>'Breaking News','name_tamil'=>'உடனடி செய்திகள்','slug'=>'breaking','description'=>''],
            'subcategories' => [],
            'activeSubSlug' => '',
            'articles'      => $data,
            'total'         => count($data),
            'page'          => 1,
            'per_page'      => 20,
            'navCategories' => $catModel->allWithParent(),
            'siteName'      => $siteName,
            'trending'      => $articles->trending(5),
            'metaTitle'     => 'உடனடி செய்திகள் | ' . $siteName,
            'metaDesc'      => 'Latest breaking news in Tamil',
            'breaking'      => $data,
        ], 'frontend');
    }
}
