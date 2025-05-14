<?php

namespace Tests;

use Includes\Core\Application;
use Includes\Routing\Request;
use Includes\Routing\Response;

class TestCase
{
    protected $app;
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->request = new Request();
        $this->response = new Response();
    }

    protected function tearDown(): void
    {
        // Clean up after tests
    }

    protected function get($uri, array $headers = [])
    {
        $this->request->setMethod('GET');
        $this->request->setUri($uri);
        $this->request->setHeaders($headers);
        
        return $this->app->handle($this->request);
    }

    protected function post($uri, array $data = [], array $headers = [])
    {
        $this->request->setMethod('POST');
        $this->request->setUri($uri);
        $this->request->setHeaders($headers);
        $this->request->setBody($data);
        
        return $this->app->handle($this->request);
    }

    protected function assertStatus($response, $expectedStatus)
    {
        if ($response->getStatusCode() !== $expectedStatus) {
            throw new \Exception(
                "Expected status $expectedStatus but got {$response->getStatusCode()}"
            );
        }
    }

    protected function assertJson($response, array $expected)
    {
        $actual = json_decode($response->getBody(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Response is not valid JSON');
        }

        foreach ($expected as $key => $value) {
            if (!isset($actual[$key])) {
                throw new \Exception("Missing expected key: $key");
            }
            if ($actual[$key] !== $value) {
                throw new \Exception(
                    "Expected $key to be '$value' but got '{$actual[$key]}'"
                );
            }
        }
    }

    protected function assertHeader($response, $header, $value)
    {
        $headers = $response->getHeaders();
        if (!isset($headers[$header])) {
            throw new \Exception("Header $header not found in response");
        }
        if ($headers[$header] !== $value) {
            throw new \Exception(
                "Expected $header to be '$value' but got '{$headers[$header]}'"
            );
        }
    }
}
