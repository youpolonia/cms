# Fallback System Documentation

## Purpose
The fallback system provides default content and behavior when:
- Primary systems fail
- Content is unavailable
- Performance thresholds are exceeded

## Files
- `default_post.txt`: Shown when posts cannot be retrieved
- `default_page.txt`: Shown when pages cannot be retrieved
- `default_error.html`: Shown for system errors

## Configuration
Set fallback behavior in:
`config/fallbacks.php` (if exists) or
`config/base.php` under 'fallback' section

## Logging
All fallback activations are logged to:
`logs/fallbacks.log` with timestamp and context