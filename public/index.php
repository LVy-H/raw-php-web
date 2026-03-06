<?php

require_once __DIR__ . '/../autoload.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

use App\Core\Container;
use App\Core\Database;
use App\Core\Router;
use App\Models\UserModel;

$container = new Container();
$container->set(Database::class, static function (Container $container): Database {
    $dbConfig = require __DIR__ . '/../config/database.php';

    return new Database(
        $dbConfig['dsn'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['options'] ?? []
    );
});

$container->set(UserModel::class, static function (Container $container): UserModel {
    return new UserModel($container->get(Database::class));
});

$routes = require __DIR__ . '/../config/routes.php';

$router = new Router($routes, $container);

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

