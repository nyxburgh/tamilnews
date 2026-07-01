<?php
namespace App\Controllers;

use App\Core\{Controller, Session, Helper, CSRF};
use App\Models\ContributorModel;

class ContributorAuthController extends Controller
{
    public function loginPage(): void
    {
        if (Session::get('contributor_id')) {
            $this->redirect('/contribute/dashboard');
        }
        $this->view('contribute.login', ['pageTitle' => 'Contributor Login'], 'contributor');
    }

    public function login(): void
    {
        CSRF::validate();
        $email    = trim($this->post('email', ''));
        $password = $this->post('password', '');

        if (!$email || !$password) {
            $this->flash('danger', 'Email and password are required.');
            $this->redirect('/contribute/login');
        }

        $model       = new ContributorModel();
        $contributor = $model->findByEmail($email);

        if (!$contributor || !password_verify($password, $contributor['password'] ?? '')) {
            $this->flash('danger', 'Invalid email or password.');
            $this->redirect('/contribute/login');
        }
        if (!$contributor['is_active']) {
            $this->flash('warning', 'Your account is pending admin approval.');
            $this->redirect('/contribute/login');
        }

        session_regenerate_id(true);
        Session::set('contributor_id', $contributor['id']);
        Session::set('contributor',    $contributor);
        $this->redirect('/contribute/dashboard');
    }

    public function registerPage(): void
    {
        if (Session::get('contributor_id')) {
            $this->redirect('/contribute/dashboard');
        }
        $this->view('contribute.register', ['pageTitle' => 'Register as Contributor'], 'contributor');
    }

    public function register(): void
    {
        CSRF::validate();
        $name     = Helper::sanitize($this->post('name', ''));
        $email    = trim($this->post('email', ''));
        $password = $this->post('password', '');
        $confirm  = $this->post('confirm_password', '');

        if (!$name || !$email || !$password) {
            $this->flash('danger', 'All fields are required.');
            $this->redirect('/contribute/register');
        }
        if (strlen($password) < 8) {
            $this->flash('danger', 'Password must be at least 8 characters.');
            $this->redirect('/contribute/register');
        }
        if ($password !== $confirm) {
            $this->flash('danger', 'Passwords do not match.');
            $this->redirect('/contribute/register');
        }

        $model = new ContributorModel();
        if ($model->findByEmail($email)) {
            $this->flash('danger', 'Email already registered.');
            $this->redirect('/contribute/register');
        }

        $model->insert([
            'name'        => $name,
            'email'       => $email,
            'password'    => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'is_approved' => 0,
            'is_active'   => 0, // pending admin approval
        ]);

        $this->flash('success', 'Registration successful! Your account is pending admin approval. You will be notified by email.');
        $this->redirect('/contribute/login');
    }

    public function logout(): void
    {
        Session::delete('contributor_id');
        Session::delete('contributor');
        $this->redirect('/contribute/login');
    }

    // ── Google OAuth redirect ─────────────────────────────────

    public function googleRedirect(): void
    {
        $cfg  = require CONFIG_PATH . '/app.php';
        $base = rtrim($cfg['url'], '/');
        $uri  = $base . '/public/contribute/auth/callback';

        $state = bin2hex(random_bytes(16));
        Session::set('contrib_oauth_state', $state);
        Helper::redirect(\App\Core\GoogleOAuth::authUrl($uri, $state));
    }

    // ── Google OAuth callback ─────────────────────────────────

    public function googleCallback(): void
    {
        $cfg  = require CONFIG_PATH . '/app.php';
        $base = rtrim($cfg['url'], '/');
        $uri  = $base . '/public/contribute/auth/callback';

        $code = $_GET['code'] ?? '';
        if (!$code) {
            $this->flash('danger', 'Google login failed — no code returned.');
            $this->redirect('/contribute/login');
        }

        $tokens  = \App\Core\GoogleOAuth::exchangeCode($code, $uri);
        $profile = $tokens ? \App\Core\GoogleOAuth::getProfile($tokens['access_token']) : null;

        if (!$profile) {
            $this->flash('danger', 'Google login failed. Please try again.');
            $this->redirect('/contribute/login');
        }

        $model = new ContributorModel();

        // Find by google_id first, then by email
        $contributor = $model->findByGoogleId($profile['google_id'])
                    ?: $model->findByEmail($profile['email']);

        if (!$contributor) {
            // Auto-register — pending admin approval
            try {
                $model->insert([
                    'name'       => $profile['name'],
                    'email'      => $profile['email'],
                    'password'   => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                    'google_id'  => $profile['google_id'],
                    'avatar'     => $profile['avatar'],
                    'is_approved'=> 0,
                    'is_active'  => 0,
                ]);
            } catch (\Exception $e) {
                // google_id column may not exist yet — try without it
                $model->insert([
                    'name'       => $profile['name'],
                    'email'      => $profile['email'],
                    'password'   => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                    'is_approved'=> 0,
                    'is_active'  => 0,
                ]);
            }
            $this->flash('success', 'Welcome ' . htmlspecialchars($profile['name'], ENT_QUOTES) . '! Account pending admin approval.');
            $this->redirect('/contribute/login');
        }

        if (!($contributor['is_active'] ?? 0)) {
            $this->flash('warning', 'Your account is pending admin approval.');
            $this->redirect('/contribute/login');
        }

        // Save google_id if missing
        try {
            if (empty($contributor['google_id'])) {
                $model->update($contributor['id'], ['google_id' => $profile['google_id']]);
            }
        } catch (\Exception $e) {}

        session_regenerate_id(true);
        Session::set('contributor_id', $contributor['id']);
        Session::set('contributor',    $contributor);
        $this->redirect('/contribute/dashboard');
    }
}
