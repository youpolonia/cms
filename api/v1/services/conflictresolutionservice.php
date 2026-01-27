<?php
class ConflictResolutionService {
    public static function checkVersionConflicts(array $versionA, array $versionB): array {
        $diff = VersionDiffService::generateConflictMarkers(
            DBSupport::getVersionDiff($versionA, $versionB)
        );
        return [
            'has_conflicts' => !empty($diff['conflict_markers']),
            'diff' => $diff
        ];
    }

    public static function resolveVersionConflict(array $resolutionData): bool {
        return DBSupport::applyVersionResolution(
            $resolutionData['tenant_id'],
            $resolutionData['content_id'],
            $resolutionData['resolution_strategy'],
            $resolutionData['resolved_by']
        );
    }
}
