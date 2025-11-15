# Production Deployment Checklist

## Pre-Deployment
- [ ] Verify all config files are production-ready
- [ ] Confirm database migrations are complete
- [ ] Validate all environment variables:
  - [ ] Copy .env.production to .env
  - [ ] Set secure production values
  - [ ] Verify file permissions (600)
- [ ] Test all critical endpoints
- [ ] Backup current production files

## Deployment Package
- [ ] Core CMS files
- [ ] Required plugins
- [ ] Theme assets
- [ ] Configuration files
- [ ] Migration scripts

## Deployment Steps
1. Upload package to production server
2. Verify file permissions
3. Test basic functionality
4. Monitor error logs
5. Verify API endpoints

## Post-Deployment
- [ ] Monitor performance metrics
- [ ] Check error logs
- [ ] Verify cron jobs
- [ ] Test admin functionality
- [ ] Document deployment version
- [ ] Verify environment security:
  - [ ] .env not committed to version control
  - [ ] File permissions correct
  - [ ] Sensitive values not logged

## Security Considerations
- Rotate APP_KEY for production
- Use different credentials for each environment
- Restrict .env file access
- Audit all environment variables