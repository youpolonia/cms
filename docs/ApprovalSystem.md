# Content Approval Workflow System

## Overview
The approval workflow system provides a structured process for reviewing and approving content changes. It implements a multi-level approval process with role-based access control and comprehensive audit logging.

## Workflow States

| State | Description | Required Role |
|-------|-------------|---------------|
| draft | Initial state, editable by author | None |
| pending_review | Submitted for first-level review | reviewer |
| pending_approval | Approved by reviewer, needs final approval | approver |
| approved | Fully approved and published | None |
| rejected | Rejected at any stage | None |

## API Usage

### Submit Content
```php
$workflow = new ApprovalWorkflow($db);
$success = $workflow->submit($contentId, $user);
```

### Process Approval/Rejection
```php
$success = $workflow->processAction(
    $contentId,
    $user,
    'approve', // or 'reject'
    'Optional comment'
);
```

## Required Permissions
- `content.edit`: Required to submit content
- `content.review`: Required for reviewers
- `content.approve`: Required for approvers

## Audit Logging
All workflow actions are logged with:
- Timestamp
- User ID
- Content ID
- Action taken
- Optional comment
- New status

Logs are stored in `logs/approvals/` directory with daily rotation.

## Error Handling
The system will:
- Rollback database changes on errors
- Log detailed error information
- Return false for failed operations

## Testing
See `tests/Phase13/ApprovalTest.php` for complete test coverage including:
- Happy path scenarios
- Unauthorized access attempts
- Invalid input handling
- Transaction rollback cases