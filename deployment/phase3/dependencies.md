# Phase 3 Dependencies

## Required PHP Extensions
- cURL (for HTTP client)
- JSON (for API responses)
- OpenSSL (for HTTPS connections)

## External Services
- Versioning Service API (HTTPS endpoint)
- Guzzle HTTP Client (included in /vendor/)

## File Structure Requirements
```
/var/www/html/cms/
├── config/
│   ├── versioning.php
│   └── http_client.php
├── services/
│   ├── VersioningService.php
│   └── test/
│       ├── connectivity.php
│       └── error_simulation.php
└── vendor/
    └── guzzlehttp/ (if using Guzzle)
```

## Minimum Permissions
- /config/ - 755 (drwxr-xr-x)
- /services/ - 755 (drwxr-xr-x)
- PHP files - 644 (-rw-r--r--)