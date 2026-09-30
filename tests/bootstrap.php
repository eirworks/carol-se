<?php

/**
 * Test bootstrap.
 *
 * Prefers the package's own Composer autoloader when dependencies are
 * installed, otherwise falls back to the host application's vendor directory
 * so the suite can run from inside a Laravel app checkout.
 */

$packageAutoload = __DIR__ . '/../vendor/autoload.php';
$hostAutoload = __DIR__ . '/../../../vendor/autoload.php';

if (is_file($packageAutoload)) {
    require $packageAutoload;
} elseif (is_file($hostAutoload)) {
    require $hostAutoload;
}

// Make the package classes autoloadable when the host app's autoloader is used.
spl_autoload_register(function (string $class): void {
    $prefix = 'eirworks\\carol\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
}, true, true);
