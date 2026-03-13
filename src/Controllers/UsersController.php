<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\NoteModel;
use App\Models\UserModel;
use App\Core\BaseController;
use App\Core\Gate;
use App\Core\ValidationService;
use App\Core\FileService;

class UsersController extends BaseController
{
    public function __construct(
        private UserModel $users,
        private NoteModel $notes,
        private Gate $gate,
        private ValidationService $validator,
        private FileService $fileService
    )
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
        $memberId = (int) $id;
        $user = $this->users->findUser(['id' => $memberId]);

        if ($user === null) {
            return $this->abort(404, 'User Not Found');
        }

        $viewerId = (int) ($_SESSION['user_id'] ?? 0);
        $successMessage = $_SESSION['flash_success'] ?? null;
        $errorMessage = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        return View::make('users/show', [
            'title' => 'User Profile',
            'user' => $user,
            'notes' => $this->notes->listVisibleForProfile($memberId, $viewerId),
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
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
        $errors = $this->validator->validate($input, [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($input['username'] !== '' && $this->users->exists('username', $input['username'])) {
            $errors[] = 'Username is already used.';
        }

        if ($input['email'] !== '' && $this->users->exists('email', $input['email'])) {
            $errors[] = 'Email is already used.';
        }

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

        return $this->redirect('/users');
    }

    public function edit(string $id): string
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $role = (string) ($_SESSION['user_role'] ?? '');
        $targetId = (int) $id;
        $target = $this->users->findUser(['id' => $targetId]);

        if ($target === null) {
            return $this->abort(404, 'User Not Found');
        }

        $target['_type'] = 'user'; // Inject type for the Gate
        $this->authorize($this->gate->allows('update', $target));

        $mode = $role === 'teacher' ? 'edit-teacher' : 'edit-self';

        return View::make('users/form', [
            'title' => $role === 'teacher' ? 'Edit Student' : 'Edit My Account',
            'mode' => $mode,
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

    public function update(string $id): string
    {
        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $currentRole = (string) ($_SESSION['user_role'] ?? '');
        $targetId = (int) $id;
        $target = $this->users->findUser(['id' => $targetId]);

        if ($target === null) {
            return $this->abort(404, 'User Not Found');
        }

        $target['_type'] = 'user';
        $this->authorize($this->gate->allows('update', $target));

        $input = $this->collectInput();
        $isTeacher = $currentRole === 'teacher';

        $rules = [
            'email' => 'required|email',
            'phone' => 'required',
        ];

        if ($isTeacher) {
            $rules['name'] = 'required';
            $rules['username'] = 'required';
        }

        $errors = $this->validator->validate($input, $rules);

        if ($isTeacher && $input['username'] !== '' && $this->users->exists('username', $input['username'], $targetId)) {
            $errors[] = 'Username is already used.';
        }

        if ($input['email'] !== '' && $this->users->exists('email', $input['email'], $targetId)) {
            $errors[] = 'Email is already used.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            $mode = $isTeacher ? 'edit-teacher' : 'edit-self';
            return View::make('users/form', [
                'title' => $isTeacher ? 'Edit Student' : 'Edit My Account',
                'mode' => $mode,
                'studentId' => $targetId,
                'errors' => $errors,
                'form' => [
                    'name' => $isTeacher ? $input['name'] : ($target['name'] ?? ''),
                    'email' => $input['email'],
                    'phone' => $input['phone'],
                    'username' => $isTeacher ? $input['username'] : ($target['username'] ?? ''),
                ],
            ]);
        }

        $passwordHash = trim($input['password']) !== ''
            ? password_hash($input['password'], PASSWORD_DEFAULT)
            : null;

        $updateData = [
            'email' => $input['email'],
            'phone' => $input['phone'],
            'password' => $passwordHash,
        ];

        if ($isTeacher) {
            $updateData['name'] = $input['name'];
            $updateData['username'] = $input['username'];
        }

        if (isset($_FILES['avatar_file']) && is_array($_FILES['avatar_file'])) {
            $file = $_FILES['avatar_file'];
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                try {
                    $storedName = $this->fileService->moveUploadedFile($file, 'avatars');
                    $updateData['avatar'] = $storedName;
                } catch (\RuntimeException) {
                    $_SESSION['flash_error'] = 'Failed to upload avatar.';
                }
            }
        }

        $this->users->updateUser($targetId, $updateData, 'student');

        if ($isTeacher) {
            return $this->redirect('/users');
        }

        return $this->redirect('/users/' . $targetId);
    }

    public function delete(string $id): string
    {
        $this->users->deleteUser((int) $id, 'student');
        return $this->redirect('/users');
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

    public function avatar(string $id): string
    {
        $targetId = (int) $id;
        $target = $this->users->findUser(['id' => $targetId]);

        if ($target === null || empty($target['avatar'])) {
            return $this->abort(404, 'Avatar Not Found');
        }

        $path = $this->fileService->uploadPath('avatars') . '/' . $target['avatar'];
        return $this->fileService->streamFile($path, $target['avatar']);
    }

}
