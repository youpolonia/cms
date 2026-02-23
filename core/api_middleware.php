<?php
declare(strict_types=1);

/**
 * API Middleware for Jessie CMS REST API
 * 
 * Provides CORS handling, JSON response formatting, error handling,
 * and pagination utilities for the public REST API.
 * 
 * @package JessieCMS
 * @since 2026-02-15
 */

namespace Core;

/**
 * Set CORS headers for API endpoints
 * Allows all origins, common HTTP methods, and headers for public API access
 */
function api_cors_headers(): void {
    // Allow all origins for public API
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key, Accept');
    header('Access-Control-Max-Age: 86400'); // 24 hours

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Send JSON response with proper headers and status code
 * 
 * @param array $data Response data
 * @param int $status HTTP status code
 */
function api_json_response(array $data, int $status = 200): void {
    api_cors_headers();
    
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    
    // Rate limiting header (simple implementation)
    header('X-RateLimit-Limit: 60');
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send JSON error response
 * 
 * @param string $message Error message
 * @param int $status HTTP status code
 */
function api_error(string $message, int $status = 400): void {
    api_json_response([
        'error' => [
            'message' => $message,
            'status' => $status
        ]
    ], $status);
}

/**
 * Generate pagination metadata
 * 
 * @param int $page Current page number (1-based)
 * @param int $perPage Items per page
 * @param int $total Total number of items
 * @return array Pagination metadata
 */
function api_paginate(int $page, int $perPage, int $total): array {
    $totalPages = max(1, (int)ceil($total / $perPage));
    
    return [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages
    ];
}

/**
 * Sanitize content for API output
 * Strips HTML tags and provides both raw and text versions
 * 
 * @param string $content Content to sanitize
 * @return array Array with 'content' (HTML) and 'content_text' (plain text)
 */
function api_sanitize_content(string $content): array {
    return [
        'content' => $content,
        'content_text' => strip_tags($content)
    ];
}

/**
 * Rate limiting check (simple IP-based implementation)
 * Uses same login_attempts table pattern for tracking requests
 * 
 * @param int $limit Requests per minute (default: 60)
 * @return bool True if request is allowed, false if rate limited
 */
function api_rate_limit_check(int $limit = 60): bool {
    try {
        $pdo = \core\Database::connection();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $now = time();
        $window = $now - 60; // 1 minute window
        
        // Clean old entries
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < ?");
        $stmt->execute([$window]);
        
        // Count current attempts
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > ?");
        $stmt->execute([$ip, $window]);
        $attempts = (int)$stmt->fetchColumn();
        
        if ($attempts >= $limit) {
            return false;
        }
        
        // Log this request
        $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempt_time, user_agent) VALUES (?, ?, ?)");
        $stmt->execute([$ip, $now, $_SERVER['HTTP_USER_AGENT'] ?? '']);
        
        return true;
    } catch (\Throwable $e) {
        // If rate limiting fails, allow the request
        error_log("API rate limit error: " . $e->getMessage());
        return true;
    }
}

/**
 * Apply rate limiting with automatic error response
 * 
 * @param int $limit Requests per minute
 */
function api_rate_limit(int $limit = 60): void {
    if (!api_rate_limit_check($limit)) {
        api_error('Rate limit exceeded. Please try again later.', 429);
    }
}

/**
 * Authenticate API request using Bearer token.
 * Checks Authorization header against api_keys table.
 *
 * @param string|null $requiredPermission  Optional permission to check (e.g. "articles:write")
 * @return array  The api_key row on success; calls api_error() and exits on failure
 */
function api_authenticate(?string $requiredPermission = null): array {
    $header = $_SERVER["HTTP_AUTHORIZATION"] ?? "";

    if ($header === "" || !str_starts_with($header, "Bearer ")) {
        api_error("Missing or invalid Authorization header. Use: Bearer {api_key}", 401);
    }

    $token = substr($header, 7);
    if ($token === "") {
        api_error("Empty Bearer token", 401);
    }

    try {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare(
            "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([$token]);
        $key = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$key) {
            api_error("Invalid or inactive API key", 401);
        }

        // Check specific permission if required
        if ($requiredPermission !== null) {
            $perms = json_decode($key["permissions"] ?? '["*"]', true) ?: [];
            if (!in_array("*", $perms, true) && !in_array($requiredPermission, $perms, true)) {
                api_error("Insufficient permissions. Required: " . $requiredPermission, 403);
            }
        }

        // Update last_used_at and request_count
        $pdo->prepare(
            "UPDATE api_keys SET last_used_at = NOW(), request_count = request_count + 1 WHERE id = ?"
        )->execute([$key["id"]]);

        return $key;

    } catch (\Throwable $e) {
        error_log("API auth error: " . $e->getMessage());
        api_error("Authentication error", 500);
    }

    // Unreachable but satisfies static analysis
    exit;
}

/**
 * Read JSON request body and decode it.
 *
 * @return array  Decoded JSON data
 */
function api_read_json(): array {
    $raw = file_get_contents("php://input");
    if ($raw === "" || $raw === false) {
        api_error("Request body is empty", 422);
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        api_error("Invalid JSON in request body", 422);
    }

    return $data;
}
