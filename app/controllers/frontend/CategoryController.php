<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{FrontendArticleModel, CategoryModel, SettingModel};

class CategoryController extends Controller
{
    public function show(string $slug): void
    {
        $catModel = new CategoryModel();
        $category = $catModel->findBySlug($slug);

        if (!$category) {
            http_response_code(404);
            require VIEW_PATH . '/errors/404.php';
            return;
        }

        $articles  = new FrontendArticleModel();
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $subSlug   = $_GET['sub'] ?? '';

        // If this is a parent category and a subcat filter is selected
        $activeSlug = $subSlug ?: $slug;
        $result     = $articles->byCategory($activeSlug, $page, 6);

        // Get subcategories for filter tabs
        $subcategories = $catModel->children((int)$category['id']);

        $navCategories = $catModel->allWithParent();
        $settings      = new SettingModel();
        $siteName      = $settings->getValue('site_name', 'வேள் சுடர்');
        $trending      = $articles->trending(5);

        $this->view('frontend.category.index', [
            'category'      => $category,
            'subcategories' => $subcategories,
            'activeSubSlug' => $subSlug,
            'articles'      => $result['data'],
            'total'         => $result['total'],
            'page'          => $result['page'],
            'per_page'      => $result['per_page'],
            'navCategories' => $navCategories,
            'siteName'      => $siteName,
            'trending'      => $trending,
            'metaTitle'     => ($category['name_tamil'] ?: $category['name']) . ' செய்திகள் | ' . $siteName,
            'metaDesc'      => $category['description'] ?? '',
            'breaking'      => [],
        ], 'frontend');
    }
}
