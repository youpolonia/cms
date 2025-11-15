# CMS Response Standards

## Overview
This document defines standard response patterns for the CMS controllers using `ResponseHandler`.

## Response Types

### 1. JSON Responses
Use for API endpoints and AJAX requests.

**Success Response:**
```php
ResponseHandler::success(
    $data,          // Response data (array/object)
    $status = 200,  // HTTP status code
    $message = null // Optional success message
);
```

**Error Response:**
```php
ResponseHandler::error(
    $message,       // Error message
    $status = 400,  // HTTP status code
    $errors = null, // Validation errors array
    $debug = null   // Debug data (only in debug mode)
);
```

### 2. Redirects
Use after form submissions or state changes.

```php
ResponseHandler::redirect(
    $url,           // Target URL
    $status = 302,  // 301 (permanent) or 302 (temporary)
    $flash = null   // Optional flash data
);
```

### 3. View Rendering
Use for standard page requests.

```php
ResponseHandler::view(
    $templatePath,  // Path to template file
    $data = [],     // Data to pass to template
    $status = 200   // HTTP status code
);
```

## Status Code Guidelines

| Code | Usage |
|------|-------|
| 200 | Standard success response |
| 201 | Resource created successfully |
| 302 | Temporary redirect (default) |
| 301 | Permanent redirect |
| 400 | Bad request/validation errors |
| 401 | Unauthorized access |
| 403 | Forbidden access |
| 404 | Resource not found |
| 422 | Validation errors (with details) |
| 500 | Server error |

## Best Practices

1. Always use ResponseHandler instead of direct HTTP functions
2. Set appropriate status codes
3. For APIs, always return consistent JSON structures
4. Use debug mode only in development
5. Document response formats in controller PHPDoc
6. Keep view templates in `views/` directory
7. Use flash data sparingly for redirects