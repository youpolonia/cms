<?php
declare(strict_types=1);

namespace Core;

class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $post;
    private array $params = [];

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $this->query = $_GET;
        $this->post = $_POST;

        if (strpos($this->path, '/public') === 0) {
            $this->path = substr($this->path, 7) ?: '/';
        }
    }

    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->post);
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function isPost(): bool { return $this->method === 'POST'; }
    public function isGet(): bool { return $this->method === 'GET'; }
}
