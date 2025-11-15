<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
/**
 * Content API Endpoint Tests
 *
 * Tests for content management endpoints including:
 * - Content creation
 * - Content retrieval
 * - Content updates
 * - Content deletion
 * - Version control
 * - State transitions
 * - Tenant isolation verification
 */

class ContentTest {
    // Test data constants
    const TEST_TENANT_ID = 'test_tenant_1';
    const INVALID_TENANT_ID = 'invalid_tenant';
    const TEST_CONTENT = [
        'title' => 'Test Content',
        'content' => 'This is test content',
        'status' => 'draft'
    ];

    // CRUD Operation Tests
    public static function testCreateContent() {
        $result = ContentController::create(self::TEST_TENANT_ID, self::TEST_CONTENT);
        
        // Verify response structure
        if (!isset($result['status']) || $result['status'] !== 'success') {
            throw new Exception('Create content failed - invalid status');
        }
        
        if (!isset($result['data']['id'])) {
            throw new Exception('Create content failed - missing content ID');
        }
        
        return $result['data']['id'];
    }

    public static function testReadContent($contentId) {
        // Test single content read
        $singleResult = ContentController::read(self::TEST_TENANT_ID, $contentId);
        if (!isset($singleResult['status']) || $singleResult['status'] !== 'success') {
            throw new Exception('Read single content failed');
        }
        
        // Test content list read
        $listResult = ContentController::read(self::TEST_TENANT_ID);
        if (!isset($listResult['status']) || $listResult['status'] !== 'success') {
            throw new Exception('Read content list failed');
        }
        
        if (!isset($listResult['data']) || !is_array($listResult['data'])) {
            throw new Exception('Invalid content list format');
        }
    }

    public static function testUpdateContent($contentId) {
        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published'
        ];
        
        $result = ContentController::update(self::TEST_TENANT_ID, $contentId, $updateData);
        
        if (!isset($result['status'])) {
            throw new Exception('Update content failed - missing status');
        }
        
