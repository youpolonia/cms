<?php
declare(strict_types=1);

class ArchiveSystem {
    public function filterArchivedContent(array $items, array $filters): array {
        return array_filter($items, function($item) use ($filters) {
            // Apply archive status filter if specified
            if (isset($filters['archived'])) {
                return $item['archived'] == (bool)$filters['archived'];
            }
            return true;
        });
    }
}
