# Deployment Guidelines

## File System Requirements
- **Root Directory**: 755 permissions
- **Configuration Files**: 644 permissions
- **Uploads Directory**: 775 permissions
- **Cache Directory**: 777 permissions (must be writable)

## Security Audit Checklist
1. Verify all PHP files have proper ownership
2. Confirm no world-writable files except cache directory
3. Check for proper .htaccess restrictions:
```apache
# Deny access to sensitive files
<FilesMatch "\.(env|log|sql)$">
  Deny from all
</FilesMatch>
```

4. Validate directory structure:
```
/var/www/html/cms/
  ├── admin/ (755)
  ├── includes/ (755)
  ├── uploads/ (775)
  └── cache/ (777)
```

## FTP Deployment
1. Always use SFTP/FTPS
2. Deploy in this order:
   - Core CMS files first
   - Configuration files second
   - Plugins last

3. Post-deployment checks:
```php
// Verify system requirements
Deployment::verifyRequirements();

// Run security audit
Deployment::runSecurityAudit();
```

## Rollback Procedure
1. Maintain versioned backups in `/backups/`
2. Use timestamped directories (YYYYMMDD-HHMMSS)
3. Include rollback instructions in each backup