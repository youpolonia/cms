#!/bin/bash
# Backup script for critical CMS tables
MYSQL_USER="cms_user"
MYSQL_PASS="secure_password" 
MYSQL_DB="cms_database"
TIMESTAMP=$(date +%Y%m%d%H%M%S)
BACKUP_DIR="/var/www/html/cms/database/backups"

mkdir -p $BACKUP_DIR

# Define critical tables
TABLES=(
  "contents"
  "content_versions" 
  "categories"
  "content_version_diffs"
  "content_analytics"
  "approval_workflows"
  "content_schedules"
  "moderation_queue"
  "theme_versions"
  "media_versions"
)

# Backup each table
for table in "${TABLES[@]}"; do
  mysqldump -u$MYSQL_USER -p$MYSQL_PASS $MYSQL_DB $table > "$BACKUP_DIR/${table}_$TIMESTAMP.sql"
  gzip "$BACKUP_DIR/${table}_$TIMESTAMP.sql"
done

echo "Backup completed at $(date)" >> "$BACKUP_DIR/backup.log"