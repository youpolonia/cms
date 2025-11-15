# Theme Developer Guide

## Overview
The CMS theme system provides a simple way to customize the look and feel of your website. Themes are located in `public/themes/` and follow a specific structure.

## Theme Structure
```
public/themes/
└── theme-name/
    ├── assets/       # CSS, JS, images
    └── templates/    # PHP template files
```

## Creating a Theme
1. Create a new directory in `public/themes/`
2. Add your template files in the `templates/` subdirectory
3. Place assets in the `assets/` subdirectory

## Template Basics
Templates are regular PHP files that can:
- Use variables passed from controllers
- Include other templates
- Use helper functions

Example template (`home.php`):
```php
<h1><?= $title ?></h1>
<div class="content">
    <?= $content ?>
</div>
```

## Template Inheritance
Use `View::render()` to nest templates:
```php
// In controller
$content = View::render('home', ['title' => 'Welcome']);
return new Response(View::render('layout', ['content' => $content]));
```

## Asset Management
Reference assets using `Theme::asset()`:
```html
<link href="<?= Theme::asset('css/style.css') ?>" rel="stylesheet">
```

## Best Practices
- Keep templates simple and focused on presentation
- Move complex logic to controllers
- Use the `View::e()` helper to escape output
- Organize assets by type (css, js, images)