<?php
namespace Admin\Controllers;

require_once __DIR__ . '/../../config.php';

// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class DashboardRenderer {
    public static function renderMarkdown(string $content): string {
        $html = htmlspecialchars($content);
        $html = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $html);
        $html = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^- \[ \] (.*)$/m', '<li class="pending">$1</li>', $html);
        $html = preg_replace('/^- \[x\] (.*)$/m', '<li class="completed">$1</li>', $html);
        return $html;
    }
}
