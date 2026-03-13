<?php

namespace App\Controllers;

use App\Models\NoteModel;
use App\Models\UserModel;
use App\Core\BaseController;
use App\Core\Gate;

class NoteController extends BaseController
{
    public function __construct(
        private NoteModel $notes,
        private UserModel $users,
        private Gate $gate
    ) {
    }

    public function store(string $profileId): string
    {
        $targetId = (int) $profileId;
        if ($this->users->findUser(['id' => $targetId]) === null) {
            return $this->abort(404, 'User not found.');
        }

        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            return $this->redirect('/users/' . $targetId, null, 'Note content is required.');
        }

        $this->notes->create($targetId, (int) ($_SESSION['user_id'] ?? 0), $content);
        return $this->redirect('/users/' . $targetId, 'Note saved.');
    }

    public function update(string $noteId): string
    {
        $note = $this->notes->findById((int) $noteId);
        if ($note === null) {
            return $this->abort(404, 'Note not found.');
        }

        $note['_type'] = 'note';
        $this->authorize($this->gate->allows('update', $note));

        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            return $this->redirect('/users/' . (int) $note['profile_user_id'], null, 'Note content is required.');
        }

        $this->notes->updateContent((int) $noteId, (int) ($_SESSION['user_id'] ?? 0), $content);
        return $this->redirect('/users/' . (int) $note['profile_user_id'], 'Note updated.');
    }

    public function delete(string $noteId): string
    {
        $note = $this->notes->findById((int) $noteId);
        if ($note === null) {
            return $this->abort(404, 'Note not found.');
        }

        $note['_type'] = 'note';
        $this->authorize($this->gate->allows('delete', $note));

        $this->notes->deleteByIdAndWriter((int) $noteId, (int) ($_SESSION['user_id'] ?? 0));
        return $this->redirect('/users/' . (int) $note['profile_user_id'], 'Note deleted.');
    }
}
