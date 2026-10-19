<?php

/**
 * Laravel built-in web server router with public asset handler.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$publicFile = __DIR__.'/public'.$uri;

if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    $extension = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'map' => 'application/json',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
    }

    readfile($publicFile);
    exit;
}

require_once __DIR__.'/public/index.php';
