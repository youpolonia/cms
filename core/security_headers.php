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
    // Minimal CSP compatible with legacy templates; can be tightened later.
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-ancestors 'self'");
    // Enforce HTTPS for one year on all subdomains; safe to add when site is served over TLS
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
