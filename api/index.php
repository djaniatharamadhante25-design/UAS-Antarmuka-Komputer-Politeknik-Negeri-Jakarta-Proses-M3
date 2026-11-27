<?php

// Ensure required writable directories exist in serverless /tmp
$dirs = [
    '/tmp/views',
    '/tmp/storage',
    '/tmp/storage/framework',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Ensure compiled views directory is pointed to /tmp/views
putenv('VIEW_COMPILED_PATH=/tmp/views');

// Forward Vercel request to Laravel's public entrypoint
require __DIR__ . '/../public/index.php';
