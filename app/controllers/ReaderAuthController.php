<?php
namespace App\Controllers;

use App\Core\{Controller, GoogleOAuth, Session, Helper, CSRF};
use App\Models\{ReaderModel, RatingModel};

class ReaderAuthController extends Controller
{
    private string $redirectUri;

    public function __construct()
    {
        $cfg = require CONFIG_PATH . '/app.php';
        $base = rtrim($cfg['url'], '/');
        $this->redirectUri = $base . '/public/auth/reader/callback';
    }

    public function googleRedirect(): void
    {
        $state = bin2hex(random_bytes(16));
        Session::set('reader_oauth_state', $state);
        Session::set('reader_return', $_GET['return'] ?? '/');
        Helper::redirect(GoogleOAuth::authUrl($this->redirectUri, $state));
    }

    public function callback(): void
    {
        $code = $_GET['code'] ?? '';
        if (!$code) { Helper::redirect('/'); }

        $tokens  = GoogleOAuth::exchangeCode($code, $this->redirectUri);
        $profile = $tokens ? GoogleOAuth::getProfile($tokens['access_token']) : null;

        if (!$profile) {
            Session::flash('alert_type', 'danger');
            Session::flash('alert_msg', 'Google login failed.');
            Helper::redirect('/');
        }

        $model    = new ReaderModel();
        $readerId = $model->upsertFromGoogle($profile);
        $reader   = $model->find($readerId);

        session_regenerate_id(true);
        Session::set('reader_id', $readerId);
        Session::set('reader',    $reader);

        $return = Session::get('reader_return', '/');
        Session::delete('reader_return');
        Helper::redirect($return);
    }

    public function logout(): void
    {
        Session::delete('reader_id');
        Session::delete('reader');
        Helper::redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    public function rate(): void
    {
        if (!Session::get('reader_id')) {
            Helper::json(['error' => 'Login required'], 401);
        }
        CSRF::validate();

        $articleId = (int)($_POST['article_id'] ?? 0);
        $rating    = max(1, min(5, (int)($_POST['rating'] ?? 0)));
        $review    = trim(htmlspecialchars($_POST['review'] ?? '', ENT_QUOTES, 'UTF-8'));

        if (!$articleId || !$rating) {
            Helper::json(['error' => 'Invalid input'], 422);
        }

        $ratingModel = new RatingModel();
        $ratingModel->upsert($articleId, Session::get('reader_id'), $rating, $review);
        $stats = $ratingModel->forArticle($articleId);
        Helper::json(['success' => true, 'stats' => $stats]);
    }
}
