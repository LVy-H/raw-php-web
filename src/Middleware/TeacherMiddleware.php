<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class TeacherMiddleware implements MiddlewareInterface
{
    public function handle(array $params, callable $next): mixed
    {
        if (($_SESSION['user_role'] ?? null) !== 'teacher') {
            http_response_code(403);
            return 'Forbidden: teacher access required.';
        }

        return $next($params);
    }
}
