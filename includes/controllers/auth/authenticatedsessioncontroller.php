<?php

namespace Includes\Controllers\Auth;

use Includes\Controllers\Controller;
use Includes\Auth\Auth;
use Includes\Routing\Request;

class AuthenticatedSessionController extends Controller
{
    public function create(): string
    {
        return $this->view('auth/login', [
            'csrfToken' => \Security\CSRF::generateToken()
        ]);
    }

    public function store(Request $request): void
    {
        try {
            \Security\CSRF::validateRequest($request);
            
            $data = $this->validate([
                'email' => ['required' => true, 'email' => true],
                'password' => ['required' => true]
            ]);

            $user = $this->auth->attempt([
                'email' => $data['email'],
                'password' => $data['password']
            ]);

            if (!$user) {
                throw new ValidationException([
                    'email' => 'These credentials do not match our records.'
                ]);
            }

            $this->redirect('/content');
        } catch (ValidationException $e) {
            $_SESSION['errors'] = $e->getErrors();
            $this->redirect($request->getReferer());
        }
    }

    public function destroy(Request $request): void
    {
        $this->auth->logout();
        $this->redirect('/');
    }
}
