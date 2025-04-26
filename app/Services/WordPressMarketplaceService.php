<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WordPressMarketplaceService extends MarketplaceService
{
    private const API_BASE_URL = 'https://api.wordpress.org/themes/info/1.2/';
    private const THEME_DOWNLOAD_URL = 'https://downloads.wordpress.org/theme/';

    public function searchThemes(string $query, array $filters = []): array
    {
        $request = [
            'search' => $query,
            'per_page' => $filters['per_page'] ?? 24,
            'page' => $filters['page'] ?? 1,
            'fields' => [
                'name',
                'slug',
                'version',
                'preview_url',
                'screenshot_url',
                'rating',
                'num_ratings',
                'downloaded',
                'last_updated',
                'homepage',
                'description',
                'tags',
            ]
        ];

        // Apply category filter
        if (!empty($filters['category'])) {
            $request['tag'] = $this->mapCategoryToTag($filters['category']);
        }

        // Apply sorting
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'popular':
                    $request['browse'] = 'popular';
                    break;
                case 'newest':
                    $request['browse'] = 'new';
                    break;
                case 'rating':
                    $request['browse'] = 'top-rated';
                    break;
            }
        }

        $response = Http::get(self::API_BASE_URL, [
            'action' => 'query_themes',
            'request' => $request
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'themes' => $data['themes'] ?? [],
                'info' => [
                    'page' => $data['info']['page'] ?? 1,
                    'pages' => $data['info']['pages'] ?? 1,
                    'results' => $data['info']['results'] ?? 0,
                    'per_page' => $request['per_page']
                ]
            ];
        }

        Log::error('WordPress theme search failed', [
            'status' => $response->status(),
            'response' => $response->body(),
            'request' => $request
        ]);

        return [
            'themes' => [],
            'info' => [
                'page' => 1,
                'pages' => 1,
                'results' => 0,
                'per_page' => $request['per_page']
            ]
        ];
    }

    protected function mapCategoryToTag(string $category): string
    {
        $mapping = [
            'blog' => 'blog',
            'business' => 'business',
            'portfolio' => 'portfolio'
        ];

        return $mapping[$category] ?? $category;
    }

    public function downloadTheme(string $themeId, string $destinationPath): array
    {
        $downloadUrl = self::THEME_DOWNLOAD_URL . "{$themeId}.latest-stable.zip";
        $tempPath = tempnam(sys_get_temp_dir(), 'wp_theme_');
        $metadata = $this->getThemeDetails($themeId);

        try {
            $response = Http::sink($tempPath)->get($downloadUrl);

            if ($response->successful() && $this->validateThemePackage($tempPath)) {
                $success = Storage::put($destinationPath, file_get_contents($tempPath));
                return [
                    'success' => $success,
                    'metadata' => $metadata
                ];
            }
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return ['success' => false, 'metadata' => []];
    }

    public function checkForUpdates(string $themeId, string $currentVersion): array
    {
        $details = $this->getThemeDetails($themeId);
        
        if (empty($details)) {
            return [
                'update_available' => false,
                'latest_version' => $currentVersion
            ];
        }

        return [
            'update_available' => version_compare($details['version'], $currentVersion, '>'),
            'latest_version' => $details['version'],
            'details' => $details
        ];
    }

    public function getThemeDetails(string $themeId): array
    {
        try {
            $response = Http::get(self::API_BASE_URL, [
                'action' => 'theme_information',
                'request' => [
                    'slug' => $themeId,
                    'fields' => [
                        'name',
                        'slug',
                        'version',
                        'author',
                        'author_profile',
                        'preview_url',
                        'screenshot_url',
                        'rating',
                        'num_ratings',
                        'downloaded',
                        'last_updated',
                        'homepage',
                        'description',
                        'download_link',
                        'tags',
                        'requires',
                        'requires_php',
                        'active_installs',
                        'contributors',
                        'sections',
                        'versions'
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return array_merge($data, [
                    'marketplace_id' => $themeId,
                    'installation_source' => 'wordpress',
                    'last_checked' => now()->toDateTimeString()
                ]);
            }

            Log::error('Failed to fetch WordPress theme details', [
                'theme_id' => $themeId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'error' => 'Failed to fetch theme details',
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Exception fetching WordPress theme details', [
                'theme_id' => $themeId,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function authenticate(array $credentials): bool
    {
        // WordPress.org API doesn't require authentication
        return true;
    }

    public function getName(): string
    {
        return 'WordPress.org Theme Directory';
    }

    public function getLogoUrl(): string
    {
        return 'https://s.w.org/style/images/about/WordPress-logotype-standard.png';
    }

    public static function getConfigFields(): array
    {
        return [
            // No configuration needed for WordPress.org API
        ];
    }
}
