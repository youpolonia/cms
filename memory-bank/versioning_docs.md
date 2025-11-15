# Content Versioning System Documentation

## Overview
The content versioning system allows tracking changes to CMS content with the ability to:
- Save versions automatically
- View version history
- Restore previous versions
- Receive notifications of version changes

## Database Schema
```sql
CREATE TABLE `content_versions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `content_id` INT NOT NULL,
  `version_number` INT NOT NULL,
  `author_id` INT NOT NULL,
  `data_json` LONGTEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  FOREIGN KEY (`content_id`) REFERENCES `content`(`id`)
);
```

## Core Components

### ContentHistoryManager
Location: `core/ContentHistoryManager.php`

#### Methods:
1. `saveVersion($contentId, $authorId, $dataArray)`
   - Saves a new version of content
   - Returns new version number
   - Triggers 'content_version_saved' notification

2. `getVersions($contentId)`
   - Returns array of all versions for content
   - Sorted by version number (descending)

3. `getVersion($contentId, $versionNumber)`
   - Returns specific version data
   - Returns false if not found

4. `restoreVersion($contentId, $versionNumber)`
   - Restores content to specified version
   - Triggers 'content_version_restored' notification
   - Returns restored data

## Admin Interface
Location: `admin/content/history.php`

Features:
- Version listing table
- Restore action with confirmation
- Compare versions
- Links back to content editor

## Notifications
Triggered events:
1. Version Saved:
   - Type: 'content_version_saved'
   - Message: "New version {n} saved for content ID {id}"

2. Version Restored:
   - Type: 'content_version_restored' 
   - Message: "Version {n} restored for content ID {id}"