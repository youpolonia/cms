<?php

namespace App\Services;

abstract class MarketplaceService
{
    /**
     * Search for themes in the marketplace
     */
    abstract public function searchThemes(string $query, array $filters = []): array;

    /**
     * Download a theme from the marketplace
     */
    abstract public function downloadTheme(string $themeId, string $destinationPath): bool;

    /**
     * Get detailed information about a theme
     */
    abstract public function getThemeDetails(string $themeId): array;

    /**
     * Authenticate with the marketplace API
     */
    abstract public function authenticate(array $credentials): bool;

    /**
     * Get the marketplace name
     */
    abstract public function getName(): string;

    /**
     * Get the marketplace logo URL
     */
    abstract public function getLogoUrl(): string;

    /**
     * Get required configuration fields
     */
    public static function getConfigFields(): array
    {
        return [];
    }

    /**
     * Validate theme package
     */
    protected function validateThemePackage(string $filePath): bool
    {
        // Basic validation - check if it's a valid zip file
        return mime_content_type($filePath) === 'application/zip';
    }
}
