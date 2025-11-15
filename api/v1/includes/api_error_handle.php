<?php
// Minimal API error handler shim
if (!function_exists('api_error')) {
    function api_error(int $code, string $message): void { http_response_code($code); }
}
