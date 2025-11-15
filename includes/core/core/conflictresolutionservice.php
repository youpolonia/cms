<?php
declare(strict_types=1);

class ConflictResolutionService {
    private static array $contentTypeRules = [
        'page' => [
            'critical_fields' => ['title', 'slug', 'status'],
            'merge_strategies' => [
                'content' => 'concat',
                'metadata' => 'overwrite'
            ]
        ],
        'post' => [
            'critical_fields' => ['title', 'author', 'publish_date'],
            'merge_strategies' => [
                'content' => 'smart_merge',
                'tags' => 'union'
            ]
        ]
    ];

    public static function detectConflicts(array $versionA, array $versionB): array {
        $diff = VersionComparator::compareVersions($versionA, $versionB);
        $contentType = $versionA['content_type'] ?? 'generic';
        $rules = self::$contentTypeRules[$contentType] ?? [];
        
        $conflictDetails = [
            'has_conflict' => $diff['similarity_score'] < 100,
            'diff' => $diff,
            'conflicting_fields' => [],
            'critical_conflicts' => [],
            'merge_strategies' => $rules['merge_strategies'] ?? ['default' => 'overwrite']
        ];

        foreach ($diff['fields_changed'] as $field => $changes) {
            $conflictDetails['conflicting_fields'][] = $field;
            
            if (in_array($field, $rules['critical_fields'] ?? [])) {
                $conflictDetails['critical_conflicts'][] = $field;
                $conflictDetails['severity'] = 'critical';
            }
        }

        $conflictDetails['automatic_merge_possible'] =
            !isset($conflictDetails['severity']) &&
            $diff['similarity_score'] > 70;

        return $conflictDetails;
    }

    public static function autoMerge(array $versionA, array $versionB): array {
        $merged = $versionA;
        $diff = VersionComparator::compareVersions($versionA, $versionB);
        $contentType = $versionA['content_type'] ?? 'generic';
        $rules = self::$contentTypeRules[$contentType] ?? [];
        
        foreach ($diff['fields_changed'] as $field => $changes) {
            $strategy = $rules['merge_strategies'][$field] ?? 'overwrite';
            
            switch ($strategy) {
                case 'concat':
                    $merged['content'][$field] = $changes['old'] . "\n" . $changes['new'];
                    break;
                case 'union':
                    $merged['content'][$field] = array_unique(
                        array_merge($changes['old'], $changes['new'])
                    );
                    break;
                case 'smart_merge':
                    $merged['content'][$field] = self::smartMergeField(
                        $changes['old'],
                        $changes['new'],
                        $field
                    );
                    break;
                default: // overwrite
                    $merged['content'][$field] = $changes['new'];
            }
        }
        
        return $merged;
    }

    private static function smartMergeField($oldValue, $newValue, string $field): mixed {
        // Basic smart merge - preserve structure when possible
        if (is_array($oldValue)) {
            return array_replace_recursive($oldValue, $newValue);
        }
        return $newValue;
    }
}
