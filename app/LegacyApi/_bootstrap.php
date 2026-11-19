<?php

if (!function_exists('app')) {
    $basePath = dirname(__DIR__, 2);
    if (file_exists($basePath . '/vendor/autoload.php')) {
        require_once $basePath . '/vendor/autoload.php';
    }
    if (file_exists($basePath . '/bootstrap/app.php')) {
        $app = require_once $basePath . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }
}
