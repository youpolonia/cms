<?php

namespace App\Services;

class MCPSearchService extends MCPBaseService
{
    protected $serverType = 'search';

    public function semanticSearch(array $params)
    {
        $response = $this->client->post('/search/semantic', [
            'json' => $params
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getSuggestions(array $params)
    {
        $response = $this->client->get('/search/suggest', [
            'query' => $params
        ]);
        return json_decode($response->getBody(), true);
    }

    public function personalizeResults(array $params)
    {
        $response = $this->client->get('/search/personalize', [
            'query' => $params
        ]);
        return json_decode($response->getBody(), true);
    }
}