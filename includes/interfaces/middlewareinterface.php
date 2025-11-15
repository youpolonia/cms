<?php

namespace Includes\RoutingV2;

interface MiddlewareInterface {
    public function process(\Includes\RoutingV2\Request $request, callable $next): \Includes\RoutingV2\Response;
}
