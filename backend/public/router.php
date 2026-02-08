<?php

/**
 * Routeur pour le serveur PHP intégré (php -S).
 * Sert les fichiers statiques s'ils existent, sinon délègue à Symfony.
 * En-têtes CORS envoyés ici pour être présents même si une couche proxy ne les transmet pas.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
if ($uri !== '' && $uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

$allowedOrigins = [
    'http://72.60.189.212:3500',
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost:4173',
    'http://127.0.0.1:4173',
];
$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';
if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require dirname(__DIR__) . '/vendor/autoload_runtime.php';
