# Frontend Access Control Documentation

## 1. Access Level Field Implementation

The system implements an `access_level` field that controls content visibility. Three main levels are supported:

- **Public**: Accessible to all users (including guests)
- **Private**: Restricted to authorized users only
- **Admin**: Restricted to administrators only

The field is checked during content rendering to determine visibility.

## 2. ContentRenderer and PreviewRenderer Changes

Both renderers now:
- Check the `access_level` field before rendering content
- Handle fallback behavior for unauthorized access
- Support custom messages for restricted content
- Maintain consistent behavior between preview and published views

Key methods added:
- `checkAccess()` - Verifies user permissions
- `getFallbackContent()` - Returns appropriate fallback content

## 3. Permission Checking Logic

The permission system follows these rules:

1. **Public Content**:
   - Always visible (lines 13-51)
   - No permission checks required

2. **Private Content**:
   - Requires user authentication (lines 53-91)
   - Additional `hasAccess` flag may be required

3. **Admin Content**:
   - Requires admin role (lines 93-131)
   - Strict role checking

Fallback behaviors:
- Returns 403 status for unauthorized access (lines 133-169)
- Can show custom messages

## 4. Test Cases Overview

The test suite verifies:

1. Public content access (testPublicContentAccess)
   - Guests can view
   - Members can view
   - Admins can view

2. Private content access (testPrivateContentAccess)
   - Guests blocked
   - Authorized members allowed
   - Unauthorized members blocked

3. Admin restrictions (testAdminContentRestrictions)
   - Guests blocked
   - Members blocked
   - Admins allowed

4. Fallback behavior (testFallbackBehavior)
   - 403 responses
   - Custom messages

## 5. Usage Examples for Content Editors

**Setting access levels**:
```php
// In content creation/editing
$content->setAccessLevel('public'); // or 'private', 'admin'
```

**Checking access**:
```php
if ($renderer->checkAccess($user, $content)) {
    // Show content
} else {
    // Show fallback
}
```

**Custom fallback messages**:
```php
$renderer->setFallbackMessage('Please contact admin for access');