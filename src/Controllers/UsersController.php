<?php

namespace App\Controllers;

use App\Models\UserModel;

class UsersController
{
    public function __construct(private UserModel $users)
    {
    }

    public function index(): string
    {
        return json_encode($this->users->all(), JSON_PRETTY_PRINT) ?: '[]';
    }

    public function show(string $id): string
    {
        $user = $this->users->findById((int) $id);

        if ($user === null) {
            http_response_code(404);
            return json_encode(['message' => 'User not found']) ?: '{"message":"User not found"}';
        }

        return json_encode($user, JSON_PRETTY_PRINT) ?: '{}';
    }
}
