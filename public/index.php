<?php



require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Загружаем переменные окружения
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

set_error_handler(/**
 * @throws ErrorException
 */ function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
});

require_once __DIR__ . '/../src/routes.php';