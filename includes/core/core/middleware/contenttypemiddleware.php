<?php
/**
 * Content-Type Validation Middleware
 * Ensures proper content type headers for API requests
 */

class ContentTypeMiddleware {
    public function process($request, $response) {
        // Skip for GET/HEAD/DELETE requests
        if (in_array($request->method, ['GET', 'HEAD', 'DELETE'])) {
            return;
        }

        $contentType = $request->headers['Content-Type'] ?? '';

        if (strpos($contentType, 'application/json') === false) {
            $response->setStatusCode(415);
            $response->setBody(json_encode([
                'error' => 'Unsupported Media Type',
                'message' => 'Content-Type must be application/json'
            ]));
            $response->send();
            exit;
        }
    }
}
