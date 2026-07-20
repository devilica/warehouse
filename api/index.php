<?php

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';

// Vercel strips /api when routing to api/index.php.
if (str_starts_with($path, '/v1')) {
    $_SERVER['REQUEST_URI'] = '/api'.$uri;
}

// Symfony derives pathInfo from SCRIPT_NAME; api/index.php would strip /api again.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';
unset($_SERVER['PATH_INFO'], $_SERVER['ORIG_PATH_INFO']);

chdir(__DIR__.'/../public');
require __DIR__.'/../public/index.php';
