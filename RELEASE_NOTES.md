# Release Notes

## 2025-09-20 — PROD Lockdown
- DEV_MODE forced to false (PHP gate)
- Test endpoints (Scheduler/Audit): DEV gate + GET-only; admin/.htaccess deny pattern
- dev_selfcheck.php removed from PROD
- Logs rotation (1MB) for both tasks
- Acceptance (DEV): status/run/logs PASS; POST → 405; PROD: wszystkie testowe endpointy 403