<?php

use App\Controllers\HomeController;
use App\Controllers\PongController;
use App\Controllers\UsersController;
use App\Controllers\LoginController;
use App\Middleware\AuthMiddleware;

return [
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/ping' => [PongController::class, 'index'],
        '/login' => [LoginController::class, 'index'],
        '/logout' => [LoginController::class, 'logout'],
        '/users' => [
            'handler' => [UsersController::class, 'index'],
            'middleware' => [AuthMiddleware::class],
        ],
        '/user/{id}' => [
            'handler' => [UsersController::class, 'show'],
            'middleware' => [AuthMiddleware::class],
        ],
    ],
    'POST' => [
        '/login' => [LoginController::class, 'login'],
        '/logout' => [LoginController::class, 'logout'],
    ],
];