# Export/Import Workflow Documentation

## Export Process
1. Call `ExportService::exportTheme($themeName)`
2. Service will:
   - Validate theme exists
   - Create ZIP archive
   - Include theme.json and referenced files
   - Add public directory
   - Generate metadata with checksum
3. Returns path to exported ZIP file

## Import Process
1. Call `ImportService::importTheme($zipPath, $themeName)`
2. Service will:
   - Validate ZIP structure
   - Check for existing theme
   - Extract to temp directory
   - Validate theme.json and files
   - Move to final location
3. Returns path to imported theme

## Security
- All exports/imports go through `/data` directory
- Direct access blocked by .htaccess
- File paths validated to prevent directory traversal
- Temporary files cleaned up automatically