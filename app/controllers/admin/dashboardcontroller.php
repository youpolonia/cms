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

        // Check if setup wizard needs to run
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'setup_wizard_completed'");
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row || $row["value"] !== "1") {
                Response::redirect("/admin/setup-wizard");
            }
        } catch (\Throwable $e) {}

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
