# View System Architecture

## Overview
The new view system provides framework-free template rendering with:
- Layout inheritance
- Partial includes
- Simple variable interpolation
- No dependencies beyond PHP 8.1+

## Core Components

### View.php
Located at `/includes/Core/View.php`
- Handles template rendering
- Manages layout inheritance
- Provides partial includes

### Templates
Located in `/templates/`
- `.php` files containing HTML/PHP mix
- Can extend layouts using `$this->extend()`
- Can include partials using `$this->include()`

## Usage Example

```php
// In controller:
$view = new View(__DIR__ . '/../../templates');
$view->setLayout('layouts/main');
$content = $view->render('home/index', [
    'title' => 'Page Title'
]);

// In template (home/index.php):
<h1><?= $title ?></h1>
<p>Page content goes here</p>

// In layout (layouts/main.php):
<html>
<head><title><?= $title ?></title></head>
<body>
    <?= $this->content() ?>
</body>
</html>
```

## Data Flow
1. Controller creates View instance
2. View loads template file
3. Template can extend a layout
4. View renders final output combining layout and template