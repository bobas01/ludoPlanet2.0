<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// if (method_exists(Dotenv::class, 'bootEnv')) {
//     (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
// }
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'prod';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? '0';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
