<?php
declare(strict_types=1);

/**
 * Route Definitions
 *
 * Maps URL patterns to handler functions
 */

require_once __DIR__ . '/includes/security/authservicewrapper.php';

// Public routes
// DB-backed pages
if (function_exists('get')) {
    if (!defined('__DB_PAGES_ROUTES__')) {
        define('__DB_PAGES_ROUTES__', true);
        get('/page/{slug}', function($slug){
            require_once __DIR__ . '/controllers/page_controller.php';
            \controllers\page_controller::show((string)$slug);
        });
        get('/about', function(){ require_once __DIR__ . '/controllers/page_controller.php'; \controllers\page_controller::show('about'); });
        get('/contact', function(){ require_once __DIR__ . '/controllers/page_controller.php'; \controllers\page_controller::show('contact'); });
    }
}
if (function_exists('router_get') && !defined('__DB_PAGES_ROUTES_RTR__')) {
    define('__DB_PAGES_ROUTES_RTR__', true);
    router_get('/page/{slug}', function($slug){
        require_once __DIR__ . '/controllers/page_controller.php';
        \controllers\page_controller::show((string)$slug);
    });
    router_get('/about', function(){ require_once __DIR__ . '/controllers/page_controller.php'; \controllers\page_controller::show('about'); });
    router_get('/contact', function(){ require_once __DIR__ . '/controllers/page_controller.php'; \controllers\page_controller::show('contact'); });
}

// Middleware functions
$middleware = [
    'auth' => function() {
        if (!AuthServiceWrapper::checkAuth()) {
            header('HTTP/1.1 401 Unauthorized');
            require_once __DIR__ . '/templates/errors/401.php';
            exit;
        }
    },
    'admin' => function() {
        if (!AuthServiceWrapper::checkAdminAuth()) {
            header('HTTP/1.1 403 Forbidden');
            require_once __DIR__ . '/templates/errors/403.php';
            exit;
        }
    }
];

// Apply middleware to route handler
function withMiddleware(array $middleware, callable $handler): callable {
    return function(...$args) use ($middleware, $handler) {
        foreach ($middleware as $mw) {
            $mw();
        }
        return $handler(...$args);
    };
}

return [
    // Homepage route
    '/' => function() {
        require_once __DIR__ . '/controllers/homecontroller.php';
        $controller = new HomeController();
        $controller->index();
    },

    // Blog routes
    '/blog' => function() {
        require_once __DIR__ . '/controllers/blogcontroller.php';
        $controller = new BlogController();
        $controller->index();
    },
    '/blog/([a-z0-9-]+)' => function($slug) {
        require_once __DIR__ . '/controllers/blogcontroller.php';
        $controller = new BlogController();
        $controller->show($slug);
    },

    // Gallery route
    '/gallery' => function() {
        require_once __DIR__ . '/controllers/gallerycontroller.php';
        $controller = new GalleryController();
        $controller->index();
    },

    // Example route with parameter
    '/user/(\d+)' => function(int $userId) {
        header('Content-Type: application/json');
        echo json_encode(['user_id' => $userId]);
    },

    // Example POST route
    '/api/data' => function() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['status' => 'success', 'data' => $data]);
    },
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

// Admin route group
'/admin/approval_dashboard' => withMiddleware(['auth'], function() {
    require_once __DIR__ . '/admin/controllers/approvaldashboardcontroller.php';
    $controller = new ApprovalDashboardController();
    $controller->index();
}),
'/admin/logs/audit' => withMiddleware(['auth', 'admin'], function() {
    require_once __DIR__ . '/admin/controllers/auditlogcontroller.php';
    $controller = new AuditLogController();
    $controller->index();
}),
'/admin/company' => withMiddleware(['auth', 'admin'], function() {
    require_once __DIR__ . '/admin/controllers/CompanyController.php';
    $controller = new CompanyController();
    $controller->index();
}),
'/admin/content_approval' => withMiddleware(['auth'], function() {
    require_once __DIR__ . '/admin/controllers/contentapprovalcontroller.php';
    $controller = new ContentApprovalController();
    $controller->index();
}),
'/admin/gdpr-tools' => withMiddleware(['auth', 'admin'], function() {
    require_once __DIR__ . '/admin/controllers/gdprcontroller.php';
    $controller = new GdprController();
    $controller->index();
}),
'/admin/cache' => withMiddleware(['auth', 'admin'], function() {
    require_once __DIR__ . '/admin/controllers/cachecontroller.php';
    $controller = new CacheController();
    $controller->index();
}),
'/admin/login' => function() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if ($username === 'admin' && $password === 'password') {
            echo json_encode(['status' => 'success', 'message' => 'Logged in']);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    },

    // 404 Error Handler
    '/404' => function() {
        http_response_code(404);
        header('Content-Type: text/html');
        require_once __DIR__.'/views/errors/404.php';
        exit;
    },

    // 500 Error Handler
    '/500' => function(Throwable $error = null) {
        http_response_code(500);
        header('Content-Type: text/html');
        echo "500 Internal Server Error";
        
        if ($error) {
            error_log($error->getMessage());
        }
    },

    // System diagnostics routes
    '/api/diagnostics/ping' => withMiddleware(['auth'], function() {
        require_once __DIR__ . '/includes/services/systemdiagnosticscontroller.php';
        $controller = new SystemDiagnosticsController();
        $controller->ping();
    }),
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
