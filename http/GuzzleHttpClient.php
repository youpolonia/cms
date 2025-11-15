<?php
declare(strict_types=1);

namespace Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleHttpClient implements HttpClientInterface
{
    private Client $client;
    private string $baseUri;

    public function __construct(string $baseUri = '')
    {
        $this->baseUri = $baseUri;
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout'  => 5.0,
        ]);
    }

    public function get(string $uri): array
    {
        try {
            $response = $this->client->get($uri);
            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            error_log("HTTP GET failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function post(string $uri, array $data = []): array
    {
        try {
            $response = $this->client->post($uri, ['json' => $data]);
            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            error_log("HTTP POST failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
