<?php
/**
 * API v1 User Controller
 */

declare(strict_types=1);

namespace Api\V1\Controllers;

use Includes\Routing\Request;
use Includes\Routing\Response;

class UserController
{
    /**
     * List all users (admin only)
     */
    public function index(Request $request, Response $response): void
    {
        $response->json([
            'success' => true,
            'data' => [], // TODO: Implement user listing
            'error' => null
        ]);
    }

    /**
     * Authenticate user
     */
    public function login(Request $request, Response $response): void
    {
        $credentials = $request->getParsedBody();
        // TODO: Implement authentication
        
        $response->json([
            'success' => true,
            'data' => [
                'token' => 'sample-token', // TODO: Generate real token
                'user' => [
                    'id' => 1,
                    'name' => 'Test User'
                ]
            ],
            'error' => null
        ]);
    }

    /**
     * Register new user
     */
    public function register(Request $request, Response $response): void
    {
        $userData = $request->getParsedBody();
        // TODO: Validate and create user
        
        $response->json([
            'success' => true,
            'data' => $userData,
            'error' => null
        ], 201);
    }
}
