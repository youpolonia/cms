<?php
require_once __DIR__ . '/controllers/PagesController.php';
require_once __DIR__ . '/controllers/usercontroller.php';

function validateCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            die('Invalid CSRF token');
        }
    }
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
parse_str($query ?? '', $params);

// Pages routes
if ($uri === '/admin/pages') {
    PagesController::index();
} elseif ($uri === '/admin/pages/create') {
    PagesController::create();
} elseif ($uri === '/admin/pages/store') {
    validateCsrfToken();
    PagesController::store($_POST);
} elseif ($uri === '/admin/pages/edit') {
    PagesController::edit($params['id'] ?? 0);
} elseif ($uri === '/admin/pages/update') {
    validateCsrfToken();
    PagesController::update($params['id'] ?? 0, $_POST);
} elseif ($uri === '/admin/pages/delete') {
    validateCsrfToken();
    PagesController::delete($params['id'] ?? 0);
}
// User routes
elseif (preg_match('#^/admin/users$#', $uri)) {
    UserController::index();
} elseif (preg_match('#^/admin/users/create$#', $uri)) {
    UserController::create();
} elseif (preg_match('#^/admin/users/store$#', $uri)) {
    validateCsrfToken();
    UserController::store($_POST);
} elseif (preg_match('#^/admin/users/(\d+)/edit$#', $uri, $matches)) {
    UserController::edit($matches[1]);
} elseif (preg_match('#^/admin/users/(\d+)/update$#', $uri, $matches)) {
    validateCsrfToken();
    UserController::update($matches[1], $_POST);
} elseif (preg_match('#^/admin/users/(\d+)/delete$#', $uri, $matches)) {
    validateCsrfToken();
    UserController::delete($matches[1]);
} else {
    http_response_code(404);
    echo 'Page not found';
}
