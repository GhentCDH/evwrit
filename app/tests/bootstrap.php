<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (file_exists(dirname(__DIR__).'/.env') && method_exists(Dotenv::class, 'bootEnv')) {
    // Only load a .env file when one is present; in containerized environments the
    // configuration is provided through real environment variables instead.
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
