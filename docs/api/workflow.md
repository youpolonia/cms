# Workflow API Documentation

## Endpoints

### POST /workflows/:instanceId/transitions
Execute a workflow transition
- Parameters:
  - `instanceId` (string): Workflow instance ID
  - `transitionName` (string): Transition to execute
  - `context` (array): Optional additional data
- Returns:
  - Success: `{status: "success", data: {...}}`
  - Error: `{status: "error", code: number, message: string}`

### GET /workflows/:instanceId/status
Get workflow status
- Parameters:
  - `instanceId` (string): Workflow instance ID
- Returns:
  - Success: `{status: "success", currentState: string}`
  - Error: `{status: "error", code: number, message: string}`

### DELETE /workflows/:instanceId
Cancel a workflow
- Parameters:
  - `instanceId` (string): Workflow instance ID
- Returns:
  - Success: `{status: "success", cancelled: true}`
  - Error: `{status: "error", code: number, message: string}`

## Error Codes
| Code | Description |
|------|-------------|
| 400 | Invalid transition |
| 404 | Workflow not found |
| 500 | Internal server error |