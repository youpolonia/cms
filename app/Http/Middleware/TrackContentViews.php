<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ContentTrackingService;
use Symfony\Component\HttpFoundation\Response;

class TrackContentViews
{
    public function __construct(
        protected ContentTrackingService $tracker
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET')) {
            $contentId = $request->route('content');
            $user = $request->user();

            if ($contentId && $user) {
                $this->tracker->trackView($contentId, $user->id);
            }
        }

        return $response;
    }
}
