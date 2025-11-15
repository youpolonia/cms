<?php

namespace Includes\Controllers\Auth;

use Includes\Controllers\Controller;
use Includes\Routing\Request;
use Includes\Auth\Auth;
use Includes\Views\View;
use Includes\Exceptions\ValidationException;
use Includes\Routing\Response;

class PasswordController extends Controller
{
    protected Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    /**
     * Show the password confirmation view.
     */
    public function showConfirm(): View
    {
        return View::make('auth/confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function confirm(Request $request): Response
    {
        try {
            \Security\CSRF::validateRequest($request);
            $user = $this->auth->user();
            if (!$user || !$this->auth->attempt([
                'email' => $user->email,
                'password' => $request->input('password')
            ])) {
                throw new ValidationException('The provided password is incorrect');
            }

            $request->session()->set('auth.password_confirmed_at', time());
            return redirect()->intended('/content');
        } catch (ValidationException $e) {
            return back()->withErrors(['password' => $e->getMessage()]);
        }
    }
}
