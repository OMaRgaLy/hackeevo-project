<?php


namespace App\Router;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $requestUri, string $requestMethod): mixed
    {
        $path = parse_url($requestUri, PHP_URL_PATH);

        if (!isset($this->routes[$requestMethod][$path])) {
            http_response_code(404);
            return "404 Not Found";
        }

        [$controller, $method] = $this->routes[$requestMethod][$path];
        return (new $controller)->$method();
    }
}