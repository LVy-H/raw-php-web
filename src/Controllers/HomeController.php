<?php

namespace App\Controllers;

use App\Core\View;

class HomeController {
    public function index(): string
    {
        $documents = [
            ['title' => 'Course Outline', 'class' => 'CS101', 'updated_at' => '2026-03-01'],
            ['title' => 'Assignment Brief', 'class' => 'SE203', 'updated_at' => '2026-03-03'],
            ['title' => 'Lecture Notes Week 4', 'class' => 'DB202', 'updated_at' => '2026-03-05'],
        ];

        return View::make('index', [
            'title' => 'Class Document Manager',
            'documents' => $documents,
        ]);
    }
}