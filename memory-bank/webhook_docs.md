# Webhook Receiver Implementation

## Security Features
- **HMAC-SHA256 Signature Verification**
  - All webhook payloads signed with secret key
  - Verified using `X-Webhook-Signature` header
- **Timestamp Validation** 
  - 5-minute window for valid timestamps
  - Prevents replay of old requests
- **Nonce Tracking**
  - Unique nonce required per request
  - Prevents request replay attacks
- **Rate Limiting**
  - 3 retry attempts maximum
  - Exponential backoff between retries

## Integration Points
1. **Workflow System**
   - Triggers on workflow state changes
   - Payload includes workflow context
2. **Notification Service**
   - Webhook notification channel
   - Configurable per worker
3. **Content Distribution**
   - Webhook distribution channel
   - Sends published content to endpoints

## API Reference
### Register Webhook
```php
POST /api/webhooks
{
  "name": "user_registered",
  "url": "https://example.com/webhook",
  "events": ["user.created"]
}
```

### Trigger Webhook
```php
POST /api/webhooks/{name}/trigger
{
  "payload": {
    "user_id": 123,
    "timestamp": 1716600000,
    "nonce": "abc123"
  }
}
```

## Usage Examples
### Basic Webhook Registration
```php
$webhookService->registerWebhook(
  'content_published',
  'https://n8n.example.com/webhook/content',
  ['content.published']
);
```

### Handling Webhook Events
```php
$webhookService->triggerEvent('content.published', [
  'content_id' => 456,
  'published_at' => time()
]);
```

## Error Handling
- **Invalid Signature**: Returns 401 Unauthorized
- **Expired Timestamp**: Returns 400 Bad Request
- **Duplicate Nonce**: Returns 409 Conflict
- **Rate Limit Exceeded**: Returns 429 Too Many Requests

## Database Schema
```sql
CREATE TABLE `workflow_webhooks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workflow_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `url` VARCHAR(2048) NOT NULL,
  `events` JSON NOT NULL,
  `secret` VARCHAR(255) NOT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active'
);