# CMS Routing System Documentation

This document outlines the current state of the routing systems used within the CMS.
The CMS currently employs two distinct routing mechanisms: `Includes\Core\Router` and `Includes\RoutingV2\Router`.

## 1. `Includes\Core\Router`

This router is primarily used for front-end routes, such as login, logout, and potentially public-facing pages.

**Key Characteristics:**
- **File Location:** [`includes/Core/Router.php`](includes/Core/Router.php)
- **Route Definition:** Typically in [`routes/web.php`](routes/web.php)
- **Handler Type:** Uses anonymous functions (closures) as route handlers.
- **Request/Response:** Interacts with `Includes\Core\Request` and `Includes\Core\Response`.
- **Dependency Injection:** Dependencies like the database connection are often passed into the closure using the `use` keyword.
- **Middleware:** Basic middleware capabilities might be present but are not as structured as in `RoutingV2`.

**Example Usage (from `routes/web.php`):**
```php
// $router is an instance of Includes\Core\Router
$router->addRoute('GET', '/login', function() use ($router) {
    $auth = new AuthController($router->getDependencies()['db']);
    $csrfToken = $auth->getCsrfToken();
    return new Response(require __DIR__.'/../templates/login.php');
});

$router->addRoute('POST', '/login', function() use ($router) {
    $auth = new AuthController($router->getDependencies()['db']);
    $request = new Request();
    // ... login logic ...
    if ($result['success']) {
        return new Response('', 302, ['Location' => '/dashboard']);
    }
    // ... error handling ...
});
```

## 2. `Includes\RoutingV2\Router`

This router is primarily used for the admin panel and more complex back-end functionalities. It offers a more structured, controller-based approach.

**Key Characteristics:**
- **File Location:** [`includes/RoutingV2/Router.php`](includes/RoutingV2/Router.php) (and related files in `includes/RoutingV2/`)
- **Route Definition:** Typically in [`routes/admin.php`](routes/admin.php)
- **Handler Type:** Uses controller classes and methods (e.g., `[DashboardController::class, 'index']`).
- **Request/Response:** Expected to interact with its own Request/Response objects, potentially different from the `Core` versions.
- **Middleware:** Has a more robust middleware system, as seen with `CheckPermission` middleware.
- **Route Parameters:** Supports named route parameters (e.g., `/admin/content/edit/{id}`).

**Example Usage (from `routes/admin.php`):**
```php
// $router is an instance of Includes\RoutingV2\Router
// Admin Dashboard
$adminRoute = $router->addRoute('GET', '/admin', [DashboardController::class, 'index']);
$adminRoute->addMiddleware(new CheckPermission('view_dashboard'));

// Content Management
$contentEditGetRoute = $router->addRoute('GET', '/admin/content/edit/{id}', [ContentController::class, 'edit']);
$contentEditGetRoute->addMiddleware(new CheckPermission('manage_content'));
```

## Inconsistencies and Standardization Goal

The primary goal of the ongoing routing standardization project is to unify these two systems, leveraging the more feature-rich `RoutingV2` as the base for all routes (both web and admin). This will involve:
- Migrating `Core\Router` routes to use the controller-based pattern of `RoutingV2`.
- Standardizing Request, Response, and Middleware handling across the entire application.
- Ensuring consistent dependency injection mechanisms.

## 3. Migration Path: `Includes\RoutingV2\CoreRouterAdapter`

To facilitate a smoother transition from `Core\Router` to `RoutingV2\Router`, a compatibility adapter has been introduced:

- **File Location:** [`includes/RoutingV2/CoreRouterAdapter.php`](includes/RoutingV2/CoreRouterAdapter.php)

This adapter allows routes originally defined for `Core\Router` (typically using closures) to be registered with the `RoutingV2\Router` system with minimal immediate changes to the route definitions themselves.

**Purpose:**
- Acts as a bridge during the migration period.
- Allows `routes/web.php` (and similar files) to be switched over to use the `RoutingV2\Router` instance by wrapping the old `Core\Router` instance or by instantiating the adapter directly.
- Internally, the adapter translates the `Core\Router`'s `addRoute()` calls and closure handlers into a format that `RoutingV2\Router` can understand. This includes handling the differences in Request and Response objects between the two systems.

**Usage during migration:**

The `CoreRouterAdapter` will be instantiated with the main `RoutingV2\Router` instance. Then, instead of calling `addRoute` on a `Core\Router` object, it will be called on the `CoreRouterAdapter` instance.

```php
// Example: In your bootstrap file or where the router is set up

// Presume $routerV2 is your main Includes\RoutingV2\Router instance
// Presume $coreRouterDependencies are the dependencies the old router closures might need
$coreAdapter = new \Includes\RoutingV2\CoreRouterAdapter($routerV2, $coreRouterDependencies);

// In routes/web.php, instead of:
// $coreRouter->addRoute('GET', '/somepath', function() use ($coreRouter) { ... });
// You would now use:
// $coreAdapter->addRoute('GET', '/somepath', function() use ($coreAdapter) { /* ... */ });
// Note: The closure might now use $coreAdapter->getDependencies() if it was using $coreRouter->getDependencies()
```

This adapter is a temporary measure. The long-term goal is to refactor all routes to use the native controller-based approach of `RoutingV2\Router` directly, as outlined in the standardization plan.

Refer to the `plans/routing-standardization.md` document for the detailed migration plan.