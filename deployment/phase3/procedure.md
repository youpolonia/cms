# Phase 3 Deployment Procedure

## FTP Deployment Steps
1. Upload all files maintaining directory structure:
   - /services/VersioningService.php
   - /config/versioning.php
   - /config/http_client.php
   - /vendor/guzzlehttp/ (if using Guzzle)

2. Verify file permissions:
   ```bash
   chmod 644 /config/*.php
   chmod 755 /services/
   ```

3. Test service initialization:
   - Access `/services/test/versioning.php`
   - Verify JSON response contains:
     ```json
     {"status":"success","version":"1.0.0"}
     ```

## Configuration
1. Edit `/config/versioning.php`:
   ```php
   return [
       'base_uri' => 'https://api.versioningservice.com/v1',
       'timeout' => 30,
       'api_key' => 'YOUR_API_KEY'
   ];
   ```

2. Edit `/config/http_client.php`:
   ```php
   return [
       'default_timeout' => 60,
       'verify_ssl' => true,
       'retry_attempts' => 3
   ];
   ```

## Verification Tests
1. Service connectivity test:
   - Access `/services/test/connectivity.php`
   - Expected response: HTTP 200 with success message

2. Error handling test:
   - Access `/services/test/error_simulation.php`
   - Verify proper error logging occurs