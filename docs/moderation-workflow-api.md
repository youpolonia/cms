# Moderation Workflow API Documentation

## Dashboard Endpoints

### Get Queue Stats
`GET /api/moderation/dashboard/queue-stats`

Returns statistics about the moderation queue.

**Response:**
```json
{
  "pending": 5,
  "in_review": 3,
  "overdue": 2,
  "escalated": 1
}
```

### Get SLA Compliance
`GET /api/moderation/dashboard/sla-compliance`

Returns SLA compliance metrics.

**Response:**
```json
{
  "total": 10,
  "on_time": 8,
  "compliance_rate": 80.0
}
```

### Get Workflow Progress
`GET /api/moderation/dashboard/workflow-progress`

Returns workflow progress statistics.

**Response:**
```json
{
  "pending": 5,
  "in_progress": 3,
  "completed": 2
}
```

### Get Escalation Tracking
`GET /api/moderation/dashboard/escalation-tracking`

Returns recent escalation cases.

**Response:**
```json
[
  {
    "content_id": 123,
    "stage": "Legal Review",
    "escalated_at": "2025-05-04T15:00:00Z",
    "moderator": "John Doe",
    "hours_overdue": 12
  }
]
```

## Notification Integration

The moderation workflow integrates with the existing notification system:

1. **Overdue Notifications**:
   - Sent when content exceeds SLA time
   - Uses `ModerationOverdueNotification` mail class
   - Template: `resources/views/emails/moderation/overdue.blade.php`

2. **Escalation Notifications**:
   - Sent when content is escalated
   - Uses `ModerationEscalationNotification` mail class
   - Template: `resources/views/emails/moderation/escalation.blade.php`