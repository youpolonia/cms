<?php

namespace Includes\Middleware;

// Include required files
require_once __DIR__ . '/../routing_v2/MiddlewareInterface.php';
require_once __DIR__ . '/../routing_v2/request.php';
require_once __DIR__ . '/../routing_v2/response.php';
require_once __DIR__ . '/../models/userbehaviormodel.php';
require_once __DIR__ . '/../auth/SessionManager.php';
require_once __DIR__ . '/../errorhandler.php';

use Includes\RoutingV2\Request;
use Includes\RoutingV2\Response;
use Includes\RoutingV2\MiddlewareInterface;
use Includes\Models\UserBehaviorModel;
use Includes\Auth\SessionManager;
use Includes\ErrorHandler;

class UserBehaviorTrackingMiddleware implements MiddlewareInterface {
    public function process(Request $request, callable $next): Response {
        $response = $next($request);

        // Only track GET requests for content pages
        if ($request->getMethod() === 'GET' && strpos($request->getPath(), '/content/') === 0) {
            try {
                $session = SessionManager::getInstance();
                $userId = $session->get('user_id') ?? 0;
                
                UserBehaviorModel::logEvent([
                    'user_id' => $userId,
                    'session_id' => $session->getId(),
                    'event_type' => 'page_view',
                    'content_id' => $this->extractContentId($request->getPath()),
                    'metadata' => [
                        'path' => $request->getPath(),
                        'user_agent' => $request->getHeader('User-Agent'),
                        'referrer' => $request->getHeader('Referer')
                    ]
                ]);
            } catch (Exception $e) {
                ErrorHandler::logError($e);
            }
        }

        return $response;
    }

    private function extractContentId(string $path): ?int {
        $parts = explode('/', $path);
        return isset($parts[2]) && is_numeric($parts[2]) ? (int)$parts[2] : null;
    }
}
