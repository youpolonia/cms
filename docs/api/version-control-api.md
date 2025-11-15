# Version Control API Specification

## Base URL
`/api/version-control`

## Endpoints

### Compare Content Versions
`POST /content/{contentId}/compare`

Compare two content versions with various diff options.

**Request:**
```json
{
  "base_version_id": "string",
  "target_version_id": "string",
  "compare_type": "line|word|semantic",
  "include_metadata": boolean,
  "context_lines": number
}
```

**Response (200):**
```json
{
  "comparison": {
    "base_version": "string",
    "target_version": "string",
    "diff": "string",
    "stats": {
      "added": number,
      "removed": number,
      "changed": number
    }
  }
}
```

### Get Detailed Diff 
`POST /diff/detailed`

Get a detailed side-by-side comparison of content.

**Request:**
```json
{
  "base_content": "string",
  "target_content": "string",
  "content_type": "text|html|markdown",
  "context_lines": number
}
```

**Response (200):**
```json
{
  "left_content": "string",
  "right_content": "string",
  "changes": [
    {
      "type": "insert|delete|replace",
      "left_line": number,
      "right_line": number,
      "content": "string"
    }
  ]
}
```

### Error Responses
**400 Bad Request**
- Invalid version IDs
- Missing required fields

**404 Not Found**
- Version not found

**500 Internal Server Error**
- Server error during comparison