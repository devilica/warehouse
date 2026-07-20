<?php

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';

// Vercel strips /api when routing to api/index.php; restore for Laravel apiPrefix.
if (str_starts_with($path, '/v1') || str_starts_with($path, '/up')) {
    $_SERVER['REQUEST_URI'] = '/api'.$uri;
    if (! empty($_SERVER['PATH_INFO']) && str_starts_with($_SERVER['PATH_INFO'], '/v1')) {
        $_SERVER['PATH_INFO'] = '/api'.$_SERVER['PATH_INFO'];
    }
}

chdir(__DIR__.'/../public');
require __DIR__.'/../public/index.php';
