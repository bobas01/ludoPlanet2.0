<?php

/**
 * Routeur pour le serveur PHP intégré (php -S).
 * Sert les fichiers statiques s'ils existent, sinon délègue à Symfony.
 * CORS géré par NelmioCorsBundle uniquement (éviter doublon d'en-têtes).
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
if ($uri !== '' && $uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require dirname(__DIR__) . '/vendor/autoload_runtime.php';
