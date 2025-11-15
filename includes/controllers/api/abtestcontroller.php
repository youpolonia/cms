<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

namespace Includes\Controllers\Api;

use Includes\Routing\Request;
use Includes\Routing\Response;
use Includes\Services\ABTestService;
use Includes\Session\SessionManager;

/**
 * ABTestController handles API endpoints for A/B testing
 */
class ABTestController {
    /**
     * Get variant for a test
     *
     * @param Request $request
     * @return Response
     */
    public function getVariant(Request $request): Response {
        $testId = $request->getQueryParams()['test_id'] ?? null;
        
        if (!$testId) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Missing test_id parameter'
            ]));
        }
        
        $session = SessionManager::getInstance();
        $userId = $session->get('user_id') ?? 0;
        
        $variant = ABTestService::getVariant($testId, $userId);
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'data' => [
                'test_id' => $testId,
                'variant' => $variant
            ]
        ]));
    }
    
    /**
     * Track a conversion for a test
     *
     * @param Request $request
     * @return Response
     */
    public function trackConversion(Request $request): Response {
        $body = json_decode($request->getBody(), true);
        
        if (!isset($body['test_id']) || !isset($body['conversion_type'])) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Missing required parameters'
            ]));
        }
        
        $testId = $body['test_id'];
        $conversionType = $body['conversion_type'];
        $metadata = $body['metadata'] ?? [];
        
        $success = ABTestService::trackConversion($testId, $conversionType, $metadata);
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => $success
        ]));
    }
    
    /**
     * Get test results
     *
     * @param Request $request
     * @return Response
     */
    public function getResults(Request $request): Response {
        $testId = $request->getQueryParams()['test_id'] ?? null;
        
        if (!$testId) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Missing test_id parameter'
            ]));
        }
        
        $results = ABTestService::getTestResults($testId);
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'data' => $results
        ]));
    }
    
    /**
     * Create a new A/B test
     *
     * @param Request $request
     * @return Response
     */
    public function createTest(Request $request): Response {
        $body = json_decode($request->getBody(), true);
        
        if (!isset($body['test_id']) || !isset($body['name'])) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Missing required parameters'
            ]));
        }
        
        $testId = $body['test_id'];
        $name = $body['name'];
        $description = $body['description'] ?? '';
        $distribution = $body['distribution'] ?? ['A' => 50, 'B' => 50];
        $startDate = $body['start_date'] ?? null;
        $endDate = $body['end_date'] ?? null;
        
        $success = ABTestService::createTest(
            $testId,
            $name,
            $description,
            $distribution,
            $startDate,
            $endDate
        );
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => $success,
            'data' => [
                'test_id' => $testId
            ]
        ]));
    }
    
    /**
     * Update an existing A/B test
     *
     * @param Request $request
     * @return Response
     */
    public function updateTest(Request $request): Response {
        $body = json_decode($request->getBody(), true);
        
        if (!isset($body['test_id'])) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Missing test_id parameter'
            ]));
        }
        
        $testId = $body['test_id'];
        unset($body['test_id']);
        
        $success = ABTestService::updateTest($testId, $body);
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => $success
        ]));
    }
    
    /**
     * List all active A/B tests
     *
     * @param Request $request
     * @return Response
     */
    public function listTests(Request $request): Response {
        $query = "
            SELECT 
                test_id,
                name,
                description,
                active,
                start_date,
                end_date,
                created_at
            FROM 
                ab_tests
            ORDER BY 
                created_at DESC
        ";
        
        $tests = \Includes\Database\DatabaseConnection::fetchAll($query);
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'data' => $tests
        ]));
    }
}
