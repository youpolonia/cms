<?php

namespace Includes\Controllers\Auth;

use Includes\Routing\Request;
use Includes\Routing\Response;
use Includes\Auth\Auth;
use Includes\Exceptions\ValidationException;

class EmailVerificationController
{
    protected $auth;
    protected $response;

    public function __construct(Auth $auth, Response $response)
    {
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Send verification email
     */
    public function sendVerificationEmail(Request $request): Response
    {
        $user = $this->auth->user();
        
        if (!$user) {
            return $this->response->redirect('/login');
        }

        if ($this->auth->hasVerifiedEmail($user)) {
            return $this->response->redirect('/content');
        }

        if (!$this->auth->sendVerificationEmail($user)) {
            throw new ValidationException('Failed to send verification email');
        }

        return $this->response->back()->with('status', 'verification-link-sent');
    }

    /**
     * Handle verification link
     */
    public function verify(Request $request): Response
    {
        $user = $this->auth->user();
        
        if (!$user) {
            return $this->response->redirect('/login');
        }

        if ($this->auth->hasVerifiedEmail($user)) {
            return $this->response->redirect('/content?verified=1');
        }

        $token = $request->input('token');
        if (empty($token)) {
            throw new ValidationException('Verification token is required');
        }

        if (!$this->auth->verifyEmail($user, $token)) {
            throw new ValidationException('Invalid verification token');
        }

        return $this->response->redirect('/content?verified=1');
    }
}
