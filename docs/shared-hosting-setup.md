# Shared Hosting Setup Guide

## Scheduled Content Publishing

To run scheduled content publishing on shared hosting:

1. **Web Request Method** (Recommended):
   - Use your hosting provider's cron job feature to call:
     ```
     curl -u username:password https://yourdomain.com/publish-scheduled
     ```
   - Set to run every 5-15 minutes depending on needs

2. **Alternative Methods**:
   - If your host supports Laravel scheduler:
     ```bash
     * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
     ```

## Security Considerations

- Keep the basic auth credentials secure
- Consider IP restricting the endpoint if possible
- Monitor execution logs for errors

## Performance Tips

- Keep execution time under 30 seconds
- Process content in small batches if needed
- Enable output caching for published content