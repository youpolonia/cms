<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CustomMarketplaceService extends MarketplaceService
{
    private string $apiBaseUrl;
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.custom_marketplace.url');
        $this->apiKey = config('services.custom_marketplace.key');
        $this->apiSecret = config('services.custom_marketplace.secret');
    }

    public function searchThemes(string $query, array $filters = []): array
    {
        $response = Http::withHeaders($this->getAuthHeaders())
            ->get("{$this->apiBaseUrl}/themes", [
                'query' => $query,
                'per_page' => $filters['per_page'] ?? 24,
                'page' => $filters['page'] ?? 1,
            ]);

        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }

        Log::error('Custom marketplace theme search failed', [
            'status' => $response->status(),
            'response' => $response->body()
        ]);

        return [];
    }

    public function downloadTheme(string $themeId, string $destinationPath): bool
    {
        $response = Http::withHeaders($this->getAuthHeaders())
            ->get("{$this->apiBaseUrl}/themes/{$themeId}/download");

        if ($response->successful()) {
            $tempPath = tempnam(sys_get_temp_dir(), 'custom_theme_');
            file_put_contents($tempPath, $response->body());

            if ($this->validateThemePackage($tempPath)) {
                $result = Storage::put($destinationPath, file_get_contents($tempPath));
                unlink($tempPath);
                return $result;
            }
        }

        return false;
    }

    public function getThemeDetails(string $themeId): array
    {
        $response = Http::withHeaders($this->getAuthHeaders())
            ->get("{$this->apiBaseUrl}/themes/{$themeId}");

        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }

        return [];
    }

    public function authenticate(array $credentials): bool
    {
        $response = Http::post("{$this->apiBaseUrl}/auth", [
            'key' => $credentials['key'],
            'secret' => $credentials['secret']
        ]);

        return $response->successful();
    }

    public function getName(): string
    {
        return 'Custom Theme Marketplace';
    }

    public function getLogoUrl(): string
    {
        return "{$this->apiBaseUrl}/logo.png";
    }

    public static function getConfigFields(): array
    {
        return [
            'url' => [
                'type' => 'text',
                'label' => 'API Base URL',
                'required' => true
            ],
            'key' => [
                'type' => 'text',
                'label' => 'API Key',
                'required' => true
            ],
            'secret' => [
                'type' => 'password',
                'label' => 'API Secret',
                'required' => true
            ]
        ];
    }

    private function getAuthHeaders(): array
    {
        return [
            'X-API-Key' => $this->apiKey,
            'X-API-Secret' => $this->apiSecret
        ];
    }
}
