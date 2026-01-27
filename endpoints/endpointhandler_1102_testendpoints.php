<?php
/**
 * Phase 11 - Testing Endpoints Migration
 * Creates web-accessible testing endpoints for version tracking system
 */
class Migration_1102_AddTestingEndpoints {
    /**
     * Test endpoint for version tracking
     * @param PDO $pdo Database connection
     */
    public static function testVersionTracking($pdo) {
        try {
            // Test version table exists
            $stmt = $pdo->query("SELECT 1 FROM content_versions LIMIT 1");
            return ['status' => 'success', 'message' => 'Version tracking system ready'];
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Test endpoint for federation system
     * @param PDO $pdo Database connection
     */
    public static function testFederation($pdo) {
        try {
            // Test federation table exists
            $stmt = $pdo->query("SELECT 1 FROM federation_log LIMIT 1");
            return ['status' => 'success', 'message' => 'Federation system ready'];
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Cleanup test data
     * @param PDO $pdo Database connection
     */
    public static function cleanupTests($pdo) {
        try {
            $pdo->exec("DELETE FROM content_versions WHERE created_by = 'test'");
            $pdo->exec("DELETE FROM federation_log WHERE source_tenant = 'test'");
            return ['status' => 'success', 'message' => 'Test data cleaned'];
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
