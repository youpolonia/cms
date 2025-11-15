<?php
/**
 * MediaBrowser - Provides media listing and search functionality
 */
class MediaBrowser {
    private $registry;
    private $itemsPerPage = 20;

    public function __construct(MediaRegistry $registry) {
        $this->registry = $registry;
    }

    /**
     * List media files with pagination
     */
    public function listMedia(int $page = 1, array $filters = []): array {
        $offset = ($page - 1) * $this->itemsPerPage;
        $where = $this->buildWhereClause($filters);
        
        // Get total count
        $countStmt = $this->registry->getDb()->prepare("
            SELECT COUNT(*) FROM media_files $where
        ");
        $countStmt->execute($this->getFilterValues($filters));
        $total = $countStmt->fetchColumn();

        // Get paginated results
        $stmt = $this->registry->getDb()->prepare("
            SELECT * FROM media_files 
            $where
            ORDER BY created_at DESC
            LIMIT $offset, $this->itemsPerPage
        ");
        $stmt->execute($this->getFilterValues($filters));

        return [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => [
                'page' => $page,
                'per_page' => $this->itemsPerPage,
                'total' => $total,
                'total_pages' => ceil($total / $this->itemsPerPage)
            ]
        ];
    }

    /**
     * Search media by filename or tags
     */
    public function searchMedia(string $query, int $page = 1): array {
        $offset = ($page - 1) * $this->itemsPerPage;
        
        // Get total count
        $countStmt = $this->registry->getDb()->prepare("
            SELECT COUNT(*) FROM media_files 
            WHERE filename LIKE :query 
            OR tags LIKE :query
        ");
        $countStmt->execute([':query' => "%$query%"]);
        $total = $countStmt->fetchColumn();

        // Get paginated results
        $stmt = $this->registry->getDb()->prepare("
            SELECT * FROM media_files 
            WHERE filename LIKE :query 
            OR tags LIKE :query
            ORDER BY created_at DESC
            LIMIT $offset, $this->itemsPerPage
        ");
        $stmt->execute([':query' => "%$query%"]);

        return [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => $this->buildPagination($page, $total)
        ];
    }

    /**
     * Build WHERE clause from filters
     */
    private function buildWhereClause(array $filters): string {
        $conditions = [];
        if (!empty($filters['type'])) {
            $conditions[] = "type = :type";
        }
        if (!empty($filters['user_id'])) {
            $conditions[] = "user_id = :user_id";
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "created_at >= :date_from";
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "created_at <= :date_to";
        }

        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }

    /**
     * Extract filter values for PDO
     */
    private function getFilterValues(array $filters): array {
        $values = [];
        foreach (['type', 'user_id', 'date_from', 'date_to'] as $key) {
            if (isset($filters[$key])) {
                $values[":$key"] = $filters[$key];
            }
        }
        return $values;
    }

    /**
     * Build pagination metadata
     */
    private function buildPagination(int $page, int $total): array {
        return [
            'page' => $page,
            'per_page' => $this->itemsPerPage,
            'total' => $total,
            'total_pages' => ceil($total / $this->itemsPerPage)
        ];
    }
}
