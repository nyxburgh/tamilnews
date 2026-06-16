<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{FrontendArticleModel, CategoryModel, SettingModel};

class SearchController extends Controller
{
    public function index(): void
    {
        $q       = trim($_GET['q'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $result  = ['data' => [], 'total' => 0, 'page' => 1, 'per_page' => 12];

        if ($q) {
            $model  = new FrontendArticleModel();
            $result = $model->search($q, $page, 12);
        }

        $settings      = new SettingModel();
        $navCategories = (new CategoryModel())->allWithParent();

        // Sidebar data
        try { $trending = (new FrontendArticleModel())->trending(6); } catch(\Exception $e) { $trending = []; }
        $this->view('frontend.search.index', [
            'q'             => $q,
            'articles'      => $result['data'],
            'total'         => $result['total'],
            'page'          => $result['page'],
            'per_page'      => $result['per_page'],
            'navCategories' => $navCategories,
            'siteName'      => $settings->getValue('site_name', 'தமிழ் செய்தி'),
            'metaTitle'     => $q ? "'{$q}' தேடல் முடிவுகள்" : 'தேடல்',
            'trending'      => $trending ?? [],
            'categoryId'    => 0,
        ], 'frontend');
    }
}
