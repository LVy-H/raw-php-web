<?php

use App\Controllers\HomeController;
use App\Controllers\PongController;
use App\Controllers\PracticeController;
use App\Controllers\UsersController;
use App\Controllers\LoginController;
use App\Middleware\AuthMiddleware;
use App\Middleware\TeacherMiddleware;

return [
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/ping' => [PongController::class, 'index'],
        '/login' => [LoginController::class, 'index'],
        '/users' => [
            'handler' => [UsersController::class, 'index'],
            'middleware' => [AuthMiddleware::class],
        ],
        '/users/create' => [
            'handler' => [UsersController::class, 'create'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class],
        ],
        '/users/{id}' => [
            'handler' => [UsersController::class, 'show'],
            'middleware' => [AuthMiddleware::class],
        ],
        '/users/{id}/edit' => [
            'handler' => [UsersController::class, 'edit'],
            'middleware' => [AuthMiddleware::class],
        ],
        '/practices' => [
            'handler' => [PracticeController::class, 'index'],
            'middleware' => [AuthMiddleware::class],
        ],
        '/practices/create' => [
            'handler' => [PracticeController::class, 'create'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class],
        ],
        '/practices/{id}/download' => [
            'handler' => [PracticeController::class, 'download'],
            'middleware' => [AuthMiddleware::class],
        ],
        '/practices/{id}/submissions' => [
            'handler' => [PracticeController::class, 'submissions'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class],
        ],
        '/submissions/{id}/download' => [
            'handler' => [PracticeController::class, 'downloadSubmission'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class],
        ],
    ],
    'POST' => [
        '/login' => [LoginController::class, 'login'],
        '/logout' => [LoginController::class, 'logout'],
        '/users' => [
            'handler' => [UsersController::class, 'store'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class],
        ],
        '/users/{id}/update' => [
            'handler' => [UsersController::class, 'update'],
            'middleware' => [AuthMiddleware::class],
        ],
        '/users/{id}/delete' => [
            'handler' => [UsersController::class, 'delete'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class],
        ],
        '/practices' => [
            'handler' => [PracticeController::class, 'store'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class],
        ],
        '/practices/{id}/submit' => [
            'handler' => [PracticeController::class, 'submit'],
            'middleware' => [AuthMiddleware::class],
        ],
    ],
];