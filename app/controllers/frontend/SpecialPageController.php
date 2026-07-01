<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{SpecialCategoryModel, FrontendArticleModel, CategoryModel, SettingModel};

class SpecialPageController extends Controller
{
    public function show(string $slug): void
    {
        $model   = new SpecialCategoryModel();
        $special = $model->findBySlug($slug);

        if (!$special) {
            http_response_code(404);
            require VIEW_PATH . '/errors/404.php';
            return;
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $arts    = new FrontendArticleModel();
        $result  = $arts->bySpecialCategory($special['id'], $page, 9);
        $settings= new SettingModel();
        $siteName= $settings->getValue('site_name', 'வேள் சுடர்');

        $this->view('frontend.special.show', [
            'special'       => $special,
            'articles'      => $result['data'],
            'total'         => $result['total'],
            'page'          => $result['page'],
            'per_page'      => $result['per_page'],
            'navCategories' => (new CategoryModel())->allWithParent(),
            'siteName'      => $siteName,
            'metaTitle'     => $special['title'] . ' | ' . $siteName,
            'breaking'      => [],
        ], 'frontend');
    }
}
