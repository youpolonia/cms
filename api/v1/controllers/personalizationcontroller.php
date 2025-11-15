<?php

namespace Api\V1\Controllers;

use Includes\Routing\Request;
use Includes\Routing\Response;
use Includes\Services\PersonalizationService;
use Includes\Services\OpenAIService;

class PersonalizationController
{
    protected $personalizationService;
    protected $openAIService;

    public function __construct()
    {
        $db = \core\Database::connection();
        $aiService = new OpenAIService();
        
        $this->personalizationService = new PersonalizationService(
            $db,
            $aiService
        );
        $this->openAIService = $aiService;
    }

    public function trackEvent(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        
        // Validate required fields
        if (empty($data['event_type']) || empty($data['session_id'])) {
            return $response->withStatus(400)->withJson([
                'error' => 'Missing required fields'
            ]);
        }

        $success = $this->personalizationService->trackEvent($data);

        return $response->withJson([
            'success' => $success
        ]);
    }

    public function getRecommendations(Request $request, Response $response)
    {
        $userId = $request->getQueryParam('user_id');
        $context = $request->getQueryParam('context', 'default');
        
        if (empty($userId)) {
            return $response->withStatus(400)->withJson([
                'error' => 'Missing user_id parameter'
            ]);
        }

        $result = $this->personalizationService->getPersonalizedContent($userId, $context);

        return $response->withJson([
            'recommendations' => $result['recommendations'],
            'behavior_analysis' => $result['behavior']
        ]);
    }

    public function getAIRecommendations(Request $request, Response $response)
    {
        $userId = $request->getQueryParam('user_id');
        $context = $request->getQueryParam('context', 'default');
        
        if (empty($userId)) {
            return $response->withStatus(400)->withJson([
                'error' => 'Missing user_id parameter'
            ]);
        }

        // Get user behavior data
        $behavior = $this->personalizationService->getUserBehavior($userId);
        
        // Generate AI-powered recommendations
        $prompt = $this->buildAIPrompt($behavior, $context);
        $recommendations = $this->openAIService->getCompletion($prompt);

        return $response->withJson([
            'recommendations' => $recommendations,
            'behavior_analysis' => $behavior,
            'ai_generated' => true
        ]);
    }

    protected function buildAIPrompt(array $behavior, string $context): string
    {
        return sprintf(
            "Based on this user behavior: %s\n\n" .
            "And this context: %s\n\n" .
            "Generate 5 personalized content recommendations in JSON format with fields: " .
            "title, description, content_type, and relevance_score (1-10). " .
            "Focus on the most relevant content first.",
            json_encode($behavior),
            $context
        );
    }
}
