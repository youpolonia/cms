<?php
declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;
use Core\Session;

class AuthMiddleware
{
    public static function handle(Request $request): void
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please log in to continue.');
            Response::redirect('/admin/login');
        }
    }
}
