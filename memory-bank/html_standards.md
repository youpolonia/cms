# Admin HTML Standards

## Semantic Structure
1. All pages must use proper HTML5 semantic elements:
   - `<header>` for page header content
   - `<main>` for primary content
   - `<footer>` for footer content
   - `<nav>` for navigation menus
   - `<section>` for thematic groupings
   - `<article>` for self-contained compositions

2. Required document structure:
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title</title>
    <!-- CSS/JS links -->
</head>
<body>
    <header role="banner">
        <!-- Header content -->
    </header>
    
    <main role="main">
        <!-- Primary content -->
    </main>
    
    <footer role="contentinfo">
        <!-- Footer content -->
    </footer>
</body>
</html>
```

## Heading Hierarchy
1. Must follow logical order (h1 > h2 > h3 etc.)
2. Each page must have exactly one h1
3. Never skip heading levels

## Forms
1. All form controls must have associated labels
2. Use proper input types (email, password, etc.)
3. Include ARIA attributes where needed
4. Structure:
```html
<form>
    <fieldset>
        <legend>Form Section</legend>
        <div class="form-group">
            <label for="input-id">Label</label>
            <input type="text" id="input-id" name="input-name">
        </div>
    </fieldset>
</form>
```

## Accessibility
1. All interactive elements must be keyboard-navigable
2. Images must have alt text
3. Use ARIA landmarks (role="banner", role="main", etc.)
4. Ensure sufficient color contrast
5. Provide text alternatives for non-text content

## Backward Compatibility
1. Maintain existing PHP functionality
2. Preserve all security headers and tokens
3. Keep existing class names for CSS compatibility
4. Verify all form actions and handlers still work