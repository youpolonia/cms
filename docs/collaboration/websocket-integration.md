# WebSocket Integration Guide

## Overview
The CMS uses WebSockets for real-time collaboration features including:
- Presence indicators
- Live content editing
- Comment threads
- User mentions

## Configuration
WebSocket server is configured in `config/websockets.php`:
```php
return [
    'dashboard' => [
        'port' => env('LARAVEL_WEBSOCKETS_PORT', 6001),
    ],
    'apps' => [
        [
            'id' => env('PUSHER_APP_ID'),
            'name' => env('APP_NAME'),
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'enable_client_messages' => true,
            'enable_statistics' => true,
        ],
    ],
    'handlers' => [
        'collaboration' => App\WebSockets\CollaborationHandler::class,
    ]
];
```

## Message Types
The CollaborationHandler processes these message types:

### 1. Presence Updates
- Tracks active users in a collaboration session
- Broadcasts join/leave events
- Example payload:
```json
{
  "type": "presence",
  "action": "join|leave",
  "session_id": "123",
  "user_id": 456
}
```

### 2. Content Updates
- Handles real-time content changes
- Uses optimistic locking to prevent conflicts
- Example payload:
```json
{
  "type": "update",
  "session_id": "123",
  "changes": {
    "content": {
      "text": "Updated content"
    }
  },
  "version": 5
}
```

### 3. Comments
- Manages threaded comments
- Supports inline comments with text selections
- Example payload:
```json
{
  "type": "comment",
  "session_id": "123",
  "text": "This needs revision",
  "selection": {
    "start": 10,
    "end": 20
  }
}
```

### 4. Mentions
- Notifies mentioned users
- Links to specific comments
- Example payload:
```json
{
  "type": "mention",
  "session_id": "123",
  "mentioned_user_id": 789,
  "comment_id": 456
}
```

## Conflict Resolution
The system implements optimistic locking:
1. User acquires edit lock for a content section
2. Changes are validated and versioned
3. Conflicts are detected if multiple users edit same section within 5 seconds
4. Conflicts are resolved through:
   - Last-write-wins (default)
   - Manual merge (optional)

```php
// Example conflict detection
public function detectConflicts(Content $content, User $user, string $section, array $changes): ?array
{
    $lockKey = "content:{$content->id}:lock:{$section}";
    $lastEditTime = Redis::get("{$lockKey}:timestamp");
    $conflictingUserId = Redis::get($lockKey);

    if ($lastEditTime && $conflictingUserId && $conflictingUserId != $user->id) {
        $lastEditTime = \Carbon\Carbon::parse($lastEditTime);
        
        if ($lastEditTime->diffInSeconds(now()) < $this->conflictThreshold) {
            return [
                'content_id' => $content->id,
                'section' => $section,
                'user_id' => $user->id,
                'conflicting_user_id' => $conflictingUserId,
                'changes' => $changes
            ];
        }
    }
    return null;
}
```

## Client Implementation
Frontend should:
1. Connect to WebSocket server
2. Subscribe to collaboration channels
3. Handle incoming messages
4. Send user actions

Example connection:
```javascript
const socket = new WebSocket(`ws://${window.location.hostname}:6001`);

socket.onmessage = (event) => {
  const message = JSON.parse(event.data);
  switch(message.type) {
    case 'presence':
      updatePresenceIndicator(message.data);
      break;
    case 'update':
      applyContentUpdates(message.data);
      break;
    // ... other message types
  }
};
```

## Best Practices
1. Use exponential backoff for reconnection
2. Queue messages during disconnection
3. Validate all incoming messages
4. Limit update frequency to 100ms
5. Use compression for large content updates