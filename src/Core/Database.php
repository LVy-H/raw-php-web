<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Database
{
    private PDO $pdo;

    public function __construct(string $dsn, string $username, #[\SensitiveParameter] string $password, array $options = [])
    {
        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed.', previous: $e);
        }
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}