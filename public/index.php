<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $path = dirname(__DIR__) . '/app/' . substr($class, 4) . '.php';
    $path = str_replace('\\', '/', $path);
    if (is_file($path)) {
        require $path;
    }
});

use App\Database;
use App\Router;

if (!is_file(dirname(__DIR__) . '/data/gravekb.sqlite')) {
    Database::migrate();
}

Router::dispatch($_SERVER['REQUEST_URI'] ?? '/');
