<?php
namespace Includes\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Router
{
    protected array $routes = [];
    protected array $routeParams = [];

    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    public function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function match(ServerRequestInterface $request): ?callable
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        // Check exact matches first
        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path];
        }

        // Check for parameterized routes
        foreach ($this->routes[$method] ?? [] as $routePath => $handler) {
            if ($this->matchParameterizedRoute($routePath, $path)) {
                return $handler;
            }
        }

        return null;
    }

    protected function matchParameterizedRoute(string $routePath, string $requestPath): bool
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        $this->routeParams = [];

        foreach ($routeParts as $i => $routePart) {
            if (str_starts_with($routePart, '{') && str_ends_with($routePart, '}')) {
                $paramName = trim($routePart, '{}');
                $this->routeParams[$paramName] = $requestParts[$i];
                continue;
            }

            if ($routePart !== $requestParts[$i]) {
                return false;
            }
        }

        return true;
    }

    public function getParams(): array
    {
        return $this->routeParams;
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $handler = $this->match($request);

        if ($handler === null) {
            return $response->withStatus(404);
        }

        $request = $request->withAttribute('routeParams', $this->getParams());
        return $handler($request, $response);
    }
}
