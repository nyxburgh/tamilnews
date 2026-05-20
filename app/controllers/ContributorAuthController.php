<?php
namespace App\Controllers;

use App\Core\{Controller, GoogleOAuth, Session, Helper};
use App\Models\ContributorModel;

class ContributorAuthController extends Controller
{
    private string $redirectUri;

    public function __construct()
    {
        $cfg = require CONFIG_PATH . '/app.php';
        $base = rtrim($cfg['url'], '/');
        $this->redirectUri = $base . '/public/contribute/auth/callback';
    }

    public function loginPage(): void
    {
        if (Session::get('contributor_id')) {
            Helper::redirect('/contribute/dashboard');
        }
        $this->view('contribute.login', [
            'pageTitle' => 'Contributor Login',
            'googleUrl' => GoogleOAuth::authUrl($this->redirectUri, 'contributor'),
        ], 'contributor');
    }

    public function googleRedirect(): void
    {
        $state = bin2hex(random_bytes(16));
        Session::set('oauth_state', $state);
        Helper::redirect(GoogleOAuth::authUrl($this->redirectUri, $state));
    }

    public function callback(): void
    {
        $code = $_GET['code'] ?? '';
        if (!$code) {
            Session::flash('alert_type', 'danger');
            Session::flash('alert_msg', 'Google login failed.');
            Helper::redirect('/contribute/login');
        }

        $tokens  = GoogleOAuth::exchangeCode($code, $this->redirectUri);
        if (!$tokens) {
            Session::flash('alert_type', 'danger');
            Session::flash('alert_msg', 'Could not verify Google account.');
            Helper::redirect('/contribute/login');
        }

        $profile = GoogleOAuth::getProfile($tokens['access_token']);
        if (!$profile) {
            Session::flash('alert_type', 'danger');
            Session::flash('alert_msg', 'Could not fetch Google profile.');
            Helper::redirect('/contribute/login');
        }

        $model         = new ContributorModel();
        $contributorId = $model->upsertFromGoogle($profile);
        $contributor   = $model->findFull($contributorId);

        if ($contributor['is_blocked'] ?? false) {
            Session::flash('alert_type', 'danger');
            Session::flash('alert_msg', 'Your account has been blocked. Contact admin.');
            Helper::redirect('/contribute/login');
        }

        if (!$contributor['is_active']) {
            Session::flash('alert_type', 'warning');
            Session::flash('alert_msg', 'Your contributor account is pending admin approval.');
            Helper::redirect('/contribute/login');
        }

        $model->updateLastLogin($contributorId);
        session_regenerate_id(true);
        Session::set('contributor_id',   $contributorId);
        Session::set('contributor',      $contributor);
        Session::set('contributor_cats', explode(',', $contributor['category_ids'] ?? ''));

        Helper::redirect('/contribute/dashboard');
    }

    public function logout(): void
    {
        Session::delete('contributor_id');
        Session::delete('contributor');
        Session::delete('contributor_cats');
        Helper::redirect('/contribute/login');
    }
}
