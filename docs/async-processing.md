# Async Processing API Documentation

## Overview
The CMS provides async processing capabilities for:
- Content generation
- Content improvement
- Content summarization
- SEO optimization

## Job Types

### GenerateContentJob
- **Purpose**: Generate new content from scratch
- **Parameters**:
  - `prompt`: Content generation prompt
  - `model`: Model to use (default: gpt-4)
  - `cache_key`: Unique key to store results

### ImproveContentJob
- **Purpose**: Improve existing content
- **Parameters**:
  - `content`: Content to improve
  - `instructions`: Improvement instructions
  - `model`: Model to use (default: gpt-4)
  - `cache_key`: Unique key to store results

### GenerateSummaryJob
- **Purpose**: Generate content summaries
- **Parameters**:
  - `content`: Content to summarize
  - `model`: Model to use (default: gpt-4)
  - `max_length`: Max summary length
  - `cache_key`: Unique key to store results

### GenerateSEOJob
- **Purpose**: Generate SEO-optimized content
- **Parameters**:
  - `content`: Base content
  - `model`: Model to use (default: gpt-4)
  - `focus_keyword`: Primary SEO keyword
  - `tone`: Content tone
  - `cache_key`: Unique key to store results

## Status Checking
Check job status via API:
```
GET /api/jobs/status?cache_key=YOUR_CACHE_KEY
```

Responses:
- `404`: Job not found/expired
- `200`: Job completed (includes result in response)