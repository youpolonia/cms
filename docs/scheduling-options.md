# Content Scheduling Options

## Basic Scheduling
```json
{
  "content_id": 123,
  "action": "publish|unpublish",
  "scheduled_at": "2025-05-05 14:00:00",
  "timezone": "America/New_York",
  "grace_period_minutes": 15
}
```

## Recurrence Patterns
```json
{
  "recurrence_pattern": {
    "FREQ": "DAILY|WEEKLY|MONTHLY|YEARLY",
    "INTERVAL": 1,
    "COUNT": 10,
    "BYDAY": "MO,WE,FR",
    "BYMONTHDAY": "15",
    "UNTIL": "2025-12-31"
  }
}
```

## Conflict Resolution
```json
{
  "conflict_resolution_rules": {
    "skip": true
  }
}
```
OR
```json
{
  "conflict_resolution_rules": {
    "reschedule": {
      "minutes": 30
    }
  }
}
```

## Notifications
```json
{
  "notify_on_success": true,
  "notify_on_failure": true
}
```

## Example: Complete Schedule
```json
{
  "content_id": 123,
  "action": "publish",
  "scheduled_at": "2025-05-05 09:00:00",
  "timezone": "America/New_York",
  "grace_period_minutes": 30,
  "recurrence_pattern": {
    "FREQ": "WEEKLY",
    "BYDAY": "MO",
    "COUNT": 4
  },
  "conflict_resolution_rules": {
    "reschedule": {
      "minutes": 15
    }
  },
  "notify_on_success": true
}