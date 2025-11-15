<?php
declare(strict_types=1);

namespace Factories;

use Services\VersioningService;
use Services\VersioningServiceInterface;
use Http\GuzzleHttpClient;
use Http\HttpClientInterface;

class VersioningServiceFactory
{
    public static function create(): VersioningServiceInterface
    {
        $httpClient = new GuzzleHttpClient();
        return new VersioningService($httpClient);
    }
}