        if ($result['status'] !== 'success') {
            throw new Exception('Update content failed - status not success');
        }
    }

    public static function testDeleteContent($contentId) {
        $result = ContentController::delete(self::TEST_TENANT_ID, $contentId);
        
        if (!isset($result['status'])) {
            throw new Exception('Delete content failed - missing status');
        }
        
        if ($result['status'] !== 'success') {
            throw new Exception('Delete content failed - status not success');
        }
    }

    // Version Control Tests
    public static function testVersionControl($contentId) {
        // Create initial version
        $version1Result = ContentController::createVersion(self::TEST_TENANT_ID, $contentId);
        if (!isset($version1Result['data']['version_id'])) {
            throw new Exception('Version 1 creation failed');
        }
        $version1Id = $version1Result['data']['version_id'];
        
        // Update content to create version difference
        $updateData = ['content' => 'Updated content for version comparison'];
        ContentController::update(self::TEST_TENANT_ID, $contentId, $updateData);
        
        // Create second version
        $version2Result = ContentController::createVersion(self::TEST_TENANT_ID, $contentId);
        if (!isset($version2Result['data']['version_id'])) {
            throw new Exception('Version 2 creation failed');
        }
        $version2Id = $version2Result['data']['version_id'];
        
        // Test version comparison with actual differences
        $compareResult = ContentController::compareVersions(
            self::TEST_TENANT_ID,
            $version1Id,
            $version2Id
        );
        if (!isset($compareResult['status']) || $compareResult['status'] !== 'success') {
            throw new Exception('Version comparison with differences failed');
        }
        
        if (!isset($compareResult['data']['diffs']) || empty($compareResult['data']['diffs'])) {
            throw new Exception('Version comparison should return diffs');
        }
        
        // Test comparing same versions
        $sameCompareResult = ContentController::compareVersions(
            self::TEST_TENANT_ID,
            $version1Id,
            $version1Id
        );
        if (!isset($sameCompareResult['status'])) {
            throw new Exception('Same version comparison failed');
        }
        
        // Rollback version
        $rollbackResult = ContentController::rollbackVersion(
            self::TEST_TENANT_ID,
            $version1Id
        );
        if (!isset($rollbackResult['status'])) {
            throw new Exception('Version rollback failed');
        }
    }

    // State Transition Tests
    public static function testStateTransitions($contentId) {
        $validTransitions = [
            ['from' => 'draft', 'to' => 'published'],
            ['from' => 'published', 'to' => 'archived'],
            ['from' => 'archived', 'to' => 'draft']
        ];
        
        // Test valid transitions
        foreach ($validTransitions as $transition) {
            // Set initial state
            ContentController::changeState(
                self::TEST_TENANT_ID,
                $contentId,
                $transition['from']
            );
            
            // Test transition
            $result = ContentController::changeState(
                self::TEST_TENANT_ID,
                $contentId,
                $transition['to']
            );
            
            if (!isset($result['status']) || $result['status'] !== 'success') {
                throw new Exception("Valid transition {$transition['from']}->{$transition['to']} failed");
            }
        }
        
        // Test invalid transitions
        $invalidTransitions = [
            ['from' => 'published', 'to' => 'draft'],
            ['from' => 'archived', 'to' => 'published']
        ];
        
        foreach ($invalidTransitions as $transition) {
            // Set initial state
            ContentController::changeState(
                self::TEST_TENANT_ID,
                $contentId,
                $transition['from']
            );
            
            // Test invalid transition
            try {
                ContentController::changeState(
                    self::TEST_TENANT_ID,
                    $contentId,
                    $transition['to']
                );
                throw new Exception("Invalid transition {$transition['from']}->{$transition['to']} should have failed");
            } catch (Exception $e) {
                if ($e->getCode() !== 400) {
                    throw new Exception("Invalid transition returned wrong error code");
                }
            }
        }
    }

    // Edge Case Tests
    public static function testEdgeCases() {
        // Test invalid content ID
        try {
            ContentController::read(self::TEST_TENANT_ID, 'invalid_id');
            throw new Exception('Invalid content ID test failed - no exception thrown');
        } catch (Exception $e) {
            if ($e->getCode() !== 404) {
                throw new Exception('Invalid content ID test failed - wrong error code');
            }
        }
        
        // Test unauthorized tenant access
        try {
            ContentController::read(self::INVALID_TENANT_ID, 'any_id');
            throw new Exception('Unauthorized access test failed - no exception thrown');
        } catch (Exception $e) {
            if ($e->getCode() !== 403) {
                throw new Exception('Unauthorized access test failed - wrong error code');
            }
        }
        
        // Test max content size
        $maxSize = 10000; // 10KB
        $largeContent = str_repeat('a', $maxSize + 1);
        try {
            ContentController::create(self::TEST_TENANT_ID, [
                'title' => 'Oversized Content',
                'content' => $largeContent,
                'status' => 'draft'
            ]);
            throw new Exception('Max content size test failed - no exception thrown');
        } catch (Exception $e) {
            if ($e->getCode() !== 413) {
                throw new Exception('Max content size test failed - wrong error code');
            }
        }
        
        // Test malformed content data
        try {
            ContentController::create(self::TEST_TENANT_ID, [
                'invalid' => 'data'
            ]);
            throw new Exception('Malformed data test failed - no exception thrown');
        } catch (Exception $e) {
            if ($e->getCode() !== 400) {
                throw new Exception('Malformed data test failed - wrong error code');
            }
        }
    }

    // Main test runner
    public static function testConcurrentUpdates() {
        $contentId = self::testCreateContent();
        
        // Simulate concurrent updates
        $update1 = ['title' => 'Update 1'];
        $update2 = ['title' => 'Update 2'];
        
        // Start both updates
        $result1 = ContentController::update(self::TEST_TENANT_ID, $contentId, $update1);
        $result2 = ContentController::update(self::TEST_TENANT_ID, $contentId, $update2);
        
        // Verify at least one update was rejected
        if ($result1['status'] === 'success' && $result2['status'] === 'success') {
            throw new Exception('Concurrent update test failed - both updates succeeded');
        }
        
        // Cleanup
        self::testDeleteContent($contentId);
    }

    public static function runAllTests() {
        echo "Running Content API Tests...\n";
        
        try {
            // CRUD tests
            echo "Testing content creation...\n";
            $contentId = self::testCreateContent();
            
            echo "Testing content retrieval...\n";
            self::testReadContent($contentId);
            
            echo "Testing content update...\n";
            self::testUpdateContent($contentId);
            
            // Version control tests
            echo "Testing version control...\n";
            self::testVersionControl($contentId);
            
            // State transition tests
            echo "Testing state transitions...\n";
            self::testStateTransitions($contentId);
            
            // Edge case tests
            echo "Testing edge cases...\n";
            self::testEdgeCases();
            
            // Concurrent update test
            echo "Testing concurrent updates...\n";
            self::testConcurrentUpdates();
            
            // Cleanup
            echo "Testing content deletion...\n";
            self::testDeleteContent($contentId);
            
            echo "All tests passed successfully!\n";
            return true;
        } catch (Exception $e) {
            echo "Test failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run tests when executed directly
if (php_sapi_name() === 'cli') {
    ContentTest::runAllTests();
}
