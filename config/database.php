<?php

$driver = $_ENV['DB_DRIVER'] ?? $_SERVER['DB_DRIVER'] ?? 'sqlite';
$host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '127.0.0.1';
$port = (int) ($_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? 3306);
$database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? 'app';
$charset = $_ENV['DB_CHARSET'] ?? $_SERVER['DB_CHARSET'] ?? 'utf8mb4';

$dsn = $driver === 'sqlite'
    ? 'sqlite:' . ($_ENV['DB_SQLITE_PATH'] ?? $_SERVER['DB_SQLITE_PATH'] ?? __DIR__ . '/../storage/database.sqlite')
    : sprintf('%s:host=%s;port=%d;dbname=%s;charset=%s', $driver, $host, $port, $database, $charset);

return [
    'driver' => $driver,
    'dsn' => $dsn,
    'username' => $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];