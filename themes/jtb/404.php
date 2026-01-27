<?php
/**
 * JTB Theme - 404 Error Page Template
 *
 * @package JTB Theme
 */

defined('CMS_ROOT') or die('Direct access not allowed');
?>
<div class="jtb-error-page jtb-404">
    <div class="container">
        <div class="error-content">
            <span class="error-code">404</span>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-message">The page you're looking for doesn't exist or has been moved.</p>

            <div class="error-actions">
                <a href="/" class="btn btn-primary">Go to Homepage</a>
                <a href="/blog" class="btn btn-secondary">Visit Blog</a>
            </div>

            <div class="error-search">
                <p>Or try searching:</p>
                <form action="/search" method="get" class="search-form">
                    <input type="search" name="q" placeholder="Search..." aria-label="Search">
                    <button type="submit" aria-label="Submit search">Search</button>
                </form>
            </div>
        </div>
    </div>
</div>
