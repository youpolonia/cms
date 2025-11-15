<?php
require_once __DIR__ . '/../../config.php';

/**
 * Admin Footer Template
 * Includes: Performance metrics, debug info, copyright
 */
?></main>
<footer class="admin-footer">
    <div class="admin-meta">
        <div class="admin-performance">
            <?php printf('Page generated in %.3f seconds', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']); ?>
            | Memory: <?php echo round(memory_get_peak_usage()/1024/1024, 2); ?>MB
        </div>
        <div class="admin-copyright">
            &copy; <?php echo date('Y'); ?> CMS Platform
        </div>
    </div>
    <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
    <div class="admin-debug">
        <h3>Debug Information</h3>
        <pre><?php print_r([
            'Session' => $_SESSION ?? [],
            'Request' => $_REQUEST,
            'Server' => array_diff_key($_SERVER, array_flip(['HTTP_COOKIE', 'PHP_AUTH_PW']))
        ]); ?></pre>
    </div>
    <?php endif; ?>
</footer>
</body>
</html>
