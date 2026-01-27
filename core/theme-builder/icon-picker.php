<?php
/**
 * Theme Builder 3.0 - Centralized Icon Picker
 * SVG-based icons that always work (no external fonts needed)
 * 
 * Supported icon sets:
 * - Lucide (line style, modern) - format: lucide:name
 * - Heroicons (solid style) - format: hero:name  
 * - Emoji - format: direct emoji character
 */

/**
 * Get all available icons as PHP array
 * This can be encoded to JSON for JavaScript
 */
function tb_get_icons_data(): array
{
    return [
        'lucideIcons' => [
            'arrows' => [
                ['name' => 'arrow-up', 'svg' => '<path d="M12 19V5M5 12l7-7 7 7"/>'],
                ['name' => 'arrow-down', 'svg' => '<path d="M12 5v14M5 12l7 7 7-7"/>'],
                ['name' => 'arrow-left', 'svg' => '<path d="M19 12H5M12 5l-7 7 7 7"/>'],
                ['name' => 'arrow-right', 'svg' => '<path d="M5 12h14M12 5l7 7-7 7"/>'],
                ['name' => 'chevron-up', 'svg' => '<path d="M18 15l-6-6-6 6"/>'],
                ['name' => 'chevron-down', 'svg' => '<path d="M6 9l6 6 6-6"/>'],
                ['name' => 'chevron-left', 'svg' => '<path d="M15 18l-6-6 6-6"/>'],
                ['name' => 'chevron-right', 'svg' => '<path d="M9 18l6-6-6-6"/>'],
            ],
            'general' => [
                ['name' => 'home', 'svg' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/>'],
                ['name' => 'settings', 'svg' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>'],
                ['name' => 'search', 'svg' => '<circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>'],
                ['name' => 'menu', 'svg' => '<path d="M4 12h16M4 6h16M4 18h16"/>'],
                ['name' => 'x', 'svg' => '<path d="M18 6L6 18M6 6l12 12"/>'],
                ['name' => 'check', 'svg' => '<path d="M20 6L9 17l-5-5"/>'],
                ['name' => 'plus', 'svg' => '<path d="M12 5v14M5 12h14"/>'],
                ['name' => 'minus', 'svg' => '<path d="M5 12h14"/>'],
                ['name' => 'star', 'svg' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
                ['name' => 'heart', 'svg' => '<path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>'],
            ],
            'communication' => [
                ['name' => 'mail', 'svg' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/>'],
                ['name' => 'phone', 'svg' => '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>'],
                ['name' => 'message-circle', 'svg' => '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>'],
                ['name' => 'bell', 'svg' => '<path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>'],
                ['name' => 'send', 'svg' => '<path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>'],
                ['name' => 'share', 'svg' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/>'],
            ],
            'media' => [
                ['name' => 'image', 'svg' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>'],
                ['name' => 'camera', 'svg' => '<path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/>'],
                ['name' => 'video', 'svg' => '<polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/>'],
                ['name' => 'music', 'svg' => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>'],
                ['name' => 'play', 'svg' => '<polygon points="5 3 19 12 5 21 5 3"/>'],
                ['name' => 'pause', 'svg' => '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>'],
            ],
            'files' => [
                ['name' => 'file', 'svg' => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>'],
                ['name' => 'folder', 'svg' => '<path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>'],
                ['name' => 'download', 'svg' => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>'],
                ['name' => 'upload', 'svg' => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>'],
                ['name' => 'trash', 'svg' => '<path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>'],
                ['name' => 'edit', 'svg' => '<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>'],
            ],
            'security' => [
                ['name' => 'lock', 'svg' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>'],
                ['name' => 'unlock', 'svg' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 019.9-1"/>'],
                ['name' => 'shield', 'svg' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
                ['name' => 'key', 'svg' => '<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>'],
                ['name' => 'eye', 'svg' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'],
                ['name' => 'eye-off', 'svg' => '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/>'],
            ],
            'business' => [
                ['name' => 'briefcase', 'svg' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v3"/>'],
                ['name' => 'building', 'svg' => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/>'],
                ['name' => 'chart-bar', 'svg' => '<path d="M12 20V10M18 20V4M6 20v-4"/>'],
                ['name' => 'trending-up', 'svg' => '<path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/>'],
                ['name' => 'dollar-sign', 'svg' => '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>'],
                ['name' => 'credit-card', 'svg' => '<rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>'],
                ['name' => 'shopping-cart', 'svg' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>'],
            ],
            'social' => [
                ['name' => 'user', 'svg' => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
                ['name' => 'users', 'svg' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>'],
                ['name' => 'thumbs-up', 'svg' => '<path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3zM7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/>'],
                ['name' => 'award', 'svg' => '<circle cx="12" cy="8" r="7"/><path d="M8.21 13.89L7 23l5-3 5 3-1.21-9.12"/>'],
                ['name' => 'gift', 'svg' => '<path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/>'],
            ],
            'misc' => [
                ['name' => 'zap', 'svg' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>'],
                ['name' => 'globe', 'svg' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>'],
                ['name' => 'map-pin', 'svg' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>'],
                ['name' => 'sun', 'svg' => '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>'],
                ['name' => 'moon', 'svg' => '<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>'],
                ['name' => 'clock', 'svg' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'],
                ['name' => 'calendar', 'svg' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
                ['name' => 'rocket', 'svg' => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 00-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 012-3.95A12.88 12.88 0 0122 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 01-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>'],
                ['name' => 'sparkles', 'svg' => '<path d="M9.937 15.5A2 2 0 008.5 14.063l-6.135-1.582a.5.5 0 010-.962L8.5 9.936A2 2 0 009.937 8.5l1.582-6.135a.5.5 0 01.963 0L14.063 8.5A2 2 0 0015.5 9.937l6.135 1.582a.5.5 0 010 .963L15.5 14.063a2 2 0 00-1.437 1.437l-1.582 6.135a.5.5 0 01-.963 0z"/><path d="M20 3v4M22 5h-4M4 17v2M5 18H3"/>'],
            ],
        ],
        'emojiIcons' => [
            'success' => ['⭐', '🌟', '✨', '💫', '🎯', '🏆', '🥇', '🏅', '👑', '💎', '🔥', '⚡', '💯', '✅', '👍', '💪'],
            'business' => ['💼', '📊', '📈', '💰', '💵', '💳', '🏦', '🏢', '👔', '📋', '📁', '✉️', '📧', '💬', '📱', '💻'],
            'nature' => ['🌍', '🌲', '🌳', '🌴', '🌿', '☘️', '🍀', '🌺', '🌸', '🌷', '🌹', '☀️', '🌙', '⭐', '🌈', '💧'],
            'people' => ['👤', '👥', '👫', '🤝', '👍', '👎', '👏', '🙌', '💪', '✌️', '👋', '🙏', '❤️', '😀', '😊', '🎉'],
            'objects' => ['🔧', '🔨', '⚙️', '🔩', '🛠️', '📦', '🎁', '🏠', '🚀', '✈️', '🚗', '⏰', '📅', '🔔', '🔒', '🔑'],
            'symbols' => ['⬆️', '➡️', '⬇️', '⬅️', '↗️', '↘️', '↙️', '↖️', '🔄', '▶️', '⏸️', '⏹️', '❌', '⭕', '❓', '❗'],
        ],
    ];
}

/**
 * Render icon HTML from format string
 * Supports: lucide:name, hero:name, or direct emoji
 */
function tb_render_icon(string $iconValue, int $size = 24): string
{
    if (empty($iconValue)) {
        return '<span style="font-size:' . $size . 'px">⭐</span>';
    }
    
    $icons = tb_get_icons_data();
    
    if (str_starts_with($iconValue, 'lucide:')) {
        $name = substr($iconValue, 7);
        foreach ($icons['lucideIcons'] as $category) {
            foreach ($category as $icon) {
                if ($icon['name'] === $name) {
                    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $icon['svg'] . '</svg>';
                }
            }
        }
    }
    
    // Emoji fallback
    return '<span style="font-size:' . $size . 'px">' . htmlspecialchars($iconValue, ENT_QUOTES, 'UTF-8') . '</span>';
}
