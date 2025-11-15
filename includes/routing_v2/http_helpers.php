<?php
/**
 * HTTP Method Helpers for Routing
 *
 * Provides convenient functions for defining routes with different HTTP methods
 * while supporting middleware and route naming.
 */

require_once __DIR__ . '/../core/router.php';

if (!function_exists('get')) {
    /**
     * Register a GET route
     *
     * @param string $uri
     * @param callable|array $action
     * @param array $middleware
     * @return \CMS\Routing\Route
     */
    function get(string $uri, $action, array $middleware = []) {
        return \CMS\Routing\Router::getInstance()
            ->addRoute('GET', $uri, $action, $middleware);
    }
}

if (!function_exists('post')) {
    /**
     * Register a POST route
     *
     * @param string $uri
     * @param callable|array $action
     * @param array $middleware
     * @return \CMS\Routing\Route
     */
    function post(string $uri, $action, array $middleware = []) {
        return \CMS\Routing\Router::getInstance()
            ->addRoute('POST', $uri, $action, $middleware);
    }
}

if (!function_exists('put')) {
    /**
     * Register a PUT route
     *
     * @param string $uri
     * @param callable|array $action
     * @param array $middleware
     * @return \CMS\Routing\Route
     */
    function put(string $uri, $action, array $middleware = []) {
        return \CMS\Routing\Router::getInstance()
            ->addRoute('PUT', $uri, $action, $middleware);
    }
}

if (!function_exists('patch')) {
    /**
     * Register a PATCH route
     *
     * @param string $uri
     * @param callable|array $action
     * @param array $middleware
     * @return \CMS\Routing\Route
     */
    function patch(string $uri, $action, array $middleware = []) {
        return \CMS\Routing\Router::getInstance()
            ->addRoute('PATCH', $uri, $action, $middleware);
    }
}

if (!function_exists('delete')) {
    /**
     * Register a DELETE route
     *
     * @param string $uri
     * @param callable|array $action
     * @param array $middleware
     * @return \CMS\Routing\Route
     */
    function delete(string $uri, $action, array $middleware = []) {
        return \CMS\Routing\Router::getInstance()
            ->addRoute('DELETE', $uri, $action, $middleware);
    }
}

if (!function_exists('options')) {
    /**
     * Register an OPTIONS route
     *
     * @param string $uri
     * @param callable|array $action
     * @param array $middleware
     * @return \CMS\Routing\Route
     */
    function options(string $uri, $action, array $middleware = []) {
        return \CMS\Routing\Router::getInstance()
            ->addRoute('OPTIONS', $uri, $action, $middleware);
    }
}

if (!function_exists('any')) {
    /**
     * Register a route that matches any HTTP method
     *
     * @param string $uri
     * @param callable|array $action
     * @param array $middleware
     * @return \CMS\Routing\Route
     */
    function any(string $uri, $action, array $middleware = []) {
        return \CMS\Routing\Router::getInstance()
            ->addRoute('ANY', $uri, $action, $middleware);
    }
}
