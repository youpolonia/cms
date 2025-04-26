<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;

class MCPMediaService extends MCPBaseService
{
    protected $serverType = 'media';

    public function processMedia(array $params)
    {
        try {
            $endpoint = str_contains($params['mime_type'], 'video')
                ? '/process/video'
                : '/process/image';
                
            $response = $this->client->post($endpoint, [
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

    public function tagMedia(array $params)
    {
        try {
            $response = $this->client->post('/tag/ai', [
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

    public function moderateMedia(array $params)
    {
        try {
            $response = $this->client->post('/moderate/content', [
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