<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../models/blogmanager.php';

class BlogController {
    private BlogManager $blogManager;

    public function __construct() {
        $this->blogManager = new BlogManager();
    }

    public function handleRequest(): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Blog list view
        if ($uri === '/blog' && $method === 'GET') {
            require_once __DIR__ . '/../views/blog-list-view.php';
            return;
        }

        // Single blog post view
        if (preg_match('#^/blog/([a-z0-9\-]+)$#', $uri, $matches) && $method === 'GET') {
            $_GET['slug'] = $matches[1];
            require_once __DIR__ . '/../views/blog-single-view.php';
            return;
        }

        // Blog admin view
        if ($uri === '/admin/blog' && $method === 'GET') {
            require_once __DIR__ . '/../admin/blog-admin-view.php';
            return;
        }

        // Handle 404
        header("HTTP/1.0 404 Not Found");
        echo "Page not found";
    }
}

// Instantiate and run the controller
$controller = new BlogController();
$controller->handleRequest();
