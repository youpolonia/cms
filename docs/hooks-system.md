# Hooks System Documentation

## Overview
The hooks system allows developers to extend CMS functionality by registering callbacks that are triggered at specific points in the application lifecycle.

## Core Components

1. **EventDispatcher** (`includes/event_dispatcher.php`)
   - Manages hook registration and dispatching
   - Provides static methods for hook management

2. **Hook Model** (`includes/Hook.php`)
   - Database representation of hooks
   - Handles CRUD operations for hooks

3. **HooksController** (`admin/controllers/HooksController.php`)
   - Admin interface for managing hooks
   - Implements create, read, update, delete operations

## Usage Examples

### Registering a Hook
```php
EventDispatcher::registerHook('content_saved', function($content) {
    // Handle content after save
    Logger::log("Content saved: " . $content->title);
}, 10);
```

### Dispatching an Event
```php
EventDispatcher::dispatch('content_saved', [$content]);
```

### Available Hooks
1. `content_saved` - Triggered after content is saved
2. `user_logged_in` - Triggered after successful login
3. `before_render` - Triggered before page render

## Admin Interface
Access the hooks management interface at `/admin/hooks`

## Best Practices
- Keep hook callbacks lightweight
- Use priorities to control execution order
- Document all custom hooks
- Prefix custom hook names with your module name