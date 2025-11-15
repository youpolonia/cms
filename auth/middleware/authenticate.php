<?php

declare(strict_types=1);

namespace Auth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Auth\Services\SessionService;

class Authenticate implements MiddlewareInterface
{
    private SessionService $session;

    public function __construct(SessionService $session)
    {
        $this->session = $session;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $this->session->start();

        if (!$this->session->has(AuthService::AUTH_USER_ID_KEY)) {
            return new \GuzzleHttp\Psr7\Response(401, [], 'Unauthorized');
        }

        // Verify tenant access if tenantId is in route
        $routeParams = $request->getAttribute('routeParams', []);
        if (isset($routeParams['tenantId'])) {
            $userTenantId = $this->session->get(AuthService::AUTH_TENANT_ID_KEY);
            if ($routeParams['tenantId'] != $userTenantId) {
                return new \GuzzleHttp\Psr7\Response(403, [], 'Forbidden');
            }
        }

        return $handler->handle($request);
    }
}
