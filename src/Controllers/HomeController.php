<?php

namespace App\Controllers;

use App\Core\BaseController;

class HomeController extends BaseController
{
    public function index(): string
    {
        $destination = isset($_SESSION['user_id'])
            ? '/users/' . (int) $_SESSION['user_id']
            : '/login';

        return $this->redirect($destination);
    }
}