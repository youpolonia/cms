<?php
/**
 * Jessie Theme - 404 Error Page Template
 * Modern error page with gradient text and action buttons
 *
 * @var array $page Page data array (optional)
 * @var string $content Raw content (optional)
 */
?>
<section class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-icon">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="url(#gradient)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#8b5cf6"/>
                            <stop offset="50%" style="stop-color:#6366f1"/>
                            <stop offset="100%" style="stop-color:#06b6d4"/>
                        </linearGradient>
                    </defs>
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    <circle cx="12" cy="16" r="1"/>
                </svg>
            </div>
            <div class="error-code">
                <span>4</span>
                <span class="zero">0</span>
                <span>4</span>
            </div>
            <h1>Page Not Found</h1>
            <p>The page you're looking for doesn't exist or has been moved to another location.</p>
            <div class="error-actions">
                <a href="/" class="btn btn-primary">Back to Home</a>
                <a href="/blog" class="btn btn-secondary">Browse Articles</a>
            </div>
            <div class="error-suggestions">
                <p>You might want to try:</p>
                <ul>
                    <li>Checking the URL for typos</li>
                    <li>Using the navigation menu above</li>
                    <li>Searching for what you need</li>
                </ul>
            </div>
        </div>
    </div>
</section>
