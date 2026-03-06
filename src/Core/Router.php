<?php

namespace App\Core;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

class Router
{
    private array $routes;
    private ?Container $container;

    public function __construct(array $routes, ?Container $container = null)
    {
        $this->routes = $routes;
        $this->container = $container;
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $path !== '/' ? rtrim($path, '/') : $path;

        if (!isset($this->routes[$method])) {
            $this->abort(405);
        }

        foreach ($this->routes[$method] as $route => $definition) {
            $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?<$1>[^/]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->execute($definition, $params);
                return;
            }
        }

        $this->abort(404);
    }

    public function execute(array $definition, array $params): void
    {
        $handler = $definition;
        $middlewares = [];

        if (isset($definition['handler'])) {
            $handler = $definition['handler'];
            $middlewares = $definition['middleware'] ?? [];
        }

        [$controllerClass, $action] = $handler;

        if (!class_exists($controllerClass) || !method_exists($controllerClass, $action)) {
            $this->abort(500);
        }

        $controller = $this->resolveClass($controllerClass);

        $destination = static function (array $routeParams) use ($controller, $action) {
            return $controller->{$action}(...array_values($routeParams));
        };

        $pipeline = array_reduce(
            array_reverse($middlewares),
            function (callable $next, string $middlewareClass) {
                return function (array $routeParams) use ($next, $middlewareClass) {
                    $middleware = $this->resolveMiddleware($middlewareClass);
                    return $middleware->handle($routeParams, $next);
                };
            },
            $destination
        );

        $result = $pipeline($params);

        if (is_string($result)) {
            echo $result;
        }
    }

    private function resolveClass(string $className): object
    {
        if ($this->container === null) {
            return new $className();
        }

        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return $reflection->newInstance();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                throw new RuntimeException(sprintf(
                    'Unable to resolve controller dependency %s::%s',
                    $className,
                    $parameter->getName()
                ));
            }

            $dependencies[] = $this->container->get($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    private function resolveMiddleware(string $middlewareClass): MiddlewareInterface
    {
        $middleware = $this->resolveClass($middlewareClass);

        if (!$middleware instanceof MiddlewareInterface) {
            throw new RuntimeException(sprintf(
                'Middleware %s must implement %s',
                $middlewareClass,
                MiddlewareInterface::class
            ));
        }

        return $middleware;
    }

    public function abort(int $code = 404): void
    {
        http_response_code($code);
        echo $code === 404 ? 'Error 404: Resource not found.' : "Error {$code}: Request failed.";
        exit;
    }
}
