<?php
declare(strict_types=1);

namespace Http;

interface HttpClientInterface
{
    public function get(string $uri): array;
    public function post(string $uri, array $data = []): array;
}
