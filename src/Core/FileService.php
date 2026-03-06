<?php

namespace App\Core;

class FileService
{
    public function moveUploadedFile(array $file, string $folder): string
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

    public function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File is too large for server limit. Current upload_max_filesize is ' . ini_get('upload_max_filesize') . '.',
            UPLOAD_ERR_FORM_SIZE => 'File is too large for form limit.',
            UPLOAD_ERR_PARTIAL => 'File upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE => 'Please choose a file to upload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server temporary upload directory is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Server failed to write uploaded file.',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by a PHP extension.',
            default => 'Upload failed due to an unknown error.',
        };
    }

    public function streamFile(string $path, string $downloadName): string
    {
        if (!is_file($path)) {
            http_response_code(404);
            return 'File not found.';
        }

        $safeName = basename($downloadName);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . str_replace(['"', '\\'], '_', $safeName) . '"; filename*=UTF-8\'\'' . rawurlencode($safeName));
        header('Content-Length: ' . (string) filesize($path));

        readfile($path);
        exit;
    }

    public function uploadPath(string $folder): string
    {
        return dirname(__DIR__, 2) . '/storage/uploads/' . ltrim($folder, '/');
    }
}
