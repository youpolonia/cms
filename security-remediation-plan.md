# Security Remediation Plan - Critical Database Vulnerabilities

## Emergency Response Plan

### Phase 1: Immediate Actions (Within 24 hours)

#### 1.1 Credential Rotation
**Priority:** CRITICAL
**Actions:**
- Rotate MySQL root password immediately
- Change all database user passwords (cms_user, test_user, etc.)
- Update application configuration with new credentials
- Verify all services reconnect successfully

#### 1.2 File Removal
**Priority:** CRITICAL
**Files to Delete:**
- [`debug_php_mysql_recovery.php`](debug_php_mysql_recovery.php) - Contains password reset functionality
- [`install/reset.php`](install/reset.php) - Installation script with hardcoded credentials
- [`data_models/utilities/setup_cms_database.php`](data_models/utilities/setup_cms_database.php) - Setup script with credentials

**Verification:**
```bash
# Check files are removed
rm -f debug_php_mysql_recovery.php
rm -f install/reset.php  
rm -f data_models/utilities/setup_cms_database.php
```

#### 1.3 Cache Purge
**Priority:** HIGH
**Actions:**
- Delete all cache files in `cms_storage/cache/` directory
- Clear public cache files in `public/cache/`
- Restart web server to ensure clean state

```bash
# Clear cache directories
rm -rf cms_storage/cache/*
rm -rf public/cache/*
```

#### 1.4 File Permissions
**Priority:** HIGH
**Actions:**
- Set strict permissions on configuration files
- Restrict access to cache directories
- Verify web server user has minimal required permissions

```bash
# Set secure permissions
chmod 640 config/*.php
chmod 750 cms_storage/cache/
chmod 750 public/cache/
```

### Phase 2: Architectural Improvements (1-2 weeks)

#### 2.1 Centralized Database Class
**Priority:** HIGH
**Implementation:**
Create [`includes/Database.php`](includes/Database.php) with secure connection handling:

```php
<?php
class Database {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            $config = self::loadConfig();
            self::$connection = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        }
        return self::$connection;
    }
    
    private static function loadConfig() {
        // Load from secure configuration
        $env = getenv('APP_ENV') ?: 'production';
        $configFile = __DIR__ . "/../config/database.{$env}.php";
        
        if (file_exists($configFile)) {
            return require $configFile;
        }
        
        throw new Exception("Database configuration not found for environment: {$env}");
    }
}
```

#### 2.2 Environment-based Configuration
**Priority:** HIGH
**Structure:**
```
config/
  database.production.php
  database.staging.php  
  database.development.php
  database.local.php
```

**Example [`config/database.production.php`](config/database.production.php):**
```php
<?php
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_DATABASE') ?: 'cms_production',
    'username' => getenv('DB_USERNAME') ?: 'cms_user',
    'password' => getenv('DB_PASSWORD') ?: '', // Must be set via environment
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];
```

#### 2.3 Credential Encryption
**Priority:** MEDIUM
**Implementation:**
- Use OpenSSL for credential encryption
- Store encrypted credentials in environment variables
- Implement key rotation policy

### Phase 3: Prevention and Monitoring (Ongoing)

#### 3.1 Security Scanning
**Priority:** MEDIUM
**Tools:**
- Implement pre-commit hooks to detect hardcoded credentials
- Use static analysis tools (PHPStan, Psalm) with security rules
- Regular security audits using this plan as baseline

#### 3.2 Monitoring
**Priority:** MEDIUM
**Implementation:**
- File integrity monitoring for configuration files
- Database connection logging
- Alerting on suspicious access patterns

#### 3.3 Training
**Priority:** LOW
**Actions:**
- Developer security training sessions
- Secure coding guidelines documentation
- Regular security awareness updates

## Implementation Timeline

### Day 1-2: Emergency Response
- [ ] Rotate all database credentials
- [ ] Remove critical vulnerable files
- [ ] Clear cache directories
- [ ] Set secure file permissions

### Week 1: Core Architecture
- [ ] Implement centralized Database class
- [ ] Create environment-based configuration
- [ ] Update all code to use new Database class

### Week 2: Security Enhancements
- [ ] Implement credential encryption
- [ ] Set up monitoring and alerting
- [ ] Conduct developer training

### Ongoing: Maintenance
- [ ] Regular security audits (quarterly)
- [ ] Automated security scanning
- [ ] Continuous improvement

## Verification Steps

### After Phase 1:
- [ ] Verify deleted files are gone
- [ ] Confirm database connections work with new credentials
- [ ] Check file permissions are secure
- [ ] Test application functionality

### After Phase 2:
- [ ] All database access goes through Database class
- [ ] No hardcoded credentials in codebase
- [ ] Environment-specific configuration working
- [ ] Performance testing completed

### After Phase 3:
- [ ] Security scanning implemented in CI/CD
- [ ] Monitoring alerts configured
- [ ] Team training completed

## Risk Mitigation

### During Implementation:
1. **Backup Strategy**: Full database and code backups before changes
2. **Rollback Plan**: Quick revert procedure if issues occur
3. **Staging Testing**: Test all changes in staging environment first
4. **Gradual Deployment**: Phase changes to minimize impact

### Post-Implementation:
1. **Regular Audits**: Quarterly security reviews
2. **Incident Response**: Documented procedures for security incidents
3. **Compliance Checks**: Regular GDPR/PCI DSS compliance verification

## Success Metrics

- **0** hardcoded credentials in codebase
- **100%** of database access through centralized class
- **< 5 min** mean time to detect security violations
- **100%** team completion of security training

## Emergency Contacts

- Database Administrator: [REDACTED]
- Security Lead: [REDACTED]  
- Incident Response: [REDACTED]

---
**Plan Created:** 2025-08-21  
**Status:** ACTIVE - REQUIRES IMMEDIATE EXECUTION