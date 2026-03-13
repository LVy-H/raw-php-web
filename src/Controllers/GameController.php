<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\BaseController;
use App\Core\Gate;

use App\Core\FileService;

class GameController extends BaseController
{
    public function __construct(
        private Gate $gate,
        private FileService $fileService
    ) {
    }
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
        $this->authorize(($_SESSION['user_role'] ?? null) === 'teacher', 'Only teachers can create games.');

        $hint = trim($_POST['hint'] ?? '');
        if ($hint === '') {
            return $this->redirect('/games', null, 'Hint is required.');
        }

        $file = $_FILES['game_file'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $this->redirect('/games', null, 'Game file upload failed.');
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension !== 'txt') {
            return $this->redirect('/games', null, 'Only .txt files are allowed.');
        }

        $gameId = bin2hex(random_bytes(8));
        $folder = $this->gamesRoot() . '/' . $gameId;
        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        try {
            $uploadedName = $this->fileService->moveUploadedFile($file, 'games/' . $gameId, true);
        } catch (\RuntimeException) {
            return $this->redirect('/games', null, 'Failed to save uploaded game file.');
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

        return $this->redirect('/games', 'Game created successfully.');
    }

    public function guess(string $id): string
    {
        $this->authorize(($_SESSION['user_role'] ?? null) === 'student', 'Only students can submit guesses.');

        $meta = $this->loadGameMeta($id);
        if ($meta === null) {
            return $this->abort(404, 'Game not found.');
        }

        $rewardFile = $this->resolveRewardFile($id);
        if ($rewardFile === null) {
            return $this->redirect('/games', null, 'Reward file not found.');
        }

        $guess = str_replace('.', '%2E', rawurldecode(trim($_POST['guess'] ?? '')));
        $answer = $rewardFile['filename'];
        $correct = strcasecmp($guess, $answer) === 0;

        if (!$correct) {
            return $this->redirect('/games', null, 'Wrong guess. Try again.');
        }

        return View::make('games/index', [
            'title' => 'Filename Guess Game',
            'games' => $this->loadGames(),
            'successMessage' => 'Correct! Reward unlocked.',
            'errorMessage' => null,
            'reward' => [
                'game_id' => $id,
                'answer' => rawurldecode($answer),
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
                'filename' => str_replace('.', '%2E', rawurldecode(basename($path))),
            ];
        }

        return null;
    }
}