<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class HomeController
{
    public function index(): void
    {
        $contentView = __DIR__ . '/../views/home/index.php';
        require_once __DIR__ . '/../views/layouts/main.php';
    }
}
