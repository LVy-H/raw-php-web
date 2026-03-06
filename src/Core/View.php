<?php

namespace App\Core;

class View {
    public static function make(string $view, array $data = []): string {
        $viewFile = __DIR__ . "/../../view/$view.view.php";
        if (!file_exists($viewFile)) {
            throw new \Exception("View [{$view}] not found at {$viewFile}");
        }

        extract($data, EXTR_SKIP);

        ob_start();

        require $viewFile;

        return ob_get_clean();
    } 
}