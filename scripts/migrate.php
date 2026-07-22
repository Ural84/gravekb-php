<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $path = dirname(__DIR__) . '/app/' . substr($class, 4) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use App\Catalog;
use App\Database;

Database::migrate();
$count = Catalog::seedFromJson();
echo "Migrated. Products seeded: {$count}\n";
