<?php
declare(strict_types=1);

namespace Services;

interface VersioningServiceInterface
{
    public function createVersion(string $contentId, string $content): array;
    public function getVersion(string $versionId): array;
    public function listVersions(string $contentId): array;
}
