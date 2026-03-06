<?php

namespace App\Core;

use RuntimeException;

class View {
    public static function make(string $view, array $data = [], ?string $layout = 'layouts/app'): string {
        $shared = [
            'authUserId' => $_SESSION['user_id'] ?? null,
            'authUsername' => $_SESSION['username'] ?? null,
        ];

        $payload = array_merge($shared, $data);
        $content = self::render($view, $payload);

        if ($layout === null) {
            return $content;
        }

        return self::render($layout, array_merge($payload, ['content' => $content]));
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private static function render(string $view, array $data): string
    {
        $viewFile = __DIR__ . "/../../view/$view.view.php";
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View [{$view}] not found at {$viewFile}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;

        return ob_get_clean();
    }
}