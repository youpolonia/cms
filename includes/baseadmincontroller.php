<?php

abstract class BaseAdminController
{
    /**
     * Verify admin authentication
     * @throws \RuntimeException If not authenticated
     */
    protected function verifyAdminAuth(): void
    {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            throw new \RuntimeException('Admin authentication required');
        }
    }

    /**
     * Verify CSRF token
     * @param string $token CSRF token from request
     * @throws \RuntimeException If token is invalid
     */
    protected function verifyCsrfToken(string $token): void
    {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new \RuntimeException('Invalid CSRF token');
        }
    }

    /**
     * Send JSON response
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Handle exceptions and return JSON error response
     * @param \Throwable $e Exception to handle
     */
    protected function handleError(\Throwable $e): void
    {
        $statusCode = $e instanceof \RuntimeException ? 400 : 500;
        $this->jsonResponse([
            'error' => $e->getMessage(),
            'trace' => DEBUG_MODE ? $e->getTrace() : null
        ], $statusCode);
    }
}
