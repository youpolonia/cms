# Queue Worker Setup Guide

## Required Setup

1. Ensure Redis is installed and running:
```bash
sudo apt-get install redis-server
sudo systemctl enable redis
sudo systemctl start redis
```

2. Update .env configuration:
```ini
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## Running Workers

### Development (manual)
```bash
php artisan queue:work --tries=3 --timeout=120
```

### Production (supervisor)
Create /etc/supervisor/conf.d/cms-worker.conf:
```ini
[program:cms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/cms/artisan queue:work --tries=3 --timeout=120
autostart=true
autorestart=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/var/log/cms-worker.log
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cms-worker:*
```

## Monitoring

Check failed jobs:
```bash
php artisan queue:failed
```

Retry failed jobs:
```bash
php artisan queue:retry all
```

## Job Timeouts
- Default timeout: 120 seconds
- Adjust in job class if needed:
```php
public $timeout = 300; // 5 minutes