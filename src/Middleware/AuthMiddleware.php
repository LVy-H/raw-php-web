<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(array $params, callable $next): mixed
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return null;
        }

        return $next($params);
    }
}
