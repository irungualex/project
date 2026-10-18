<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Vercel's deployment bundle is read-only. Keep Laravel's runtime files in /tmp.
if (getenv('VERCEL') || str_starts_with(__DIR__, '/var/task/')) {
    foreach (['/tmp/civicdesk-views', '/tmp/civicdesk-sessions', '/tmp/civicdesk-cache'] as $runtimeDirectory) {
        if (! is_dir($runtimeDirectory)) {
            @mkdir($runtimeDirectory, 0775, true);
        }
    }
}

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
