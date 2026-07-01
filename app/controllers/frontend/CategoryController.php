<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{FrontendArticleModel, CategoryModel, SettingModel, LocationModel};

class CategoryController extends Controller
{
    // Slug of the Tamil Nadu news category — district widget shown only here
    private const TAMILNADU_SLUG = 'tamilnadu';

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
        $districtId= (int)($_GET['district'] ?? 0);

        // District widget — only on Tamil Nadu category
        $isTamilNadu = ($slug === self::TAMILNADU_SLUG || ($category['parent_id'] == 0 && str_contains(strtolower($slug), 'tamilnadu')));
        $districts   = [];
        $activeDistrict = null;
        if ($isTamilNadu) {
            $loc       = new LocationModel();
            $districts = $loc->allDistricts();
            if ($districtId) {
                foreach ($districts as $d) {
                    if ($d['id'] == $districtId) { $activeDistrict = $d; break; }
                }
            }
        }

        // If this is a parent category and a subcat filter is selected
        $activeSlug = $subSlug ?: $slug;
        $result     = $articles->byCategory($activeSlug, $page, 10, $districtId);

        // AJAX request — return JSON for infinite scroll
        if (!empty($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'articles' => $result['data'],
                'total'    => $result['total'],
                'page'     => $result['page'],
                'per_page' => $result['per_page'],
                'has_more' => ($result['page'] * $result['per_page']) < $result['total'],
                'base_url' => defined('ASSET_URL') ? ASSET_URL : '',
            ]);
            exit;
        }

        // Get subcategories for filter tabs
        $subcategories = $catModel->children((int)$category['id']);

        $navCategories = $catModel->allWithParent();
        $settings      = new SettingModel();
        $siteName      = $settings->getValue('site_name', 'வேள் சுடர்');
        $trending      = $articles->trending(5);

        $this->view('frontend.category.index', [
            'category'       => $category,
            'subcategories'  => $subcategories,
            'activeSubSlug'  => $subSlug,
            'articles'       => $result['data'],
            'total'          => $result['total'],
            'page'           => $result['page'],
            'per_page'       => $result['per_page'],
            'navCategories'  => $navCategories,
            'siteName'       => $siteName,
            'trending'       => $trending,
            'metaTitle'      => ($category['name_tamil'] ?: $category['name']) . ' செய்திகள் | ' . $siteName,
            'metaDesc'       => $category['description']
                                ?: (($category['name_tamil'] ?: $category['name']) . ' பிரிவில் சமீபத்திய செய்திகள் — ' . $siteName),
            'canonical'      => rtrim(BASE_URL . '/public', '/') . '/tamil-news/' . $slug . ($result['page'] > 1 ? '?page=' . $result['page'] : ''),
            'ogImage'        => BASE_URL . '/public/uploads/vaqua.jpeg',
            'robotsContent'  => $result['total'] < 1 ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1',
            'paginationPrev' => $result['page'] > 1 ? rtrim(BASE_URL . '/public', '/') . '/tamil-news/' . $slug . ($result['page'] > 2 ? '?page=' . ($result['page'] - 1) : '') : null,
            'paginationNext' => ($result['page'] * $result['per_page'] < $result['total']) ? rtrim(BASE_URL . '/public', '/') . '/tamil-news/' . $slug . '?page=' . ($result['page'] + 1) : null,
            'breaking'       => [],
            'categoryId'     => (int)($category['id'] ?? 0),
            'isTamilNadu'    => $isTamilNadu,
            'districts'      => $districts,
            'activeDistrict' => $activeDistrict,
            'activeDistrictId' => $districtId,
        ], 'frontend');
    }
}
