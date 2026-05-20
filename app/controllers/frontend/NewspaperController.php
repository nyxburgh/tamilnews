<?php
namespace App\Controllers\Frontend;

use App\Core\{Controller, Helper};
use App\Models\{NewspaperModel, SettingModel, CategoryModel};

class NewspaperController extends Controller
{
    public function index(): void
    {
        $model    = new NewspaperModel();
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $year     = $_GET['year'] ?? '';
        $result   = $model->allPaginated($page, 12, $year);
        $settings = new SettingModel();
        $siteName = $settings->getValue('site_name', 'Tamil News');

        $this->view('frontend.newspaper.index', [
            'pageTitle'     => 'இ-பேப்பர் | ' . $siteName,
            'metaTitle'     => 'E-Paper Archive | ' . $siteName,
            'metaDesc'      => 'Read and download daily Tamil newspaper editions',
            'papers'        => $result['data'],
            'total'         => $result['total'],
            'page'          => $result['page'],
            'per_page'      => $result['per_page'],
            'years'         => $model->availableYears(),
            'selectedYear'  => $year,
            'navCategories' => (new CategoryModel())->allWithParent(),
            'siteName'      => $siteName,
            'breaking'      => [],
        ], 'frontend');
    }

    public function showPaper(string $date): void
    {
        $model  = new NewspaperModel();
        $paper  = $model->byDate($date);

        if (!$paper) {
            http_response_code(404);
            require VIEW_PATH . '/errors/404.php';
            return;
        }

        $settings = new SettingModel();
        $siteName = $settings->getValue('site_name', 'Tamil News');

        $this->view('frontend.newspaper.view', [
            'pageTitle'     => $paper['title'] . ' | ' . $siteName,
            'metaTitle'     => $paper['title'],
            'metaDesc'      => 'Read ' . $paper['title'] . ' online',
            'paper'         => $paper,
            'nearby'        => $model->latest(6),
            'navCategories' => (new CategoryModel())->allWithParent(),
            'siteName'      => $siteName,
            'breaking'      => [],
        ], 'frontend');
    }

    public function download(string $id): void
    {
        $model = new NewspaperModel();
        $paper = $model->find((int)$id);

        if (!$paper || !$paper['is_active']) {
            http_response_code(404); exit;
        }

        $model->incrementDownload((int)$id);

        $filePath = dirname(__DIR__, 3) . '/public' . $paper['pdf_path'];

        if (!file_exists($filePath)) {
            http_response_code(404); exit;
        }

        $filename = 'TamilNews_' . $paper['edition_date'] . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }
}
