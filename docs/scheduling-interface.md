# Scheduling Interface Specification

## API Endpoints

### Base URL: `/api/v1/schedules`

| Method | Endpoint | Description | Permissions |
|--------|----------|-------------|-------------|
| GET | `/` | List all scheduled content | viewAny:ScheduledEvent |
| POST | `/` | Create new schedule | create:ScheduledEvent |
| GET | `/{id}` | Get schedule details | view:ScheduledEvent |
| PUT | `/{id}` | Update schedule | update:ScheduledEvent |
| DELETE | `/{id}` | Delete schedule | delete:ScheduledEvent |
| GET | `/check-conflicts` | Check for scheduling conflicts | viewAny:ScheduledEvent |
| POST | `/bulk` | Bulk create schedules | create:ScheduledEvent |
| PUT | `/bulk` | Bulk update schedules | update:ScheduledEvent |
| DELETE | `/bulk` | Bulk delete schedules | delete:ScheduledEvent |

## Request/Response Examples

**Create Schedule:**
```json
{
  "content_id": 123,
  "publish_time": "2025-05-15 08:00:00",
  "expire_time": "2025-06-15 08:00:00",
  "priority": 1
}
```

**Schedule Response:**
```json
{
  "id": 456,
  "content_id": 123,
  "publish_time": "2025-05-15 08:00:00",
  "expire_time": "2025-06-15 08:00:00",
  "priority": 1,
  "status": "scheduled",
  "created_at": "2025-05-12T15:30:00Z",
  "updated_at": "2025-05-12T15:30:00Z"
}
```

## Error Codes
- 400: Invalid schedule data
- 403: Insufficient permissions
- 404: Schedule not found
- 409: Scheduling conflict