# A/B Testing API Standards

## Endpoints

### Create Test
`POST /api/ab-tests`

**Request Body:**
```json
{
  "content_id": "integer|required",
  "name": "string|required|max:255",
  "status": "string|in:draft,active,paused,completed",
  "control_group_percentage": "integer|min:1|max:100",
  "variants": "array|required",
  "metrics": "array|required",
  "anonymize_data": "boolean"
}
```

### Get Tests for Content
`GET /api/contents/{content_id}/ab-tests`

### Record Participation
`POST /api/ab-tests/{test_id}/participate`

**Request Body:**
```json
{
  "variant": "string|required"
}
```

### Get Test Results
`GET /api/ab-tests/{test_id}/results`

## GDPR Compliance

- All participant data must be anonymized when `anonymize_data` is true
- IP addresses must be hashed
- User identifiers must be replaced with anonymous tokens
- Audit logs must retain original data for 90 days before purging

## Workflow Integration

- All test state changes must be logged
- Participation events must be logged
- Logs must include:
  - Timestamp
  - Action type
  - User/participant reference (anonymized if applicable)
  - Test ID
  - Variant selected

## Workflow Enhancements

### Status Values
- `pending_approval` - Requires manager approval
- `approved` - Approved by manager
- `rejected` - Rejected by manager
- `scheduled` - Approved and scheduled
- `archived` - Completed and archived

### Performance Thresholds
- Test creation: ≤0.3s
- Result calculation: ≤1.5s
- Participation recording: ≤0.2s
- Concurrent operations: 10 max

### Locking Mechanism
```php
DB::transaction(function () use ($testId) {
    $test = ABTest::lockForUpdate()->find($testId);
    // Process participation
});
```

### Audit Logging
- `/api/ab-tests/{test_id}/audit` - Get workflow audit log
- `/api/ab-tests/{test_id}/performance` - Get performance metrics