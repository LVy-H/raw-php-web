<?php

namespace App\Models;

use InvalidArgumentException;

class UserModel extends BaseModel
{
    private const ALLOWED_COLUMNS = ['id', 'name', 'email', 'phone', 'username', 'role', 'password', '*'];
    private const ALLOWED_FILTER_FIELDS = ['id', 'username', 'email', 'role'];
    private const ALLOWED_EXISTS_FIELDS = ['username', 'email'];

    public function listUsers(array $filters = [], array $columns = ['id', 'name', 'email', 'phone', 'username', 'role']): array
    {
        $this->validateColumns($columns);
        [$where, $params] = $this->buildWhere($filters);
        $sql = sprintf('SELECT %s FROM users%s ORDER BY id DESC', implode(', ', $columns), $where);
        return $this->db->query($sql, $params)->fetchAll();
    }

    public function findUser(array $filters, array $columns = ['id', 'name', 'email', 'phone', 'username', 'role']): ?array
    {
        $this->validateColumns($columns);
        [$where, $params] = $this->buildWhere($filters);
        $sql = sprintf('SELECT %s FROM users%s LIMIT 1', implode(', ', $columns), $where);
        return $this->db->query($sql, $params)->fetch() ?: null;
    }

    public function exists(string $field, string $value, ?int $excludeId = null): bool
    {
        if (!in_array($field, self::ALLOWED_EXISTS_FIELDS, true)) {
            throw new InvalidArgumentException("Field '{$field}' is not allowed in exists().");
        }

        $params = ['value' => $value];
        $sql = "SELECT COUNT(*) FROM users WHERE {$field} = :value";
        if ($excludeId !== null) {
            $sql .= ' AND id != :excludeId';
            $params['excludeId'] = $excludeId;
        }
        return (int) $this->db->query($sql, $params)->fetchColumn() > 0;
    }

    public function createUser(array $data): void
    {
        $this->db->query(
            'INSERT INTO users (name, email, phone, username, password, role)
             VALUES (:name, :email, :phone, :username, :password, :role)',
            $data
        );
    }

    public function updateUser(int $id, array $data, ?string $role = null): void
    {
        $allowed = ['name', 'email', 'phone', 'username', 'password'];
        $set = [];
        $params = ['id' => $id];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null && $data[$field] !== '') {
                $set[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }
        if (empty($set)) {
            return;
        }
        $sql = 'UPDATE users SET ' . implode(', ', $set) . ' WHERE id = :id';
        if ($role !== null) {
            $sql .= ' AND role = :role';
            $params['role'] = $role;
        }
        $this->db->query($sql, $params);
    }

    public function deleteUser(int $id, ?string $role = null): void
    {
        $params = ['id' => $id];
        $sql = 'DELETE FROM users WHERE id = :id';
        if ($role !== null) {
            $sql .= ' AND role = :role';
            $params['role'] = $role;
        }
        $this->db->query($sql, $params);
    }

    private function validateColumns(array $columns): void
    {
        foreach ($columns as $col) {
            if (!in_array($col, self::ALLOWED_COLUMNS, true)) {
                throw new InvalidArgumentException("Column '{$col}' is not allowed.");
            }
        }
    }

    private function buildWhere(array $filters): array
    {
        if (empty($filters)) {
            return ['', []];
        }
        $clauses = [];
        $params = [];
        foreach ($filters as $field => $value) {
            if (!in_array($field, self::ALLOWED_FILTER_FIELDS, true)) {
                throw new InvalidArgumentException("Field '{$field}' is not allowed as a filter.");
            }
            $param = 'f_' . $field;
            $clauses[] = "{$field} = :{$param}";
            $params[$param] = $value;
        }
        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }
}