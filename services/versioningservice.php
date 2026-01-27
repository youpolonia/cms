<?php
declare(strict_types=1);

namespace Services;

use Http\GuzzleHttpClient;
use Http\HttpClientInterface;

class VersioningService implements VersioningServiceInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function createVersion(string $contentId, string $content): array
    {
        return $this->httpClient->post('/versions', [
            'content_id' => $contentId,
            'content' => $content
        ]);
    }

    public function getVersion(string $versionId): array
    {
        return $this->httpClient->get("/versions/$versionId");
    }

    public function listVersions(string $contentId): array
    {
        return $this->httpClient->get("/contents/$contentId/versions");
    }
}
