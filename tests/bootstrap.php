<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Ensure APP_ENV is set to "test"
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
