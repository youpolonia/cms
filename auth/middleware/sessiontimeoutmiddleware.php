<?php

declare(strict_types=1);

namespace Auth\Middleware;

use Auth\Services\SessionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionTimeoutMiddleware implements MiddlewareInterface
{
    private SessionService $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($this->sessionService->isExpired()) {
            $this->sessionService->destroy();
            throw new \RuntimeException('Session expired due to inactivity');
        }

        $this->sessionService->updateActivity();
        return $handler->handle($request);
    }
}
