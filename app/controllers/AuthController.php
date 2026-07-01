<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helper;
use App\Models\UserModel;

class AuthController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function loginForm(): void
    {
        if (Auth::check()) {
            $role = Auth::role();
            if ($role === 'admin')     $this->redirect('/admin/dashboard');
            elseif ($role === 'ad_owner') $this->redirect('/portal/my-ads');
            else                       $this->redirect('/portal/dashboard');
        }
        $this->view('auth.login', ['pageTitle' => 'Login'], 'auth');
    }

    public function login(): void
    {
        CSRF::validate();

        $email    = trim($this->post('email', ''));
        $password = $this->post('password', '');

        if (!$email || !$password) {
            $this->flash('danger', 'Email and password are required.');
            $this->redirect('/admin/login');
        }

        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->flash('danger', 'Invalid credentials.');
            $this->redirect('/admin/login');
        }

        Auth::login($user);
        $this->users->updateLastLogin($user['id']);

        // Admin → admin dashboard, Ad Owner → my-ads, Editor/Reporter → portal dashboard
        $role = $user['role_slug'] ?? 'reporter';
        if ($role === 'admin') {
            $this->redirect('/admin/dashboard');
        } elseif ($role === 'ad_owner') {
            $this->redirect('/portal/my-ads');
        } else {
            $this->redirect('/portal/dashboard');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/admin/login');
    }
}
