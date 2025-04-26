# Theme Approval Workflow API

## Submit Theme Version for Approval

`POST /themes/{theme}/versions/{version}/submit-for-approval`

Submits a theme version for approval through the configured workflow.

### Request
```json
{}
```

### Response (Success - 200)
```json
{
    "status": "success",
    "message": "Theme version submitted for approval",
    "data": {
        "approval_status": "pending",
        "current_step": "Design Review"
    }
}
```

### Response (Error - 400)
```json
{
    "status": "error",
    "message": "Failed to submit for approval: Workflow requirements not met"
}
```

## Approve Current Step

`POST /themes/{theme}/versions/{version}/approve`

Approves the current step in the approval workflow.

### Request
```json
{
    "comments": "Looks good, approved"
}
```

### Response (Success - 200)
```json
{
    "status": "success",
    "message": "Approval recorded successfully",
    "data": {
        "approval_status": "pending",
        "current_step": "QA Review"
    }
}
```

### Response (Error - 400)
```json
{
    "status": "error",
    "message": "Failed to approve: User not authorized for this step"
}
```

## Reject Current Step

`POST /themes/{theme}/versions/{version}/reject`

Rejects the current step in the approval workflow.

### Request
```json
{
    "comments": "Needs more work on responsive design",
    "reason": "Design issues"
}
```

### Response (Success - 200)
```json
{
    "status": "success",
    "message": "Rejection recorded successfully",
    "data": {
        "approval_status": "rejected",
        "rejection_reason": "Design issues"
    }
}
```

### Response (Error - 400)
```json
{
    "status": "error",
    "message": "Failed to reject: Comments are required"
}
```

## Get Approval Status

`GET /themes/{theme}/versions/{version}/approval-status`

Returns the current approval status of a theme version.

### Response (Success - 200)
```json
{
    "status": "success",
    "data": {
        "approval_status": "pending",
        "current_step": {
            "id": 1,
            "name": "Design Review",
            "order": 1
        },
        "submitted_at": "2025-04-11T21:00:00Z",
        "approved_at": null,
        "rejection_reason": null
    }
}
```

## Get Approval Steps

`GET /themes/{theme}/versions/{version}/approval-steps`

Returns all steps in the approval workflow with their current status.

### Response (Success - 200)
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "Design Review",
            "order": 1,
            "status": "approved",
            "approvers": [
                {
                    "id": 1,
                    "name": "John Doe",
                    "email": "john@example.com"
                }
            ]
        },
        {
            "id": 2,
            "name": "QA Review",
            "order": 2,
            "status": "pending",
            "approvers": [
                {
                    "id": 2,
                    "name": "Jane Smith",
                    "email": "jane@example.com"
                }
            ]
        }
    ]
}
```

## Authentication
All endpoints require authentication via Sanctum token.

## Error Responses
Common error responses include:

- `401 Unauthorized` - Invalid or missing authentication token
- `403 Forbidden` - User lacks permission for the operation
- `404 Not Found` - Theme or version not found
- `422 Unprocessable Entity` - Validation errors
