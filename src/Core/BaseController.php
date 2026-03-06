<?php

namespace App\Core;

abstract class BaseController
{
    protected function redirect(string $url, ?string $success = null, ?string $error = null): string
    {
        if ($success !== null) {
            $_SESSION['flash_success'] = $success;
        }

        if ($error !== null) {
            $_SESSION['flash_error'] = $error;
        }

        header('Location: ' . $url);
        return '';
    }

    protected function abort(int $code, string $message = ''): string
    {
        http_response_code($code);
        if ($message === '') {
            $message = $code === 404 ? 'Resource not found.' : "Request failed.";
        }
        
        if ($code === 403) {
            return View::make('users/show', [
                'title' => 'Forbidden',
                'user' => null,
                'forbiddenMessage' => $message,
            ]);
        }

        return $message;
    }

    protected function authorize(bool $condition, string $message = 'Forbidden.'): void
    {
        if (!$condition) {
            echo $this->abort(403, $message);
            exit;
        }
    }
}
