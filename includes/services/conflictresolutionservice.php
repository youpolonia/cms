<?php
/**
 * Conflict Resolution Service
 * Handles version, content merge, and permission conflicts
 */
class ConflictResolutionService
{
    /**
     * Detect conflicts between two versions or content states
     * 
     * @param array $currentState Current content/version state
     * @param array $proposedState Proposed changes
     * @return array Detected conflicts
     */
    public function detectConflicts(array $currentState, array $proposedState): array
    {
        $conflicts = [
            'version' => $this->detectVersionConflicts($currentState, $proposedState),
            'content' => $this->detectContentConflicts($currentState, $proposedState),
            'permission' => $this->detectPermissionConflicts($currentState, $proposedState)
        ];

        return array_filter($conflicts);
    }

    /**
     * Detect version conflicts
     */
    private function detectVersionConflicts(array $current, array $proposed): ?array
    {
        if (isset($current['version'], $proposed['version']) 
            && $current['version'] !== $proposed['version']) {
            return [
                'type' => 'version',
                'current' => $current['version'],
                'proposed' => $proposed['version']
            ];
        }
        return null;
    }

    /**
     * Detect content merge conflicts
     */
    private function detectContentConflicts(array $current, array $proposed): ?array
    {
        $conflicts = [];
        
        foreach ($proposed as $key => $value) {
            if (isset($current[$key]) && $current[$key] !== $value) {
                $conflicts[$key] = [
                    'current' => $current[$key],
                    'proposed' => $value
                ];
            }
        }

        return empty($conflicts) ? null : [
            'type' => 'content',
            'fields' => $conflicts
        ];
    }

    /**
     * Detect permission conflicts
     */
    private function detectPermissionConflicts(array $current, array $proposed): ?array
    {
        if (isset($proposed['permissions'])) {
            $invalid = array_diff($proposed['permissions'], $current['allowed_permissions'] ?? []);
            if (!empty($invalid)) {
                return [
                    'type' => 'permission',
                    'invalid_permissions' => $invalid
                ];
            }
        }
        return null;
    }

    /**
     * Get available resolution options for detected conflicts
     * 
     * @param array $conflicts Detected conflicts from detectConflicts()
     * @return array Available resolution options
     */
    public function getResolutionOptions(array $conflicts): array
    {
        $options = [];

        foreach ($conflicts as $type => $conflict) {
            switch ($type) {
                case 'version':
                    $options['version'] = [
                        'overwrite' => 'Overwrite current version',
                        'merge' => 'Attempt automatic merge',
                        'abort' => 'Abort changes'
                    ];
                    break;
                    
                case 'content':
                    $options['content'] = [
                        'overwrite' => 'Overwrite all conflicting fields',
                        'selective' => 'Choose field-by-field',
                        'merge' => 'Attempt content merge'
                    ];
                    break;
                    
                case 'permission':
                    $options['permission'] = [
                        'remove' => 'Remove invalid permissions',
                        'abort' => 'Abort changes'
                    ];
                    break;
            }
        }

        return $options;
    }

    /**
     * Apply selected resolution to conflicts
     * 
     * @param array $currentState Current content/version state
     * @param array $proposedState Proposed changes
     * @param array $resolution Selected resolution options
     * @return array Resolved state
     */
    public function applyResolution(array $currentState, array $proposedState, array $resolution): array
    {
        $resolved = $currentState;

        foreach ($resolution as $type => $option) {
            switch ($type) {
                case 'version':
                    if ($option === 'overwrite') {
                        $resolved['version'] = $proposedState['version'];
                    }
                    break;
                    
                case 'content':
                    if ($option === 'overwrite') {
                        $resolved = array_merge($resolved, $proposedState);
                    } elseif ($option === 'selective' && isset($resolution['selected_fields'])) {
                        foreach ($resolution['selected_fields'] as $field) {
                            if (isset($proposedState[$field])) {
                                $resolved[$field] = $proposedState[$field];
                            }
                        }
                    }
                    break;
                    
                case 'permission':
                    if ($option === 'remove' && isset($proposedState['permissions'])) {
                        $resolved['permissions'] = array_diff(
                            $proposedState['permissions'],
                            $conflicts['permission']['invalid_permissions'] ?? []
                        );
                    }
                    break;
            }
        }

        return $resolved;
    }
}
