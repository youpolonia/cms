<?php
require_once __DIR__ . '/../statustransitionhandler.php';

class StatusTransitionTest {
    private static $testContentId = 'test_content_123';
    private static $testUserId = 'user_456';

    public static function runAllTests() {
        self::testValidDraftToPublishedTransition();
        self::testValidPublishedToArchivedTransition();
        self::testInvalidDirectDraftToArchivedTransition();
        self::testPublishedToArchivedWithoutUserId();
        self::testTransitionLogging();
    }

    private static function testValidDraftToPublishedTransition() {
        StatusTransitionHandler::registerTransition(
            'draft',
            'published',
            function(array $context) {
                if ($context['content_id'] !== self::$testContentId) {
                    throw new Exception("Invalid content ID");
                }
                return ['status' => 'success'];
            }
        );

        try {
            $result = StatusTransitionHandler::executeTransition(
                'draft',
                'published',
                ['content_id' => self::$testContentId]
            );
            
            if ($result['status'] !== 'success') {
                echo "FAIL: testValidDraftToPublishedTransition - Expected success status\n";
                return false;
            }
            echo "PASS: testValidDraftToPublishedTransition\n";
            return true;
        } catch (Exception $e) {
            echo "FAIL: testValidDraftToPublishedTransition - " . $e->getMessage() . "\n";
            return false;
        }
    }

    private static function testValidPublishedToArchivedTransition() {
        StatusTransitionHandler::registerTransition(
            'published',
            'archived',
            function(array $context) {
                if (empty($context['user_id'])) {
                    throw new Exception("User ID required");
                }
                return ['status' => 'success'];
            }
        );

        try {
            $result = StatusTransitionHandler::executeTransition(
                'published',
                'archived',
                [
                    'content_id' => self::$testContentId,
                    'user_id' => self::$testUserId
                ]
            );
            
            if ($result['status'] !== 'success') {
                echo "FAIL: testValidPublishedToArchivedTransition - Expected success status\n";
                return false;
            }
            echo "PASS: testValidPublishedToArchivedTransition\n";
            return true;
        } catch (Exception $e) {
            echo "FAIL: testValidPublishedToArchivedTransition - " . $e->getMessage() . "\n";
            return false;
        }
    }

    private static function testInvalidDirectDraftToArchivedTransition() {
        try {
            StatusTransitionHandler::executeTransition(
                'draft',
                'archived',
                ['content_id' => self::$testContentId]
            );
            echo "FAIL: testInvalidDirectDraftToArchivedTransition - Expected exception\n";
            return false;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Invalid transition') === false) {
                echo "FAIL: testInvalidDirectDraftToArchivedTransition - Wrong exception message\n";
                return false;
            }
            echo "PASS: testInvalidDirectDraftToArchivedTransition\n";
            return true;
        }
    }

    private static function testPublishedToArchivedWithoutUserId() {
        try {
            StatusTransitionHandler::executeTransition(
                'published',
                'archived',
                ['content_id' => self::$testContentId]
            );
            echo "FAIL: testPublishedToArchivedWithoutUserId - Expected exception\n";
            return false;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'User ID required') === false) {
                echo "FAIL: testPublishedToArchivedWithoutUserId - Wrong exception message\n";
                return false;
            }
            echo "PASS: testPublishedToArchivedWithoutUserId\n";
            return true;
        }
    }

    private static function testTransitionLogging() {
        $logged = false;
        StatusTransitionHandler::setLogger(function(array $log) use (&$logged) {
            $logged = true;
            if ($log['from_state'] !== 'draft' || $log['to_state'] !== 'published') {
                echo "FAIL: testTransitionLogging - Incorrect log state\n";
                return false;
            }
            if (!isset($log['context']['content_id'])) {
                echo "FAIL: testTransitionLogging - Missing context in log\n";
                return false;
            }
        });

        try {
            StatusTransitionHandler::executeTransition(
                'draft',
                'published',
                ['content_id' => self::$testContentId]
            );
            
            if (!$logged) {
                echo "FAIL: testTransitionLogging - No log entry created\n";
                return false;
            }
            echo "PASS: testTransitionLogging\n";
            return true;
        } catch (Exception $e) {
            echo "FAIL: testTransitionLogging - " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run tests
StatusTransitionTest::runAllTests();
