# Conflict Resolution Integration Proposal

## WorkflowService Modifications

1. Add ConflictResolutionService dependency:
```php
public function __construct($db, ConditionEvaluator $conditionEvaluator, ConflictResolutionService $conflictResolver)
{
    $this->conflictResolver = $conflictResolver;
    // ... existing code
}
```

2. Enhance executeContentPublish():
```php
private function executeContentPublish(array $config, array $context)
{
    // Check for conflicts
    $conflicts = $this->conflictResolver->detectConflicts(
        $context['content_id'],
        $context['version_id']
    );

    if (!empty($conflicts)) {
        $resolution = $this->conflictResolver->resolveConflicts(
            $conflicts,
            $config['resolution_strategy'] ?? 'abort'
        );
        
        if ($resolution->shouldAbort()) {
            throw new ConflictException($resolution->getMessage());
        }
    }

    // Proceed with publishing
    // ... existing publishing logic
}
```

## API Endpoint Enhancements

1. Add conflict detection to scheduling endpoint:
```php
// In scheduling controller
public function scheduleContent(Request $request)
{
    $conflicts = $this->conflictService->checkScheduleConflicts(
        $request->input('content_id'),
        $request->input('publish_at')
    );

    if ($conflicts->hasConflicts()) {
        return response()->json([
            'conflicts' => $conflicts->toArray(),
            'resolution_options' => $conflicts->getResolutionOptions()
        ], 409);
    }

    // ... existing scheduling logic
}