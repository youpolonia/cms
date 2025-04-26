<?php

if (!function_exists('theme_asset')) {
    function theme_asset($path)
    {
        $theme = config('cms.themes.active', 'default');
        $publicPath = config('cms.themes.public_path', 'themes');
        
        return asset("{$publicPath}/{$theme}/assets/{$path}");
    }
}