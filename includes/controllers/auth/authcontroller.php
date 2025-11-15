<?php

namespace Includes\Controllers\Auth;

use Includes\Controllers\Controller;
use Includes\Routing\Request;
use Includes\Auth\Auth;
use Includes\Views\View;
use Includes\Exceptions\ValidationException;
use Includes\Routing\Response;

class AuthController extends Controller
{
    protected Auth $auth;

    public function __construct(Request $request, Auth $auth)
    {
        parent::__construct($request, $auth);
        
        // Verify CSRF token and session validity for all methods except logout
        if ($request->method() !== 'GET' && !in_array($request->getPathInfo(), ['/logout'])) {
            if (!\Security\CSRF::validateToken($request->getCsrfToken())) {
                throw new \RuntimeException('CSRF token validation failed');
            }
            if ($auth->isSessionExpired()) {
                $auth->logout();
                throw new \RuntimeException('Session expired. Please login again.');
            }
            $auth->renewSession();
        }
    }

    /**
     * Display the login view.
     */
    public function showLogin(): View
    {
        $tenants = $this->getAvailableTenants();
        return View::make('auth/login', ['tenants' => $tenants]);
    }

    /**
     * Handle an incoming login request.
     */
    public function login(Request $request): Response
    {
        try {
            // TODO: Implement validation
            $credentials = $request->only('email', 'password');
            $tenantId = $request->input('tenant_id') ?? $this->getTenantFromDomain($request);
            
            if ($this->auth->isSessionExpired()) {
                $this->auth->logout();
                throw new ValidationException('Session expired. Please login again.');
            }

            if (!$this->auth->attempt($credentials, $tenantId)) {
                throw new ValidationException('Invalid credentials');
            }

            $request->regenerateSession();
            $this->auth->renewSession();
            $this->redirect('/content');
        } catch (ValidationException $e) {
            $_SESSION['errors'] = ['email' => $e->getMessage()];
            $this->redirect($request->getReferer());
        }
    }

    /**
     * Display the registration view.
     */
    public function showRegister(): View
    {
        return View::make('auth/register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request): Response
    {
        try {
            // TODO: Implement validation
            $user = $this->auth->createUser([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $request->input('password')
            ]);

            $this->auth->login($user);
            $this->redirect('/content');
        } catch (ValidationException $e) {
            $_SESSION['errors'] = $e->getErrors();
            $this->redirect($request->getReferer());
        }
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): Response
    {
        $this->auth->logout();
        $request->invalidateSession();
        $_SESSION = [];
        $this->redirect('/');
    }
}
