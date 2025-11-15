# AI Content Generation Deployment Checklist

## Pre-Deployment
- [ ] Verify all database migrations are run (`php artisan migrate`)
- [ ] Confirm AI service API keys are configured in .env
- [ ] Set appropriate rate limits in config/ai.php
- [ ] Validate content moderation settings in config/content_moderation.php
- [ ] Test generation endpoints in staging environment

## Environment Configuration
- [ ] Set `AI_CONTENT_ENABLED=true` in .env
- [ ] Configure `OPENAI_API_KEY` in .env
- [ ] Set `AI_CONTENT_RATE_LIMIT` in .env
- [ ] Configure `AI_CONTENT_MODERATION_LEVEL` in .env

## Monitoring Setup
- [ ] Enable Prometheus metrics endpoint
- [ ] Configure Grafana dashboard for:
  - Generation success rates
  - API response times
  - Content moderation stats
  - User usage metrics
- [ ] Set up alerts for:
  - Failed generation attempts
  - Rate limit breaches
  - Moderation queue backlog

## Backup Procedures
- Automated backups via `scripts/backup-ai-content.sh`:
  - Database backups (compressed SQL dumps)
  - Configuration files backup
  - Content versions backup
  - Automatic retention (7 days)
- Manual backup steps:
  1. Run `chmod +x scripts/backup-ai-content.sh` to make executable
  2. Add to cron for daily execution: `0 2 * * * /var/www/html/cms/scripts/backup-ai-content.sh`
  3. Verify backups in `/var/backups/ai-content`
- Content versioning:
  - All generated content automatically versioned
  - Version history retained for 30 days
- Configuration backups:
  - Daily backup of config files
  - Version-controlled deployment scripts

## Rollback Procedures

### Database Rollback
1. Single migration rollback:
   ```bash
   php artisan migrate:rollback --step=1
   ```
2. Multiple migrations rollback:
   ```bash
   php artisan migrate:rollback --step=3
   ```
3. Full database restoration:
   ```bash
   mysql -u [user] -p [database] < /var/backups/ai-content/ai-content-db-[timestamp].sql.gz
   ```

### Configuration Rollback
1. Restore config files:
   ```bash
   tar -xzf /var/backups/ai-content/ai-content-config-[timestamp].tar.gz -C /
   ```
2. Restore .env file from backup
3. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Content Restoration
1. Via content versioning system:
   ```bash
   php artisan content:restore [version_id]
   ```
2. Bulk restoration from backup:
   ```bash
   php artisan content:restore:batch --backup=[backup_timestamp]
   ```

### Full Deployment Rollback
1. Stop current services
2. Redeploy previous container/image version
3. Restore database if needed
4. Verify rollback:
   ```bash
   php artisan ai:status
   php artisan queue:restart
   ```

### Verification Steps
1. Check generation API endpoints
2. Verify moderation queue processing
3. Confirm monitoring metrics are reporting
4. Validate backup system functionality