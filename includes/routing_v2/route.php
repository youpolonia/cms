<?php

namespace Includes\RoutingV2;

class Route {
    protected string $method;
    protected string $path;
    protected $handler;
    protected array $middlewares = [];

    public function __construct(string $method, string $path, $handler) {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    public function matches(string $method, string $path): bool {
        return $this->method === $method && $this->path === $path;
    }

    public function addMiddleware(MiddlewareInterface $middleware): self {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function getMiddlewares(): array {
        return $this->middlewares;
    }

    public function getHandler() {
        return $this->handler;
    }
}
