<?php

namespace App\Controllers;

use App\Models\NoteModel;
use App\Models\UserModel;

class NoteController
{
    public function __construct(
        private NoteModel $notes,
        private UserModel $users
    ) {
    }

    public function store(string $profileId): string
    {
        $targetId = (int) $profileId;
        if ($this->users->findUser(['id' => $targetId]) === null) {
            http_response_code(404);
            return 'User not found.';
        }

        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $_SESSION['flash_error'] = 'Note content is required.';
            header('Location: /users/' . $targetId);
            return '';
        }

        $this->notes->create($targetId, (int) ($_SESSION['user_id'] ?? 0), $content);
        $_SESSION['flash_success'] = 'Note saved.';
        header('Location: /users/' . $targetId);
        return '';
    }

    public function update(string $noteId): string
    {
        $note = $this->notes->findById((int) $noteId);
        if ($note === null) {
            http_response_code(404);
            return 'Note not found.';
        }

        $writerId = (int) ($_SESSION['user_id'] ?? 0);
        if ((int) $note['writer_user_id'] !== $writerId) {
            http_response_code(403);
            return 'Forbidden.';
        }

        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $_SESSION['flash_error'] = 'Note content is required.';
            header('Location: /users/' . (int) $note['profile_user_id']);
            return '';
        }

        $this->notes->updateContent((int) $noteId, $writerId, $content);
        $_SESSION['flash_success'] = 'Note updated.';
        header('Location: /users/' . (int) $note['profile_user_id']);
        return '';
    }

    public function delete(string $noteId): string
    {
        $note = $this->notes->findById((int) $noteId);
        if ($note === null) {
            http_response_code(404);
            return 'Note not found.';
        }

        $writerId = (int) ($_SESSION['user_id'] ?? 0);
        if ((int) $note['writer_user_id'] !== $writerId) {
            http_response_code(403);
            return 'Forbidden.';
        }

        $this->notes->deleteByIdAndWriter((int) $noteId, $writerId);
        $_SESSION['flash_success'] = 'Note deleted.';
        header('Location: /users/' . (int) $note['profile_user_id']);
        return '';
    }
}
