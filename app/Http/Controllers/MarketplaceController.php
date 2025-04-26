<?php

namespace App\Http\Controllers;

use App\Services\MarketplaceService;
use App\Services\WordPressMarketplaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MarketplaceController extends Controller
{
    protected $marketplaceService;

    public function __construct()
    {
        // Default to WordPress service for now
        $this->marketplaceService = new WordPressMarketplaceService();
    }

    public function index(Request $request)
    {
        $query = $request->input('query', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 24);
        $category = $request->input('category');
        $sort = $request->input('sort');

        $searchParams = [
            'page' => $page,
            'per_page' => $perPage
        ];

        if ($category) {
            $searchParams['category'] = $category;
        }

        if ($sort) {
            $searchParams['sort'] = $sort;
        }

        $results = $this->marketplaceService->searchThemes($query, $searchParams);

        // Get local theme ratings for matching themes
        if (!empty($results['themes'])) {
            $themeSlugs = array_column($results['themes'], 'slug');
            $localThemes = \App\Models\Theme::whereIn('marketplace_id', $themeSlugs)
                ->withCount('ratings')
                ->withAvg('ratings', 'rating')
                ->get();

            foreach ($results['themes'] as &$theme) {
                $localTheme = $localThemes->firstWhere('marketplace_id', $theme['slug']);
                if ($localTheme) {
                    $theme['local_rating'] = $localTheme->ratings_avg_rating;
                    $theme['local_rating_count'] = $localTheme->ratings_count;
                }
            }
        }

        return response()->json([
            'data' => $results['themes'] ?? [],
            'meta' => [
                'current_page' => $results['info']['page'] ?? $page,
                'per_page' => $results['info']['per_page'] ?? $perPage,
                'total_pages' => $results['info']['pages'] ?? 1,
                'total_results' => $results['info']['results'] ?? 0,
                'marketplace' => $this->marketplaceService->getName(),
                'logo_url' => $this->marketplaceService->getLogoUrl()
            ]
        ]);
    }

    public function show(string $themeId)
    {
        try {
            $theme = $this->marketplaceService->getThemeDetails($themeId);

            if (empty($theme)) {
                return response()->json([
                    'message' => 'Theme not found'
                ], 404);
            }

            // Check if we have a local version to compare for updates
            $localTheme = \App\Models\Theme::where('marketplace_id', $themeId)->first();
            if ($localTheme) {
                $updateInfo = $this->marketplaceService->checkForUpdates(
                    $themeId,
                    $localTheme->version
                );
                $theme['update_available'] = $updateInfo['update_available'] ?? false;
                $theme['latest_version'] = $updateInfo['latest_version'] ?? $localTheme->version;
                $theme['local_rating'] = $localTheme->ratings_avg_rating;
                $theme['local_rating_count'] = $localTheme->ratings_count;
            }

            // Ensure all required fields for the modal are present
            $theme['tags'] = $theme['tags'] ?? [];
            $theme['description'] = $theme['description'] ?? 'No description available';
            $theme['screenshot_url'] = $theme['screenshot_url'] ?? 'https://via.placeholder.com/600x400?text=Theme+Preview';
            $theme['homepage'] = $theme['homepage'] ?? '#';
            $theme['version'] = $theme['version'] ?? '1.0';
            $theme['downloaded'] = $theme['downloaded'] ?? 0;
            $theme['rating'] = $theme['rating'] ?? 0;
            $theme['num_ratings'] = $theme['num_ratings'] ?? 0;

            return response()->json([
                'data' => $theme
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch theme details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function download(Request $request, string $themeId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'destination' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->marketplaceService->downloadTheme(
                $themeId,
                $request->input('destination')
            );

            if (!$result['success']) {
                return response()->json([
                    'message' => 'Failed to download theme',
                    'error' => $result['error'] ?? null
                ], 500);
            }

            return response()->json([
                'message' => 'Theme downloaded successfully',
                'metadata' => $result['metadata'],
                'theme_id' => $themeId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to download theme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkForUpdates(Request $request, string $themeId)
    {
        $validator = Validator::make($request->all(), [
            'current_version' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $updateInfo = $this->marketplaceService->checkForUpdates(
            $themeId,
            $request->input('current_version')
        );

        return response()->json([
            'data' => $updateInfo
        ]);
    }

    public function marketplaces()
    {
        return response()->json([
            'data' => [
                [
                    'id' => 'wordpress',
                    'name' => (new WordPressMarketplaceService())->getName(),
                    'logo_url' => (new WordPressMarketplaceService())->getLogoUrl(),
                    'config_fields' => WordPressMarketplaceService::getConfigFields()
                ]
            ]
        ]);
    }
}
