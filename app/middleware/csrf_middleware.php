<?php
declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;

class CsrfMiddleware
{
    public static function handle(Request $request): void
    {
        $token = $request->post('csrf_token', '');
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
            Response::forbidden('Invalid security token. Please refresh and try again.');
        }
    }
}
