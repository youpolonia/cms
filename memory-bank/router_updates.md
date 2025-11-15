# Router Updates - 2025-05-10

## Changes Implemented

1. **Controller Method Resolution**
   - Added support for "Controller@method" syntax in routes
   - Automatic instantiation and method calling
   - Proper error handling for missing controllers/methods

2. **Namespace Support**  
   - Added `controllerNamespace` property
   - Added `setControllerNamespace()` method
   - Automatic namespace prefixing for relative controller names

3. **Request/Response Handling**
   - Integrated Request and Response objects from Includes\Routing namespace
   - All handlers now receive request/response parameters
   - Response is automatically sent after handler execution

4. **Backward Compatibility**
   - Maintained support for callable route handlers
   - Existing routes will continue to work unchanged
   - Added proper namespace support

## Testing Instructions

Test with:
- http://localhost:8000/ (existing routes)
- http://localhost:8000/test (new controller routes)

Example usage:
```php
$router = new Router('/basepath', 'App\\Controllers');
$router->addRoute('GET', '/test', 'TestController@index');
```

## Dependencies
- Requires Request and Response classes from includes/routing/
- Uses PHP 8.1+ features (typed properties, str_contains)