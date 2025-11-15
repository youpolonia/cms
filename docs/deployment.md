# Deployment Guide

## System Requirements
- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.6+
- Redis 6.2+
- Node.js 18+
- Composer 2.5+

## Development Setup
1. Clone repository:
   ```bash
   git clone https://github.com/your-repo/cms.git
   cd cms
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Database setup:
   ```bash
   php artisan migrate --seed
   ```

5. Start development servers:
   ```bash
   php artisan serve
   npm run dev
   ```

## Production Deployment
### Docker Setup
```bash
docker-compose up -d --build
```

### Kubernetes (Helm)
```bash
helm install cms ./charts/cms
```

### Configuration
Required environment variables:
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=mysql
DB_DATABASE=cms
DB_USERNAME=cms_user
DB_PASSWORD=secure_password

REDIS_HOST=redis
OPENAI_API_KEY=your_key
```

## Maintenance
- Scheduled backups:
  ```bash
  php artisan backup:run
  ```
- Queue workers:
  ```bash
  php artisan queue:work --daemon
  ```
- Scheduled tasks (cron):
  ```bash
  * * * * * cd /path/to/cms && php artisan schedule:run >> /dev/null 2>&1
  ```

## Monitoring
- Grafana dashboards available at `/grafana`
- Prometheus metrics endpoint: `/metrics`
- Health check endpoint: `/health`