<?php

declare(strict_types=1);

namespace App\Router;
use App\Application\Exception\RouteNotFoundException;
use App\Infrastructure\Container\Container;
use Closure;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $basePath = '';

    /**
     * @var array<string, string[]>
     */
    private array $groupMiddlewares = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function get(string $path, array|string|Closure $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array|string|Closure $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array|string|Closure $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function group(string $prefix, array $middlewares, Closure $callback): self
    {
        $previousBasePath = $this->basePath;
        $this->basePath .= $prefix;

        $this->groupMiddlewares[$this->basePath] = $middlewares;

        $callback($this);

        $this->basePath = $previousBasePath;

        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        $this->middlewares = array_merge(
            $this->middlewares,
            is_array($middleware) ? $middleware : [$middleware]
        );

        return $this;
    }

    private function addRoute(string $method, string $path, array|string|Closure $handler): self
    {
        $path = $this->basePath . $path;

        $this->routes[$method . $path] = [
            'handler' => $handler,
            'middlewares' => $this->middlewares
        ];

        $this->middlewares = [];

        return $this;
    }

    /**
     *
     * @throws RouteNotFoundException
     */
    public function resolve(string $method, string $path): mixed
    {
        foreach ($this->routes as $routePath => $route) {
            if (!str_starts_with($routePath, $method)) {
                continue;
            }

            $routePattern = substr($routePath, strlen($method));

            $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $routePattern);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                $middlewares = $this->collectMiddlewares($path, $route['middlewares']);
                $shouldContinue = $this->executeMiddlewareChain($middlewares);

                if (!$shouldContinue) {
                    return null;
                }

                return $this->executeHandler($route['handler'], $params);
            }
        }

        throw new RouteNotFoundException();
    }

    private function collectMiddlewares(string $path, array $routeMiddlewares): array
    {
        $middlewares = $routeMiddlewares;

        foreach ($this->groupMiddlewares as $groupPath => $groupMiddlewares) {
            if (str_starts_with($path, $groupPath)) {
                $middlewares = array_merge($middlewares, $groupMiddlewares);
            }
        }

        return $middlewares;
    }

    private function executeMiddlewareChain(array $middlewares): bool
    {
        foreach ($middlewares as $middleware) {
            $middlewareInstance = $this->container->get($middleware);

            if (!$middlewareInstance->handle()) {
                return false;
            }
        }

        return true;
    }

    private function executeHandler(array|string|Closure $handler, array $params = []): mixed
    {
        if ($handler instanceof Closure) {
            return $handler($params);
        }

        if (is_array($handler)) {
            [$class, $method] = $handler;

            if (is_string($class)) {
                $class = $this->container->get($class);
            }

            return $class->$method($params);
        }

        if (is_string($handler) && str_contains($handler, '::')) {
            [$class, $method] = explode('::', $handler);
            $class = $this->container->get($class);

            return $class->$method($params);
        }

        throw new \RuntimeException('Invalid route handler');
    }

    public function delete(string $path, array|string|Closure $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }
}