<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\PracticeModel;
use App\Models\SubmissionModel;
use App\Core\BaseController;
use App\Core\Gate;
use App\Core\FileService;

class PracticeController extends BaseController
{
    public function __construct(
        private PracticeModel $practices,
        private SubmissionModel $submissions,
        private Gate $gate,
        private FileService $fileService
    ) {
    }

    public function index(): string
    {
        $practiceList = $this->practices->allWithUploader();
        $role = (string) ($_SESSION['user_role'] ?? '');
        $studentSubmissions = [];
        $successMessage = $_SESSION['flash_success'] ?? null;
        $errorMessage = $_SESSION['flash_error'] ?? null;

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        if ($role === 'student') {
            $studentId = (int) ($_SESSION['user_id'] ?? 0);
            foreach ($this->submissions->forStudent($studentId) as $submission) {
                $studentSubmissions[(int) $submission['practice_id']] = $submission;
            }
        }

        return View::make('practices/index', [
            'title' => 'Practices',
            'practices' => $practiceList,
            'studentSubmissions' => $studentSubmissions,
            'errors' => [],
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
        ]);
    }

    public function create(): string
    {
        return View::make('practices/create', [
            'title' => 'Upload Practice',
            'errors' => [],
            'form' => [
                'title' => '',
                'description' => '',
            ],
        ]);
    }

    public function store(): string
    {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $errors = [];

        if ($title === '') {
            $errors[] = 'Title is required.';
        }

        if (!isset($_FILES['practice_file']) || !is_array($_FILES['practice_file'])) {
            $errors[] = 'Practice file is required.';
        }

        $file = $_FILES['practice_file'] ?? null;
        if (is_array($file) && (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)) {
            $errors[] = $this->fileService->getUploadErrorMessage((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE));
        }

        if (!empty($errors)) {
            http_response_code(422);
            return View::make('practices/create', [
                'title' => 'Upload Practice',
                'errors' => $errors,
                'form' => [
                    'title' => $title,
                    'description' => $description,
                ],
            ]);
        }

        try {
            $storedName = $this->fileService->moveUploadedFile($file, 'practices');
        } catch (\RuntimeException) {
            http_response_code(422);
            return View::make('practices/create', [
                'title' => 'Upload Practice',
                'errors' => ['Failed to save uploaded file. Please try again.'],
                'form' => [
                    'title' => $title,
                    'description' => $description,
                ],
            ]);
        }

        $this->practices->create([
            'title' => $title,
            'description' => $description,
            'file_name' => (string) ($file['name'] ?? 'practice-file'),
            'stored_name' => $storedName,
            'uploaded_by' => (int) ($_SESSION['user_id'] ?? 0),
        ]);

        return $this->redirect('/practices', 'Practice uploaded successfully.');
    }

    public function download(string $id): string
    {
        $practice = $this->practices->findById((int) $id);
        if ($practice === null) {
            http_response_code(404);
            return 'Practice not found.';
        }

        $path = $this->fileService->uploadPath('practices') . '/' . $practice['stored_name'];
        return $this->fileService->streamFile($path, (string) $practice['file_name']);
    }

    public function submit(string $id): string
    {
        $this->authorize(($_SESSION['user_role'] ?? null) === 'student', 'Only students can submit practice files.');

        $practice = $this->practices->findById((int) $id);
        if ($practice === null) {
            return $this->abort(404, 'Practice not found.');
        }

        if (!isset($_FILES['submission_file']) || !is_array($_FILES['submission_file'])) {
            return $this->redirect('/practices', null, 'Submission file is required.');
        }

        $file = $_FILES['submission_file'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $this->redirect('/practices', null, $this->fileService->getUploadErrorMessage((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)));
        }

        try {
            $storedName = $this->fileService->moveUploadedFile($file, 'submissions');
        } catch (\RuntimeException) {
            return $this->redirect('/practices', null, 'Failed to save submission file. Please try again.');
        }

        $this->submissions->upsert(
            (int) $id,
            (int) ($_SESSION['user_id'] ?? 0),
            (string) ($file['name'] ?? 'submission-file'),
            $storedName
        );

        return $this->redirect('/practices', 'Submission uploaded successfully.');
    }

    public function submissions(string $id): string
    {
        $practice = $this->practices->findById((int) $id);
        if ($practice === null) {
            return $this->abort(404, 'Practice not found.');
        }

        return View::make('practices/submissions', [
            'title' => 'Practice Submissions',
            'practice' => $practice,
            'submissions' => $this->submissions->forPractice((int) $id),
        ]);
    }

    public function downloadSubmission(string $id): string
    {
        $submission = $this->submissions->findById((int) $id);
        if ($submission === null) {
            return $this->abort(404, 'Submission not found.');
        }

        $path = $this->fileService->uploadPath('submissions') . '/' . $submission['stored_name'];
        return $this->fileService->streamFile($path, (string) $submission['file_name']);    
    }
}
