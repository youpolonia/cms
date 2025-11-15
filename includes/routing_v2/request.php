<?php

namespace Includes\RoutingV2;

class Request {
    private string $method;
    private string $path;
    private array $queryParams;
    private array $headers;
    private $body;
    private ?string $tenantId = null;

    public function __construct(string $method, string $path, array $queryParams = [], array $headers = [], $body = null) {
        $this->method = $method;
        $this->path = $path;
        $this->queryParams = $queryParams;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getQueryParams(): array {
        return $this->queryParams;
    }

    public function getHeaders(): array {
        return $this->headers;
    }
    
    public function getHeader(string $name): ?string {
        $normalizedName = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $normalizedName) {
                return $value;
            }
        }
        return null;
    }

    public function getBody() {
        return $this->body;
    }

    public function setTenantId(string $tenantId): void {
        $this->tenantId = $tenantId;
    }

    public function getTenantId(): ?string {
        return $this->tenantId;
    }

    public function hasTenant(): bool {
        return $this->tenantId !== null;
    }

    public function setPath(string $path): void {
        $this->path = $path;
    }
}
