<?php

namespace Includes\RoutingV2;

class RouteTable {
    private array $globalRoutes = [];
    private array $tenantRoutes = [];

    public function addRoute(Route $route, ?string $tenantId = null): void {
        if ($tenantId) {
            if (!isset($this->tenantRoutes[$tenantId])) {
                $this->tenantRoutes[$tenantId] = [];
            }
            $this->tenantRoutes[$tenantId][] = $route;
        } else {
            $this->globalRoutes[] = $route;
        }
    }

    public function findRoute(string $method, string $path, ?string $tenantId = null): ?Route {
        // First try tenant-specific routes if tenant is specified
        if ($tenantId && isset($this->tenantRoutes[$tenantId])) {
            foreach ($this->tenantRoutes[$tenantId] as $route) {
                if ($route->matches($method, $path)) {
                    return $route;
                }
            }
        }

        // Fall back to global routes
        foreach ($this->globalRoutes as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }

        return null;
    }
}
