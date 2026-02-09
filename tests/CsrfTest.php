<?php
/**
 * CSRF Tests
 * 
 * @package JessieCMS
 * @since 2026-02-08
 */

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once __DIR__ . '/TestRunner.php';

$runner = new TestRunner();

// Start session for tests (suppress warnings when output already started)
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Test 1: csrf_boot creates token
$runner->addTest('csrf_boot creates token', function() {
    csrf_boot('test');
    TestRunner::assertNotEmpty($_SESSION['csrf_token']);
});

// Test 2: csrf_token returns string
$runner->addTest('csrf_token returns string', function() {
    $token = csrf_token();
    TestRunner::assert(is_string($token), 'Token should be string');
    TestRunner::assert(strlen($token) > 20, 'Token should be long enough');
});

// Test 3: csrf_field returns HTML
$runner->addTest('csrf_field returns hidden input', function() {
    ob_start();
    csrf_field();
    $html = ob_get_clean();
    TestRunner::assert(strpos($html, 'type="hidden"') !== false);
    TestRunner::assert(strpos($html, 'csrf_token') !== false);
});

// Test 4: csrf_validate with valid token
$runner->addTest('csrf_validate accepts valid token', function() {
    $token = $_SESSION['csrf_token'];
    $_POST['csrf_token'] = $token;
    $valid = csrf_validate($_POST['csrf_token']);
    TestRunner::assert($valid, 'Valid token should pass');
});

// Test 5: csrf_validate rejects invalid token
$runner->addTest('csrf_validate rejects invalid token', function() {
    $_POST['csrf_token'] = 'invalid_token_12345';
    $valid = csrf_validate($_POST['csrf_token']);
    TestRunner::assert(!$valid, 'Invalid token should fail');
});

$runner->run();

// Cleanup
unset($_POST['csrf_token']);
