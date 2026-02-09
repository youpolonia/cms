<?php
/**
 * Database Tests
 * 
 * @package JessieCMS
 * @since 2026-02-08
 */

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';
require_once __DIR__ . '/TestRunner.php';

use core\Database;

$runner = new TestRunner();

// Test 1: Connection
$runner->addTest('Database::connection returns PDO', function() {
    $db = Database::connection();
    TestRunner::assertInstanceOf(PDO::class, $db);
});

// Test 2: Singleton pattern
$runner->addTest('Database uses singleton pattern', function() {
    $db1 = Database::connection();
    $db2 = Database::connection();
    TestRunner::assert($db1 === $db2, 'Should return same instance');
});

// Test 3: Query execution
$runner->addTest('Database can execute query', function() {
    $db = Database::connection();
    $stmt = $db->query('SELECT 1 as test');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    TestRunner::assertEquals(1, (int)$row['test']);
});

// Test 4: Prepared statements
$runner->addTest('Database prepared statements work', function() {
    $db = Database::connection();
    $stmt = $db->prepare('SELECT ? as value');
    $stmt->execute(['hello']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    TestRunner::assertEquals('hello', $row['value']);
});

// Test 5: Table exists
$runner->addTest('users table exists', function() {
    $db = Database::connection();
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    $result = $stmt->fetch();
    TestRunner::assertNotEmpty($result, 'users table should exist');
});

// Test 6: Transaction support
$runner->addTest('Database supports transactions', function() {
    $db = Database::connection();
    $db->beginTransaction();
    $inTransaction = $db->inTransaction();
    $db->rollBack();
    TestRunner::assert($inTransaction, 'Should be in transaction');
});

$runner->run();
