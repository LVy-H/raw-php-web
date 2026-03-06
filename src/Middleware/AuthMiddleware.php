<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(array $params, callable $next): mixed
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']) ?: '{"message":"Unauthorized"}';
            return null;
        }

        return $next($params);
    }
}
