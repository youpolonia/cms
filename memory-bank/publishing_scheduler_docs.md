# Content Publishing Scheduler Documentation

## Overview
The publishing scheduler allows content to be automatically published/unpublished at specified future dates. Key features:
- Future publish/unpublish dates for content items
- Automatic status updates via cron or manual trigger
- Permission-controlled access

## Database Changes
Added to `content_items` table:
- `publish_at` (DATETIME, nullable) - Scheduled publish date/time
- `unpublish_at` (DATETIME, nullable) - Scheduled unpublish date/time

## Implementation Details

### WorkflowManager Updates
Added methods:
1. `publish($contentId)` - Publishes content immediately if not scheduled
2. `schedule($contentId, $publishAt, $unpublishAt)` - Sets future dates
3. `processScheduledContent()` - Processes pending actions

### Scheduler Components
1. **Cron Script**: `scripts/scheduler.php`
   - Processes scheduled content
   - Logs results to `logs/scheduler.log`
   - Returns JSON status

2. **Admin Trigger**: `admin/workflow/run_scheduler.php`
   - Manual execution interface
   - Requires `run_scheduler` permission
   - Shows results summary

## Usage Instructions

### Setting Scheduled Dates
1. Edit content item
2. Set `Publish At` and/or `Unpublish At` dates
3. Save changes

### Automatic Processing
Set up cron job to run every 5-15 minutes:
```bash
*/10 * * * * php /path/to/cms/scripts/scheduler.php
```

### Manual Processing
1. Navigate to Admin > Workflow > Run Scheduler
2. Click "Run Scheduler Now"

## Technical Notes
- Timezone: All dates processed in server timezone
- Permissions:
  - `schedule_content` - Required to set dates
  - `run_scheduler` - Required for manual trigger
- Logs: Written to `logs/scheduler.log`