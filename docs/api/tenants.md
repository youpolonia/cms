# Tenant Management API

## Overview
Provides endpoints for managing multi-tenant isolation and configuration.

## Base URL
`/api/v1/tenants`

## Endpoints

### Create Tenant
`POST /`

**Request:**
```json
{
  "tenant_id": "string",
  "name": "string",
  "quota": {
    "storage": "number",
    "users": "number"
  }
}
```

**Response:**
```json
{
  "id": "string",
  "storage_path": "string",
  "config": {}
}
```

### Get Tenant Configuration
`GET /{tenant_id}`

**Response:**
```json
{
  "id": "string",
  "quota": {
    "storage": {
      "allocated": "number",
      "used": "number"
    },
    "users": {
      "allocated": "number",
      "active": "number"
    }
  },
  "config": {}
}
```

### Update Tenant Quota
`PATCH /{tenant_id}/quota`

**Request:**
```json
{
  "storage": "number",
  "users": "number"
}
```

### Get Tenant Storage Path
`GET /{tenant_id}/storage`

**Response:**
```json
{
  "path": "string",
  "available": "number"
}
```

## Error Responses
```json
{
  "error": {
    "code": "number",
    "message": "string",
    "details": {}
  }
}