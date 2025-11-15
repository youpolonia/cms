<?php
namespace Includes\Core;

class Router {
    private $routes = [];

    public function addRoute($method, $path, $handler) {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch($method, $path) {
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            if (is_callable($handler)) {
                return $handler();
            }
            if (is_array($handler) && count($handler) === 2) {
                $className = $handler[0];
                $methodName = $handler[1];
                if (class_exists($className) && method_exists($className, $methodName)) {
                    $instance = new $className();
                    return $instance->$methodName();
                }
            }
        }
        throw new \Exception("Route not found: $method $path");
    }
}
