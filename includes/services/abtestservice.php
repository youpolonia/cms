<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

namespace Includes\Services;

use Includes\Database\DatabaseConnection;
use Includes\Session\SessionManager;

/**
 * ABTestService provides functionality for A/B testing
 */
class ABTestService {
    /**
     * Get the variant for a user in a specific test
     *
     * @param string $testId Test identifier
     * @param int|null $userId User ID (null for anonymous users)
     * @return string Variant identifier (A or B)
     */
    public static function getVariant(string $testId, ?int $userId = null): string {
        $session = SessionManager::getInstance();
        
        // Use user ID if available, otherwise use session ID
        $userId = $userId ?? 0;
        $sessionId = $session->getId();
        
        // Check if user already has an assigned variant
        $existingVariant = self::getUserVariant($testId, $userId, $sessionId);
        if ($existingVariant) {
            return $existingVariant;
        }
        
        // Assign a variant (50/50 split)
        $variant = self::assignVariant($testId, $userId, $sessionId);
        return $variant;
    }
    
    /**
     * Get existing variant for a user
     *
     * @param string $testId Test identifier
     * @param int $userId User ID
     * @param string $sessionId Session ID
     * @return string|null Variant or null if not assigned
     */
    private static function getUserVariant(string $testId, int $userId, string $sessionId): ?string {
        $query = "
            SELECT variant
            FROM ab_test_assignments
            WHERE test_id = ? AND (user_id = ? OR session_id = ?)
            LIMIT 1
        ";
        
        $result = DatabaseConnection::fetchOne($query, [$testId, $userId, $sessionId]);
        return $result ? $result['variant'] : null;
    }
    
    /**
     * Assign a variant to a user
     *
     * @param string $testId Test identifier
     * @param int $userId User ID
     * @param string $sessionId Session ID
     * @return string Assigned variant
     */
    private static function assignVariant(string $testId, int $userId, string $sessionId): string {
        // Check if test exists and is active
        if (!self::isTestActive($testId)) {
            // Default to variant A if test doesn't exist or is inactive
            return 'A';
        }
        
        // Get test configuration
        $testConfig = self::getTestConfig($testId);
        
        // Determine variant based on distribution
        $variant = self::determineVariant($testConfig['distribution'] ?? ['A' => 50, 'B' => 50]);
        
        // Store assignment
        self::storeVariantAssignment($testId, $userId, $sessionId, $variant);
        
        return $variant;
    }
    
    /**
     * Check if a test is active
     *
     * @param string $testId Test identifier
     * @return bool True if test is active
     */
    public static function isTestActive(string $testId): bool {
        $query = "
            SELECT active
            FROM ab_tests
            WHERE test_id = ?
        ";
        
        $result = DatabaseConnection::fetchOne($query, [$testId]);
        return $result && $result['active'] == 1;
    }
    
    /**
     * Get test configuration
     *
     * @param string $testId Test identifier
     * @return array Test configuration
     */
    public static function getTestConfig(string $testId): array {
        $query = "
            SELECT *
            FROM ab_tests
            WHERE test_id = ?
        ";
        
        $result = DatabaseConnection::fetchOne($query, [$testId]);
        
        if (!$result) {
            return [
                'test_id' => $testId,
                'name' => 'Unknown Test',
                'description' => '',
                'distribution' => ['A' => 50, 'B' => 50],
                'active' => false,
                'start_date' => null,
                'end_date' => null
            ];
        }
        
        // Parse distribution from JSON
        $distribution = json_decode($result['distribution'], true) ?? ['A' => 50, 'B' => 50];
        
        return [
            'test_id' => $result['test_id'],
            'name' => $result['name'],
            'description' => $result['description'],
            'distribution' => $distribution,
            'active' => (bool)$result['active'],
            'start_date' => $result['start_date'],
            'end_date' => $result['end_date']
        ];
    }
    
    /**
     * Determine variant based on distribution
     *
     * @param array $distribution Distribution configuration (e.g. ['A' => 70, 'B' => 30])
     * @return string Selected variant
     */
    private static function determineVariant(array $distribution): string {
        $total = array_sum($distribution);
        $random = mt_rand(1, $total);
        
        $cumulative = 0;
        foreach ($distribution as $variant => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $variant;
            }
        }
        
