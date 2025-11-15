# Content Approval Workflow Documentation

## Components

### Dashboard Widget
- Location: `admin/js/approval_dashboard.js`
- Auto-refreshes stats every 30 seconds
- Displays counts for pending/approved/rejected content

### Approval Queue
- Location: `admin/js/approval_queue.js`
- Handles approve/reject actions
- Removes approved/rejected items with fade animation
- CSRF token protected

### History Viewer
- Location: `admin/js/approval_history.js`
- Filter by status (all/approved/rejected/pending)
- Filter by date
- Client-side filtering for performance

### API Endpoints
- Location: `api/v1/ApprovalWorkflowController.php`
- `/api/v1/approval/stats` - Get approval statistics
- `/api/v1/approval/{id}/approve` - Approve content
- `/api/v1/approval/{id}/reject` - Reject content
- `/api/v1/approval/history` - Get approval history

## Permissions
- `content_approval` - Required for approve/reject actions
- `content_approval_view` - Required for viewing stats/history

## Audit Logging
All approval actions are logged with:
- User ID
- Action type
- Content ID
- Timestamp