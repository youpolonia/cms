# AI Content Generation API

## Endpoints

### POST /api/ai/generate-layout

Generate AI-powered layout structure based on content requirements.

**Authentication**: Required
**Rate Limit**: 60 requests per minute
**Validation**: Input validation applied

#### Request Parameters

```json
{
  "content_type": "string", // Required (article/page/product)
  "layout_style": "string", // Optional (modern/classic/minimal)
  "sections": "number",     // Optional (default: 3)
  "color_scheme": "string", // Optional (light/dark/custom)
  "components": [           // Optional array of required components
    "header",
    "footer",
    "sidebar"
  ]
}
```

#### Response

Success:
```json
{
  "success": true,
  "layout": {
    "structure": "HTML structure",
    "components": {
      "header": "HTML",
      "footer": "HTML"
    },
    "css": "CSS styles"
  }
}
```

Error:
```json
{
  "success": false,
  "error": "Error message"
}
```

#### Examples

1. Basic Article Layout:
```json
{
  "content_type": "article",
  "layout_style": "modern",
  "sections": 3
}
```

2. Custom Product Page:
```json
{
  "content_type": "product",
  "layout_style": "minimal",
  "color_scheme": "dark",
  "components": ["gallery", "specs", "reviews"]
}
```

### POST /api/ai/generate

Generate AI content based on input parameters.

**Authentication**: Required  
**Rate Limit**: 60 requests per minute  
**Validation**: Input validation applied

#### Request Parameters

```json
{
  "type": "title|expand|rewrite",
  "data": {
    "content": "string", // Required for expand/rewrite
    "title": "string",   // Required for title generation
    "tone": "string",    // Required for rewrite (professional/casual/friendly/academic)
    "params": {
      "length": "number", // Optional word count target
      "target_length": "number" // Optional for expand
    }
  }
}
```

#### Response

Success:
```json
{
  "success": true,
  "result": "Generated content string"
}
```

Error:
```json
{
  "success": false,
  "error": "Error message"
}
```

#### Examples

1. Title Generation:
```json
{
  "type": "title",
  "data": {
    "title": "Climate Change Effects",
    "params": {
      "length": 500
    }
  }
}
```

2. Content Expansion:
```json
{
  "type": "expand",
  "data": {
    "content": "The quick brown fox",
    "params": {
      "target_length": 100
    }
  }
}
```

3. Tone Rewrite:
```json
{
  "type": "rewrite",
  "data": {
    "content": "This is a test",
    "tone": "professional"
  }
}