<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Session;
use Core\Response;

class DashboardController
{
    public function index(): void
    {
        // Check authentication - redirect to login if not logged in
        if (!Session::isLoggedIn()) {
            Response::redirect('/admin/login');
        }

        // Render the MVC dashboard view with modern UI
        $title = 'Dashboard';
        $success = Session::getFlash('success');
        $error = Session::getFlash('error');
        render('admin/dashboard/index', [
            'title' => $title,
            'success' => $success,
            'error' => $error
        ]);
    }
}
