<?php

namespace App\Policies;

class UserPolicy
{
    public function update(array $user, array $targetUser): bool
    {
        if ($user['role'] === 'teacher') {
            return ($targetUser['role'] ?? 'student') === 'student';
        }

        if ($user['role'] === 'student') {
            return $user['id'] === (int) ($targetUser['id'] ?? 0) 
                && ($targetUser['role'] ?? 'student') === 'student';
        }

        return false;
    }
}
