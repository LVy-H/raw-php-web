<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\UserModel;
use App\Core\BaseController;

class LoginController extends BaseController {
    public function __construct(private UserModel $users)
    {
    }

    public function index(): string
    {
        if (isset($_SESSION['user_id'])) {
            return $this->redirect('/users');
        }

        return View::make('login', [
            'title' => 'Sign In',
            'error' => null,
        ]);
    }

    public function login(): string
    {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $user = $username !== '' ? $this->users->findUser(['username' => $username], ['*']) : null;

        if ($user === null || !isset($user['password']) || !password_verify($password, (string) $user['password'])) {
            http_response_code(401);
            return View::make('login', [
                'title' => 'Sign In',
                'error' => 'Invalid username or password.',
                'form' => ['username' => $username],
            ]);
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = (string) $user['username'];
        $_SESSION['user_name'] = (string) ($user['name'] ?? $user['username']);
        $_SESSION['user_role'] = (string) ($user['role'] ?? 'student');

        session_regenerate_id(true);

        return $this->redirect('/users');
    }

    public function logout(): string
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        session_regenerate_id(true);

        return $this->redirect('/login');
    }
}