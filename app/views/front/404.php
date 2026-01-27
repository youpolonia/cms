<?php
/**
 * 404 Not Found Page
 * Supports Theme Builder custom 404 template
 */
$pageTitle = '404 Not Found';
http_response_code(404);

// Load Theme Builder functions for custom 404 template
if (!function_exists('tb_render_site_template')) {
    $tbDatabasePath = dirname(__DIR__, 3) . '/core/theme-builder/database.php';
    if (file_exists($tbDatabasePath)) {
        require_once $tbDatabasePath;
    }
}

// Try to get TB 404 template
$tb404 = null;
if (function_exists('tb_render_site_template')) {
    $tb404 = tb_render_site_template('404');
}

require_once __DIR__ . '/layouts/header.php';

if ($tb404): ?>
    <!-- Theme Builder 404 Page -->
    <?= $tb404 ?>
<?php else: ?>
    <!-- Static Fallback 404 -->
    <section class="error-page">
        <div class="container">
            <div class="error-content">
                <div class="error-code">4<span class="zero">🤖</span>4</div>
                <h1>Page Not Found</h1>
                <p>The page you're looking for doesn't exist or has been moved.</p>
                <div class="error-actions">
                    <a href="/" class="btn btn-primary">← Back to Home</a>
                    <a href="/articles" class="btn btn-secondary">Browse Articles</a>
                </div>
            </div>
        </div>
    </section>

    <style>
    .error-page { min-height: 80vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 120px 0; }
    .error-code { font-size: clamp(6rem, 20vw, 12rem); font-weight: 800; background: var(--gradient-text); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; margin-bottom: 20px; }
    .error-code .zero { -webkit-text-fill-color: initial; }
    .error-content h1 { margin-bottom: 16px; }
    .error-content p { font-size: 1.2rem; margin-bottom: 32px; max-width: 400px; margin-left: auto; margin-right: auto; }
    .error-actions { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
    </style>
<?php endif; ?>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
