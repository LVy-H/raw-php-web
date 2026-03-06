<?php

namespace App\Core;

interface MiddlewareInterface
{
    public function handle(array $params, callable $next): mixed;
}
