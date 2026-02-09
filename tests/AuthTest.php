<?php
/**
 * Auth Tests
 * Tests for core/auth.php authentication
 */

require_once __DIR__ . '/TestRunner.php';

// Need config + database
if (!defined('CMS_ROOT')) define('CMS_ROOT', realpath(__DIR__ . '/..'));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/auth.php';

$runner = new TestRunner();

$runner->addTest('authenticateAdmin function exists', function () {
    TestRunner::assert(function_exists('authenticateAdmin'), 'authenticateAdmin() should exist');
});

$runner->addTest('authenticateAdmin rejects invalid user', function () {
    $result = authenticateAdmin('nonexistent_user_xyz', 'password123');
    TestRunner::assert(is_array($result), 'Should return array');
    TestRunner::assertEquals(false, $result[0], 'Should return [false, ...] for invalid user');
});

$runner->addTest('authenticateAdmin rejects empty credentials', function () {
    $result = authenticateAdmin('', '');
    TestRunner::assert(is_array($result), 'Should return array');
    TestRunner::assertEquals(false, $result[0], 'Should return false for empty credentials');
});

$runner->addTest('authenticateAdmin rejects invalid table', function () {
    $result = authenticateAdmin('admin', 'pass', 'malicious_table');
    TestRunner::assertEquals(false, $result[0], 'Should reject non-allowed table');
});

$runner->run();
