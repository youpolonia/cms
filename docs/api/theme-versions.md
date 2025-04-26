## Get File-by-File Comparison

`GET /themes/{themeId}/versions/{baseVersionId}/compare/{targetVersionId}/files`

Get detailed file-by-file comparison between two theme versions.

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| page | integer | Page number for pagination (default: 1) |
| per_page | integer | Items per page (default: 20) |
| file_type | string | Filter by file extension (e.g. 'css', 'js') |

### Response

```json
{
  "data": {
    "changes": [
      {
        "file_path": "assets/css/app.css",
        "change_type": "modified",
        "size_diff_kb": 2.5,
        "line_changes": {
          "added": 15,
          "removed": 12
        }
      }
    ],
    "pagination": {
      "total": 42,
      "per_page": 20,
      "current_page": 1,
      "last_page": 3
    }
  },
  "message": "Theme version file changes retrieved successfully"
}
```
