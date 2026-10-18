<?php

$stylesheet = __DIR__.'/../public/css/app.css';

if (! is_file($stylesheet)) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/css; charset=UTF-8');
header('Cache-Control: public, max-age=0, must-revalidate');
header('X-Content-Type-Options: nosniff');

readfile($stylesheet);
