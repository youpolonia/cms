<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CMS') }}</title>

    @foreach(config('cms.theme_assets.css', []) as $css)
        <link href="{{ theme_asset($css) }}" rel="stylesheet">
    @endforeach
</head>
<body>
    <div class="container">
        @yield('content')
    </div>

    @foreach(config('cms.theme_assets.js', []) as $js)
        <script src="{{ theme_asset($js) }}"></script>
    @endforeach
</body>
</html>