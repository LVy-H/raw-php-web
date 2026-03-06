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
            'title' => 'Manage Students',
            'users' => $this->users->allStudents(),
        ]);
    }

    public function show(string $id): string
    {
        $user = $this->users->findStudentById((int) $id);

        if ($user === null) {
            http_response_code(404);
            return View::make('users/show', [
                'title' => 'Member Not Found',
                'user' => null,
            ]);
        }

        return View::make('users/show', [
            'title' => 'Student Profile',
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

        $this->users->createStudent([
            ...$input,
            'password' => password_hash($input['password'], PASSWORD_DEFAULT),
        ]);

        header('Location: /students');
        return '';
    }

    public function edit(string $id): string
    {
        $student = $this->users->findStudentById((int) $id);

        if ($student === null) {
            http_response_code(404);
            return View::make('users/show', [
                'title' => 'Student Not Found',
                'user' => null,
            ]);
        }

        return View::make('users/form', [
            'title' => 'Edit Student',
            'mode' => 'edit',
            'studentId' => (int) $id,
            'errors' => [],
            'form' => [
                'name' => $student['name'] ?? '',
                'email' => $student['email'] ?? '',
                'phone' => $student['phone'] ?? '',
                'username' => $student['username'] ?? '',
            ],
        ]);
    }

    public function update(string $id): string
    {
        $studentId = (int) $id;
        $student = $this->users->findStudentById($studentId);

        if ($student === null) {
            http_response_code(404);
            return View::make('users/show', [
                'title' => 'Student Not Found',
                'user' => null,
            ]);
        }

        $input = $this->collectInput();
        $errors = $this->validate($input, false, $studentId);

        if (!empty($errors)) {
            http_response_code(422);
            return View::make('users/form', [
                'title' => 'Edit Student',
                'mode' => 'edit',
                'studentId' => $studentId,
                'errors' => $errors,
                'form' => $input,
            ]);
        }

        $passwordHash = trim($input['password']) !== ''
            ? password_hash($input['password'], PASSWORD_DEFAULT)
            : null;

        $this->users->updateStudent($studentId, [
            ...$input,
            'password' => $passwordHash,
        ]);

        header('Location: /students');
        return '';
    }

    public function delete(string $id): string
    {
        $this->users->deleteStudent((int) $id);
        header('Location: /students');
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

        if ($input['username'] !== '' && $this->users->usernameExists($input['username'], $excludeId)) {
            $errors[] = 'Username is already used.';
        }

        if ($input['email'] !== '' && $this->users->emailExists($input['email'], $excludeId)) {
            $errors[] = 'Email is already used.';
        }

        return $errors;
    }
}
