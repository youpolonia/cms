<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;

class MCPContentGenerationService extends MCPBaseService
{
    protected $serverType = 'content-generation';

    public function generateContent(array $params)
    {
        try {
            $response = $this->client->post('/generate/content', [
                'json' => $params
            ]);
            return [
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true)
            ];
        } catch (RequestException $e) {
            return [
                'error' => $e->getMessage(),
                'status' => $e->getCode() ?: 500
            ];
        }
    }

    public function generateSummary(array $params)
    {
        try {
            $response = $this->client->post('/generate/summary', [
                'json' => $params
            ]);
            return [
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true)
            ];
        } catch (RequestException $e) {
            return [
                'error' => $e->getMessage(),
                'status' => $e->getCode() ?: 500
            ];
        }
    }

    public function generateSeo(array $params)
    {
        try {
            $response = $this->client->post('/generate/seo', [
                'json' => $params
            ]);
            return [
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true)
            ];
        } catch (RequestException $e) {
            return [
                'error' => $e->getMessage(),
                'status' => $e->getCode() ?: 500
            ];
        }
    }
}