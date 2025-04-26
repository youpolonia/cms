# Category Content Management API

## Endpoints

### Add Content to Category
`POST /api/categories/{category}/contents/add`

**Parameters:**
- `content_id` (required): ID of content to add
- `order` (optional): Position in category (defaults to last)

**Request Example:**
```json
{
  "content_id": 123,
  "order": 2
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Content added to category"
}
```

### Remove Content from Category  
`POST /api/categories/{category}/contents/remove`

**Parameters:**
- `content_id` (required): ID of content to remove

**Request Example:**
```json
{
  "content_id": 123
}
```

### Reorder Contents  
`POST /api/categories/{category}/contents/reorder`

**Parameters:**
- `content_ids` (required): Array of content IDs in new order

**Request Example:**
```json
{
  "content_ids": [123, 456, 789]
}
```

### Bulk Content Management  
`POST /api/categories/{category}/contents/bulk`

**Parameters:**
- `add` (optional): Array of content IDs to add
- `remove` (optional): Array of content IDs to remove

**Request Example:**
```json
{
  "add": [123, 456],
  "remove": [789]
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    // Validation errors if applicable
  }
}
```

**Status Codes:**
- 400: Bad request (validation errors)
- 404: Category or content not found
- 500: Server error