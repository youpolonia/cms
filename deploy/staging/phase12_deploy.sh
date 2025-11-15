#!/bin/bash
# Phase 12 Staging Deployment Script - Analytics Service
# For testing environment with sample data

# Load configuration
source ../../ftp-config.env

# Deployment variables
DEPLOY_DIR="/var/www/html/cms-staging"
DB_HOST="localhost"
DB_USER="$FTP_DB_USER"
DB_PASS="$FTP_DB_PASS"
DB_NAME="${FTP_DB_NAME}_staging"

echo "Starting Phase 12 staging deployment"

# Database deployment
echo "Creating staging analytics tables..."
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

-- Insert test data
INSERT INTO analytics_events 
(site_id, event_type, event_data, created_at)
VALUES 
(1, 'page_view', '{"page":"/test"}', NOW()),
(1, 'click', '{"element":"button"}', NOW());

INSERT INTO analytics_metrics
(site_id, metric_type, value, recorded_date)
VALUES
(1, 'response_time', 120.5, CURDATE()),
(1, 'uptime', 99.9, CURDATE());
EOF

# Verify database changes
if [ $? -ne 0 ]; then
    echo "Error: Staging database deployment failed"
    exit 1
fi

# File deployment
echo "Deploying to staging..."
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

# Verify deployment
if [ $? -ne 0 ]; then
    echo "Error: Staging file deployment failed"
    exit 1
fi

echo "Staging deployment completed with test data"