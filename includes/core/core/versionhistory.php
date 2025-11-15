<?php
declare(strict_types=1);

namespace Core;

class VersionHistory
{
    private const DEFAULT_PER_PAGE = 20;
    private const MAX_PER_PAGE = 100;

    /**
     * Get paginated version history
     * @param int $page Current page number (1-based)
     * @param int $perPage Items per page
     * @param ?string $startDate Filter start date (Y-m-d)
     * @param ?string $endDate Filter end date (Y-m-d)
     * @return array{data: array, pagination: array}
     */
    public static function getPaginatedVersions(
        int $page = 1,
        int $perPage = self::DEFAULT_PER_PAGE,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        // Validate and sanitize inputs
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), self::MAX_PER_PAGE);
        
        // Get versions from version control API
        $versions = self::getVersionsFromApi($startDate, $endDate);
        
        // Implement pagination
        $total = count($versions);
        $offset = ($page - 1) * $perPage;
        $paginated = array_slice($versions, $offset, $perPage);
        
        return [
            'data' => $paginated,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
            ]
        ];
    }

    /**
     * Get versions from version control API with date filtering
     */
    private static function getVersionsFromApi(?string $startDate, ?string $endDate): array
    {
        // Validate date formats if provided
        if ($startDate && !self::validateDate($startDate)) {
            throw new \InvalidArgumentException('Invalid start date format. Expected Y-m-d');
        }
        
        if ($endDate && !self::validateDate($endDate)) {
            throw new \InvalidArgumentException('Invalid end date format. Expected Y-m-d');
        }

        // Simulate API response with test data
        $versions = [
            ['id' => 1, 'created_at' => '2025-05-01', 'author' => 'user1', 'message' => 'Initial version'],
            ['id' => 2, 'created_at' => '2025-05-02', 'author' => 'user2', 'message' => 'Updated content'],
            ['id' => 3, 'created_at' => '2025-05-03', 'author' => 'user1', 'message' => 'Fixed bugs'],
            ['id' => 4, 'created_at' => '2025-05-04', 'author' => 'user3', 'message' => 'Added features'],
            ['id' => 5, 'created_at' => '2025-05-05', 'author' => 'user2', 'message' => 'Refactored code'],
        ];

        // Filter by date range if provided
        return array_filter($versions, function($version) use ($startDate, $endDate) {
            $versionDate = $version['created_at'];
            $afterStart = !$startDate || $versionDate >= $startDate;
            $beforeEnd = !$endDate || $versionDate <= $endDate;
            return $afterStart && $beforeEnd;
        });
    }

    /**
     * Validate date format (Y-m-d)
     */
    private static function validateDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
