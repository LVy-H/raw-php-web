<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(array $params, callable $next): mixed
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /users/' . ($_SESSION['user_id'] ?? ''));
            return null;
        }

        return $next($params);
    }
}
