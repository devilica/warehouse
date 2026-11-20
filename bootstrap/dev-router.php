<?php

/**
 * Router for `php artisan serve`.
 *
 * The public directory is resolved from this file, not getcwd(). On Windows the
 * built-in server reports the router script's directory as the working directory.
 */
$publicPath = dirname(__DIR__).DIRECTORY_SEPARATOR.'public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$remoteAddress = ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1').':'.($_SERVER['REMOTE_PORT'] ?? '0');

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.DIRECTORY_SEPARATOR.'index.php';
