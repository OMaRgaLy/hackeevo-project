<?php

require_once __DIR__ . '/../vendor/autoload.php';

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

use App\Application\Exception\RouteNotFoundException;
use App\Router\Router;
use App\Http\Controller\AuthController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Controller\TaskController;
use App\Http\Controller\UserController;

$app = new App\Bootstrap();
$router = $app->init();

$router
    ->get('/', function() {
        return ['message' => 'Welcome to the Task Manager!'];
    })
    ->group('/api', [], function(Router $router) {
        $router
            ->group('/auth', [], function(Router $router) {
                $router
                    ->post('/register', [AuthController::class, 'register'])
                    ->post('/login', [AuthController::class, 'login']);
            })
            ->group('/users', [AuthMiddleware::class], function(Router $router) {
                $router
                    ->get('', [UserController::class, 'findAll'])
                    ->get('/{id}', [UserController::class, 'findById'])
                    ->post('', [UserController::class, 'create'])
                    ->put('/{id}', [UserController::class, 'update'])
                    ->delete('/{id}', [UserController::class, 'delete']);
            })
            ->group('/tasks', [AuthMiddleware::class], function(Router $router) {
                $router
                    ->get('', [TaskController::class, 'getMyTasks'])
                    ->get('/all', [TaskController::class, 'getMyTasks'])
                    ->get('/{id}', [TaskController::class, 'getTaskById'])
                    ->post('', [TaskController::class, 'create'])
                    ->put('/{id}', [TaskController::class, 'update'])
                    ->delete('/{id}', [TaskController::class, 'delete']);
            });
    })
    ->group('/auth', [], function(Router $router) {
        $router
            ->post('/register', [AuthController::class, 'register'])
            ->post('/login', [AuthController::class, 'login']);
    })
    ->group('/users', [AuthMiddleware::class], function(Router $router) {
        $router
            ->get('', [UserController::class, 'findAll'])
            ->get('/{id}', [UserController::class, 'findById'])
            ->post('', [UserController::class, 'create'])
            ->put('/{id}', [UserController::class, 'update'])
            ->delete('/{id}', [UserController::class, 'delete']);
    })
    ->group('/tasks', [AuthMiddleware::class], function(Router $router) {
        $router
            ->get('', [TaskController::class, 'getMyTasks'])
            ->get('/all', [TaskController::class, 'getMyTasks'])
            ->get('/{id}', [TaskController::class, 'getTaskById'])
            ->post('', [TaskController::class, 'create'])
            ->put('/{id}', [TaskController::class, 'update'])
            ->delete('/{id}', [TaskController::class, 'delete']);
    });;

try {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    $response = $router->resolve($method, $path);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $response
    ]);

} catch (RouteNotFoundException $e) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Route not found'
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'/*,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()*/
    ]);
}