# Deployment Requirements

## Infrastructure

### Minimum Requirements
- **CPU**: 2 cores
- **Memory**: 4GB RAM
- **Storage**: 20GB SSD
- **OS**: Linux (Ubuntu 20.04+ recommended)

### Recommended Production
- **CPU**: 4+ cores
- **Memory**: 8GB+ RAM
- **Storage**: 50GB+ SSD with backups
- **Load Balancer**: For high availability

## Software Dependencies
- **PHP**: 8.2+ with extensions:
  - BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Cache**: Redis 6.0+
- **Queue**: Supervisor for worker processes
- **Web Server**: Nginx or Apache
- **Node.js**: 18+ for asset compilation

## Network Requirements
- Port 80/443 open for web traffic
- Outbound connections to:
  - MCP services
  - Package repositories
  - External APIs (if used)

## Environment Variables
Key required variables:
- `APP_ENV=production`
- `APP_KEY` (generated)
- Database credentials
- Redis configuration
- MCP service URLs and keys