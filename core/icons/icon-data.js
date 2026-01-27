/**
 * Centralized Icon Data
 * Inline SVG icons - no CDN dependencies
 */

// Extend TB object with icon data and functions
Object.assign(TB, {
    iconPickerCallback: null,
    currentIconStyle: 'lucide',
    currentIconCategory: 'all',
    
    // Lucide Icons (line style, modern)
    lucideIcons: {
        arrows: [
            { name: 'arrow-up', svg: '<path d="M12 19V5M5 12l7-7 7 7"/>' },
            { name: 'arrow-down', svg: '<path d="M12 5v14M5 12l7 7 7-7"/>' },
            { name: 'arrow-left', svg: '<path d="M19 12H5M12 5l-7 7 7 7"/>' },
            { name: 'arrow-right', svg: '<path d="M5 12h14M12 5l7 7-7 7"/>' },
            { name: 'chevron-up', svg: '<path d="M18 15l-6-6-6 6"/>' },
            { name: 'chevron-down', svg: '<path d="M6 9l6 6 6-6"/>' },
            { name: 'chevron-left', svg: '<path d="M15 18l-6-6 6-6"/>' },
            { name: 'chevron-right', svg: '<path d="M9 18l6-6-6-6"/>' },
            { name: 'move', svg: '<path d="M5 9l-3 3 3 3M9 5l3-3 3 3M15 19l-3 3-3-3M19 9l3 3-3 3M2 12h20M12 2v20"/>' }
        ],
        general: [
            { name: 'home', svg: '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/>' },
            { name: 'settings', svg: '<circle cx="12" cy="12" r="3"/><path d="M12 1v3M12 20v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M1 12h3M20 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"/>' },
            { name: 'search', svg: '<circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>' },
            { name: 'menu', svg: '<path d="M4 12h16M4 6h16M4 18h16"/>' },
            { name: 'x', svg: '<path d="M18 6L6 18M6 6l12 12"/>' },
            { name: 'check', svg: '<path d="M20 6L9 17l-5-5"/>' },
            { name: 'plus', svg: '<path d="M12 5v14M5 12h14"/>' },
            { name: 'minus', svg: '<path d="M5 12h14"/>' },
            { name: 'more-horizontal', svg: '<circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>' },
            { name: 'filter', svg: '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>' },
            { name: 'refresh-cw', svg: '<path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>' }
        ],
        business: [
            { name: 'briefcase', svg: '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v3"/>' },
            { name: 'building', svg: '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/>' },
            { name: 'chart-bar', svg: '<path d="M12 20V10M18 20V4M6 20v-4"/>' },
            { name: 'trending-up', svg: '<path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/>' },
            { name: 'pie-chart', svg: '<path d="M21.21 15.89A10 10 0 118 2.83"/><path d="M22 12A10 10 0 0012 2v10z"/>' },
            { name: 'dollar-sign', svg: '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>' },
            { name: 'credit-card', svg: '<rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>' },
            { name: 'wallet', svg: '<path d="M20 12V8H6a2 2 0 01-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12a2 2 0 002 2h14v-4"/><path d="M18 12a2 2 0 00-2 2c0 1.1.9 2 2 2h4v-4h-4z"/>' },
            { name: 'target', svg: '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>' }
        ],
        communication: [
            { name: 'mail', svg: '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/>' },
            { name: 'message-circle', svg: '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>' },
            { name: 'phone', svg: '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>' },
            { name: 'video', svg: '<polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/>' },
            { name: 'bell', svg: '<path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>' },
            { name: 'send', svg: '<path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>' },
            { name: 'share', svg: '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/>' }
        ],
        tech: [
            { name: 'monitor', svg: '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>' },
            { name: 'smartphone', svg: '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/>' },
            { name: 'laptop', svg: '<path d="M20 16V7a2 2 0 00-2-2H6a2 2 0 00-2 2v9m16 0H4m16 0l1.28 2.55a1 1 0 01-.9 1.45H3.62a1 1 0 01-.9-1.45L4 16"/>' },
            { name: 'server', svg: '<rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><path d="M6 6h.01M6 18h.01"/>' },
            { name: 'database', svg: '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>' },
            { name: 'cloud', svg: '<path d="M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z"/>' },
            { name: 'cpu', svg: '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3"/>' },
            { name: 'wifi', svg: '<path d="M5 12.55a11 11 0 0114.08 0M1.42 9a16 16 0 0121.16 0M8.53 16.11a6 6 0 016.95 0M12 20h.01"/>' },
            { name: 'code', svg: '<path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/>' },
            { name: 'terminal', svg: '<path d="M4 17l6-6-6-6M12 19h8"/>' }
        ],
        media: [
            { name: 'image', svg: '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>' },
            { name: 'camera', svg: '<path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/>' },
            { name: 'film', svg: '<rect x="2" y="2" width="20" height="20" rx="2.18"/><path d="M7 2v20M17 2v20M2 12h20M2 7h5M2 17h5M17 17h5M17 7h5"/>' },
            { name: 'music', svg: '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>' },
            { name: 'headphones', svg: '<path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>' },
            { name: 'mic', svg: '<path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/><path d="M19 10v2a7 7 0 01-14 0v-2M12 19v4M8 23h8"/>' },
            { name: 'play', svg: '<polygon points="5 3 19 12 5 21 5 3"/>' },
            { name: 'pause', svg: '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>' },
            { name: 'volume-2', svg: '<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"/>' }
        ],
        security: [
            { name: 'lock', svg: '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>' },
            { name: 'unlock', svg: '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 019.9-1"/>' },
            { name: 'shield', svg: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>' },
            { name: 'key', svg: '<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>' },
            { name: 'eye', svg: '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>' },
            { name: 'eye-off', svg: '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/>' }
        ],
        files: [
            { name: 'file', svg: '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>' },
            { name: 'folder', svg: '<path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>' },
            { name: 'download', svg: '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>' },
            { name: 'upload', svg: '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>' },
            { name: 'clipboard', svg: '<path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/>' },
            { name: 'trash', svg: '<path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>' }
        ],
        social: [
            { name: 'users', svg: '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>' },
            { name: 'user', svg: '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>' },
            { name: 'user-plus', svg: '<path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/>' },
            { name: 'heart', svg: '<path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>' },
            { name: 'thumbs-up', svg: '<path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3zM7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/>' },
            { name: 'star', svg: '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>' },
            { name: 'award', svg: '<circle cx="12" cy="8" r="7"/><path d="M8.21 13.89L7 23l5-3 5 3-1.21-9.12"/>' },
            { name: 'gift', svg: '<path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/>' }
        ],
        misc: [
            { name: 'zap', svg: '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>' },
            { name: 'globe', svg: '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>' },
            { name: 'map-pin', svg: '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>' },
            { name: 'sun', svg: '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>' },
            { name: 'moon', svg: '<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>' },
            { name: 'clock', svg: '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>' },
            { name: 'calendar', svg: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>' },
            { name: 'flag', svg: '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/>' },
            { name: 'bookmark', svg: '<path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/>' },
            { name: 'tag', svg: '<path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><path d="M7 7h.01"/>' },
            { name: 'layers', svg: '<polygon points="12 2 2 7 12 12 22 7 12 2"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>' },
            { name: 'rocket', svg: '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 00-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 012-3.95A12.88 12.88 0 0122 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 01-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>' },
            { name: 'sparkles', svg: '<path d="M12 3L14.12 8.88L20 11L14.12 13.12L12 19L9.88 13.12L4 11L9.88 8.88L12 3zM19 2v4M21 4h-4M5 18v2M6 19H4"/>' }
        ]
    },

    // Heroicons (solid style)
    heroicons: {
        general: [
            { name: 'home', svg: '<path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>' },
            { name: 'cog', svg: '<path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>' },
            { name: 'search', svg: '<path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>' },
            { name: 'check', svg: '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>' },
            { name: 'x', svg: '<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>' },
            { name: 'plus', svg: '<path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>' },
            { name: 'menu', svg: '<path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>' }
        ],
        business: [
            { name: 'briefcase', svg: '<path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/><path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>' },
            { name: 'chart-bar', svg: '<path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>' },
            { name: 'currency-dollar', svg: '<path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>' },
            { name: 'shopping-cart', svg: '<path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>' }
        ],
        social: [
            { name: 'heart', svg: '<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>' },
            { name: 'star', svg: '<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>' },
            { name: 'user', svg: '<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>' },
            { name: 'user-group', svg: '<path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>' },
            { name: 'thumb-up', svg: '<path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>' }
        ],
        security: [
            { name: 'lock-closed', svg: '<path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>' },
            { name: 'shield-check', svg: '<path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>' },
            { name: 'key', svg: '<path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>' },
            { name: 'eye', svg: '<path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>' }
        ],
        misc: [
            { name: 'lightning-bolt', svg: '<path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>' },
            { name: 'fire', svg: '<path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>' },
            { name: 'globe-alt', svg: '<path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd"/>' },
            { name: 'sparkles', svg: '<path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>' },
            { name: 'clock', svg: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>' },
            { name: 'calendar', svg: '<path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>' }
        ]
    },
    
    // Emoji icons
    emojiIcons: {
        business: ['💼', '📊', '📈', '📉', '💹', '💰', '💵', '💳', '🏦', '🏢', '🏭', '🏗️', '👔', '📋', '📁', '📂'],
        tech: ['💻', '🖥️', '⌨️', '🖱️', '📱', '📲', '☎️', '📞', '🔌', '🔋', '💾', '💿', '📀', '🎮', '🕹️', '🎧'],
        communication: ['✉️', '📧', '📨', '📩', '📤', '📥', '📫', '📬', '💬', '💭', '🗨️', '📢', '📣', '🔔'],
        success: ['⭐', '🌟', '✨', '💫', '🎯', '🏆', '🥇', '🥈', '🥉', '🏅', '🎖️', '👑', '💎', '🔥', '⚡', '💯', '✅'],
        security: ['🔒', '🔓', '🔐', '🔑', '🗝️', '🛡️', '⚔️', '🔰', '🚨', '👮', '🔏', '🔎', '🔍', '👁️'],
        nature: ['🌍', '🌎', '🌏', '🌐', '🗺️', '🌲', '🌳', '🌴', '🌵', '🌾', '🌿', '☘️', '🍀', '🌺', '🌸', '🌷', '🌹'],
        weather: ['☀️', '🌤️', '⛅', '🌥️', '☁️', '🌦️', '🌧️', '⛈️', '🌩️', '❄️', '🌊', '💧', '💦', '🌈', '🌙'],
        people: ['👤', '👥', '👫', '🤝', '👍', '👎', '👏', '🙌', '💪', '✌️', '🤞', '👋', '🖐️', '✋', '👐', '🙏'],
        health: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💝', '🏥', '💊'],
        food: ['🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍒', '🍑', '🥭', '🍍', '☕', '🍵', '🧃', '🥤'],
        transport: ['🚀', '✈️', '🛫', '🚁', '🛸', '🚂', '🚗', '🚕', '🚙', '🏎️', '🚌', '🚐', '🛴', '🚲', '🛵'],
        tools: ['🔧', '🔨', '⚒️', '🛠️', '⚙️', '🔩', '⛏️', '🪓', '🔪', '🛡️', '🔮', '🧰', '🧲', '🪜', '🧪'],
        art: ['🎨', '🖼️', '🎭', '🎪', '🎬', '🎤', '🎧', '🎼', '🎵', '🎶', '🎹', '🥁', '🎷', '🎺', '🎸', '🎻'],
        education: ['📚', '📖', '📕', '📗', '📘', '📙', '📓', '📔', '📃', '📜', '📄', '📰', '✏️', '✒️', '🖋️', '🖊️'],
        time: ['⏰', '⏱️', '⏲️', '🕰️', '⌚', '📅', '📆', '🗓️', '⏳', '⌛'],
        shapes: ['⬛', '⬜', '◼️', '◻️', '🔶', '🔷', '🔸', '🔹', '🔺', '🔻', '💠', '🔘', '⭕', '❌', '❓', '❗'],
        arrows: ['⬆️', '↗️', '➡️', '↘️', '⬇️', '↙️', '⬅️', '↖️', '↕️', '↔️', '↩️', '↪️', '🔄', '🔃', '▶️', '◀️']
    },

    // Icon Picker Functions
    switchIconStyle(style) {
        this.currentIconStyle = style;
        this.currentIconCategory = 'all';
        document.querySelectorAll('.tb-icon-tab').forEach(t => t.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById('tbIconSearchInput').value = '';
        this.renderIconCategories();
        this.renderIconGrid();
    },
    
    renderIconCategories() {
        const container = document.getElementById('tbIconCategories');
        let categories = [];
        
        if (this.currentIconStyle === 'lucide') {
            categories = Object.keys(this.lucideIcons);
        } else if (this.currentIconStyle === 'heroicons') {
            categories = Object.keys(this.heroicons);
        } else {
            categories = Object.keys(this.emojiIcons);
        }
        
        container.innerHTML = `
            <span class="tb-icon-category ${this.currentIconCategory === 'all' ? 'active' : ''}" onclick="TB.filterByCategory('all')">All</span>
            ${categories.map(cat => `
                <span class="tb-icon-category ${this.currentIconCategory === cat ? 'active' : ''}" onclick="TB.filterByCategory('${cat}')">${cat.charAt(0).toUpperCase() + cat.slice(1)}</span>
            `).join('')}
        `;
    },
    
    filterByCategory(category) {
        this.currentIconCategory = category;
        document.querySelectorAll('.tb-icon-category').forEach(c => c.classList.remove('active'));
        event.target.classList.add('active');
        this.renderIconGrid();
    },
    
    renderIconGrid(searchQuery = '') {
        const grid = document.getElementById('tbIconGrid');
        let icons = [];
        const q = searchQuery.toLowerCase();
        
        if (this.currentIconStyle === 'lucide') {
            const cats = this.currentIconCategory === 'all' ? Object.keys(this.lucideIcons) : [this.currentIconCategory];
            cats.forEach(cat => {
                if (this.lucideIcons[cat]) {
                    this.lucideIcons[cat].forEach(icon => {
                        if (!q || icon.name.includes(q)) {
                            icons.push({ type: 'lucide', ...icon });
                        }
                    });
                }
            });
            grid.innerHTML = icons.map(icon => `
                <div class="tb-icon-option lucide" onclick="TB.selectIcon('lucide:${icon.name}')" title="${icon.name}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icon.svg}</svg>
                </div>
            `).join('');
        } else if (this.currentIconStyle === 'heroicons') {
            const cats = this.currentIconCategory === 'all' ? Object.keys(this.heroicons) : [this.currentIconCategory];
            cats.forEach(cat => {
                if (this.heroicons[cat]) {
                    this.heroicons[cat].forEach(icon => {
                        if (!q || icon.name.includes(q)) {
                            icons.push({ type: 'heroicons', ...icon });
                        }
                    });
                }
            });
            grid.innerHTML = icons.map(icon => `
                <div class="tb-icon-option heroicons" onclick="TB.selectIcon('hero:${icon.name}')" title="${icon.name}">
                    <svg viewBox="0 0 20 20" fill="currentColor">${icon.svg}</svg>
                </div>
            `).join('');
        } else {
            const cats = this.currentIconCategory === 'all' ? Object.keys(this.emojiIcons) : [this.currentIconCategory];
            cats.forEach(cat => {
                if (this.emojiIcons[cat]) {
                    this.emojiIcons[cat].forEach(emoji => {
                        icons.push(emoji);
                    });
                }
            });
            grid.innerHTML = icons.map(icon => `
                <div class="tb-icon-option emoji" onclick="TB.selectIcon('${icon}')">${icon}</div>
            `).join('');
        }
        
        if (icons.length === 0) {
            grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--tb-text-muted)">No icons found</div>';
        }
    },
    
    openIconPicker(callback) {
        this.iconPickerCallback = callback;
        this.currentIconStyle = 'lucide';
        this.currentIconCategory = 'all';
        
        const modal = document.getElementById('tbIconPickerModal');
        document.querySelectorAll('.tb-icon-tab').forEach((t, i) => t.classList.toggle('active', i === 0));
        document.getElementById('tbIconSearchInput').value = '';
        
        this.renderIconCategories();
        this.renderIconGrid();
        modal.classList.add('active');
    },
    
    closeIconPicker() {
        document.getElementById('tbIconPickerModal').classList.remove('active');
        this.iconPickerCallback = null;
    },
    
    selectIcon(iconValue) {
        if (this.iconPickerCallback) {
            this.iconPickerCallback(iconValue);
        }
        this.closeIconPicker();
        this.showToast && this.showToast('Icon selected: ' + iconValue, 'success');
    },
    
    filterIcons(query) {
        this.renderIconGrid(query);
    },
    
    // Helper to render icon in blocks and canvas
    renderIcon(iconValue, size = 24) {
        if (!iconValue) return '<span style="font-size:' + size + 'px">⭐</span>';
        
        if (iconValue.startsWith('lucide:')) {
            const name = iconValue.replace('lucide:', '');
            for (const cat of Object.values(this.lucideIcons)) {
                const icon = cat.find(i => i.name === name);
                if (icon) {
                    return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + icon.svg + '</svg>';
                }
            }
        }
        
        if (iconValue.startsWith('hero:')) {
            const name = iconValue.replace('hero:', '');
            for (const cat of Object.values(this.heroicons)) {
                const icon = cat.find(i => i.name === name);
                if (icon) {
                    return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 20 20" fill="currentColor">' + icon.svg + '</svg>';
                }
            }
        }
        
        // Emoji fallback or direct emoji
        return '<span style="font-size:' + size + 'px">' + iconValue + '</span>';
    }
});
