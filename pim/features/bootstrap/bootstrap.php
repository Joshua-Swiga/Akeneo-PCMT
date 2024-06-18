<?php

use Symfony\Component\Dotenv\Dotenv;

if(!isset($_SERVER['APP_ENV'])) {
    if(!class_exists(Dotenv::class)) {
        throw new RuntimeException('APP_ENV environment variable is not defined.');
    }
    (new Dotenv())->load(__DIR__.'/.env.behat');
}

