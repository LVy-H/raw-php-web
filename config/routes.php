<?php

use App\Controllers\HomeController;
use App\Controllers\GameController;
use App\Controllers\NoteController;
use App\Controllers\PongController;
use App\Controllers\PracticeController;
use App\Controllers\UsersController;
use App\Controllers\LoginController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\TeacherMiddleware;

return [
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/ping' => [PongController::class, 'index'],
        '/login' => [
            'handler' => [LoginController::class, 'index'],
            'middleware' => [GuestMiddleware::class],
        ],
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
        '/games' => [
            'handler' => [GameController::class, 'index'],
            'middleware' => [AuthMiddleware::class],
        ],
        '/submissions/{id}/download' => [
            'handler' => [PracticeController::class, 'downloadSubmission'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class],
        ],
    ],
    'POST' => [
        '/login' => [
            'handler' => [LoginController::class, 'login'],
            'middleware' => [GuestMiddleware::class, CsrfMiddleware::class],
        ],
        '/logout' => [
            'handler' => [LoginController::class, 'logout'],
            'middleware' => [CsrfMiddleware::class],
        ],
        '/users' => [
            'handler' => [UsersController::class, 'store'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class, CsrfMiddleware::class],
        ],
        '/users/{id}/update' => [
            'handler' => [UsersController::class, 'update'],
            'middleware' => [AuthMiddleware::class, CsrfMiddleware::class],
        ],
        '/users/{id}/delete' => [
            'handler' => [UsersController::class, 'delete'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class, CsrfMiddleware::class],
        ],
        '/users/{id}/notes' => [
            'handler' => [NoteController::class, 'store'],
            'middleware' => [AuthMiddleware::class, CsrfMiddleware::class],
        ],
        '/notes/{id}/update' => [
            'handler' => [NoteController::class, 'update'],
            'middleware' => [AuthMiddleware::class, CsrfMiddleware::class],
        ],
        '/notes/{id}/delete' => [
            'handler' => [NoteController::class, 'delete'],
            'middleware' => [AuthMiddleware::class, CsrfMiddleware::class],
        ],
        '/practices' => [
            'handler' => [PracticeController::class, 'store'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class, CsrfMiddleware::class],
        ],
        '/practices/{id}/submit' => [
            'handler' => [PracticeController::class, 'submit'],
            'middleware' => [AuthMiddleware::class, CsrfMiddleware::class],
        ],
        '/games' => [
            'handler' => [GameController::class, 'store'],
            'middleware' => [AuthMiddleware::class, TeacherMiddleware::class, CsrfMiddleware::class],
        ],
        '/games/{id}/guess' => [
            'handler' => [GameController::class, 'guess'],
            'middleware' => [AuthMiddleware::class, CsrfMiddleware::class],
        ],
    ],
];