# Backup and Recovery Runbook

## Purpose
This document outlines procedures for backing up and restoring the CMS system.

## Backup Procedures

### Database Backups
1. Full database backup (daily):
   ```bash
   mysqldump -u [user] -p[password] [database] > /backups/db/full_backup_$(date +\%Y\%m\%d).sql
   ```
2. Critical tables backup (hourly):
   ```bash
   mysqldump -u [user] -p[password] [database] contents content_versions users > /backups/db/critical_tables_$(date +\%Y\%m\%d_\%H).sql
   ```

### File System Backups
1. Storage directory backup:
   ```bash
   tar -czvf /backups/storage/storage_$(date +\%Y\%m\%d).tar.gz storage/app/
   ```
2. Configuration backup:
   ```bash
   tar -czvf /backups/config/config_$(date +\%Y\%m\%d).tar.gz config/ .env
   ```

## Recovery Procedures

### Database Recovery
1. Restore full database:
   ```bash
   mysql -u [user] -p[password] [database] < /backups/db/full_backup_[date].sql
   ```
2. Restore critical tables only:
   ```bash
   mysql -u [user] -p[password] [database] < /backups/db/critical_tables_[date].sql
   ```

### File System Recovery
1. Restore storage:
   ```bash
   tar -xzvf /backups/storage/storage_[date].tar.gz -C /
   ```
2. Restore configuration:
   ```bash
   tar -xzvf /backups/config/config_[date].tar.gz -C /
   ```

## Verification
1. Check backup logs:
   ```bash
   cat /var/log/backup.log
   ```
2. Test restore on staging environment monthly

## Automated Backup Schedule
- Full database: Daily at 2AM
- Critical tables: Hourly
- Storage: Weekly
- Configuration: Daily

## Retention Policy
- Daily backups: 7 days
- Weekly backups: 4 weeks
- Monthly backups: 12 months