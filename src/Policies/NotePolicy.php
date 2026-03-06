<?php

namespace App\Policies;

class NotePolicy
{
    public function update(array $user, array $note): bool
    {
        return $user['id'] === (int) $note['writer_user_id'];
    }

    public function delete(array $user, array $note): bool
    {
        return $user['id'] === (int) $note['writer_user_id'];
    }
}
