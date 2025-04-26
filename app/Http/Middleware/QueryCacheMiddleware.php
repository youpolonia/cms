<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\QueryCacheService;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class QueryCacheMiddleware
{
    protected QueryCacheService $cacheService;

    public function __construct(QueryCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function handle($request, Closure $next, $ttl = '3600')
    {
        $key = $this->getCacheKey($request);
        $tags = $this->getCacheTags($request);

        if ($request->isMethod('GET')) {
            $response = $this->cacheService->remember(
                fn() => $next($request),
                $key,
                (int)$ttl,
                $tags
            );

            if ($response instanceof Response) {
                $response->header('X-Cache', 'HIT');
            }
            return $response;
        }

        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->cacheService->flushByTag($tags);
        }

        return $response;
    }

    protected function getCacheKey($request): string
    {
        return 'route_'.Route::currentRouteName().'_'.md5($request->fullUrl());
    }

    protected function getCacheTags($request): array
    {
        return ['route_'.Route::currentRouteName()];
    }
}