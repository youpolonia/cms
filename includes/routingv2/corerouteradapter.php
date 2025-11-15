<?php
declare(strict_types=1);

namespace Includes\RoutingV2;

use Closure;
use Core\Request as CoreRequest;
use Core\Response as CoreResponse;
use Includes\RoutingV2\Router; // The target router
use Includes\RoutingV2\Request; // RoutingV2 Request
use Includes\RoutingV2\Response; // RoutingV2 Response

/**
 * Class CoreRouterAdapter
 *
 * Provides a compatibility layer to adapt routes defined for
 * the old Core\Router to the new RoutingV2\Router system.
 */
class CoreRouterAdapter
{
    private Router $routerV2;
    private $dependencies; // To mimic Core\Router's getDependencies()

    public function __construct(Router $routerV2, array $dependencies = [])
    {
        $this->routerV2 = $routerV2;
        $this->dependencies = $dependencies;
    }

    /**
     * Mimics Core\Router->getDependencies() for adapted closures.
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Adapts the addRoute method from Core\Router.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path The route path
     * @param Closure $handler The closure handler from the old router
     * @return mixed Whatever RoutingV2\Router->addRoute returns (likely a Route object)
     */
    public function addRoute(string $method, string $path, Closure $handler)
    {
        // The core of the adaptation:
        // We need to wrap the Core\Router's closure handler in a way
        // that it can be called by RoutingV2\Router, which expects
        // a [controller, method] array or a callable that matches its own signature.
        // For now, let's create a new closure that handles the Request/Response conversion.

        $adaptedHandler = function (Request $requestV2) use ($handler) {
            // 1. Potentially convert RoutingV2\Request to CoreRequest if needed by the closure.
            //    For simplicity, let's assume the old closures might not strictly type-hint CoreRequest
            //    or can work with a generic request object for now.
            //    A more robust solution might involve creating a CoreRequest from $requestV2.
            //    $coreRequest = new CoreRequest($requestV2->getGlobals(), ...);

            // 2. Call the original handler.
            //    The original handler expects $this (the CoreRouterAdapter instance) to be available
            //    if it uses $router->getDependencies() internally. We bind $this.
            $boundHandler = $handler->bindTo($this);
            $coreResponse = $boundHandler(); // Or $boundHandler($coreRequest);

            // 3. Convert the CoreResponse to a RoutingV2\Response.
            if ($coreResponse instanceof CoreResponse) {
                // This is a simplified conversion. A more detailed mapping would be needed.
                $content = $coreResponse->getBody();
                $status = $coreResponse->getStatusCode();
                $headers = $coreResponse->getHeaders();

                // If the CoreResponse was a redirect, preserve the Location header
                if ($status === 301 || $status === 302) {
                    $location = $coreResponse->getHeaderLine('Location');
                    if ($location) {
                        return Response::redirect($location, $status);
                    }
                }
                return new Response($content, $status, $headers);
            }

            // If the handler returned something else (e.g., a string), wrap it.
            return new Response((string) $coreResponse);
        };

        // Add the route to the RoutingV2 router with the adapted handler.
        // RoutingV2\Router might expect [controller, method] or a callable.
        // We pass the callable $adaptedHandler.
        return $this->routerV2->addRoute($method, $path, $adaptedHandler);
    }

    // Potentially add other methods from Core\Router if they are used and need adaptation.
    // For example, if group() or middleware() methods were used in a way that needs translation.
}
