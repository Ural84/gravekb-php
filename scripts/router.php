<?php
declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = dirname(__DIR__) . '/public' . $uri;

// Отдаём существующие файлы (css/js/img) встроенным сервером PHP
if ($uri !== '/' && is_file($file)) {
    return false;
}

require dirname(__DIR__) . '/public/index.php';
