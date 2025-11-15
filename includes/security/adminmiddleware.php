<?php
/**
 * Admin Middleware - Checks if user is admin
 */
function adminMiddleware(): bool {
    return AuthServiceWrapper::checkAdminAuth();
}
