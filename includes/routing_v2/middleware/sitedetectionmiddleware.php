<?php

namespace Includes\RoutingV2\Middleware;

use Closure;
use PDO;
use Includes\Database\TenantContext;
use Includes\Services\SiteService;
use Includes\Database\Connection;

class SiteDetectionMiddleware
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $db = Connection::getInstance()->getPDO();
        $siteService = new SiteService($db);
        $site = $siteService->findByDomain($host);
        
        if ($site) {
            TenantContext::setCurrentTenantId($site['id']);
        }

        return $next($request);
    }
}
