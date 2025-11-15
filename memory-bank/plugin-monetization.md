# Plugin Monetization System Documentation

## 1. System Architecture Diagram

```text
+-------------------+     +-------------------+     +-------------------+
|   Plugin Registry |     |  Plugin Installer |     | Admin Dashboard   |
|   (Remote JSON)   |<--->| (Local System)    |<--->| (PluginManager.vue)|
+-------------------+     +-------------------+     +-------------------+
      ^  |                      |  ^                      ^  |
      |  v                      v  |                      |  v
+-------------------+     +-------------------+     +-------------------+
| License Validation|     |  Plugin API       |     | User Management   |
|   Service         |     |  (REST Endpoints) |     | System            |
+-------------------+     +-------------------+     +-------------------+
```

## 2. JSON Registry Schema Specification

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "plugin_id": {
      "type": "string",
      "description": "Unique plugin identifier"
    },
    "name": {
      "type": "string",
      "description": "Human-readable plugin name"
    },
    "version": {
      "type": "string",
      "pattern": "^\\d+\\.\\d+\\.\\d+$"
    },
    "monetization": {
      "type": "object",
      "properties": {
        "type": {
          "type": "string",
          "enum": ["free", "premium", "freemium"]
        },
        "price": {
          "type": "number",
          "minimum": 0
        },
        "license_required": {
          "type": "boolean"
        }
      },
      "required": ["type"]
    }
  },
  "required": ["plugin_id", "name", "version"]
}
```

## 3. Admin Panel Usage Guide

### Accessing Plugin Management
1. Navigate to Admin Dashboard → Extensions → Plugins
2. The PluginManager.vue interface provides:
   - Marketplace view (lists available plugins)
   - Installed plugins view
   - License management section

### Key Operations:
- **Install Plugin**: 
  1. Click "Add New" button
  2. Search/browse available plugins
  3. Click "Install" and follow prompts

- **Manage Licenses**:
  1. Select installed premium plugin
  2. Click "License" tab
  3. Enter valid license key
  4. Click "Validate & Save"

## 4. Installation Workflow Steps

1. **Registry Fetch**:
   - System queries remote registry for plugin metadata
   - Validates JSON structure against schema

2. **Download & Verification**:
   - Package downloaded over HTTPS
   - Checksum verified against registry data

3. **License Check**:
   - For premium plugins, validates license key
   - Caches validation result for 24 hours

4. **Installation**:
   - Unpacks plugin to `/plugins/{plugin_id}`
   - Creates database records if needed
   - Runs post-install hooks

5. **Activation**:
   - Plugin appears in admin UI
   - Available for immediate use

## 5. Licensing Activation Process

1. **Obtain License**:
   - Purchase from marketplace
   - Receive license key via email

2. **Activate License**:
   - Navigate to plugin in admin UI
   - Enter license key in settings
   - System validates with remote server

3. **Validation**:
   - Key checked against issuer's API
   - Domain/installation binding applied
   - Validation cached locally

4. **Renewal**:
   - System notifies 30 days before expiry
   - Renew through same process

## 6. Security Considerations

### Data Protection
- All license keys encrypted at rest (AES-256)
- API communications use TLS 1.3
- No sensitive data stored in plaintext

### Package Security
- All downloads require valid SSL certificates
- Packages verified before unpacking
- SHA-256 checksums enforced

### API Security
- Rate limiting on validation endpoints
- CSRF protection on all admin routes
- IP-based request filtering