        // Fallback to first variant
        return array_key_first($distribution);
    }
    
    /**
     * Store variant assignment
     *
     * @param string $testId Test identifier
     * @param int $userId User ID
     * @param string $sessionId Session ID
     * @param string $variant Assigned variant
     * @return bool Success status
     */
    private static function storeVariantAssignment(string $testId, int $userId, string $sessionId, string $variant): bool {
        $query = "
            INSERT INTO ab_test_assignments
            (test_id, user_id, session_id, variant, assigned_at)
            VALUES (?, ?, ?, ?, NOW())
        ";
        
        return DatabaseConnection::execute($query, [$testId, $userId, $sessionId, $variant]);
    }
    
    /**
     * Track a conversion for a test
     *
     * @param string $testId Test identifier
     * @param string $conversionType Type of conversion
     * @param array $metadata Additional metadata
     * @return bool Success status
     */
    public static function trackConversion(string $testId, string $conversionType, array $metadata = []): bool {
        $session = SessionManager::getInstance();
        $userId = $session->get('user_id') ?? 0;
        $sessionId = $session->getId();
        
        // Get the user's variant
        $variant = self::getUserVariant($testId, $userId, $sessionId);
        
        if (!$variant) {
            // User not in test
            return false;
        }
        
        $query = "
            INSERT INTO ab_test_conversions
            (test_id, user_id, session_id, variant, conversion_type, metadata, converted_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";
        
        return DatabaseConnection::execute($query, [
            $testId,
            $userId,
            $sessionId,
            $variant,
            $conversionType,
            json_encode($metadata)
        ]);
    }
    
    /**
     * Get test results
     *
     * @param string $testId Test identifier
     * @return array Test results
     */
    public static function getTestResults(string $testId): array {
        // Get test configuration
        $testConfig = self::getTestConfig($testId);
        
        // Get assignments count per variant
        $assignmentsQuery = "
            SELECT 
                variant,
                COUNT(*) as assignments
            FROM 
                ab_test_assignments
            WHERE 
                test_id = ?
            GROUP BY 
                variant
        ";
        
        $assignments = DatabaseConnection::fetchAll($assignmentsQuery, [$testId]);
        $assignmentsByVariant = [];
        foreach ($assignments as $row) {
            $assignmentsByVariant[$row['variant']] = (int)$row['assignments'];
        }
        
        // Get conversions count per variant and type
        $conversionsQuery = "
            SELECT 
                variant,
                conversion_type,
                COUNT(*) as conversions
            FROM 
                ab_test_conversions
            WHERE 
                test_id = ?
            GROUP BY 
                variant, conversion_type
        ";
        
        $conversions = DatabaseConnection::fetchAll($conversionsQuery, [$testId]);
        $conversionsByVariant = [];
        foreach ($conversions as $row) {
            if (!isset($conversionsByVariant[$row['variant']])) {
                $conversionsByVariant[$row['variant']] = [];
            }
            $conversionsByVariant[$row['variant']][$row['conversion_type']] = (int)$row['conversions'];
        }
        
        // Calculate conversion rates
        $results = [
            'test_id' => $testId,
            'name' => $testConfig['name'],
            'description' => $testConfig['description'],
            'active' => $testConfig['active'],
            'start_date' => $testConfig['start_date'],
            'end_date' => $testConfig['end_date'],
            'variants' => []
        ];
        
        foreach ($testConfig['distribution'] as $variant => $weight) {
            $assignments = $assignmentsByVariant[$variant] ?? 0;
            $variantConversions = $conversionsByVariant[$variant] ?? [];
            
            $conversionRates = [];
            foreach ($variantConversions as $type => $count) {
                $conversionRates[$type] = $assignments > 0 ? ($count / $assignments) * 100 : 0;
            }
            
            $results['variants'][$variant] = [
                'weight' => $weight,
                'assignments' => $assignments,
                'conversions' => $variantConversions,
                'conversion_rates' => $conversionRates
            ];
        }
        
        return $results;
    }
    
    /**
     * Create a new A/B test
     *
     * @param string $testId Test identifier
     * @param string $name Test name
     * @param string $description Test description
     * @param array $distribution Variant distribution
     * @param string|null $startDate Start date (null for immediate)
     * @param string|null $endDate End date (null for no end)
     * @return bool Success status
     */
    public static function createTest(
        string $testId,
        string $name,
        string $description,
        array $distribution = ['A' => 50, 'B' => 50],
        ?string $startDate = null,
        ?string $endDate = null
    ): bool {
        $query = "
            INSERT INTO ab_tests
            (test_id, name, description, distribution, active, start_date, end_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $active = $startDate ? (strtotime($startDate) <= time()) : true;
        
        return DatabaseConnection::execute($query, [
            $testId,
            $name,
            $description,
            json_encode($distribution),
            $active ? 1 : 0,
            $startDate,
            $endDate
        ]);
    }
    
    /**
     * Update an existing A/B test
     *
     * @param string $testId Test identifier
     * @param array $data Test data to update
     * @return bool Success status
     */
    public static function updateTest(string $testId, array $data): bool {
        $allowedFields = ['name', 'description', 'distribution', 'active', 'start_date', 'end_date'];
        $updates = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                if ($field === 'distribution') {
                    $value = json_encode($value);
                } elseif ($field === 'active') {
                    $value = $value ? 1 : 0;
                }
                
                $updates[] = "$field = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $query = "
            UPDATE ab_tests
            SET " . implode(', ', $updates) . ", updated_at = NOW()
            WHERE test_id = ?
        ";
        
        $params[] = $testId;
        
        return DatabaseConnection::execute($query, $params);
    }
}
