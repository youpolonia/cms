# Analytics Processing Cron Job Setup

## Required Configuration

1. Add to your `.env` file:
```bash
CRON_SECRET=your_secure_token_here
ANALYTICS_CRON_NOTIFICATION_EMAIL=admin@example.com
```

## Cron Job Command

Add this to your system's crontab (run `crontab -e`):
```bash
0 3 * * * curl -X POST -H "X-Cron-Token: your_secure_token_here" https://yourdomain.com/api/analytics/process > /var/log/analytics_cron.log 2>&1
```

## Verification

After setup, you can test with:
```bash
curl -X POST -H "X-Cron-Token: your_secure_token_here" https://yourdomain.com/api/analytics/process
```

## Monitoring

Check logs at:
- System cron logs: `/var/log/cron`
- Application logs: `storage/logs/analytics.log`
- Specific output: `/var/log/analytics_cron.log`

## Retry Logic

The system will automatically retry failed jobs (3 attempts with 5 minute delays) as configured in `config/analytics.php`.