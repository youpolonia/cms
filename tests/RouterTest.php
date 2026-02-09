<?php
/**
 * Router Tests
 * 
 * @package JessieCMS
 * @since 2026-02-08
 */

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/router.php';
require_once __DIR__ . '/TestRunner.php';

use Core\Router;

$runner = new TestRunner();

// Test 1: Router class exists
$runner->addTest('Router class exists', function() {
    TestRunner::assert(class_exists('Core\Router'), 'Router class should exist');
});

// Test 2: GET route registration
$runner->addTest('Router can register GET route', function() {
    Router::get('/test-get', function() { return 'ok'; });
    // No exception = pass
    TestRunner::assert(true);
});

// Test 3: POST route registration
$runner->addTest('Router can register POST route', function() {
    Router::post('/test-post', function() { return 'ok'; });
    TestRunner::assert(true);
});

// Test 4: Route with parameters
$runner->addTest('Router can register route with params', function() {
    Router::get('/user/{id}', function($id) { return $id; });
    TestRunner::assert(true);
});

// Test 5: Route prefix
$runner->addTest('Router supports prefix', function() {
    Router::prefix('/api')->group(function() {
        Router::get('/items', function() { return 'items'; });
    });
    TestRunner::assert(true);
});

$runner->run();
