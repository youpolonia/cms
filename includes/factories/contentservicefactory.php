<?php
declare(strict_types=1);

namespace Factories;

use Services\ContentService;
use Services\ContentServiceInterface;
use Http\GuzzleHttpClient;

class ContentServiceFactory
{
    public static function create(): ContentServiceInterface
    {
        $httpClient = new GuzzleHttpClient();
        return new ContentService($httpClient);
    }
}
