# WorkflowService API Specification

## Endpoints

### Single Workflow Transition
`POST /api/workflow/transition`

**Parameters**:
- `instance_id` (string): The workflow instance ID
- `action` (string): Transition action to perform

**Response**:
```json
{
  "success": boolean,
  "new_state": string,
  "timestamp": string
}
```

### Batch Workflow Transition
`POST /api/workflow/batch_transition`

**Parameters**:
- `instance_ids` (array): Array of workflow instance IDs
- `action` (string): Transition action to perform

**Response**:
```json
{
  "processed": integer,
  "failed": integer,
  "timestamp": string
}
```

## Error Codes

| Code | Description |
|------|-------------|
| 4001 | Invalid instance ID |
| 4002 | Invalid transition action |
| 5001 | Database error |
| 5002 | Tenant isolation violation |

## Test Endpoints

### Test Single Transition
`GET /api/test/workflow.php?action=test_transition`

### Test Batch Transition
`GET /api/test/workflow.php?action=test_batch`