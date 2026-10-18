<?php

use Illuminate\Support\Str;

$isVercel = (bool) env('VERCEL', false) || str_starts_with(base_path(), '/var/task/');

return [
    'default' => env('CACHE_STORE', 'file'),
    'stores' => [
        'array' => ['driver' => 'array', 'serialize' => false],
        'file' => [
            'driver' => 'file',
            'path' => $isVercel ? '/tmp/civicdesk-cache' : storage_path('framework/cache/data'),
            'lock_path' => $isVercel ? '/tmp/civicdesk-cache' : storage_path('framework/cache/data'),
        ],
        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],
    ],
    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-cache-'),
];
