<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\PracticeModel;
use App\Models\SubmissionModel;

class PracticeController
{
    public function __construct(
        private PracticeModel $practices,
        private SubmissionModel $submissions
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
            $errors[] = 'Practice file upload failed.';
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
            $storedName = $this->moveUploadedFile($file, 'practices');
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

        $_SESSION['flash_success'] = 'Practice uploaded successfully.';
        header('Location: /practices');
        return '';
    }

    public function download(string $id): string
    {
        $practice = $this->practices->findById((int) $id);
        if ($practice === null) {
            http_response_code(404);
            return 'Practice not found.';
        }

        $path = $this->uploadPath('practices') . '/' . $practice['stored_name'];
        return $this->streamFile($path, (string) $practice['file_name']);
    }

    public function submit(string $id): string
    {
        if (($_SESSION['user_role'] ?? null) !== 'student') {
            http_response_code(403);
            return 'Only students can submit practice files.';
        }

        $practice = $this->practices->findById((int) $id);
        if ($practice === null) {
            http_response_code(404);
            return 'Practice not found.';
        }

        if (!isset($_FILES['submission_file']) || !is_array($_FILES['submission_file'])) {
            $_SESSION['flash_error'] = 'Submission file is required.';
            header('Location: /practices');
            return '';
        }

        $file = $_FILES['submission_file'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Submission upload failed.';
            header('Location: /practices');
            return '';
        }

        try {
            $storedName = $this->moveUploadedFile($file, 'submissions');
        } catch (\RuntimeException) {
            $_SESSION['flash_error'] = 'Failed to save submission file. Please try again.';
            header('Location: /practices');
            return '';
        }

        $this->submissions->upsert(
            (int) $id,
            (int) ($_SESSION['user_id'] ?? 0),
            (string) ($file['name'] ?? 'submission-file'),
            $storedName
        );

        $_SESSION['flash_success'] = 'Submission uploaded successfully.';
        header('Location: /practices');
        return '';
    }

    public function submissions(string $id): string
    {
        $practice = $this->practices->findById((int) $id);
        if ($practice === null) {
            http_response_code(404);
            return 'Practice not found.';
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
            http_response_code(404);
            return 'Submission not found.';
        }

        $path = $this->uploadPath('submissions') . '/' . $submission['stored_name'];
        return $this->streamFile($path, (string) $submission['file_name']);
    }

    private function moveUploadedFile(array $file, string $folder): string
    {
        $originalName = (string) ($file['name'] ?? 'file');
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $storedName = bin2hex(random_bytes(16));

        if ($extension !== '') {
            $storedName .= '.' . strtolower($extension);
        }

        $directory = $this->uploadPath($folder);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !move_uploaded_file($tmpName, $directory . '/' . $storedName)) {
            throw new \RuntimeException('Failed to move uploaded file.');
        }

        return $storedName;
    }

    private function uploadPath(string $folder): string
    {
        return dirname(__DIR__, 2) . '/storage/uploads/' . $folder;
    }

    private function streamFile(string $path, string $downloadName): string
    {
        if (!is_file($path)) {
            http_response_code(404);
            return 'File not found.';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
        header('Content-Length: ' . (string) filesize($path));

        readfile($path);
        exit;
    }
}
