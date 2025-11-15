<?php
/**
 * Error Handling Middleware
 * Catches and formats exceptions for API responses
 */

class ErrorHandlerMiddleware {
    public function process($request, $response) {
        try {
            // Let the request continue to next middleware/handler
            return;
        } catch (Exception $e) {
            $statusCode = $this->getStatusCode($e);
            $errorData = [
                'error' => $this->getErrorType($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];

            // Include trace in development
            if ($this->isDevelopment()) {
                $errorData['trace'] = $e->getTrace();
            }

            $response->setStatusCode($statusCode);
            $response->setBody(json_encode($errorData));
            $response->send();
            exit;
        }
    }

    private function getStatusCode($exception) {
        if ($exception instanceof InvalidArgumentException) {
            return 400;
        }
        if ($exception instanceof AuthenticationException) {
            return 401;
        }
        if ($exception instanceof AuthorizationException) {
            return 403;
        }
        if ($exception instanceof NotFoundException) {
            return 404;
        }
        return 500;
    }

    private function getErrorType($exception) {
        $className = get_class($exception);
        return strtolower(substr($className, strrpos($className, '\\') + 1));
    }

    private function isDevelopment() {
        return ($_ENV['APP_ENV'] ?? 'production') === 'development';
    }
}
