<?php

namespace Includes\Auth;

class AuthController
{
    private static ?AuthService $authService = null;

    public static function login(string $username, string $password): bool
    {
        if (self::$authService === null) {
            self::$authService = new AuthService();
        }

        $user = self::$authService->authenticate($username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }

        return false;
    }
}
