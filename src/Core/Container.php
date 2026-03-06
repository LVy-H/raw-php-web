<?php

namespace App\Core;

use RuntimeException;

final class Container
{
    private array $factories = [];
    private array $instances = [];

    public function set(string $name, callable $factory): void
    {
        $this->factories[$name] = $factory;
    }

    public function get(string $name): mixed
    {
        if (array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        if (!isset($this->factories[$name])) {
            throw new RuntimeException("No entry found for '$name'.");
        }

        $factory = $this->factories[$name];
        $this->instances[$name] = $factory($this);
        return $this->instances[$name];
    }
}