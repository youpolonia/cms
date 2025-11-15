<?php

declare(strict_types=1);

namespace Includes\Controllers\Admin;

use Core\Auth;

class DashboardController
{
    /**
     * Displays the main admin dashboard.
     */
    public function index(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        if (!Auth::hasRole('admin')) {
            header('Location: /');
            exit;
        }

        $title = "Admin Dashboard";
        $content = "<h1>Welcome to Admin Panel</h1><p>Manage your site content and settings</p>";

        ob_start();
        require_once __DIR__ . '/../../../templates/admin/dashboard.php';
        $output = ob_get_clean();
        
        echo $output;
    }
}
