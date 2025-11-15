# Core Routing System Documentation

## Components

### Request.php
- Handles HTTP request data
- Methods:
  - `getMethod()` - Returns HTTP method (GET/POST/etc)
  - `getPath()` - Returns request path
  - `get()` - Access GET parameters
  - `post()` - Access POST parameters

### Response.php  
- Handles HTTP responses
- Methods:
  - `send()` - Outputs response
  - `setHeader()` - Sets response headers
  - `setContent()` - Sets response content

### Router.php
- Routes requests to handlers
- Methods:
  - `addRoute()` - Register new route
  - `dispatch()` - Process current request

## Usage Example

```php
// In routes.php:
$router->addRoute('GET', '/about', function(Request $request) {
    return new Response('About Page');
});

// With JSON response:
$router->addRoute('GET', '/api/data', function(Request $request) {
    return (new Response())
        ->setHeader('Content-Type', 'application/json')
        ->setContent(json_encode(['data' => 'value']));
});
```

## Requirements Met
- Pure PHP implementation
- No framework dependencies
- FTP-deployable
- Shared hosting compatible
- Modular structure