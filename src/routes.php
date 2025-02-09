<?php

use App\Core\Router;
use App\Controller\TaskController;

$router = new Router();

// Определяем маршруты
$router->get('/', function () {
    return 'Добро пожаловать в Task Manager!';
});

$router
    ->get('/tasks', [TaskController::class, 'index'])  // Получить список задач
    ->get('/tasks/{id}', [TaskController::class, 'show'])  // Получить одну задачу по ID
    ->post('/tasks', [TaskController::class, 'create'])  // Создать новую задачу
    ->put('/tasks/{id}', [TaskController::class, 'update'])  // Обновить задачу по ID
    ->delete('/tasks/{id}/delete', [TaskController::class, 'delete']);  // Удалить задачу

// Получаем текущий метод и путь
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Отправляем запрос на обработку
echo $router->dispatch($method, $path);