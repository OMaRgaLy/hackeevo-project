<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    public function post(string $path, $handler): self
    {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    public function put(string $path, $handler): self
    {
        $this->routes['PUT'][$path] = $handler;
        return $this;
    }

    public function delete(string $path, $handler): self
    {
        $this->routes['DELETE'][$path] = $handler;
        return $this;
    }


    public function dispatch(string $method, string $path)
    {
        if (!isset($this->routes[$method])) {
            http_response_code(404);
            return 'Not Found';
        }

        foreach ($this->routes[$method] as $route => $handler) {
            // Замена {param} на регулярное выражение
            $pattern = preg_replace('/\{[^\/]+\}/', '([^\/]+)', $route);
            if (preg_match("#^$pattern$#", $path, $matches)) {
                array_shift($matches); // Убираем полное совпадение

                try {
                    if (is_array($handler)) {
                        [$class, $method] = $handler;
                        $controller = new $class();
                        return $controller->$method(...$matches);
                    }
                    if (is_callable($handler)) {
                        return $handler(...$matches);
                    }
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                    http_response_code(500);
                    return 'Internal Server Error';
                }
            }
        }

        http_response_code(404);
        return 'Not Found';
    }

}