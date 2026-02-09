<?php
declare(strict_types=1);

/**
 * Route Definitions
 *
 * Maps URL patterns to handler functions
 * Auth routes handled by MVC (config/routes.php)
 */

// Page routes removed (2026-02-08) - page_controller.php did not exist
// Auth middleware routes removed (2026-02-08) - AuthServiceWrapper broken, MVC handles auth

return [
    // Homepage route
    '/' => function() {
        require_once __DIR__ . '/controllers/homecontroller.php';
        $controller = new HomeController();
        $controller->index();
    },

    // Blog routes - using new Article system
    '/blog' => function() {
        require_once __DIR__ . '/controllers/articlefrontcontroller.php';
        $controller = new ArticleFrontController();
        $controller->index();
    },
    '/blog/{slug}' => function($slug) {
        require_once __DIR__ . '/controllers/articlefrontcontroller.php';
        $controller = new ArticleFrontController();
        $controller->show($slug);
    },

    // API routes
    '/api/versions/compare/{version1}/{version2}' => function($version1, $version2) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        require_once __DIR__.'/includes/api/versioncontroller.php';
        $controller = new VersionController();
        echo json_encode($controller->compare($version1, $version2));
    },

    '/api/versions' => function() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        require_once __DIR__.'/includes/api/versioncontroller.php';
        $controller = new VersionController();
        echo json_encode($controller->listVersions());
    },

    '/api/versions/{id}' => function($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        require_once __DIR__.'/includes/api/versioncontroller.php';
        $controller = new VersionController();
        echo json_encode($controller->getVersion($id));
    },

    '/api/versions/diffs' => function() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        require_once __DIR__.'/includes/api/versioncontroller.php';
        $controller = new VersionController();
        echo json_encode($controller->getComparisonHistory());
    },

    // Error handlers
    '/404' => function() {
        http_response_code(404);
        header('Content-Type: text/html');
        require_once __DIR__.'/views/errors/404.php';
        exit;
    },

    '/500' => function(Throwable $error = null) {
        http_response_code(500);
        header('Content-Type: text/html');
        echo "500 Internal Server Error";
        if ($error) {
            error_log($error->getMessage());
        }
    },

    // System diagnostics routes (public - no auth)
    '/api/diagnostics/database' => function() {
        require_once __DIR__ . '/includes/services/systemdiagnosticscontroller.php';
        $controller = new SystemDiagnosticsController();
        $controller->checkDatabase();
    },
    '/api/diagnostics/system' => function() {
        require_once __DIR__ . '/includes/services/systemdiagnosticscontroller.php';
        $controller = new SystemDiagnosticsController();
        $controller->systemInfo();
    },
    '/api/diagnostics/security' => function() {
        require_once __DIR__ . '/includes/services/systemdiagnosticscontroller.php';
        $controller = new SystemDiagnosticsController();
        $controller->securityStatus();
    }
];
