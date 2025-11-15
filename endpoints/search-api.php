<?php
declare(strict_types=1);

/**
 * Search API with archive integration and faceted search
 */

require_once __DIR__ . '/../includes/core/searchindex.php';
require_once __DIR__ . '/../includes/core/archivesystem.php';
require_once __DIR__ . '/../includes/core/cachemanager.php';

class SearchAPI {
    private static string $cacheDir = __DIR__ . '/../storage/cache/search';
    private static int $cacheTTL = 3600; // 1 hour

    public static function handleRequest(): void {
        header('Content-Type: application/json');
        
        try {
            $query = self::sanitizeInput($_GET['q'] ?? '');
            $filters = self::parseFilters($_GET['filters'] ?? '');
            
            $results = self::getCachedResults($query, $filters);
            if ($results === null) {
                $results = self::executeSearch($query, $filters);
                self::cacheResults($query, $filters, $results);
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $results,
                'facets' => self::generateFacets($results)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private static function executeSearch(string $query, array $filters): array {
        $index = new SearchIndex();
        $archive = new ArchiveSystem();
        
        $results = $index->search($query);
        $results = $archive->filterArchivedContent($results, $filters);
        
        return $results;
    }

    private static function generateFacets(array $results): array {
        $facets = [
            'content_type' => [],
            'archive_status' => [],
            'date_ranges' => []
        ];
        
        foreach ($results as $item) {
            // Count content types
            $facets['content_type'][$item['type']] = ($facets['content_type'][$item['type']] ?? 0) + 1;
            
            // Archive status
            $status = $item['archived'] ? 'archived' : 'active';
            $facets['archive_status'][$status] = ($facets['archive_status'][$status] ?? 0) + 1;
            
            // Date ranges
            $year = date('Y', strtotime($item['date']));
            $facets['date_ranges'][$year] = ($facets['date_ranges'][$year] ?? 0) + 1;
        }
        
        return $facets;
    }

    private static function cacheResults(string $query, array $filters, array $results): void {
        $cacheKey = self::generateCacheKey($query, $filters);
        CacheManager::set($cacheKey, $results, self::$cacheTTL);
    }

    private static function getCachedResults(string $query, array $filters): ?array {
        $cacheKey = self::generateCacheKey($query, $filters);
        return CacheManager::get($cacheKey);
    }

    private static function generateCacheKey(string $query, array $filters): string {
        return md5($query . json_encode($filters));
    }

    private static function sanitizeInput(string $input): string {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    private static function parseFilters(string $filterString): array {
        $filters = [];
        parse_str($filterString, $filters);
        return array_map('self::sanitizeInput', $filters);
    }
}

SearchAPI::handleRequest();
