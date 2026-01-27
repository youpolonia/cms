#!/bin/bash
# TB4 Backup Restore Script
# Created: 2026-01-09 18:30
# Use: sudo bash RESTORE.sh

BACKUP_DIR="/var/www/html/cms/backups/tb4-backup-20260109-1830"

echo "Restoring TB4 from backup..."

# Restore core TB4
rm -rf /var/www/html/cms/core/tb4
cp -r $BACKUP_DIR/core-tb4 /var/www/html/cms/core/tb4
chown -R www-data:www-data /var/www/html/cms/core/tb4
echo "Core TB4 restored"

# Restore assets TB4
rm -rf /var/www/html/cms/public/assets/tb4
cp -r $BACKUP_DIR/assets-tb4 /var/www/html/cms/public/assets/tb4
chown -R www-data:www-data /var/www/html/cms/public/assets/tb4
echo "Assets TB4 restored"

# Restore views TB4
rm -rf /var/www/html/cms/app/views/admin/tb4
cp -r $BACKUP_DIR/views-tb4 /var/www/html/cms/app/views/admin/tb4
chown -R www-data:www-data /var/www/html/cms/app/views/admin/tb4
echo "Views TB4 restored"

# Restore Lucide
cp $BACKUP_DIR/lucide.min.js /var/www/html/cms/public/assets/js/lucide.min.js
chown www-data:www-data /var/www/html/cms/public/assets/js/lucide.min.js
echo "Lucide.js restored"

echo ""
echo "TB4 RESTORE COMPLETE!"
echo "Backup source: $BACKUP_DIR"
