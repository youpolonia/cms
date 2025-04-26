<?php

namespace App\Contracts;

use App\Models\ContentVersion;

interface DiffServiceInterface
{
    /**
     * Compare two theme versions and return semantic differences
     */
    public function compareVersions(ContentVersion $oldVersion, ContentVersion $newVersion): array;

    /**
     * Compare specific files between versions
     */
    public function compareFiles(ContentVersion $oldVersion, ContentVersion $newVersion, array $filePaths = []): array;

    /**
     * Get semantic change summary between versions
     */
    public function getSemanticChanges(ContentVersion $oldVersion, ContentVersion $newVersion): array;

    /**
     * Generate HTML diff between versions
     */
    public function generateHtmlDiff(ContentVersion $oldVersion, ContentVersion $newVersion): string;

    /**
     * Generate CSS diff between versions
     */
    public function generateCssDiff(ContentVersion $oldVersion, ContentVersion $newVersion): string;

    /**
     * Generate JS diff between versions
     */
    public function generateJsDiff(ContentVersion $oldVersion, ContentVersion $newVersion): string;
}