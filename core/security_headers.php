<?php
/**
 * Security headers for admin and public endpoints.
 * Idempotent: safe to call multiple times; no output buffering assumptions.
 */
function cms_emit_security_headers(): void {
    if (headers_sent()) { return; }
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer-when-downgrade');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // CSP with external media sources for Theme Builder, TinyMCE, and Lucide icons
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob: https://images.pexels.com https://*.pexels.com https://sp.tinymce.com; media-src 'self' https://videos.pexels.com https://*.pexels.com blob:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tiny.cloud https://unpkg.com https://cdnjs.cloudflare.com; frame-src 'self' https://www.youtube.com https://player.vimeo.com https://maps.google.com https://*.google.com https://www.openstreetmap.org; frame-ancestors 'self'; font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com; connect-src 'self' https://cdn.tiny.cloud https://sp.tinymce.com");
    // Enforce HTTPS for one year on all subdomains; safe to add when site is served over TLS
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
