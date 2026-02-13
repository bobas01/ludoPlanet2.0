<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// Désactive le chargement du .env et force l'utilisation des variables du conteneur
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'prod';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? '0';
putenv('APP_ENV=' . $_SERVER['APP_ENV']);
putenv('APP_DEBUG=' . $_SERVER['APP_DEBUG']);
// Empêche Symfony de chercher un .env
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->usePutenv(false);
    // (new Dotenv())->bootEnv(dirname(DIR).'/.env'); // Désactivé
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
