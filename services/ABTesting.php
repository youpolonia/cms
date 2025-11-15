<?php
declare(strict_types=1);
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

namespace CMS\Services;

use CMS\Includes\Models\AnalyticsModel;
use CMS\Includes\Database\DatabaseConnection;

class ABTesting {
    private DatabaseConnection $db;
    private AnalyticsModel $analyticsModel;
    private array $config;

    public function __construct(
        DatabaseConnection $db,
        AnalyticsModel $analyticsModel,
        array $config = []
    ) {
        $this->db = $db;
        $this->analyticsModel = $analyticsModel;
        $this->config = $config;
    }

    /**
     * Create a new A/B test
     */
    public function createTest(
        string $name,
        array $variations,
        string $target,
        ?string $segment = null,
        ?array $metrics = null
    ): int {
        $testId = $this->db->insert('ab_tests', [
            'name' => $name,
            'target' => $target,
            'segment' => $segment,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        foreach ($variations as $variation) {
            $this->db->insert('ab_test_variations', [
                'test_id' => $testId,
                'name' => $variation['name'],
                'identifier' => $variation['identifier'],
                'weight' => $variation['weight'] ?? 1.0
            ]);
        }

        if ($metrics) {
            foreach ($metrics as $metric) {
                $this->db->insert('ab_test_metrics', [
                    'test_id' => $testId,
                    'name' => $metric['name'],
                    'event_name' => $metric['event_name'],
                    'aggregation' => $metric['aggregation'] ?? 'count'
                ]);
            }
        }

        return $testId;
    }

    /**
     * Get variation for a user/test combination
     */
    public function getVariation(int $userId, int $testId): string {
        // Check if user already has a variation assigned
        $existing = $this->db->fetchOne(
            "SELECT variation FROM ab_test_assignments 
             WHERE user_id = ? AND test_id = ?",
            [$userId, $testId]
        );

        if ($existing) {
            return $existing['variation'];
        }

        // Get all variations and their weights
        $variations = $this->db->fetchAll(
            "SELECT identifier, weight FROM ab_test_variations 
             WHERE test_id = ? ORDER BY id",
            [$testId]
        );

        // Assign variation based on weights
        $totalWeight = array_sum(array_column($variations, 'weight'));
        $random = mt_rand() / mt_getrandmax() * $totalWeight;
        $cumulative = 0;

        foreach ($variations as $variation) {
            $cumulative += $variation['weight'];
            if ($random <= $cumulative) {
                $this->recordAssignment($userId, $testId, $variation['identifier']);
                return $variation['identifier'];
            }
        }

        return 'control'; // Fallback
    }

    private function recordAssignment(int $userId, int $testId, string $variation): void {
        $this->db->insert('ab_test_assignments', [
            'user_id' => $userId,
            'test_id' => $testId,
            'variation' => $variation,
            'assigned_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if test results are statistically significant
     */
    public function isSignificant(int $testId, float $confidenceLevel = 0.95): bool {
        $metrics = $this->db->fetchAll(
            "SELECT name, aggregation FROM ab_test_metrics WHERE test_id = ?",
            [$testId]
        );

        foreach ($metrics as $metric) {
            $results = $this->analyticsModel->getTestResults($testId, $metric['name']);
            if (!$this->checkMetricSignificance($results, $confidenceLevel)) {
                return false;
            }
        }

        return true;
    }

    private function checkMetricSignificance(array $results, float $confidenceLevel): bool {
        // Implement basic chi-squared test for proportions
        // This is a simplified version - consider using a proper stats library
        $control = $results['control'] ?? [];
        $variation = $results['variation'] ?? [];

        if (empty($control) || empty($variation)) {
            return false;
        }

        $controlSuccess = $control['success'] ?? 0;
        $controlTotal = $control['total'] ?? 0;
        $variationSuccess = $variation['success'] ?? 0;
        $variationTotal = $variation['total'] ?? 0;

        if ($controlTotal === 0 || $variationTotal === 0) {
            return false;
        }

        $pControl = $controlSuccess / $controlTotal;
        $pVariation = $variationSuccess / $variationTotal;

        // Simple z-test for proportions
        $pPooled = ($controlSuccess + $variationSuccess) / ($controlTotal + $variationTotal);
        $z = ($pVariation - $pControl) / sqrt(
            $pPooled * (1 - $pPooled) * (1/$controlTotal + 1/$variationTotal)
        );

        // For 95% confidence, z should be > 1.96
        return abs($z) > $this->getCriticalValue($confidenceLevel);
    }

    private function getCriticalValue(float $confidenceLevel): float {
        // Common z-values for confidence levels
        $values = [
            0.90 => 1.645,
            0.95 => 1.960,
            0.99 => 2.576
        ];

        return $values[$confidenceLevel] ?? 1.960;
    }

    /**
     * Complete a test and declare a winner
     */
    public function completeTest(int $testId, ?string $winner = null): void {
        if ($winner === null) {
            $winner = $this->determineWinner($testId);
        }

        $this->db->update('ab_tests', [
            'status' => 'completed',
            'winner' => $winner,
            'completed_at' => date('Y-m-d H:i:s')
        ], ['id' => $testId]);
    }

    private function determineWinner(int $testId): ?string {
        $metrics = $this->db->fetchAll(
            "SELECT name FROM ab_test_metrics WHERE test_id = ?",
            [$testId]
        );

        $variations = $this->db->fetchAll(
            "SELECT identifier FROM ab_test_variations WHERE test_id = ?",
            [$testId]
        );

        $scores = [];
        foreach ($variations as $variation) {
            $scores[$variation['identifier']] = 0;
        }

        foreach ($metrics as $metric) {
            $results = $this->analyticsModel->getTestResults($testId, $metric['name']);
            foreach ($results as $variation => $data) {
                if (isset($scores[$variation])) {
                    $scores[$variation] += $data['value'] ?? 0;
                }
            }
        }

        arsort($scores);
        return key($scores) ?: null;
    }
}
