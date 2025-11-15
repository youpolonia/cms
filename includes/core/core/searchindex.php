<?php
declare(strict_types=1);

class SearchIndex {
    public function search(string $query): array {
        // TODO: Implement actual search logic
        // For now return mock results
        return [
            [
                'id' => 1,
                'title' => 'Example Document',
                'content' => 'This contains the search term',
                'type' => 'document',
                'archived' => false,
                'date' => '2025-01-15'
            ],
            [
                'id' => 2,
                'title' => 'Archived Article',
                'content' => 'This was archived last year',
                'type' => 'article',
                'archived' => true,
                'date' => '2024-06-10'
            ]
        ];
    }
}
