# Auto-Updater Component Specifications

## 1. UpdaterController
**Location**: `core/Updater/Controller.php`
**Responsibilities**:
- Orchestrate update process
- Handle error recovery
- Maintain update state

**Methods**:
```php
public function checkForUpdates(): UpdateStatus
public function downloadUpdate(string $packageUrl): string // returns temp path
public function applyUpdate(string $packagePath): bool
public function rollback(string $backupId): bool
```

## 2. PackageHandler
**Location**: `core/Updater/PackageHandler.php`
**Features**:
- Download packages via cURL
- Validate checksums (SHA-256)
- Extract ZIP files
- Verify file structure

**Methods**:
```php
public function download(string $url, string $checksum): string
public function validate(string $packagePath, string $checksum): bool
public function extract(string $packagePath, string $destination): array // extracted files
```

## 3. BackupManager
**Location**: `core/Updater/BackupManager.php`
**Operations**:
- Create atomic backups
- Track backup versions
- Restore from backup

**Methods**:
```php
public function createBackup(array $paths): string // backup ID
public function restoreBackup(string $backupId): bool
public function listBackups(): array
```

## 4. UpdateLog
**Location**: `models/UpdateLog.php`
**Schema**:
```sql
CREATE TABLE update_log (
    id VARCHAR(36) PRIMARY KEY,
    type ENUM('core','plugin','template'),
    version VARCHAR(32),
    status ENUM('pending','success','failed'),
    backup_id VARCHAR(36),
    created_at DATETIME,
    completed_at DATETIME
);
```

## 5. JSON Index Format
**Example**:
```json
{
  "core": {
    "version": "1.2.0",
    "url": "https://updates.example.com/core-1.2.0.zip",
    "checksum": "sha256:abc123...",
    "min_php": "8.1",
    "changelog": "Security fixes..."
  },
  "plugins": {
    "example-plugin": {
      "version": "2.1.0",
      "url": "https://updates.example.com/plugins/example-2.1.0.zip",
      "requires_core": "1.1.0",
      "checksum": "sha256:def456..."
    }
  }
}
```

## Security Considerations
- HTTPS required for all downloads
- Checksum verification mandatory
- Temporary files cleaned after update
- No executable files allowed in updates