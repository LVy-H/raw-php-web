<?php

spl_autoload_register(function ($class) {
    $prefix = "App\\";
    $basedir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (!str_starts_with($class, $prefix))
        return;
    $relativeClass = substr($class, $len);

    $file = $basedir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file))
        require_once $file;
});