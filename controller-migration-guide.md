# Controller Migration Guide

## Overview
This document outlines the process for migrating controllers from Laravel to native PHP implementation.

## Base Controller Implementation

### Key Features
- Request handling via `Includes\Routing\Request`
- Auth integration via `Includes\Auth\Auth`
- Response types:
  - Views: `$this->view('template', $data)`
  - Redirects: `$this->redirect('/path')` 
  - JSON: `$this->json($data, $status)`
- Basic validation: `$this->validate($rules)`

### Differences from Laravel
1. No facades - dependencies injected via constructor
2. Responses return arrays with type indicators
3. Validation is simpler but extensible
4. No service container - manual dependency management

## Migration Steps

1. Create new controller in `includes/controllers/`
2. Extend base `Controller` class
3. Replace Laravel-specific code:
   - `return view()` → `return $this->view()`
   - `return redirect()` → `return $this->redirect()`
   - `Auth::` → `$this->auth->`
4. Update route definitions to use new controllers

## Example Migration

### Before (Laravel)
```php
public function store(LoginRequest $request)
{
    $request->authenticate();
    $request->session()->regenerate();
    return redirect()->intended('/dashboard');
}
```

### After (Native)
```php
public function store(Request $request)
{
    $user = $this->auth->attempt([
        'email' => $request->input('email'),
        'password' => $request->input('password')
    ]);
    
    if (!$user) {
        return $this->json(['error' => 'Invalid credentials'], 401);
    }
    
    return $this->redirect('/dashboard');
}
```

## Testing
1. Verify all routes work with new controllers
2. Test authentication flows
3. Check session persistence
4. Validate response types