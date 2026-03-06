<?php

namespace App\Controllers;

use App\Core\View;

class HomeController {
    function index() {
        return View::make('index');
    }
}