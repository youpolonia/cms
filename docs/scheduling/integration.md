# Content Scheduling Integration Guide

## Backward Compatibility
- API v1: Uses basic scheduling
- API v2: Adds priority queuing
- API v3: Adds timezone awareness

## Permission Requirements
Required permissions:
- `content.schedule.view` - View schedules
- `content.schedule.create` - Create schedules
- `content.schedule.edit` - Modify schedules
- `content.schedule.delete` - Remove schedules

## API Versioning
Current version: v3  
Specify version via:
- Header: `Accept: application/vnd.cms.v3+json`
- Query param: `?version=3`

## Webhook Events
The system emits these webhook events:
- `schedule.created`
- `schedule.updated` 
- `schedule.deleted`
- `schedule.processed`
- `schedule.failed`

Example payload:
```json
{
  "event": "schedule.created",
  "data": {
    "id": "sch_123",
    "content_id": "cont_456",
    "publish_at": "2025-05-10T09:00:00Z"
  }
}
```

## Best Practices
1. Always specify API version
2. Implement webhook verification
3. Handle timezone conversion client-side
4. Use exponential backoff for retries
5. Monitor queue depth and worker health

## Rate Limits
- 60 requests/minute per API key
- 10 schedules/minute per content item
- 100 schedules/hour per user

## Example Integration Code

```javascript
// Create a schedule
async function createSchedule(contentId, publishAt, priority = 3) {
  const response = await fetch(`/api/content/${contentId}/schedules`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${apiKey}`
    },
    body: JSON.stringify({
      publish_at: publishAt,
      priority: priority
    })
  });
  
  if (!response.ok) throw new Error('Failed to create schedule');
  return await response.json();
}