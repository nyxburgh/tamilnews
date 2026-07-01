<?php
namespace App\Controllers;

use App\Core\{Controller, Auth, Session, Helper, CSRF};
use App\Models\UserModel;

class UserAuthController extends Controller
{
    private UserModel $users;
    public function __construct() { $this->users = new UserModel(); }

    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/portal/dashboard');
        }
        $this->view('auth.user_login', ['pageTitle' => 'Staff Login'], 'user_auth');
    }

    public function login(): void
    {
        CSRF::validate();
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            Session::flash('alert_type', 'danger');
            Session::flash('alert_msg',  'Email and password are required.');
            $this->redirect('/login');
        }

        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('alert_type', 'danger');
            Session::flash('alert_msg',  'Invalid email or password.');
            $this->redirect('/login');
        }

        // Admin must use /admin/login
        if ($user['role_slug'] === 'admin') {
            Session::flash('alert_type', 'warning');
            Session::flash('alert_msg',  'Admin accounts must use the admin login page.');
            $this->redirect('/login');
        }

        Auth::login($user);
        $this->users->updateLastLogin($user['id']);

        // Route by role
        $role = $user['role_slug'] ?? '';
        if ($role === 'ad_owner') {
            $this->redirect('/portal/my-ads');
        }
        $this->redirect('/portal/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
