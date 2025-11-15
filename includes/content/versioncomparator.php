<?php
declare(strict_types=1);

class VersionComparator {
    public static function compareVersions(array $versionA, array $versionB): array {
        $diff = [
            'fields_changed' => [],
            'similarity_score' => 0
        ];

        $versionA['content'] = $versionA['content'] ?? [];
        $versionB['content'] = $versionB['content'] ?? [];

        $allFields = array_unique(
            array_merge(
                array_keys($versionA['content']),
                array_keys($versionB['content'])
            )
        );

        $changedFields = [];
        foreach ($allFields as $field) {
            $valueA = $versionA['content'][$field] ?? null;
            $valueB = $versionB['content'][$field] ?? null;

            if ($valueA !== $valueB) {
                $changedFields[$field] = [
                    'old' => $valueA,
                    'new' => $valueB
                ];
            }
        }

        $totalFields = count($allFields);
        $changedCount = count($changedFields);
        $diff['similarity_score'] = $totalFields > 0 
            ? (int)round((($totalFields - $changedCount) / $totalFields) * 100)
            : 100;

        $diff['fields_changed'] = $changedFields;
        return $diff;
    }

    public static function getChangeSummary(array $versionA, array $versionB): string {
        $diff = self::compareVersions($versionA, $versionB);
        return sprintf(
            "Similarity: %d%%\nChanged fields: %s",
            $diff['similarity_score'],
            implode(', ', array_keys($diff['fields_changed']))
        );
    }
}
