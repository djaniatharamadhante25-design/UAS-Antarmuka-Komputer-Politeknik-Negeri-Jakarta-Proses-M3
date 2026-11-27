<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Prepare writable directories in /tmp for serverless environment
$storagePath = '/tmp/storage';
$dirs = [
    '/tmp/views',
    $storagePath,
    $storagePath . '/framework',
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// 2. Set environment defaults for serverless environment
putenv('VIEW_COMPILED_PATH=/tmp/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';

// Show debug trace so we can pinpoint errors if any
putenv('APP_DEBUG=true');
$_ENV['APP_DEBUG'] = 'true';

if (empty($_ENV['APP_KEY']) && !getenv('APP_KEY')) {
    putenv('APP_KEY=base64:5nHw0MHWC1QoRUHUCkGfU8Rkhyc39ODjaThZDTqlr8k=');
    $_ENV['APP_KEY'] = 'base64:5nHw0MHWC1QoRUHUCkGfU8Rkhyc39ODjaThZDTqlr8k=';
}

if (empty($_ENV['SESSION_DRIVER']) && !getenv('SESSION_DRIVER')) {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = 'cookie';
}

if (empty($_ENV['CACHE_STORE']) && !getenv('CACHE_STORE')) {
    putenv('CACHE_STORE=array');
    $_ENV['CACHE_STORE'] = 'array';
}

if (empty($_ENV['OTAKU_CACHE_STORE']) && !getenv('OTAKU_CACHE_STORE')) {
    putenv('OTAKU_CACHE_STORE=array');
    $_ENV['OTAKU_CACHE_STORE'] = 'array';
}

if (empty($_ENV['LOG_CHANNEL']) && !getenv('LOG_CHANNEL')) {
    putenv('LOG_CHANNEL=stderr');
    $_ENV['LOG_CHANNEL'] = 'stderr';
}

// Safe database fallback in /tmp if cloud DB not configured
if (empty($_ENV['DB_HOST']) && !getenv('DB_HOST')) {
    $sqlitePath = '/tmp/database.sqlite';
    if (!file_exists($sqlitePath)) {
        @touch($sqlitePath);
    }
    putenv('DB_CONNECTION=sqlite');
    $_ENV['DB_CONNECTION'] = 'sqlite';
    putenv("DB_DATABASE={$sqlitePath}");
    $_ENV['DB_DATABASE'] = $sqlitePath;
}

// 3. Register Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// 4. Bootstrap Laravel
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 5. Force storage path to /tmp/storage so Laravel never touches read-only /var/task
$app->useStoragePath($storagePath);

// 6. Handle request
$app->handleRequest(Request::capture());
