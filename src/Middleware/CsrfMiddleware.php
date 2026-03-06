<?php

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\MiddlewareInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(array $params, callable $next): mixed
    {
        if (!Csrf::validate()) {
            http_response_code(419);
            return 'Session expired or invalid request token. Please go back and try again.';
        }

        return $next($params);
    }
}
