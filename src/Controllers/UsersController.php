<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\UserModel;

class UsersController
{
    public function __construct(private UserModel $users)
    {
    }

    public function index(): string
    {
        return View::make('users/index', [
            'title' => 'Class Members',
            'users' => $this->users->all(),
        ]);
    }

    public function show(string $id): string
    {
        $user = $this->users->findById((int) $id);

        if ($user === null) {
            http_response_code(404);
            return View::make('users/show', [
                'title' => 'Member Not Found',
                'user' => null,
            ]);
        }

        return View::make('users/show', [
            'title' => 'Member Profile',
            'user' => $user,
            'documents' => [
                ['name' => 'Syllabus Notes', 'updated_at' => '2026-03-02'],
                ['name' => 'Project Milestone', 'updated_at' => '2026-03-04'],
            ],
        ]);
    }
}
