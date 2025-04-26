<?php

namespace App\Services;

use App\Jobs\CheckThemeUpdatesJob;
use App\Models\Theme;
use App\Notifications\ThemeUpdateAvailable;

class ThemeUpdateChecker
{
    public function __construct(
        protected CheckThemeUpdatesJob $job,
        protected \Illuminate\Cache\Repository $cache,
        protected \Illuminate\Http\Client\Factory $http
    ) {}

    /**
     * Fetch changelog for a theme version
     */
    public function fetchChangelog(Theme $theme, string $version): ?string
    {
        $cacheKey = "theme_changelog_{$theme->id}_{$version}";
        
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            // TODO: Implement actual API call to fetch changelog
            // This is a placeholder - replace with real API integration
            $changelog = "## Version {$version}\n- Bug fixes\n- Performance improvements";
            
            $this->cache->put($cacheKey, $changelog, now()->addHours(24));
            return $changelog;
        } catch (\Exception $e) {
            \Log::error("Failed to fetch changelog for theme {$theme->id} version {$version}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch latest version for a theme from remote repository
     */
    public function fetchLatestVersion(Theme $theme): ?string
    {
        $cacheKey = "theme_version_{$theme->id}";
        
        // Return cached version if available and not expired
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            // Check if theme has a marketplace API endpoint
            if (empty($theme->marketplace_api_url)) {
                \Log::warning("No marketplace API URL configured for theme {$theme->id}");
                return null;
            }

            $response = $this->http->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.marketplace.api_key')
            ])->get($theme->marketplace_api_url . '/latest-version');

            if ($response->failed()) {
                throw new \Exception("API request failed with status: " . $response->status());
            }

            $data = $response->json();
            $latestVersion = $data['version'] ?? null;

            if (!$latestVersion) {
                throw new \Exception("Invalid version data received from API");
            }

            // Cache the result for 24 hours
            $this->cache->put($cacheKey, $latestVersion, now()->addHours(24));
            
            return $latestVersion;
        } catch (\Exception $e) {
            \Log::error("Failed to fetch latest version for theme {$theme->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check theme dependencies against available versions
     */
    public function checkDependencies(Theme $theme, string $targetVersion): array
    {
        $issues = [];
        
        try {
            $response = $this->http->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.marketplace.api_key')
            ])->get($theme->marketplace_api_url . "/{$targetVersion}/dependencies");

            if ($response->failed()) {
                throw new \Exception("API request failed with status: " . $response->status());
            }

            $dependencies = $response->json();

            foreach ($dependencies as $depName => $requiredVersion) {
                $installedDep = $theme->dependencies()->where('name', $depName)->first();
                
                if (!$installedDep) {
                    $issues[] = "Missing dependency: {$depName}@{$requiredVersion}";
                    continue;
                }

                if ($this->compareVersions($installedDep->version, $requiredVersion) < 0) {
                    $issues[] = "Outdated dependency: {$depName} (installed: {$installedDep->version}, required: {$requiredVersion})";
                }
            }

        } catch (\Exception $e) {
            \Log::error("Failed to check dependencies for theme {$theme->id}: " . $e->getMessage());
            $issues[] = "Unable to verify dependencies due to API error";
        }

        return $issues;
    }

