<?php

namespace Includes\Middleware;

use Includes\RoutingV2\Request;
use Includes\RoutingV2\Response;
use Includes\RoutingV2\MiddlewareInterface;
use App\Models\Role;
use App\Models\User;

class CheckPermission implements MiddlewareInterface
{
    private string $permission;
    
    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }
    
    public function process(Request $request, callable $next): Response
    {
        // Get authenticated user
        $user = $request->user();
        
        if (!$user) {
            return new Response(401, ['Content-Type' => 'text/plain'], 'Unauthorized');
        }

        // Check if user has any role with required permission
        $hasPermission = false;
        foreach ($user->roles() as $role) {
            if ($role->hasPermissionTo($this->permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return new Response(403, ['Content-Type' => 'text/plain'], 'Forbidden');
        }

        return $next($request);
    }
}
