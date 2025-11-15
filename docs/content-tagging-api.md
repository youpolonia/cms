# Content Tagging API Documentation

## Overview
The Content Tagging API provides endpoints for automated content tagging, suggestion, and analysis.

## Authentication
All endpoints require JWT authentication via Bearer token.

## Endpoints

### POST /api/tags/suggest
Get tag suggestions for content

**Request Body:**
```json
{
  "content": "The text content to analyze",
  "query": "optional search filter"
}
```

**Response:**
```json
{
  "suggestions": ["tag1", "tag2"],
  "scores": {
    "tag1": 85,
    "tag2": 72
  }
}
```

### POST /api/tags/analyze
Analyze and tag specific content

**Request Body:**
```json
{
  "content_id": 123
}
```

**Response:**
```json
{
  "tags": ["tag1", "tag2"],
  "scores": [85, 72],
  "version_id": 123
}
```

### POST /api/tags/bulk
Bulk process multiple content items

**Request Body:**
```json
{
  "content_ids": [123, 456]
}
```

**Response:**
```json
{
  "processed": {
    "123": {
      "tags": ["tag1", "tag2"],
      "scores": [85, 72]
    },
    "456": {
      "tags": ["tag3"],
      "scores": [91]
    }
  }
}
```

### GET /api/tags/analytics
Get tag analytics (used by dashboard)

**Query Parameters:**
- start_date: ISO date string
- end_date: ISO date string

**Response:**
```json
{
  "metrics": {
    "total_tags": 150,
    "auto_tagged": 120,
    "manual_tags": 30,
    "avg_tags": 2.5
  },
  "tag_usage": [...],
  "relevance_trend": [...],
  "tag_cloud": [...]
}