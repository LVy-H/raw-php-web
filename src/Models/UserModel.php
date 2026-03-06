<?php

namespace App\Models;

use PDO;

class UserModel extends BaseModel
{
    public function allPublic(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name, email, phone, username, role FROM users ORDER BY id DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPublicById(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT id, name, email, phone, username, role FROM users WHERE id = :id",
            ['id' => $id]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE username = :username", ['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE id = :id", ['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findStudentById(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT id, name, email, phone, username, role FROM users WHERE id = :id AND role = 'student'",
            ['id' => $id]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allStudents(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name, email, phone, username, role FROM users WHERE role = 'student' ORDER BY id DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE email = :email", ['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE username = :username';
        $params = ['username' => $username];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->db->query($sql, $params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->db->query($sql, $params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function createStudent(array $data): void
    {
        $this->db->query(
            'INSERT INTO users (name, email, phone, username, password, role)
            VALUES (:name, :email, :phone, :username, :password, :role)',
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => 'student',
            ]
        );
    }

    public function updateStudent(int $id, array $data): void
    {
        $sql = 'UPDATE users
                SET name = :name,
                    email = :email,
                    phone = :phone,
                    username = :username';

        $params = [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'username' => $data['username'],
        ];

        if (!empty($data['password'])) {
            $sql .= ', password = :password';
            $params['password'] = $data['password'];
        }

        $sql .= " WHERE id = :id AND role = 'student'";

        $this->db->query($sql, $params);
    }

    public function deleteStudent(int $id): void
    {
        $this->db->query("DELETE FROM users WHERE id = :id AND role = 'student'", ['id' => $id]);
    }

    public function updateStudentSelf(int $id, array $data): void
    {
        $sql = 'UPDATE users
                SET email = :email,
                    phone = :phone';

        $params = [
            'id' => $id,
            'email' => $data['email'],
            'phone' => $data['phone'],
        ];

        if (!empty($data['password'])) {
            $sql .= ', password = :password';
            $params['password'] = $data['password'];
        }

        $sql .= " WHERE id = :id AND role = 'student'";

        $this->db->query($sql, $params);
    }
}