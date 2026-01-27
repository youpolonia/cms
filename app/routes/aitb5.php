<?php
/**
 * AI Theme Builder 5.0 Routes
 * Include this file in your CMS index.php to enable AITB5 routes
 *
 * Add before "// END JTB ROUTES" in index.php:
 *
 * // AI Theme Builder 5.0
 * require_once CMS_ROOT . '/app/routes/aitb5.php';
 */

// ============================================
// AI THEME BUILDER 5.0 ROUTES
// ============================================

// Get URI for matching (reuse $jtbUri if available)
$_aitb5Uri = $jtbUri ?? '';
if (empty($_aitb5Uri)) {
    $_aitb5Uri = $_SERVER["REQUEST_URI"] ?? "/";
    $_aitb5Qpos = strpos($_aitb5Uri, "?");
    if ($_aitb5Qpos !== false) {
        $_aitb5Uri = substr($_aitb5Uri, 0, $_aitb5Qpos);
    }
}

// AI Theme Builder v5 Main Page
if (preg_match('#^/admin/ai-theme-builder-v5/?$#', $_aitb5Uri)) {
    require_once CMS_ROOT . '/core/session_boot.php';
    cms_session_start('admin');

    if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
        header('Location: /admin/login');
        exit;
    }

    require_once CMS_ROOT . '/app/controllers/admin/AiTb5Controller.php';
    $controller = new \App\Controllers\Admin\AiTb5Controller();
    $controller->index();
    exit;
}

// AI Theme Builder v5 API - Generate
if (preg_match('#^/api/jtb/ai/generate$#', $_aitb5Uri) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once CMS_ROOT . '/core/session_boot.php';
    cms_session_start('admin');

    if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    require_once CMS_ROOT . '/app/controllers/admin/AiTb5Controller.php';
    $controller = new \App\Controllers\Admin\AiTb5Controller();
    $controller->generate();
    exit;
}

// AI Theme Builder v5 API - Regenerate
if (preg_match('#^/api/jtb/ai/regenerate$#', $_aitb5Uri) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once CMS_ROOT . '/core/session_boot.php';
    cms_session_start('admin');

    if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    require_once CMS_ROOT . '/app/controllers/admin/AiTb5Controller.php';
    $controller = new \App\Controllers\Admin\AiTb5Controller();
    $controller->regenerate();
    exit;
}

// AI Theme Builder v5 API - Deploy
if (preg_match('#^/api/jtb/ai/deploy$#', $_aitb5Uri) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once CMS_ROOT . '/core/session_boot.php';
    cms_session_start('admin');

    if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    require_once CMS_ROOT . '/app/controllers/admin/AiTb5Controller.php';
    $controller = new \App\Controllers\Admin\AiTb5Controller();
    $controller->deploy();
    exit;
}

// AI Theme Builder v5 API - Fetch Images
if (preg_match('#^/api/jtb/ai/fetch-images$#', $_aitb5Uri) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once CMS_ROOT . '/core/session_boot.php';
    cms_session_start('admin');

    if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    require_once CMS_ROOT . '/app/controllers/admin/AiTb5Controller.php';
    $controller = new \App\Controllers\Admin\AiTb5Controller();
    $controller->fetchImages();
    exit;
}

// END AI THEME BUILDER 5.0 ROUTES
