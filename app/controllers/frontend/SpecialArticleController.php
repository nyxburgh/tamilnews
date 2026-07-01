<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{FrontendArticleModel, CategoryModel, SettingModel};

class SpecialArticleController extends Controller
{
    public function index(): void
    {
        $articles = new FrontendArticleModel();
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $result   = $articles->byContentTypePaginated('special', $page, 12);

        $navCategories = (new CategoryModel())->allWithParent();
        $settings      = new SettingModel();
        $siteName      = $settings->getValue('site_name', 'வேள் சுடர்');
        $trending      = $articles->trending(5);

        $this->view('frontend.special.index', [
            'articles'      => $result['data'],
            'total'         => $result['total'],
            'page'          => $result['page'],
            'per_page'      => $result['per_page'],
            'navCategories' => $navCategories,
            'siteName'      => $siteName,
            'trending'      => $trending,
            'metaTitle'     => 'சிறப்புக் கட்டுரைகள் | ' . $siteName,
            'metaDesc'      => 'எங்கள் நிருபர்கள் மற்றும் கட்டுரையாளர்களின் சிறப்புக் கட்டுரைகள்.',
            'breaking'      => [],
            'categoryId'    => 0,
        ], 'frontend');
    }
}
