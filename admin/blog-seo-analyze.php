<?php
require_once __DIR__ . '/../models/blogmanager.php';
require_once __DIR__ . '/../models/SEOToolkit.php';
// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

cms_session_start('admin');


// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }

// Sanitize input
$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
$content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
$tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_STRING);

// Analyze with SEO Toolkit
$seoToolkit = new SEOToolkit();
$suggestions = $seoToolkit->analyzeContent($title, $content, $tags);

// Store suggestions in session
$_SESSION['seo_suggestions'] = [
    'meta_title' => $suggestions->getMetaTitle(),
    'meta_description' => $suggestions->getMetaDescription(),
    'keywords' => $suggestions->getKeywords()
];

// Redirect back to admin
header("Location: /admin/blog-admin-view.php" . (isset($_GET['slug']) ? '?slug=' . urlencode($_GET['slug']) : ''));
exit;
