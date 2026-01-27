<?php
declare(strict_types=1);

namespace Core;

class Response
{
    public static function redirect(string $url, int $code = 302): never
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    public static function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function html(string $content, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
        exit;
    }

    public static function notFound(string $message = 'Page not found'): never
    {
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>" . htmlspecialchars($message) . "</p>";
        exit;
    }

    public static function forbidden(string $message = 'Access denied'): never
    {
        http_response_code(403);
        echo "<h1>403 Forbidden</h1><p>" . htmlspecialchars($message) . "</p>";
        exit;
    }
}
