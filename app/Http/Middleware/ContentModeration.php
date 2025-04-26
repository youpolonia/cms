<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;

class ContentModeration
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldModerate($request)) {
            return $next($request);
        }

        $content = $this->getContentToModerate($request);

        if (!empty($content)) {
            $provider = config('content_moderation.default_provider');
            $service = app(config("content_moderation.providers.{$provider}.service"));
            $method = config("content_moderation.providers.{$provider}.method");

            $result = $service->{$method}($content);

            if (!$result['safe']) {
                return response()->json([
                    'error' => 'Content violates moderation policy',
                    'flags' => $result['categories'],
                    'scores' => $result['scores'],
                    'provider' => $provider
                ], 422);
            }
        }

        return $next($request);
    }

    protected function shouldModerate(Request $request): bool
    {
        $routes = config('content_moderation.routes', []);
        $methodMatches = in_array($request->method(), ['POST', 'PUT', 'PATCH']);

        return $methodMatches && collect($routes)->contains(function ($pattern) use ($request) {
            return str_is($pattern, $request->path());
        });
    }

    protected function getContentToModerate(Request $request): ?string
    {
        $fields = config('content_moderation.fields', []);

        return collect($fields)
            ->map(fn ($field) => $request->input($field))
            ->filter()
            ->first();
    }
}
