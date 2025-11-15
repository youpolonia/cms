<?php
/**
 * Auth Middleware - Checks if user is authenticated
 */
function authMiddleware(): bool {
    return AuthServiceWrapper::checkAuth();
}
