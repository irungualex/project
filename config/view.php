<?php

$isVercel = (bool) env('VERCEL', false) || str_starts_with(base_path(), '/var/task/');
$compiledPath = env('VIEW_COMPILED_PATH', $isVercel ? '/tmp/civicdesk-views' : storage_path('framework/views'));

if (! is_dir($compiledPath) && ! @mkdir($compiledPath, 0775, true) && ! is_dir($compiledPath)) {
    throw new RuntimeException("Unable to create the compiled view directory: {$compiledPath}");
}

return [
    'paths' => [resource_path('views')],
    'compiled' => $compiledPath,
];
