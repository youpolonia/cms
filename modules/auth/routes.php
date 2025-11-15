<?php
// Auth module routes
return [
    'GET /login' => function() {
        // Login form display
        return render_theme_view('auth/login');
    },
    'POST /login' => [AuthController::class, 'login'],
    'GET /logout' => [AuthController::class, 'logout']
];
