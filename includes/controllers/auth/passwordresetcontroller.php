<?php

namespace Includes\Controllers\Auth;

use Includes\Controllers\Controller;
use Includes\Routing\Request;
use Includes\Auth\Auth;
use Includes\Views\View;
use Includes\Exceptions\ValidationException;
use Includes\Routing\Response;
use Includes\Middleware\RateLimiterMiddleware;

class PasswordResetController extends Controller
{
    protected Auth $auth;
    protected RateLimiterMiddleware $rateLimiter;

    public function __construct(Request $request, Auth $auth, RateLimiterMiddleware $rateLimiter)
    {
        parent::__construct($request, $auth);
        $this->rateLimiter = $rateLimiter;
        
        if ($request->method() !== 'GET') {
            if (!\Security\CSRF::validateToken($request->getCsrfToken())) {
                throw new \RuntimeException('CSRF token validation failed');
            }
        }
    }

    public function showLinkRequestForm(): View
    {
        return View::make('auth/passwords/email');
    }

    public function sendResetLinkEmail(Request $request): Response
    {
        try {
            // Apply rate limiting (5 attempts per minute)
            $this->rateLimiter->setLimit(5)->setWindow(60);
            $response = $this->rateLimiter->handle($request, function($req) {
                $email = $req->input('email');
                
                // TODO: Implement actual password reset logic
                // $this->auth->sendPasswordResetLink($email);
                
                return new Response('Password reset link sent', 200);
            });
            
            return $response;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['email' => $e->getMessage()];
            return $this->redirect($request->getReferer());
        }
    }

    public function showResetForm(Request $request, string $token): View
    {
        return View::make('auth/passwords/reset', ['token' => $token]);
    }

    public function reset(Request $request): Response
    {
        try {
            $credentials = $request->only('email', 'password', 'password_confirmation', 'token');
            
            // TODO: Implement actual password reset logic
            // $this->auth->resetPassword($credentials);
            
            return $this->redirect('/login');
        } catch (ValidationException $e) {
            $_SESSION['errors'] = $e->getErrors();
            return $this->redirect($request->getReferer());
        }
    }
}
