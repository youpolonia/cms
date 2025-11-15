# Phase 10 Rollback Procedures

## Database Migrations
Execute in reverse chronological order:
```php
// Example rollback for analytics aggregates
require_once 'migrations/2025_phase10_analytics_aggregates.php';
Migration_2025_phase10_analytics_aggregates::rollback($pdo);
```

## Component Reversion
1. Restore previous versions from backup:
   - `/components/content_testing_engine/`
   - `/api/analytics/`

2. Verify checksums:
```php
if (md5_file('engine.php') !== 'backup_md5_hash') {
    throw new Exception('File integrity check failed');
}
```

## Configuration Restoration
1. Restore from `/backups/config/phase10_pre/`
2. Verify:
```php
require_once 'config_verifier.php';
ConfigVerifier::verifyPhase9Compatibility();
```

## Verification Checklist
- [ ] Database schema matches Phase 9
- [ ] All endpoints respond with Phase 9 formats
- [ ] Content testing disabled
- [ ] Analytics reports show pre-phase10 data