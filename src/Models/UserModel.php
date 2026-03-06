<?php

namespace App\Models;

use PDO;

class UserModel extends BaseModel
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE id = :id", ['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE email = :email", ['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}