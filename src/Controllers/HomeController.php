<?php

namespace App\Controllers;

class HomeController
{
    public function index(): string
    {
        $destination = isset($_SESSION['user_id'])
            ? '/users/' . (int) $_SESSION['user_id']
            : '/login';

        header('Location: ' . $destination);
        return '';
    }
}