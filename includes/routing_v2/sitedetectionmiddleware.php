<?php

class SiteDetectionMiddleware {
    public function handle($request, $next) {
        // Try multiple tenant detection methods
        $site = $this->detectTenant($request);
        
        if (!$site) {
            return $this->handleTenantNotFound($request);
        }

        // Verify tenant is active
        if (!$site->is_active) {
            return $this->handleTenantInactive($request);
        }

        // Store tenant in request context
        $request->setSiteContext($site);

        return $next($request);
    }

    protected function detectTenant($request) {
        // 1. Try subdomain detection (primary method)
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);
        
        if ($subdomain) {
            $site = $this->getSiteByDomain($subdomain . '.' . $this->getBaseDomain());
            if ($site) {
                return $site;
            }
        }

        // 2. Try URL path prefix (fallback)
        $path = $request->getPathInfo();
        $pathPrefix = $this->extractPathPrefix($path);
        
        if ($pathPrefix) {
            $site = $this->getSiteByName($pathPrefix);
            if ($site) {
                return $site;
            }
        }

        // 3. Try custom header (for API requests)
        if ($request->headers->has('X-Tenant-ID')) {
            $tenantId = $request->headers->get('X-Tenant-ID');
            return $this->getSiteById($tenantId);
        }

        // 4. Fallback to default site
        return $this->getDefaultSite();
    }

    protected function extractSubdomain($host) {
        $parts = explode('.', $host);
        if (count($parts) > 2) {
            return $parts[0];
        }
        return null;
    }

    protected function extractPathPrefix($path) {
        $parts = explode('/', ltrim($path, '/'));
        return $parts[0] ?? null;
    }

    protected function getBaseDomain() {
        return defined('MULTISITE_BASE_DOMAIN') ? MULTISITE_BASE_DOMAIN : 'example.com';
    }

    protected function getSiteByDomain($domain) {
        $result = db_query(
            "SELECT * FROM sites WHERE domain = ?",
            [$domain]
        );
        return $result->fetchObject();
    }

    protected function getSiteByName($name) {
        $result = db_query(
            "SELECT * FROM sites WHERE name = ?",
            [$name]
        );
        return $result->fetchObject();
    }

    protected function getSiteById($id) {
        $result = db_query(
            "SELECT * FROM sites WHERE id = ?",
            [$id]
        );
        return $result->fetchObject();
    }

    protected function getDefaultSite() {
        $defaultSite = defined('MULTISITE_DEFAULT_SITE') ? MULTISITE_DEFAULT_SITE : 'default';
        return $this->getSiteByName($defaultSite);
    }

    protected function handleTenantNotFound($request) {
        if ($request->isApiRequest()) {
            return new JsonResponse([
                'error' => 'Tenant not found',
                'code' => 404
            ], 404);
        }

        // For web requests, redirect to default site
        return new RedirectResponse(
            $this->getBaseDomain() . $request->getPathInfo()
        );
    }

    protected function handleTenantInactive($request) {
        if ($request->isApiRequest()) {
            return new JsonResponse([
                'error' => 'Tenant is inactive',
                'code' => 403
            ], 403);
        }

        // For web requests, show maintenance page
        return new Response(
            file_get_contents(__DIR__ . '/../views/tenant_inactive.html'),
            403
        );
    }
}
