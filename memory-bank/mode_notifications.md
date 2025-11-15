# Mode Notification System

## Current Notifications (2025-06-03)
- **Progress Log Rotation**: All modes should now:
  - Check memory-bank/progress/progress_*.md for historical logs
  - Use memory-bank/progress.md only for current session
  - Limit reads to specific line ranges when possible

## Notification Format
```
## [yyyy-mm-dd] Notification Type
- Affected: [list of modes]
- Action: [required changes]
- Reference: [related files]
```

## Access Rules
1. Orchestrator maintains notifications
2. All modes must check this file on startup
3. Notifications remain active for 7 days