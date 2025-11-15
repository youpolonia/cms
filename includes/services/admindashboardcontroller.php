<?php
/**
 * Admin Dashboard Controller
 * Returns JSON response with system status and user info
 */
class AdminDashboardController {
    public function index(): void {
        header('Content-Type: application/json');
        
        $user = AuthServiceWrapper::getUser();
        $response = [
            'status' => 'success',
            'data' => [
                'user' => [
                    'email' => $user['email'] ?? null,
                    'username' => $user['username'] ?? null
                ],
                'timestamp' => time()
            ]
        ];

        echo json_encode($response);
    }
}
