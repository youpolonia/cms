<?php

declare(strict_types=1);

namespace Auth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Auth\Services\SessionService;

class Guest implements MiddlewareInterface
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

        if ($this->session->has('user')) {
            // TODO: Redirect to dashboard or home
            throw new \RuntimeException('Already authenticated');
        }

        return $handler->handle($request);
    }
}
