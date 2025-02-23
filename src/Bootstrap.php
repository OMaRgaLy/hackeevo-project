<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;
use PDO;
use App\Infrastructure\Container\Container;
use App\Router\Router;
use App\Config\Database;

class Bootstrap
{
    private Container $container;
    private string $rootPath;
    private ?PDO $pdo = null;

    public function __construct()
    {
        $this->container = new Container();
        $this->rootPath = dirname(__DIR__);


    }

    public function init(): Router
    {
        $this->loadEnvironment();
        $this->pdo = Database::getInstance();
        $this->setupCors();
        $this->registerServices();

        return new Router($this->container);
    }

    private function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable($this->rootPath);
        $dotenv->load();
    }


    private function setupCors(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    private function registerServices(): void
    {
        $jwtSecret = $_ENV['JWT_SECRET'];

        $userRepository = new Infrastructure\Repository\UserRepository();
        $taskRepository = new Infrastructure\Repository\TaskRepository($this->pdo);

        $this->container->set(Infrastructure\Repository\UserRepository::class, $userRepository);
        $this->container->set(Domain\Repository\UserRepositoryInterface::class, $userRepository);
        $this->container->set(Infrastructure\Repository\TaskRepository::class, $taskRepository);

        $authMiddleware = new Http\Middleware\AuthMiddleware($userRepository);
        $this->container->set(Http\Middleware\AuthMiddleware::class, $authMiddleware);

        $authService = new Application\Service\AuthService($userRepository);
        $jwtService = new Application\Service\JWTService($jwtSecret);
        $taskService = new Application\Service\TaskService($taskRepository);
        $userService = new Application\Service\UserService($userRepository);

        $this->container->set(Application\Service\AuthService::class, $authService);
        $this->container->set(Application\Service\JWTService::class, $jwtService);
        $this->container->set(Application\Service\TaskService::class, $taskService);
        $this->container->set(Application\Service\UserService::class, $userService);

        $authController = new Http\Controller\AuthController($authService, $jwtService);
        $taskController = new Http\Controller\TaskController($taskService);
        $userController = new Http\Controller\UserController($userService);

        $this->container->set(Http\Controller\AuthController::class, $authController);
        $this->container->set(Http\Controller\TaskController::class, $taskController);
        $this->container->set(Http\Controller\UserController::class, $userController);

    }
}