<?php

namespace App\Controllers;

use App\Core\View;

class GameController
{
    public function index(): string
    {
        $successMessage = $_SESSION['flash_success'] ?? null;
        $errorMessage = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        return View::make('games/index', [
            'title' => 'Filename Guess Game',
            'games' => $this->loadGames(),
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
        ]);
    }

    public function store(): string
    {
        if (($_SESSION['user_role'] ?? null) !== 'teacher') {
            http_response_code(403);
            return 'Only teachers can create games.';
        }

        $hint = trim($_POST['hint'] ?? '');
        if ($hint === '') {
            $_SESSION['flash_error'] = 'Hint is required.';
            header('Location: /games');
            return '';
        }

        $file = $_FILES['game_file'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Game file upload failed.';
            header('Location: /games');
            return '';
        }

        $gameId = bin2hex(random_bytes(8));
        $folder = $this->gamesRoot() . '/' . $gameId;
        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        $uploadedName = basename((string) ($file['name'] ?? 'reward.txt'));
        if ($uploadedName === '') {
            $uploadedName = 'reward.txt';
        }

        $filePath = $folder . '/' . $uploadedName;
        if (!move_uploaded_file((string) $file['tmp_name'], $filePath)) {
            $_SESSION['flash_error'] = 'Failed to save uploaded game file.';
            header('Location: /games');
            return '';
        }

        $meta = [
            'id' => $gameId,
            'hint' => $hint,
            'created_by' => (int) ($_SESSION['user_id'] ?? 0),
            'created_at' => date('c'),
        ];
        $metaRoot = $this->gamesMetaRoot();
        if (!is_dir($metaRoot)) {
            mkdir($metaRoot, 0775, true);
        }
        file_put_contents($metaRoot . '/' . $gameId . '.json', json_encode($meta, JSON_PRETTY_PRINT));

        $_SESSION['flash_success'] = 'Game created successfully.';
        header('Location: /games');
        return '';
    }

    public function guess(string $id): string
    {
        if (($_SESSION['user_role'] ?? null) !== 'student') {
            http_response_code(403);
            return 'Only students can submit guesses.';
        }

        $meta = $this->loadGameMeta($id);
        if ($meta === null) {
            http_response_code(404);
            return 'Game not found.';
        }

        $rewardFile = $this->resolveRewardFile($id);
        if ($rewardFile === null) {
            $_SESSION['flash_error'] = 'Reward file not found.';
            header('Location: /games');
            return '';
        }

        $guess = trim($_POST['guess'] ?? '');
        $answer = $rewardFile['filename'];
        $correct = strcasecmp($guess, $answer) === 0;

        if (!$correct) {
            $_SESSION['flash_error'] = 'Wrong guess. Try again.';
            header('Location: /games');
            return '';
        }

        return View::make('games/index', [
            'title' => 'Filename Guess Game',
            'games' => $this->loadGames(),
            'successMessage' => 'Correct! Reward unlocked.',
            'errorMessage' => null,
            'reward' => [
                'game_id' => $id,
                'answer' => $answer,
                'content' => (string) file_get_contents($rewardFile['path']),
            ],
        ]);
    }

    private function loadGames(): array
    {
        $metaRoot = $this->gamesMetaRoot();
        if (!is_dir($metaRoot)) {
            return [];
        }

        $games = [];
        foreach (glob($metaRoot . '/*.json') ?: [] as $metaFile) {
            $meta = json_decode((string) file_get_contents($metaFile), true);
            if (is_array($meta)) {
                $folderId = (string) ($meta['id'] ?? '');
                $rewardFile = $this->resolveRewardFile($folderId);
                if ($rewardFile !== null) {
                    $meta['answer_filename'] = $rewardFile['filename'];
                }
                $games[] = $meta;
            }
        }

        usort($games, static fn(array $a, array $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
        return $games;
    }

    private function loadGameMeta(string $id): ?array
    {
        if (!preg_match('/^[a-f0-9]{16}$/', $id)) {
            return null;
        }

        $metaFile = $this->gamesMetaRoot() . '/' . $id . '.json';
        if (!is_file($metaFile)) {
            return null;
        }

        $meta = json_decode((string) file_get_contents($metaFile), true);
        return is_array($meta) ? $meta : null;
    }

    private function gamesRoot(): string
    {
        return $this->uploadsRoot() . '/games';
    }

    private function gamesMetaRoot(): string
    {
        return $this->uploadsRoot() . '/games-meta';
    }

    private function uploadsRoot(): string
    {
        return dirname(__DIR__, 2) . '/storage/uploads';
    }

    private function resolveRewardFile(string $gameId): ?array
    {
        if (!preg_match('/^[a-f0-9]{16}$/', $gameId)) {
            return null;
        }

        $folder = $this->gamesRoot() . '/' . $gameId;
        if (!is_dir($folder)) {
            return null;
        }

        foreach (glob($folder . '/*') ?: [] as $path) {
            if (!is_file($path)) {
                continue;
            }

            return [
                'path' => $path,
                'filename' => basename($path),
            ];
        }

        return null;
    }
}