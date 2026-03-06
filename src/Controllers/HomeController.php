<?php

namespace App\Controllers;

use App\Core\View;

class HomeController {
    public function index(): string
    {
        if (isset($_SESSION['user_id'])) {
        header('Location: /users/' . ($_SESSION['user_id'] ?? ''));
        return '';
        }
        else {
            header('Location: /login');
            return '';
        }
    }
}