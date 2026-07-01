<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\ShortUrlModel;

class ShortUrlController extends Controller
{
    public function redirect(string $code): void
    {
        $model = new ShortUrlModel();
        $row   = $model->resolve($code);

        if (!$row) {
            http_response_code(404);
            require VIEW_PATH . '/errors/404.php';
            return;
        }

        $model->incrementClicks($code);
        header('Location: ' . $row['target_url'], true, 301);
        exit;
    }
}
