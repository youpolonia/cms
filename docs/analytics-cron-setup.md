# Analytics Processing Cron Job Setup

## Daily Processing

To schedule the daily analytics processing, add the following cron job:

```bash
# Run daily at 1:00 AM
0 1 * * * /usr/bin/php /var/www/html/cms/cli.php process-analytics >> /var/www/html/cms/storage/logs/analytics-process.log 2>&1
```

## Verification

After setting up the cron job, you can verify it works by manually running:

```bash
php /var/www/html/cms/cli.php process-analytics
```

## Log Rotation

Configure log rotation by creating `/etc/logrotate.d/cms-analytics` with:

```
/var/www/html/cms/storage/logs/analytics-process.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 640 www-data www-data
    sharedscripts
    postrotate
        /usr/bin/systemctl reload rsyslog >/dev/null 2>&1 || true
    endscript
}
```

## Monitoring

The system will log processing results to:
- `storage/logs/analytics-process.log` - CLI output
- `storage/logs/analytics-errors.log` - Any processing errors

## Troubleshooting

If processing fails:
1. Check disk space
2. Verify database connection
3. Check for lock files in `storage/framework/cache/`
4. Review the analytics_events table size