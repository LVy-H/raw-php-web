<?php

namespace App\Controllers;

class LoginController {
    public function index(): string
    {
        return json_encode([
            'message' => 'Send POST /login to authenticate and GET /logout to sign out'
        ]) ?: '{"message":"Send POST /login to authenticate and GET /logout to sign out"}';
    }

    public function login(): string
    {
        $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 1;
        $_SESSION['user_id'] = $userId;

        return json_encode([
            'message' => 'Logged in',
            'user_id' => $userId,
        ]) ?: '{"message":"Logged in"}';
    }

    public function logout(): string
    {
        unset($_SESSION['user_id']);

        return json_encode(['message' => 'Logged out']) ?: '{"message":"Logged out"}';
    }
}