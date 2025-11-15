# FTP Installation Guide

## 1. FTP Upload Process
1. Connect to your server using SFTP/FTPS (recommended port 22 for SFTP or 990 for FTPS)
2. Upload all files to your web root directory (typically `/var/www/html/`)
3. Recommended upload order:
   - Core CMS files first
   - Configuration files second
   - Plugins and themes last

## 2. Required Folder Permissions
The following directories must be writable:
- `/cache/` - 777 permissions (must be writable by web server)
- `/uploads/` - 775 permissions
- `/admin/assets/` - 775 permissions
- `/includes/config/` - 775 permissions (for .env file)

Set permissions using your FTP client or run:
```bash
chmod 777 cache/
chmod 775 uploads/ admin/assets/ includes/config/
```

## 3. Database Setup
1. Create a new MySQL database via your hosting control panel
2. Create a database user with full privileges to this database
3. Note these credentials for .env configuration:
   - Database name
   - Database username
   - Database password
   - Database host (usually 'localhost')

## 4. SQL Migration Execution
1. Locate the latest migration file in `/database/migrations/`
2. Import it using phpMyAdmin or similar tool:
   - Select your database
   - Go to "Import" tab
   - Choose the migration SQL file
   - Click "Execute"

## 5. .env Configuration
1. Rename `/includes/config/.env.example` to `.env`
2. Edit with your database credentials:
```ini
DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_username
DB_PASS=your_password
```
3. Set your application URL:
```ini
APP_URL=https://yourdomain.com
```
4. Save the file

## Post-Installation Checks
1. Verify all required PHP extensions are enabled:
   - PDO
   - JSON
   - cURL
   - OpenSSL
2. Visit your site URL to complete installation
3. Login to admin panel at `/admin/`

## Troubleshooting
- **500 Errors**: Check folder permissions and .env configuration
- **Database Errors**: Verify credentials and that migrations ran successfully
- **File Upload Issues**: Ensure uploads directory is writable
