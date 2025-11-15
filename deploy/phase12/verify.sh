#!/bin/bash
# Phase 12 Deployment Verification Script

echo "=== Version Control System Verification ==="

# Check API endpoints
echo -n "Testing API endpoints... "
curl -s http://localhost/api/versions/list/test_page_1 | grep -q '"versions"' && echo "OK" || echo "FAILED"

# Check UI components
echo -n "Checking UI components... "
[ -f "assets/js/version-management.js" ] && [ -f "assets/css/version-management.css" ] && echo "OK" || echo "MISSING"

# Check database connectivity
echo -n "Testing database connection... "
php -r "require 'includes/Database.php'; echo Database::testConnection() ? 'OK' : 'FAILED';"
echo

# Check file permissions
echo -n "Verifying file permissions... "
[ -w "includes/Versioning" ] && echo "OK" || echo "INSUFFICIENT PERMISSIONS"

echo "Verification complete"