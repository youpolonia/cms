<?php

namespace App\Services;

class MCPPersonalizationService extends MCPBaseService
{
    protected $serverType = 'personalization';

    public function getUserProfile(string $userId)
    {
        $response = $this->client->get("/personalization/profile/{$userId}");
        return json_decode($response->getBody(), true);
    }

    public function updateUserPreferences(array $params)
    {
        $response = $this->client->post('/personalization/preferences', [
            'json' => $params
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getRecommendations(array $params)
    {
        $response = $this->client->get('/personalization/recommendations', [
            'query' => $params
        ]);
        return json_decode($response->getBody(), true);
    }
}