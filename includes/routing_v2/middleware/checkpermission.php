<?php
declare(strict_types=1);

namespace Includes\RoutingV2\Middleware;

use Includes\RoutingV2\Request;
use Includes\RoutingV2\Response;
use Includes\RoutingV2\MiddlewareInterface;

class CheckPermission implements MiddlewareInterface
{
    private string $permission;
    
    public function __construct(string $permission = '')
    {
        $this->permission = $permission;
    }
    
    public function process(Request $request, callable $next): Response
    {
        $requiredPermission = $this->permission;
        
        if (!$requiredPermission) {
            return $next($request);
        }

        // TODO: Implement actual permission check against user's permissions
        // For testing purposes, we'll just check if permission is in route name
        $userHasPermission = str_contains($request->getPath(), $requiredPermission);

        if (!$userHasPermission) {
            return new Response(403, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Permission denied',
                'required_permission' => $requiredPermission
            ]));
        }

        return $next($request);
    }
}
