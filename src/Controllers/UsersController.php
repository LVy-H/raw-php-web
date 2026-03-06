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
            'title' => 'User Directory',
            'users' => $this->users->listUsers(),
        ]);
    }

    public function show(string $id): string
    {
        $user = $this->users->findUser(['id' => (int) $id]);

        if ($user === null) {
            http_response_code(404);
            return View::make('users/show', [
                'title' => 'User Not Found',
                'user' => null,
            ]);
        }

        return View::make('users/show', [
            'title' => 'User Profile',
            'user' => $user,
            'documents' => [
                ['name' => 'Syllabus Notes', 'updated_at' => '2026-03-02'],
                ['name' => 'Project Milestone', 'updated_at' => '2026-03-04'],
            ],
        ]);
    }

    public function create(): string
    {
        return View::make('users/form', [
            'title' => 'Create Student',
            'mode' => 'create',
            'errors' => [],
            'form' => [
                'name' => '',
                'email' => '',
                'phone' => '',
                'username' => '',
            ],
        ]);
    }

    public function store(): string
    {
        $input = $this->collectInput();
        $errors = $this->validate($input, true);

        if (!empty($errors)) {
            http_response_code(422);
            return View::make('users/form', [
                'title' => 'Create Student',
                'mode' => 'create',
                'errors' => $errors,
                'form' => $input,
            ]);
        }

        $this->users->createUser([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'username' => $input['username'],
            'password' => password_hash($input['password'], PASSWORD_DEFAULT),
            'role' => 'student',
        ]);

        header('Location: /users');
        return '';
    }

    public function edit(string $id): string
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $role = (string) ($_SESSION['user_role'] ?? '');
        $targetId = (int) $id;
        $target = $this->users->findUser(['id' => $targetId]);

        if ($target === null) {
            http_response_code(404);
            return View::make('users/show', [
                'title' => 'User Not Found',
                'user' => null,
            ]);
        }

        if ($role === 'teacher') {
            if (($target['role'] ?? null) !== 'student') {
                return $this->forbidden('Teachers can only edit student accounts.');
            }

            return View::make('users/form', [
                'title' => 'Edit Student',
                'mode' => 'edit-teacher',
                'studentId' => $targetId,
                'errors' => [],
                'form' => [
                    'name' => $target['name'] ?? '',
                    'email' => $target['email'] ?? '',
                    'phone' => $target['phone'] ?? '',
                    'username' => $target['username'] ?? '',
                ],
            ]);
        }

        if ($role === 'student' && $userId === $targetId) {
            return View::make('users/form', [
                'title' => 'Edit My Account',
                'mode' => 'edit-self',
                'studentId' => $targetId,
                'errors' => [],
                'form' => [
                    'name' => $target['name'] ?? '',
                    'email' => $target['email'] ?? '',
                    'phone' => $target['phone'] ?? '',
                    'username' => $target['username'] ?? '',
                ],
            ]);
        }

        return $this->forbidden('You can only edit your own account.');
    }

    public function update(string $id): string
    {
        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $currentRole = (string) ($_SESSION['user_role'] ?? '');
        $targetId = (int) $id;
        $target = $this->users->findUser(['id' => $targetId]);

        if ($target === null) {
            http_response_code(404);
            return View::make('users/show', [
                'title' => 'User Not Found',
                'user' => null,
            ]);
        }

        $input = $this->collectInput();

        if ($currentRole === 'teacher') {
            if (($target['role'] ?? null) !== 'student') {
                return $this->forbidden('Teachers can only edit student accounts.');
            }

            $errors = $this->validate($input, false, $targetId);

            if (!empty($errors)) {
                http_response_code(422);
                return View::make('users/form', [
                    'title' => 'Edit Student',
                    'mode' => 'edit-teacher',
                    'studentId' => $targetId,
                    'errors' => $errors,
                    'form' => $input,
                ]);
            }

            $passwordHash = trim($input['password']) !== ''
                ? password_hash($input['password'], PASSWORD_DEFAULT)
                : null;

            $this->users->updateUser($targetId, [
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'username' => $input['username'],
                'password' => $passwordHash,
            ], 'student');

            header('Location: /users');
            return '';
        }

        if ($currentRole === 'student' && $currentUserId === $targetId && ($target['role'] ?? null) === 'student') {
            $selfErrors = $this->validateSelfEdit($input, $targetId);

            if (!empty($selfErrors)) {
                http_response_code(422);
                return View::make('users/form', [
                    'title' => 'Edit My Account',
                    'mode' => 'edit-self',
                    'studentId' => $targetId,
                    'errors' => $selfErrors,
                    'form' => [
                        'name' => $target['name'] ?? '',
                        'email' => $input['email'],
                        'phone' => $input['phone'],
                        'username' => $target['username'] ?? '',
                    ],
                ]);
            }

            $passwordHash = trim($input['password']) !== ''
                ? password_hash($input['password'], PASSWORD_DEFAULT)
                : null;

            $this->users->updateUser($targetId, [
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => $passwordHash,
            ], 'student');

            header('Location: /users/' . $targetId);
            return '';
        }

        return $this->forbidden('You do not have permission to edit this account.');
    }

    public function delete(string $id): string
    {
        $this->users->deleteUser((int) $id, 'student');
        header('Location: /users');
        return '';
    }

    private function collectInput(): array
    {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => trim($_POST['password'] ?? ''),
        ];
    }

    private function validate(array $input, bool $creating, ?int $excludeId = null): array
    {
        $errors = [];

        if ($input['name'] === '') {
            $errors[] = 'Name is required.';
        }

        if ($input['email'] === '' || filter_var($input['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'A valid email is required.';
        }

        if ($input['phone'] === '') {
            $errors[] = 'Phone is required.';
        }

        if ($input['username'] === '') {
            $errors[] = 'Username is required.';
        }

        if ($creating && $input['password'] === '') {
            $errors[] = 'Password is required.';
        }

        if ($input['username'] !== '' && $this->users->exists('username', $input['username'], $excludeId)) {
            $errors[] = 'Username is already used.';
        }

        if ($input['email'] !== '' && $this->users->exists('email', $input['email'], $excludeId)) {
            $errors[] = 'Email is already used.';
        }

        return $errors;
    }

    private function validateSelfEdit(array $input, int $selfId): array
    {
        $errors = [];

        if ($input['email'] === '' || filter_var($input['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'A valid email is required.';
        }

        if ($input['phone'] === '') {
            $errors[] = 'Phone is required.';
        }

        if ($input['email'] !== '' && $this->users->exists('email', $input['email'], $selfId)) {
            $errors[] = 'Email is already used.';
        }

        return $errors;
    }

    private function forbidden(string $message): string
    {
        http_response_code(403);
        return View::make('users/show', [
            'title' => 'Forbidden',
            'user' => null,
            'forbiddenMessage' => $message,
        ]);
    }
}
