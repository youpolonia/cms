<?php
class SearchEngine {
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function search(string $query, string $tenantId, array $options = []): array {
        $options = array_merge([
            'limit' => 20,
            'offset' => 0,
            'version' => null,
            'min_score' => 1.0,
            'date_range' => null,
            'operator' => 'AND'
        ], $options);

        $params = [
            ':tenant_id' => $tenantId,
            ':limit' => $options['limit'],
            ':offset' => $options['offset']
        ];

        $where = "WHERE tenant_id = :tenant_id";
        
        if ($options['version']) {
            $where .= " AND version_id = :version_id";
            $params[':version_id'] = $options['version'];
        }

        if (!empty($options['date_range'])) {
            if (!empty($options['date_range']['start'])) {
                $startDate = $this->parseDate($options['date_range']['start']);
                if ($startDate) {
                    $where .= " AND created_at >= :start_date";
                    $params[':start_date'] = $startDate;
                }
            }
            if (!empty($options['date_range']['end'])) {
                $endDate = $this->parseDate($options['date_range']['end']);
                if ($endDate) {
                    $where .= " AND created_at <= :end_date";
                    $params[':end_date'] = $endDate;
                }
            }
        }

        if (!empty($options['fields'])) {
            foreach ($options['fields'] as $field => $value) {
                $paramName = ':field_' . str_replace('.', '_', $field);
                $where .= " AND JSON_EXTRACT(metadata, '$." . str_replace('.', '"."', $field) . "') = $paramName";
                $params[$paramName] = $value;
            }
        }

        try {
            // Try full-text search first
            // Cast limit/offset to integers for direct interpolation
            $limit = (int)$options['limit'];
            $offset = (int)$options['offset'];
            
            $stmt = $this->pdo->prepare("
                SELECT
                    id,
                    content_id,
                    version_id,
                    title,
                    content,
                    metadata,
                    MATCH(title, content) AGAINST(:query IN BOOLEAN MODE) AS score
                FROM search_index
                $where
                HAVING score >= :min_score
                ORDER BY score DESC
                LIMIT $limit OFFSET $offset
            ");

            // Bind remaining parameters
            $stmt->bindValue(':min_score', $options['min_score']);
            $stmt->bindValue(':query', $this->formatBooleanQuery($query, $options['operator']));
            
            // Only bind parameters that exist in the query
            $queryParams = [':min_score', ':query'];
            foreach ($params as $param => $value) {
                if (in_array($param, $queryParams) || str_starts_with($param, ':field_')) {
                    $stmt->bindValue($param, $value);
                }
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Fallback to partial match if no results
            if (empty($results)) {
                // Cast limit/offset to integers for direct interpolation
                $limit = (int)$options['limit'];
                $offset = (int)$options['offset'];
                
                $stmt = $this->pdo->prepare("
                    SELECT
                        id,
                        content_id,
                        version_id,
                        title,
                        content,
                        metadata,
                        1.0 AS score
                    FROM search_index
                    WHERE tenant_id = :tenant_id
                    AND (title LIKE :like_query OR content LIKE :like_query)
                    LIMIT $limit OFFSET $offset
                ");

                // Bind remaining parameters
                $stmt->bindValue(':tenant_id', $tenantId);
                $stmt->bindValue(':like_query', $this->formatLikeQuery($query, $options['operator']));
                
                // Only bind parameters that exist in the query
                $queryParams = [':tenant_id', ':like_query'];
                foreach ($queryParams as $param) {
                    if (isset($params[$param])) {
                        $stmt->bindValue($param, $params[$param]);
                    }
                }
                
                $stmt->execute();
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Get total count for pagination
            $totalCount = 0;
            $countStmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM search_index
                $where
            ");
            $countStmt->execute($params);
            $totalCount = (int)$countStmt->fetchColumn();

            // Calculate next cursor (last result's ID)
            $nextCursor = null;
            if (count($results) > 0 && count($results) >= $options['limit']) {
                $lastResult = end($results);
                $nextCursor = $lastResult['id'];
            }

            return [
                'items' => $results,
                'pagination' => [
                    'total_results' => $totalCount,
                    'total_pages' => ceil($totalCount / $options['limit']),
                    'current_page' => floor($options['offset'] / $options['limit']) + 1,
                    'next_cursor' => $nextCursor
                ]
            ];
        } catch (\PDOException $e) {
            error_log("Search failed: " . $e->getMessage());
            return [];
        }
    }

    private function formatBooleanQuery(string $query, string $operator): string {
        $terms = array_filter(array_map('trim', explode(' ', $query)));
        if (count($terms) < 2) {
            return $query;
        }

        switch ($operator) {
            case 'AND':
                $prefix = '+';
                break;
            case 'NOT':
                $prefix = '-';
                break;
            default:
                $prefix = '';
        }

        return implode(' ', array_map(
            function($term) use ($prefix) {
                return $prefix . $term;
            },
            $terms
        ));
    }

    private function parseDate($dateInput): ?string {
        if ($dateInput instanceof \DateTimeInterface) {
            return $dateInput->format('Y-m-d H:i:s');
        }

        try {
            $date = new \DateTime($dateInput);
            return $date->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            error_log("Invalid date format: " . $e->getMessage());
            return null;
        }
    }

    private function formatLikeQuery(string $query, string $operator): string {
        $terms = array_filter(array_map('trim', explode(' ', $query)));
        if (empty($terms)) {
            return '';
        }

        switch ($operator) {
            case 'AND':
                return '%' . implode('%', $terms) . '%';
            case 'NOT':
                return '%' . $terms[0] . '%';
            default:
                return '%' . implode('% %', $terms) . '%';
        }
    }
}
