# System Startup Runbook

## Purpose
This document outlines the procedures for starting up the CMS system in various environments.

## Prerequisites
- Database server running
- Redis cache available
- Required environment variables set

## Startup Procedures

### Development Environment
1. Run database migrations:
   ```bash
   php artisan migrate
   ```
2. Start the queue worker:
   ```bash
   php artisan queue:work
   ```
3. Start the development server:
   ```bash
   php artisan serve
   ```

### Staging Environment
1. Pull latest code:
   ```bash
   git pull origin staging
   ```
2. Run migrations:
   ```bash
   php artisan migrate --force
   ```
3. Restart queue workers:
   ```bash
   sudo systemctl restart cms-queue
   ```
4. Restart web server:
   ```bash
   sudo systemctl restart apache2
   ```

### Production Environment
1. Deploy using CI/CD pipeline
2. Verify database backups exist
3. Monitor startup logs:
   ```bash
   journalctl -u cms-web -f
   ```

## Verification Steps
1. Check health endpoint:
   ```bash
   curl http://localhost/health
   ```
2. Verify queue worker status:
   ```bash
   sudo systemctl status cms-queue
   ```
3. Check error logs:
   ```bash
   tail -f storage/logs/cms.log
   ```

## Troubleshooting
- If migrations fail, check `database/migrations/` for pending migrations
- If queue worker fails, check Redis connection in `.env`
- If web server fails, check port conflicts and PHP version