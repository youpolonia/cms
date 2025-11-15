# Default Theme Documentation

## Overview
The default theme provides responsive layouts with dark/light mode support and modular components.

## Features
- Responsive layouts for all screen sizes
- Dark/light mode toggle
- Accessible components
- Performance-optimized assets
- Shared hosting compatibility
- Page builder support

## Theme Structure
```
resources/views/themes/default/
├── layout.blade.php       # Base layout
├── frontend.blade.php     # Frontend pages
├── blog.blade.php         # Blog/archive views  
├── single.blade.php       # Single content pages
├── error.blade.php        # Error pages
└── components/            # Theme-specific components
```

## Customization
Override these variables in your `tailwind.config.js`:
```js
theme: {
  extend: {
    colors: {
      primary: {...},
      secondary: {...}
    }
  }
}
```

## Usage
Extend the base layout in your views:
```blade
@extends('themes.default.layout')

@section('content')
  <!-- Your content -->
@endsection
```

## Components
Reusable components are located in `resources/views/themes/default/components/`.