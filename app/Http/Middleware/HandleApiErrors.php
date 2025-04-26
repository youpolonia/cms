<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleApiErrors
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->isServerError()) {
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }

        if ($response->isNotFound()) {
            return response()->json([
                'message' => 'The requested resource was not found.'
            ], 404);
        }

        return $response;
    }
}