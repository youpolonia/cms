# Administrator Guide

## System Configuration
1. **Environment Setup**:
   - Configure `.env` file
   - Set up cron jobs for scheduled tasks
   - Configure queue workers

2. **User Management**:
   ```bash
   php artisan cms:assign-role {email} {role}
   ```
   Available roles: admin, editor, viewer

3. **Content Workflows**:
   - Configure approval chains
   - Set default publishing rules
   - Manage content types

## Monitoring
- **Dashboard**: `/admin/dashboard`
- **Logs**: `storage/logs/cms.log`
- **Queue**: Horizon dashboard at `/horizon`
- **Cache**: Redis metrics via Grafana

## Maintenance
1. **Backups**:
   ```bash
   php artisan backup:run
   ```
2. **Updates**:
   ```bash
   composer update
   php artisan migrate
   npm install && npm run prod
   ```

## Security
- Regular user permission audits
- API token rotation
- Review access logs monthly