#!/bin/bash
# Automated rollback safety mechanism
MYSQL_USER="cms_user"
MYSQL_PASS="secure_password"
MYSQL_DB="cms_database"
LOG_FILE="/var/www/html/cms/database/rollback.log"
BACKUP_DIR="/var/www/html/cms/database/backups"

function log_rollback() {
  echo "[$(date)] $1" >> $LOG_FILE
}

function restore_table() {
  local table=$1
  local latest_backup=$(ls -t $BACKUP_DIR/${table}_*.sql.gz | head -1)
  
  if [ -z "$latest_backup" ]; then
    log_rollback "ERROR: No backup found for table $table"
    return 1
  fi
  
  log_rollback "Restoring $table from $latest_backup"
  gunzip -c $latest_backup | mysql -u$MYSQL_USER -p$MYSQL_PASS $MYSQL_DB
  
  if [ $? -ne 0 ]; then
    log_rollback "ERROR: Failed to restore $table"
    return 1
  fi
  
  log_rollback "Successfully restored $table"
}

function handle_migration_failure() {
  local migration=$1
  
  log_rollback "Migration failure detected: $migration"
  log_rollback "Initiating rollback procedure"
  
  # Get affected tables from migration file
  local affected_tables=$(grep -oP 'CREATE TABLE `\K[^`]+' $migration)
  
  if [ -z "$affected_tables" ]; then
    affected_tables=$(grep -oP 'ALTER TABLE `\K[^`]+' $migration)
  fi
  
  # Restore affected tables
  for table in $affected_tables; do
    restore_table $table || return 1
  done
  
  log_rollback "Rollback completed for migration: $migration"
}

# Main execution
if [ $# -eq 0 ]; then
  echo "Usage: $0 <failed_migration_file>"
  exit 1
fi

handle_migration_failure "$1"
exit $?