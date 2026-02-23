<?php
/**
 * Rate Limiting Tests
 * Tests login_attempts table and rate limiting logic
 */

require_once __DIR__ . '/TestRunner.php';
require_once __DIR__ . '/../core/database.php';

$runner = new TestRunner();

$runner->addTest('login_attempts table exists', function () {
    $pdo = \core\Database::connection();
    $stmt = $pdo->query("SHOW TABLES LIKE 'login_attempts'");
    TestRunner::assert($stmt->rowCount() > 0, 'login_attempts table should exist');
});

$runner->addTest('can insert and read login attempt', function () {
    $pdo = \core\Database::connection();
    $testIp = '192.168.99.99';

    // Cleanup
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$testIp]);

    $stmt = $pdo->prepare(
        "INSERT INTO login_attempts (username, ip_address, user_agent, success, failure_reason, attempted_at)
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $stmt->execute(['test_user', $testIp, 'TestRunner/1.0', 0, 'test']);
    $id = (int)$pdo->lastInsertId();

    TestRunner::assert($id > 0, 'Should get a positive insert ID');

    // Cleanup
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$testIp]);
});

$runner->addTest('rate limit counts failed attempts correctly', function () {
    $pdo = \core\Database::connection();
    $testIp = '10.255.255.255';

    // Cleanup
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$testIp]);

    // Insert 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $pdo->prepare(
            "INSERT INTO login_attempts (username, ip_address, user_agent, success, failure_reason, attempted_at)
             VALUES ('attacker', ?, 'bot', 0, 'invalid_credentials', NOW())"
        )->execute([$testIp]);
    }

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE ip_address = ? AND success = 0
         AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
    );
    $stmt->execute([$testIp]);
    $count = (int)$stmt->fetchColumn();

    TestRunner::assertEquals(5, $count, 'Should count 5 failed attempts');

    // Cleanup
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$testIp]);
});

$runner->addTest('successful logins not counted as failures', function () {
    $pdo = \core\Database::connection();
    $testIp = '10.255.255.254';

    // Cleanup
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$testIp]);

    // Insert 3 failed + 1 success
    for ($i = 0; $i < 3; $i++) {
        $pdo->prepare(
            "INSERT INTO login_attempts (username, ip_address, user_agent, success, failure_reason, attempted_at)
             VALUES ('user', ?, 'browser', 0, 'invalid_credentials', NOW())"
        )->execute([$testIp]);
    }
    $pdo->prepare(
        "INSERT INTO login_attempts (username, ip_address, user_agent, success, failure_reason, attempted_at)
         VALUES ('user', ?, 'browser', 1, '', NOW())"
    )->execute([$testIp]);

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE ip_address = ? AND success = 0
         AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
    );
    $stmt->execute([$testIp]);
    $count = (int)$stmt->fetchColumn();

    TestRunner::assertEquals(3, $count, 'Should count only 3 failures, not the success');

    // Cleanup
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$testIp]);
});

$runner->run();
