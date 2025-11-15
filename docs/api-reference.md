# Comprehensive API Reference

## Overview
This document serves as the master reference for all CMS APIs. For detailed documentation of each API, see the linked documents.

## Authentication
- [Authentication API](api/authentication.md)
- [API Rate Limiting](api/rate-limiting.md)

## Content Management
- [Content Versions API](api/content-versions.md)
- [Content Generation API](api/content-generation.md)
- [Conditional Publishing API](api/conditional-publishing.md)

## Theme Management
- [Theme Versions API](api/theme-versions.md)
- [Theme Approvals API](api/theme-approvals.md)
- [Theme Rollbacks API](api/theme-rollbacks.md)

## Analytics
- [Analytics API](api/analytics.md)
- [Analytics Exports API](api/analytics-exports.md)

## AI Features
- [AI Content Generation API](api/automated-generation.md)
- [AI Recommendations API](api/recommendations.md)

## Version Control
- [Version Control API](api/version-control-api.md)

## OpenAPI Specifications
- [AI Content Generation OpenAPI](ai-content-generation/api.openapi.yaml)
- [Analytics Exports OpenAPI](api/analytics-exports.openapi.yaml)
- [Conditional Publishing OpenAPI](api/conditional-publishing.openapi.yaml)

## Usage Examples
```javascript
// Example: Creating content version
fetch('/api/content/versions', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer {token}',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    content_id: 123,
    version_data: '<h1>New version</h1>'
  })
});
```

## Error Handling
All APIs return standardized error responses:
```json
{
  "error": {
    "code": "invalid_request",
    "message": "Missing required parameter",
    "details": {
      "parameter": "content_id"
    }
  }
}
```

## Versioning
API version is specified in the URL path:
- `/v1/content/versions`
- `/v2/analytics/exports`

## Changelog
- 2025-05-01: Added version control API
- 2025-04-15: Updated analytics endpoints
- 2025-04-01: Initial API release