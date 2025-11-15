#!/bin/bash
# Phase 12 Deployment Script - Analytics Service
# FTP-compatible deployment for framework-free CMS

# Load configuration
source ../ftp-config.env

# Deployment variables
DEPLOY_DIR="/var/www/html/cms"
BACKUP_DIR="/var/www/html/backups/phase12_$(date +%Y%m%d_%H%M%S)"
DB_HOST="localhost"
DB_USER="$FTP_DB_USER"
DB_PASS="$FTP_DB_PASS"
DB_NAME="$FTP_DB_NAME"

# Create backup directory
mkdir -p "$BACKUP_DIR"

echo "Starting Phase 12 deployment - Analytics Service"

# Database deployment
echo "Creating analytics tables..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    event_type VARCHAR(255) NOT NULL,
    event_data TEXT,
    created_at DATETIME NOT NULL,
    INDEX (site_id, created_at)
);

CREATE TABLE IF NOT EXISTS analytics_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    metric_type VARCHAR(255) NOT NULL,
    value FLOAT NOT NULL,
    recorded_date DATE NOT NULL,
    INDEX (site_id, recorded_date)
);
EOF

# Verify database changes
DB_RESULT=$?
if [ $DB_RESULT -ne 0 ]; then
    echo "Error: Database deployment failed"
    exit 1
fi

# File deployment
echo "Deploying analytics service files..."
ftp -n <<EOF
open $FTP_HOST
user $FTP_USER $FTP_PASS
binary
cd $DEPLOY_DIR/app/Services
put app/Services/AnalyticsService.php
cd $DEPLOY_DIR/tests
put tests/AnalyticsServiceTest.php
quit
EOF

# Verify file deployment
FTP_RESULT=$?
if [ $FTP_RESULT -ne 0 ]; then
    echo "Error: File deployment failed"
    
    # Rollback database changes
    echo "Attempting rollback..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
    DROP TABLE IF EXISTS analytics_events;
    DROP TABLE IF EXISTS analytics_metrics;
EOF
    
    exit 1
fi

echo "Phase 12 deployment completed successfully"