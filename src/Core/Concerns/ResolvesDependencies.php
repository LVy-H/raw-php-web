<?php

namespace App\Core\Concerns;

use App\Core\MiddlewareInterface;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

trait ResolvesDependencies
{
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
}
