# CMS Maintenance — PROD

## Monthly
- Verify `DEV_MODE=false` in `config.php`.
- Confirm test endpoints return **403** (status/run/logs for Scheduler & Audit).
- Check `.htaccess` deny pattern covers all test endpoints.
- Rotate and archive logs (`logs/*_manager.log` → keep last 90 days).
- Snapshot `VERSION` and top section of `RELEASE_NOTES.md`.
- Back up `/var/www/html/cms` and database.

## After Any Change
- Run acceptance in DEV (status/run/logs PASS; POST → 405).
- Ensure no new PHP files under `/admin/test-*` are publicly reachable.
- Update `RELEASE_NOTES.md` and bump `VERSION` if applicable.

## Incident Quick Checks
- Recent errors in webserver/PHP error logs.
- File integrity: unexpected new/changed files under `/admin` or `/core`.
- Access control: confirm deny rules still effective.