    public function checkForUpdates(): void
    {
        Theme::query()
            ->where('is_active', true)
            ->each(function (Theme $theme) {
                try {
                    $latestVersion = $this->fetchLatestVersion($theme);
                    
                    if (!$latestVersion) {
                        \Log::warning("Could not fetch latest version for theme {$theme->id}");
                        return;
                    }

                    // Update theme with latest version
                    $theme->latest_version = $latestVersion;
                    $theme->save();

                    if ($this->hasUpdateAvailable($theme)) {
                        // Record comparison statistics
                        $this->recordComparisonStats($theme, $latestVersion);
                        // Check dependencies before notifying
                        $dependencyIssues = $this->checkDependencies($theme, $latestVersion);
                        
                        $theme->user->notify(new ThemeUpdateAvailable($theme, $dependencyIssues));
                        
                        if (!empty($dependencyIssues)) {
                            \Log::warning("Update available for theme {$theme->name} but has dependency issues", [
                                'issues' => $dependencyIssues
                            ]);
                        }
                        \Log::info("Notified user about theme update for {$theme->name}");
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to check updates for theme {$theme->id}: " . $e->getMessage());
                }
            });
    }

    /**
     * Get formatted list of available theme updates
     */
    public function getAvailableUpdates()
    {
        return Theme::query()
            ->where('is_active', true)
            ->get()
            ->map(function (Theme $theme) {
                return (object)[
                    'name' => $theme->name,
                    'current_version' => $theme->current_version,
                    'latest_version' => $theme->latest_version,
                    'update_available' => $this->hasUpdateAvailable($theme)
                ];
            })
            ->filter(fn($theme) => $theme->update_available);
    }

    /**
     * Record version comparison statistics
     */
    protected function recordComparisonStats(Theme $theme, string $targetVersion): void
    {
        try {
            $comparison = ThemeVersionComparisonStat::create([
                'theme_id' => $theme->id,
                'base_version_id' => $theme->current_version_id,
                'target_version_id' => $theme->versions()
                    ->where('version', $targetVersion)
                    ->first()?->id,
                'files_added' => 0, // TODO: Implement actual file diff analysis
                'files_removed' => 0, // TODO: Implement actual file diff analysis
                'files_modified' => 0, // TODO: Implement actual file diff analysis
                'lines_added' => 0, // TODO: Implement actual line diff analysis
                'lines_removed' => 0, // TODO: Implement actual line diff analysis
                'total_size_diff_kb' => 0, // TODO: Calculate size difference
                'css_size_diff_kb' => 0, // TODO: Calculate CSS size difference
                'js_size_diff_kb' => 0, // TODO: Calculate JS size difference
                'image_size_diff_kb' => 0, // TODO: Calculate image size difference
                'file_count_diff' => 0, // TODO: Calculate file count difference
                'quality_score' => 0, // TODO: Implement quality analysis
                'performance_impact' => 0, // TODO: Implement performance analysis
                'comparison_data' => [] // TODO: Store detailed comparison data
            ]);

            \Log::info("Recorded version comparison stats for theme {$theme->name}", [
                'comparison_id' => $comparison->id
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to record comparison stats for theme {$theme->id}: " . $e->getMessage());
        }
    }

    protected function hasUpdateAvailable(Theme $theme): bool
    {
        if (empty($theme->current_version)) {
            return false;
        }

        if (empty($theme->latest_version)) {
            return false;
        }

        return $this->compareVersions($theme->current_version, $theme->latest_version) < 0;
    }

    /**
     * Compare two semantic versions
     * 
     * @return int Returns:
     *   -1 if version1 < version2
     *    0 if version1 == version2
     *    1 if version1 > version2
     */
    public function compareVersions(string $version1, string $version2): int
    {
        $v1 = $this->parseVersion($version1);
        $v2 = $this->parseVersion($version2);

        // Compare major versions
        if ($v1['major'] !== $v2['major']) {
            return $v1['major'] <=> $v2['major'];
        }

        // Compare minor versions
        if ($v1['minor'] !== $v2['minor']) {
            return $v1['minor'] <=> $v2['minor'];
        }

        // Compare patch versions
        if ($v1['patch'] !== $v2['patch']) {
            return $v1['patch'] <=> $v2['patch'];
        }

        // Compare pre-release versions if both have them
        if (!empty($v1['pre_release']) && !empty($v2['pre_release'])) {
            return strcmp($v1['pre_release'], $v2['pre_release']);
        }

        // Version with pre-release is considered older
        if (!empty($v1['pre_release'])) {
            return -1;
        }

        if (!empty($v2['pre_release'])) {
            return 1;
        }

        return 0;
    }

    /**
     * Parse a semantic version string into components
     */
    protected function parseVersion(string $version): array
    {
        $pattern = '/^v?(\d+)\.(\d+)\.(\d+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/';
        if (!preg_match($pattern, $version, $matches)) {
            throw new \InvalidArgumentException("Invalid version format: {$version}");
        }

        return [
            'major' => (int)$matches[1],
            'minor' => (int)$matches[2],
            'patch' => (int)$matches[3],
            'pre_release' => $matches[4] ?? '',
            'build' => $matches[5] ?? ''
        ];
    }
}
