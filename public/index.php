<?php

use App\Router;

require_once __DIR__ . '/../vendor/autoload.php';

// Загружаем переменные окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Создаём экземпляр маршрутизатора
$router = new Router();

// Регистрируем маршруты
$router->get('/', [App\Controller\HomeController::class, 'index']);

// Запускаем приложение
echo $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);