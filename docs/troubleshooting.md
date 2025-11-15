# Troubleshooting Guide

## Common Issues and Solutions

### 500 Internal Server Error
1. **Check PHP version**: Ensure server runs PHP 8.1+
2. **Verify file permissions**:
   ```bash
   chmod -R 755 includes/
   chmod -R 775 storage/
   ```
3. **Review error logs**: Check server error logs for details

### Database Connection Issues
1. Verify credentials in `config/database.php`
2. Check database server is running
3. Ensure user has proper permissions:
   ```sql
   GRANT ALL PRIVILEGES ON cms_database.* TO 'cms_user'@'localhost';
   ```

### File Upload Problems
1. Check `upload_max_filesize` in php.ini
2. Verify directory permissions:
   ```bash
   chmod -R 775 public/uploads/
   ```
3. Check open_basedir restrictions

### Admin Panel Not Loading
1. Clear browser cache
2. Verify .htaccess rules
3. Check for JavaScript errors in browser console

### Email Not Sending
1. Verify SMTP settings in `config/mail.php`
2. Check spam folder
3. Test with different email providers

## Performance Issues
1. **Enable caching** in `config/cache.php`
2. **Optimize images** before uploading
3. **Reduce plugins** if experiencing slowdowns

## Security Issues
1. **Change default admin credentials**
2. **Rotate APP_KEY** if compromised
3. **Update regularly** when new versions are available

## Getting Help
1. Check the [Deployment Guide](../deploy/README.md)
2. Review server error logs
3. Contact your hosting provider for server-specific issues