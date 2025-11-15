<?php

namespace Includes\RoutingV2\Middleware;

use Includes\RoutingV2\Request;
use Includes\RoutingV2\Response;
use Includes\RoutingV2\MiddlewareInterface;
use Includes\Database\TenantContext;
use InvalidArgumentException;

class TenantDetectionMiddleware implements MiddlewareInterface {
    public function process(Request $request, callable $next): Response {
        // Check subdomain first (e.g., tenant1.example.com)
        $host = $request->getHeader('Host');
        $parts = explode('.', $host);
        if (count($parts) > 2) {
            $tenantId = $parts[0];
            try {
                TenantContext::setCurrentTenantId((int)$tenantId);
                return $next($request);
            } catch (InvalidArgumentException $e) {
                return new Response(404, [], 'Tenant not found');
            }
        }

        // Check path prefix (e.g., /tenant1/route)
        $path = $request->getPath();
        $pathParts = explode('/', trim($path, '/'));
        if (!empty($pathParts[0])) {
            try {
                TenantContext::setCurrentTenantId((int)$pathParts[0]);
                // Remove tenant prefix from path for route matching
                $request->setPath('/' . implode('/', array_slice($pathParts, 1)));
                return $next($request);
            } catch (InvalidArgumentException $e) {
                // Continue to next detection method
            }
        }

        // Check custom header (X-Tenant-ID)
        if ($request->hasHeader('X-Tenant-ID')) {
            $tenantId = $request->getHeader('X-Tenant-ID');
            try {
                TenantContext::setCurrentTenantId((int)$tenantId);
                return $next($request);
            } catch (InvalidArgumentException $e) {
                return new Response(403, [], 'Invalid tenant ID');
            }
        }

        // No tenant detected - proceed with default behavior
        return $next($request);
    }
}
