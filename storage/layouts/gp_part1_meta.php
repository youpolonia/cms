<?php
/**
 * Golden Plate Layout - Part 1: Metadata & Design System
 */

// Layout metadata
$layoutData = [
    'name' => 'Golden Plate Fine Dining',
    'slug' => 'golden-plate-fine-dining',
    'description' => 'Luxurious dark-themed restaurant template with elegant gold accents. Perfect for upscale restaurants, fine dining establishments, wine bars, and exclusive culinary venues. Features stunning hero sections, menu displays, team showcase, gallery, and reservation system.',
    'category' => 'restaurant',
    'industry' => 'Restaurant & Hospitality',
    'style' => 'luxury',
    'page_count' => 5,
    'thumbnail' => img('gp-hero.jpg'),
    'is_premium' => 0,
    'is_ai_generated' => 0,
    'downloads' => 0,
    'rating' => null,
    'created_by' => 1
];

// Design system
$layoutMeta = [
    'name' => 'Golden Plate Fine Dining',
    'version' => '1.0.0',
    'created' => date('Y-m-d H:i:s'),
    'brief' => 'Luxurious dark-themed restaurant template with gold accents. 2025/2026 design trends.',
        'design_system' => [
        'colors' => [
            'primary_dark' => 'var(--color-background)',
            'secondary_dark' => 'var(--color-surface)',
            'accent' => 'var(--color-accent)',
            'accent_hover' => 'var(--color-accent)',
            'accent_light' => 'var(--color-accent)',
            'white' => 'var(--color-text)',
            'text_light' => 'var(--color-text)',
            'text_muted' => 'var(--color-text-muted)',
            'overlay' => 'rgba(10,10,10,0.8)'
        ],
        'typography' => [
            'heading_font' => 'Playfair Display, serif',
            'body_font' => 'Inter, sans-serif',
            'h1_size' => '64px',
            'h2_size' => '48px',
            'h3_size' => '28px',
            'h4_size' => '22px',
            'body_size' => '16px',
            'line_height' => '1.7'
        ],
        'spacing' => [
            'section_padding' => '100px',
            'element_gap' => '30px',
            'container_max' => '1200px'
        ],
        'effects' => [
            'card_shadow' => '0 8px 32px rgba(0,0,0,0.3)',
            'card_radius' => '0',
            'button_radius' => '0',
            'transition' => '0.3s ease'
        ]
    ]
];
