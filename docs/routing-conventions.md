# Routing Security Conventions

## Route Protection
1. All admin routes must:
   - Use AdminAuth middleware
   - Verify session validity
   - Check user role permissions
   - Log access attempts

2. API routes must:
   - Use API authentication
   - Implement rate limiting
   - Validate all inputs
   - Return standardized error responses

## CSRF Protection
1. Required for all:
   - State-changing requests (POST, PUT, PATCH, DELETE)
   - Form submissions
   - AJAX requests modifying data

2. Implementation:
```php
// In your form templates
<input type="hidden" name="csrf_token" value="<?= $session->get('csrf_token') ?>">

// In your middleware
if ($request->isMethod('POST') && !$csrf->validate($request->get('csrf_token'))) {
    throw new InvalidCsrfTokenException();
}
```

## Rate Limiting
1. Configure limits in config/rate-limiter.php:
```php
return [
    'admin' => [
        'max_attempts' => 10,
        'decay_minutes' => 1
    ],
    'api' => [
        'max_attempts' => 100,
        'decay_minutes' => 5  
    ]
];
```

2. Apply to routes:
```php
$router->group(['middleware' => 'throttle:admin'], function() {
    // Admin routes
});

$router->group(['middleware' => 'throttle:api'], function() {
    // API routes  
});
```

## Input Validation
1. All inputs must be validated before processing
2. Use the validation helper:
```php
$validator = new Validator($request->all(), [
    'username' => 'required|string|max:255',
    'email' => 'required|email',
    'role' => 'in:user,editor,admin'
]);

if ($validator->fails()) {
    return response()->json($validator->errors(), 422);
}
```

## Error Handling
1. Never expose system details in errors
2. Standard error responses:
```php
// 401 Unauthorized
return response()->json(['error' => 'Authentication required'], 401);

// 403 Forbidden  
return response()->json(['error' => 'Insufficient permissions'], 403);

// 404 Not Found
return response()->json(['error' => 'Resource not found'], 404);

// 500 Server Error
return response()->json(['error' => 'Something went wrong'], 500);