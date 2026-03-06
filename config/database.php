<?php

use PDO;

$driver = getenv('DB_DRIVER') ?: 'sqlite';
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = (int) (getenv('DB_PORT') ?: 3306);
$database = getenv('DB_DATABASE') ?: 'app';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

$dsn = $driver === 'sqlite'
    ? 'sqlite:' . (getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../storage/database.sqlite')
    : sprintf('%s:host=%s;port=%d;dbname=%s;charset=%s', $driver, $host, $port, $database, $charset);

return [
    'driver' => $driver,
    'dsn' => $dsn,
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];