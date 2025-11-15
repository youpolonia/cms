# Environment Configuration Guide

## Overview
This CMS uses environment variables for configuration, with sensible defaults for development. Environment variables can be set in a `.env` file or directly in the server environment.

## Configuration Loading Order
1. System environment variables (highest priority)
2. `.env` file in project root
3. Hardcoded defaults in [`config.php`](config.php)

## Required Environment Variables

### Database Configuration
- `DB_HOST`: Database host (default: `localhost`)
- `DB_NAME`: Database name (default: `cms`)
- `DB_USER`: Database username (default: `cms_user`)
- `DB_PASS`: Database password (default: `secure_password_123`)

### Application Environment
- `APP_ENV`: Environment mode - `dev`, `staging`, `production` (default: `dev`)
- `DEV_MODE`: Development mode - `true` or `false` (default: `true`)

### Maintenance Mode
- `MAINTENANCE_ALLOW_IPS`: Comma-separated list of IP addresses allowed during maintenance

## Example .env File

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=my_cms_database
DB_USER=my_cms_user
DB_PASS=my_secure_password

# Application Environment
APP_ENV=production
DEV_MODE=false

# Maintenance Bypass
MAINTENANCE_ALLOW_IPS=127.0.0.1,192.168.1.100
```

## Environment-Specific Configuration

### Development (APP_ENV=dev)
- Error reporting enabled
- Debug mode active
- Development features available

### Production (APP_ENV=production)
- Error reporting disabled
- Debug mode inactive
- Performance optimizations enabled

## Environment Variable Access

The system provides two ways to access environment variables:

1. **Direct Access**: Use `$_ENV['VARIABLE_NAME']` or `getenv('VARIABLE_NAME')`
2. **Helper Functions**: Use the environment helper functions in:
   - [`includes/helpers/env.php`](includes/helpers/env.php)
   - [`includes/utilities/helpers/helpers.php`](includes/utilities/helpers/helpers.php)

## Security Considerations

- Never commit `.env` files to version control
- Use different database credentials for development and production
- Set strong passwords for production environments
- Regularly rotate database credentials

## Troubleshooting

### Common Issues

1. **Environment variables not loading**:
   - Check file permissions on `.env` file
   - Verify `.env` file is in project root
   - Check for syntax errors in `.env` file

2. **Database connection issues**:
   - Verify database credentials
   - Check database server accessibility
   - Ensure database exists

3. **Configuration not applying**:
   - Clear any opcode caches (OPcache, APC)
   - Restart web server if needed

### Debug Mode

When `DEV_MODE=true`, the system will:
- Display detailed error messages
- Show debug information
- Enable development features

In production (`DEV_MODE=false`), errors are logged but not displayed to users.

## Best Practices

1. **Development**:
   - Use `.env` file for local development
   - Keep development credentials separate from production
   - Enable debug mode for troubleshooting

2. **Production**:
   - Set environment variables at server level
   - Disable debug mode
   - Use strong, unique passwords
   - Regularly review and update credentials

3. **Staging**:
   - Mirror production environment configuration
   - Use production-like credentials
   - Enable limited debugging if needed