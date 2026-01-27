<?php
declare(strict_types=1);
/**
 * AI Designer - Design System Generator
 * 
 * STEP 2: Generates complete design system based on analysis and personality.
 * Creates colors, typography, spacing, components that ensure visual consistency.
 *
 * Supports all 10 design styles from AI Theme Builder:
 * modern, corporate, creative, minimal, elegant, vintage, luxury, bold, organic, industrial
 *
 * Supports all 30 industries from AI Theme Builder.
 *
 * @package AiDesigner
 * @version 4.0
 */

namespace Core\AiDesigner;

class DesignSystem
{
    private array $aiSettings;
    
    // ═══════════════════════════════════════════════════════════════════════════
    // 10 DESIGN STYLES - Color Palettes
    // Mapped to AI Theme Builder design styles
    // ═══════════════════════════════════════════════════════════════════════════
    
    private array $styleColors = [
        'modern' => [
            'primary' => '#2563EB',
            'primary_dark' => '#1D4ED8',
            'primary_light' => '#60A5FA',
            'secondary' => '#10B981',
            'accent' => '#F59E0B',
            'background' => '#FFFFFF',
            'surface' => '#F8FAFC',
            'text' => '#1E293B',
            'text_muted' => '#64748B',
            'border' => '#E2E8F0'
        ],
        'corporate' => [
            'primary' => '#0F4C81',
            'primary_dark' => '#0A3A63',
            'primary_light' => '#3B7AB8',
            'secondary' => '#1E824C',
            'accent' => '#E67E22',
            'background' => '#FFFFFF',
            'surface' => '#F5F7FA',
            'text' => '#2C3E50',
            'text_muted' => '#7F8C8D',
            'border' => '#DCE1E6'
        ],
        'creative' => [
            'primary' => '#8B5CF6',
            'primary_dark' => '#7C3AED',
            'primary_light' => '#A78BFA',
            'secondary' => '#EC4899',
            'accent' => '#06B6D4',
            'background' => '#FAFAFA',
            'surface' => '#FFFFFF',
            'text' => '#18181B',
            'text_muted' => '#71717A',
            'border' => '#E4E4E7'
        ],
        'minimal' => [
            'primary' => '#18181B',
            'primary_dark' => '#09090B',
            'primary_light' => '#3F3F46',
            'secondary' => '#A1A1AA',
            'accent' => '#18181B',
            'background' => '#FFFFFF',
            'surface' => '#FAFAFA',
            'text' => '#18181B',
            'text_muted' => '#71717A',
            'border' => '#E4E4E7'
        ],
        'elegant' => [
            'primary' => '#78350F',
            'primary_dark' => '#5C2A0A',
            'primary_light' => '#A65D21',
            'secondary' => '#B8860B',
            'accent' => '#1F4E4E',
            'background' => '#FFFBF5',
            'surface' => '#FFFFFF',
            'text' => '#1C1917',
            'text_muted' => '#78716C',
            'border' => '#E7E5E4'
        ],
        'vintage' => [
            'primary' => '#8B4513',
            'primary_dark' => '#6B3410',
            'primary_light' => '#A0522D',
            'secondary' => '#DEB887',
            'accent' => '#CD853F',
            'background' => '#FDF5E6',
            'surface' => '#FFFAF0',
            'text' => '#3E2723',
            'text_muted' => '#795548',
            'border' => '#D7CCC8'
        ],
        'luxury' => [
            'primary' => '#1A1A1A',
            'primary_dark' => '#000000',
            'primary_light' => '#333333',
            'secondary' => '#C9A962',
            'accent' => '#B8860B',
            'background' => '#0A0A0A',
            'surface' => '#1A1A1A',
            'text' => '#FFFFFF',
            'text_muted' => '#A0A0A0',
            'border' => '#333333'
        ],
        'bold' => [
            'primary' => '#DC2626',
            'primary_dark' => '#B91C1C',
            'primary_light' => '#EF4444',
            'secondary' => '#1E1E2E',
            'accent' => '#FBBF24',
            'background' => '#0F0F1A',
            'surface' => '#1A1A2E',
            'text' => '#FFFFFF',
            'text_muted' => '#9CA3AF',
            'border' => '#374151'
        ],
        'organic' => [
            'primary' => '#166534',
            'primary_dark' => '#14532D',
            'primary_light' => '#22C55E',
            'secondary' => '#A16207',
            'accent' => '#65A30D',
            'background' => '#FEFDF8',
            'surface' => '#F7FEE7',
            'text' => '#1A2E1A',
            'text_muted' => '#4D7C4D',
            'border' => '#D9F99D'
        ],
        'industrial' => [
            'primary' => '#374151',
            'primary_dark' => '#1F2937',
            'primary_light' => '#6B7280',
            'secondary' => '#F59E0B',
            'accent' => '#EF4444',
            'background' => '#F3F4F6',
            'surface' => '#FFFFFF',
            'text' => '#111827',
            'text_muted' => '#6B7280',
            'border' => '#D1D5DB'
        ]
    ];

    // ═══════════════════════════════════════════════════════════════════════════
    // 10 DESIGN STYLES - Typography
    // ═══════════════════════════════════════════════════════════════════════════
    
    private array $styleTypography = [
        'modern' => [
            'heading_font' => 'Inter',
            'body_font' => 'Inter',
            'style' => 'clean geometric sans-serif'
        ],
        'corporate' => [
            'heading_font' => 'Roboto',
            'body_font' => 'Open Sans',
            'style' => 'professional readable'
        ],
        'creative' => [
            'heading_font' => 'Space Grotesk',
            'body_font' => 'DM Sans',
            'style' => 'artistic modern'
        ],
        'minimal' => [
            'heading_font' => 'Helvetica Neue',
            'body_font' => 'Helvetica Neue',
            'style' => 'ultra-clean simple'
        ],
        'elegant' => [
            'heading_font' => 'Playfair Display',
            'body_font' => 'Lato',
            'style' => 'refined serif classic'
        ],
        'vintage' => [
            'heading_font' => 'Libre Baskerville',
            'body_font' => 'Source Serif Pro',
            'style' => 'classic retro serif'
        ],
        'luxury' => [
            'heading_font' => 'Cormorant Garamond',
            'body_font' => 'Montserrat',
            'style' => 'sophisticated premium'
        ],
        'bold' => [
            'heading_font' => 'Bebas Neue',
            'body_font' => 'Oswald',
            'style' => 'impactful strong'
        ],
        'organic' => [
            'heading_font' => 'Fraunces',
            'body_font' => 'Nunito',
            'style' => 'natural friendly warm'
        ],
        'industrial' => [
            'heading_font' => 'Barlow Condensed',
            'body_font' => 'Barlow',
            'style' => 'mechanical structured'
        ]
    ];

    // ═══════════════════════════════════════════════════════════════════════════
    // 30 INDUSTRIES - Color Overrides
    // Industry-specific primary/secondary colors to override style defaults
    // ═══════════════════════════════════════════════════════════════════════════
    
    private array $industryColors = [
        // Core
        'business' => ['primary' => '#2563EB', 'secondary' => '#10B981'],
        'restaurant' => ['primary' => '#B91C1C', 'secondary' => '#D97706'],
        'technology' => ['primary' => '#6366F1', 'secondary' => '#06B6D4'],
        'healthcare' => ['primary' => '#0891B2', 'secondary' => '#10B981'],
        'ecommerce' => ['primary' => '#7C3AED', 'secondary' => '#F59E0B'],
        'professional_services' => ['primary' => '#1E40AF', 'secondary' => '#047857'],
        
        // Beauty & Wellness
        'barber' => ['primary' => '#1F2937', 'secondary' => '#B91C1C'],
        'salon' => ['primary' => '#BE185D', 'secondary' => '#F472B6'],
        'spa' => ['primary' => '#5EEAD4', 'secondary' => '#A78BFA'],
        'fitness' => ['primary' => '#DC2626', 'secondary' => '#1F2937'],
        'yoga' => ['primary' => '#7C3AED', 'secondary' => '#34D399'],
        
        // Food & Hospitality
        'cafe' => ['primary' => '#78350F', 'secondary' => '#D4A574'],
        'bar' => ['primary' => '#1E1B4B', 'secondary' => '#C084FC'],
        'hotel' => ['primary' => '#1E3A5F', 'secondary' => '#C9A962'],
        'catering' => ['primary' => '#7F1D1D', 'secondary' => '#F59E0B'],
        'foodtruck' => ['primary' => '#EA580C', 'secondary' => '#FBBF24'],
        
        // Creative Services
        'photography' => ['primary' => '#18181B', 'secondary' => '#F5F5F5'],
        'wedding' => ['primary' => '#831843', 'secondary' => '#FDF2F8'],
        'music' => ['primary' => '#7C3AED', 'secondary' => '#F472B6'],
        'tattoo' => ['primary' => '#18181B', 'secondary' => '#DC2626'],
        'art' => ['primary' => '#4338CA', 'secondary' => '#F59E0B'],
        
        // Professional
        'realestate' => ['primary' => '#1E40AF', 'secondary' => '#10B981'],
        'finance' => ['primary' => '#0F4C81', 'secondary' => '#059669'],
        'education' => ['primary' => '#4338CA', 'secondary' => '#F59E0B'],
        'nonprofit' => ['primary' => '#047857', 'secondary' => '#0891B2'],
        'automotive' => ['primary' => '#B91C1C', 'secondary' => '#1F2937'],
        
        // Other
        'construction' => ['primary' => '#D97706', 'secondary' => '#374151'],
        'blog' => ['primary' => '#4F46E5', 'secondary' => '#EC4899'],
        'portfolio' => ['primary' => '#18181B', 'secondary' => '#6366F1'],
        'landing' => ['primary' => '#7C3AED', 'secondary' => '#06B6D4']
    ];

    // ═══════════════════════════════════════════════════════════════════════════
    // 10 DESIGN STYLES - Border Radius
    // ═══════════════════════════════════════════════════════════════════════════
    
    private array $styleBorders = [
        'modern' => ['sm' => '6px', 'md' => '12px', 'lg' => '16px', 'xl' => '24px'],
        'corporate' => ['sm' => '4px', 'md' => '6px', 'lg' => '8px', 'xl' => '12px'],
        'creative' => ['sm' => '8px', 'md' => '16px', 'lg' => '24px', 'xl' => '32px'],
        'minimal' => ['sm' => '0', 'md' => '0', 'lg' => '0', 'xl' => '0'],
        'elegant' => ['sm' => '2px', 'md' => '4px', 'lg' => '6px', 'xl' => '8px'],
        'vintage' => ['sm' => '0', 'md' => '2px', 'lg' => '4px', 'xl' => '6px'],
        'luxury' => ['sm' => '0', 'md' => '0', 'lg' => '2px', 'xl' => '4px'],
        'bold' => ['sm' => '4px', 'md' => '8px', 'lg' => '12px', 'xl' => '16px'],
        'organic' => ['sm' => '12px', 'md' => '20px', 'lg' => '28px', 'xl' => '40px'],
        'industrial' => ['sm' => '2px', 'md' => '4px', 'lg' => '4px', 'xl' => '6px']
    ];

    // ═══════════════════════════════════════════════════════════════════════════
    // 10 DESIGN STYLES - Shadows
    // ═══════════════════════════════════════════════════════════════════════════
    
    private array $styleShadows = [
        'modern' => [
            'sm' => '0 1px 3px rgba(0,0,0,0.08)',
            'md' => '0 4px 6px rgba(0,0,0,0.1)',
            'lg' => '0 10px 20px rgba(0,0,0,0.12)',
            'xl' => '0 20px 40px rgba(0,0,0,0.15)'
        ],
        'corporate' => [
            'sm' => '0 1px 2px rgba(0,0,0,0.06)',
            'md' => '0 2px 4px rgba(0,0,0,0.08)',
            'lg' => '0 4px 8px rgba(0,0,0,0.1)',
            'xl' => '0 8px 16px rgba(0,0,0,0.12)'
        ],
        'creative' => [
            'sm' => '0 2px 8px rgba(139,92,246,0.15)',
            'md' => '0 4px 16px rgba(139,92,246,0.2)',
            'lg' => '0 8px 24px rgba(139,92,246,0.25)',
            'xl' => '0 16px 48px rgba(139,92,246,0.3)'
        ],
        'minimal' => [
            'sm' => 'none',
            'md' => '0 1px 2px rgba(0,0,0,0.04)',
            'lg' => '0 2px 4px rgba(0,0,0,0.06)',
            'xl' => '0 4px 8px rgba(0,0,0,0.08)'
        ],
        'elegant' => [
            'sm' => '0 2px 4px rgba(0,0,0,0.06)',
            'md' => '0 4px 12px rgba(0,0,0,0.08)',
            'lg' => '0 8px 24px rgba(0,0,0,0.1)',
            'xl' => '0 16px 48px rgba(0,0,0,0.12)'
        ],
        'vintage' => [
            'sm' => '2px 2px 4px rgba(0,0,0,0.1)',
            'md' => '3px 3px 8px rgba(0,0,0,0.12)',
            'lg' => '4px 4px 16px rgba(0,0,0,0.15)',
            'xl' => '6px 6px 24px rgba(0,0,0,0.18)'
        ],
        'luxury' => [
            'sm' => '0 2px 8px rgba(0,0,0,0.4)',
            'md' => '0 4px 16px rgba(0,0,0,0.5)',
            'lg' => '0 8px 32px rgba(0,0,0,0.6)',
            'xl' => '0 16px 64px rgba(0,0,0,0.7)'
        ],
        'bold' => [
            'sm' => '0 4px 8px rgba(0,0,0,0.3)',
            'md' => '0 8px 16px rgba(0,0,0,0.35)',
            'lg' => '0 16px 32px rgba(0,0,0,0.4)',
            'xl' => '0 24px 48px rgba(0,0,0,0.45)'
        ],
        'organic' => [
            'sm' => '0 2px 6px rgba(22,101,52,0.1)',
            'md' => '0 4px 12px rgba(22,101,52,0.12)',
            'lg' => '0 8px 20px rgba(22,101,52,0.15)',
            'xl' => '0 16px 32px rgba(22,101,52,0.18)'
        ],
        'industrial' => [
            'sm' => '0 2px 4px rgba(0,0,0,0.15)',
            'md' => '0 4px 8px rgba(0,0,0,0.18)',
            'lg' => '0 8px 16px rgba(0,0,0,0.2)',
            'xl' => '0 12px 24px rgba(0,0,0,0.22)'
        ]
    ];

    // ═══════════════════════════════════════════════════════════════════════════
    // 10 DESIGN STYLES - Button Styles
    // ═══════════════════════════════════════════════════════════════════════════
    
    private array $styleButtons = [
        'modern' => ['transform' => 'none', 'letter_spacing' => '0', 'font_weight' => 600],
        'corporate' => ['transform' => 'none', 'letter_spacing' => '0.25px', 'font_weight' => 600],
        'creative' => ['transform' => 'none', 'letter_spacing' => '0.5px', 'font_weight' => 700],
        'minimal' => ['transform' => 'uppercase', 'letter_spacing' => '2px', 'font_weight' => 500],
        'elegant' => ['transform' => 'none', 'letter_spacing' => '1px', 'font_weight' => 500],
        'vintage' => ['transform' => 'uppercase', 'letter_spacing' => '1.5px', 'font_weight' => 600],
        'luxury' => ['transform' => 'uppercase', 'letter_spacing' => '3px', 'font_weight' => 400],
        'bold' => ['transform' => 'uppercase', 'letter_spacing' => '1px', 'font_weight' => 800],
        'organic' => ['transform' => 'none', 'letter_spacing' => '0', 'font_weight' => 600],
        'industrial' => ['transform' => 'uppercase', 'letter_spacing' => '1px', 'font_weight' => 700]
    ];

    public function __construct(array $aiSettings)
    {
        $this->aiSettings = $aiSettings;
    }

    /**
     * Generate complete design system
     */
    public function generate(array $analysis, array $personality, array $input): array
    {
        $style = $input['design_style'] ?? $input['style'] ?? 'modern';
        $industry = $input['industry'] ?? 'business';
        
        // Normalize style name
        $style = $this->normalizeStyleName($style);
        
        // Try AI-generated design system first
        $aiDesignSystem = $this->callAIForDesignSystem($analysis, $personality, $input, $style);
        
        if (!empty($aiDesignSystem) && $this->validateDesignSystem($aiDesignSystem)) {
            return $this->mergeWithDefaults($aiDesignSystem, $style, $industry);
        }
        
        // Fallback to template-based design system
        return $this->generateFromTemplates($style, $industry, $analysis);
    }

    /**
     * Normalize style name to match our keys
     */
    private function normalizeStyleName(string $style): string
    {
        $style = strtolower(trim($style));
        
        // Map UI names to keys (including new expanded styles)
        $styleMap = [
            // Original mappings
            'modern & clean' => 'modern',
            'modern_clean' => 'modern',
            'creative & bold' => 'creative',
            'creative_bold' => 'creative',
            'vintage & classic' => 'vintage',
            'vintage_classic' => 'vintage',
            'bold & dynamic' => 'bold',
            'bold_dynamic' => 'bold',
            'organic & natural' => 'organic',
            'organic_natural' => 'organic',
            
            // New Business & Professional styles
            'professional' => 'corporate',
            'startup' => 'modern',
            'startup & tech' => 'modern',
            'saas' => 'modern',
            'saas product' => 'modern',
            
            // New Creative styles
            'artistic' => 'creative',
            'playful' => 'creative',
            'playful & fun' => 'creative',
            'retro' => 'vintage',
            'retro & nostalgic' => 'vintage',
            
            // New Luxury styles
            'dark' => 'elegant',
            'dark & sophisticated' => 'elegant',
            
            // New Nature styles
            'eco' => 'organic',
            'eco-friendly' => 'organic',
            'wellness' => 'organic',
            'wellness & spa' => 'organic',
            
            // New Bold styles
            'brutalist' => 'bold'
        ];
        
        return $styleMap[$style] ?? $style;
    }

    /**
     * Call AI to generate custom design system
     */
    private function callAIForDesignSystem(array $analysis, array $personality, array $input, string $style): array
    {
        $prompt = $this->buildDesignSystemPrompt($analysis, $personality, $input, $style);
        
        try {
            $response = $this->callAI($prompt);
            
            // Clean response
            $response = trim($response);
            if (strpos($response, '```json') !== false) {
                $response = preg_replace('/```json\s*/', '', $response);
                $response = preg_replace('/```\s*$/', '', $response);
            }
            if (strpos($response, '```') !== false) {
                $response = preg_replace('/```\s*/', '', $response);
            }
            
            $designSystem = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("[AI-Designer] Design system JSON parse error: " . json_last_error_msg());
                return [];
            }
            
            return $designSystem;
        } catch (\Exception $e) {
            error_log("[AI-Designer] Design system AI call failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Build prompt for AI design system generation
     */
    private function buildDesignSystemPrompt(array $analysis, array $personality, array $input, string $style): string
    {
        $businessName = $input['business_name'] ?? 'Business';
        $industry = $input['industry'] ?? 'general';
        
        // Get style info
        $styleColors = $this->styleColors[$style] ?? $this->styleColors['modern'];
        $styleTypo = $this->styleTypography[$style] ?? $this->styleTypography['modern'];
        
        $styleDescription = $this->getStyleDescription($style);
        
        return <<<PROMPT
You are an expert web designer creating a design system for "{$businessName}" ({$industry}).

DESIGN STYLE: {$style}
STYLE CHARACTERISTICS: {$styleDescription}

BASE COLORS (refine these for the specific business):
- Primary: {$styleColors['primary']}
- Secondary: {$styleColors['secondary']}
- Background: {$styleColors['background']}

BASE TYPOGRAPHY:
- Headings: {$styleTypo['heading_font']}
- Body: {$styleTypo['body_font']}

Create a REFINED design system that:
1. Keeps the core style characteristics
2. Adapts colors to perfectly fit {$industry} industry
3. Maintains brand consistency

Respond ONLY with valid JSON:

{
    "colors": {
        "primary": "#hex (refined for this business)",
        "primary_dark": "#hex",
        "primary_light": "#hex",
        "secondary": "#hex",
        "accent": "#hex",
        "background": "#hex",
        "surface": "#hex",
        "text": "#hex",
        "text_muted": "#hex"
    },
    "typography": {
        "heading_font": "Font name",
        "body_font": "Font name"
    }
}
PROMPT;
    }

    /**
     * Get style description for AI prompt
     */
    private function getStyleDescription(string $style): string
    {
        $descriptions = [
            'modern' => 'Clean, contemporary, uses whitespace effectively, geometric shapes, professional yet approachable',
            'corporate' => 'Professional, trustworthy, structured, conservative colors, formal typography',
            'creative' => 'Artistic, bold colors, unique layouts, expressive, memorable, stands out',
            'minimal' => 'Ultra-clean, lots of whitespace, simple typography, monochromatic, essential elements only',
            'elegant' => 'Refined, sophisticated, serif fonts, warm colors, tasteful, premium feel',
            'vintage' => 'Retro-inspired, classic typography, warm earthy tones, nostalgic, timeless',
            'luxury' => 'High-end, dark backgrounds, gold accents, exclusive feel, premium materials',
            'bold' => 'High contrast, large typography, striking colors, energetic, attention-grabbing',
            'organic' => 'Natural colors, rounded shapes, earthy tones, friendly, sustainable feel',
            'industrial' => 'Raw, utilitarian, exposed elements, monochrome with accent, structural'
        ];
        
        return $descriptions[$style] ?? $descriptions['modern'];
    }

    /**
     * Validate AI-generated design system has required fields
     */
    private function validateDesignSystem(array $ds): bool
    {
        if (empty($ds['colors'])) {
            return false;
        }
        
        $requiredColors = ['primary', 'secondary', 'background', 'text'];
        foreach ($requiredColors as $key) {
            if (empty($ds['colors'][$key])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Merge AI design system with defaults
     */
    private function mergeWithDefaults(array $aiDs, string $style, string $industry): array
    {
        $defaults = $this->generateFromTemplates($style, $industry, []);
        
        return [
            'colors' => array_merge($defaults['colors'], $aiDs['colors'] ?? []),
            'typography' => array_merge($defaults['typography'], $aiDs['typography'] ?? []),
            'spacing' => $defaults['spacing'],
            'borders' => $defaults['borders'],
            'shadows' => $defaults['shadows'],
            'components' => $defaults['components'],
            'effects' => $defaults['effects']
        ];
    }

    /**
     * Generate design system from templates (fallback)
     */
    private function generateFromTemplates(string $style, string $industry, array $analysis): array
    {
        // Get base style colors
        $colors = $this->styleColors[$style] ?? $this->styleColors['modern'];
        
        // Override with industry-specific colors if available
        if (isset($this->industryColors[$industry])) {
            $colors['primary'] = $this->industryColors[$industry]['primary'];
            $colors['secondary'] = $this->industryColors[$industry]['secondary'];
            // Recalculate variants
            $colors['primary_dark'] = $this->darkenColor($colors['primary'], 15);
            $colors['primary_light'] = $this->lightenColor($colors['primary'], 30);
        }
        
        // Add utility colors
        $colors['success'] = '#10B981';
        $colors['warning'] = '#F59E0B';
        $colors['error'] = '#EF4444';
        $colors['info'] = '#3B82F6';
        
        // Get typography
        $typography = $this->styleTypography[$style] ?? $this->styleTypography['modern'];
        $typography = $this->expandTypography($typography);
        
        // Get borders
        $borders = $this->styleBorders[$style] ?? $this->styleBorders['modern'];
        $borders['full'] = '9999px';
        
        // Get shadows
        $shadows = $this->styleShadows[$style] ?? $this->styleShadows['modern'];
        
        // Standard spacing scale
        $spacing = [
            'xs' => '4px',
            'sm' => '8px',
            'md' => '16px',
            'lg' => '32px',
            'xl' => '64px',
            '2xl' => '96px',
            '3xl' => '128px'
        ];
        
        // Get button style
        $buttonStyle = $this->styleButtons[$style] ?? $this->styleButtons['modern'];
        
        // Components
        $components = $this->buildComponents($colors, $borders, $shadows, $buttonStyle);
        
        // Effects
        $effects = [
            'transition_fast' => '0.15s ease',
            'transition_normal' => '0.3s ease',
            'transition_slow' => '0.5s ease',
            'hover_lift' => 'translateY(-4px)',
            'hover_scale' => 'scale(1.02)'
        ];
        
        return [
            'style' => $style,
            'industry' => $industry,
            'colors' => $colors,
            'typography' => $typography,
            'spacing' => $spacing,
            'borders' => $borders,
            'shadows' => $shadows,
            'components' => $components,
            'effects' => $effects
        ];
    }

    /**
     * Expand typography with full settings
     */
    private function expandTypography(array $base): array
    {
        return [
            'heading_font' => $base['heading_font'],
            'body_font' => $base['body_font'],
            'style' => $base['style'] ?? 'normal',
            'sizes' => [
                'h1' => '56px',
                'h2' => '42px',
                'h3' => '32px',
                'h4' => '24px',
                'h5' => '20px',
                'body' => '16px',
                'small' => '14px',
                'xs' => '12px'
            ],
            'weights' => [
                'light' => 300,
                'normal' => 400,
                'medium' => 500,
                'semibold' => 600,
                'bold' => 700,
                'extrabold' => 800
            ],
            'line_heights' => [
                'tight' => 1.2,
                'snug' => 1.375,
                'normal' => 1.6,
                'relaxed' => 1.75
            ]
        ];
    }

    /**
     * Build component styles
     */
    private function buildComponents(array $colors, array $borders, array $shadows, array $buttonStyle): array
    {
        return [
            'button_primary' => [
                'background' => $colors['primary'],
                'color' => $this->getContrastColor($colors['primary']),
                'padding' => '14px 28px',
                'border_radius' => $borders['md'],
                'font_weight' => $buttonStyle['font_weight'],
                'text_transform' => $buttonStyle['transform'],
                'letter_spacing' => $buttonStyle['letter_spacing'],
                'shadow' => $shadows['sm'],
                'hover_background' => $colors['primary_dark'] ?? $this->darkenColor($colors['primary'], 10)
            ],
            'button_secondary' => [
                'background' => 'transparent',
                'color' => $colors['primary'],
                'border' => '2px solid ' . $colors['primary'],
                'padding' => '12px 26px',
                'border_radius' => $borders['md'],
                'font_weight' => $buttonStyle['font_weight'],
                'text_transform' => $buttonStyle['transform'],
                'hover_background' => $colors['primary'],
                'hover_color' => $this->getContrastColor($colors['primary'])
            ],
            'button_ghost' => [
                'background' => 'transparent',
                'color' => $colors['text'],
                'padding' => '12px 24px',
                'border_radius' => $borders['md'],
                'hover_background' => $colors['surface']
            ],
            'card' => [
                'background' => $colors['surface'],
                'border_radius' => $borders['lg'],
                'shadow' => $shadows['md'],
                'padding' => '32px',
                'border' => '1px solid ' . ($colors['border'] ?? '#e5e5e5')
            ],
            'input' => [
                'background' => $colors['background'],
                'border' => '1px solid ' . ($colors['border'] ?? '#e5e5e5'),
                'border_radius' => $borders['sm'],
                'padding' => '12px 16px',
                'focus_border_color' => $colors['primary'],
                'focus_shadow' => '0 0 0 3px ' . $this->hexToRgba($colors['primary'], 0.15)
            ],
            'badge' => [
                'background' => $colors['primary_light'] ?? $this->lightenColor($colors['primary'], 40),
                'color' => $colors['primary_dark'] ?? $colors['primary'],
                'padding' => '4px 12px',
                'border_radius' => $borders['full'],
                'font_size' => '12px',
                'font_weight' => 600
            ]
        ];
    }

    /**
     * Get contrasting text color (white or black) for a background
     */
    private function getContrastColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Calculate luminance
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        return $luminance > 0.5 ? '#000000' : '#FFFFFF';
    }

    /**
     * Convert hex to rgba
     */
    private function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "rgba({$r},{$g},{$b},{$alpha})";
    }

    /**
     * Darken a hex color
     */
    private function darkenColor(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, (int)($r - ($r * $percent / 100)));
        $g = max(0, (int)($g - ($g * $percent / 100)));
        $b = max(0, (int)($b - ($b * $percent / 100)));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Lighten a hex color
     */
    private function lightenColor(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = min(255, (int)($r + ((255 - $r) * $percent / 100)));
        $g = min(255, (int)($g + ((255 - $g) * $percent / 100)));
        $b = min(255, (int)($b + ((255 - $b) * $percent / 100)));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Get available styles
     */
    public function getAvailableStyles(): array
    {
        return [
            'modern' => '✨ Modern & Clean',
            'corporate' => '🏛️ Corporate',
            'creative' => '🎨 Creative & Bold',
            'minimal' => '⬜ Minimal',
            'elegant' => '👑 Elegant',
            'vintage' => '📜 Vintage & Classic',
            'luxury' => '💎 Luxury',
            'bold' => '🔥 Bold & Dynamic',
            'organic' => '🌿 Organic & Natural',
            'industrial' => '🏭 Industrial'
        ];
    }

    /**
     * Get available industries
     */
    public function getAvailableIndustries(): array
    {
        return [
            'business' => '🏢 Business',
            'restaurant' => '🍽️ Restaurant',
            'technology' => '💻 Technology',
            'healthcare' => '🏥 Healthcare',
            'ecommerce' => '🛒 E-commerce',
            'professional_services' => '💼 Professional Services',
            'barber' => '💈 Barbershop',
            'salon' => '💇 Hair Salon',
            'spa' => '🧖 Spa & Wellness',
            'fitness' => '💪 Fitness / Gym',
            'yoga' => '🧘 Yoga Studio',
            'cafe' => '☕ Cafe / Coffee',
            'bar' => '🍸 Bar / Cocktails',
            'hotel' => '🏨 Hotel',
            'catering' => '🍴 Catering',
            'foodtruck' => '🚚 Food Truck',
            'photography' => '📷 Photography',
            'wedding' => '💒 Wedding Planner',
            'music' => '🎵 Music / Band',
            'tattoo' => '🎨 Tattoo Studio',
            'art' => '🖼️ Art / Gallery',
            'realestate' => '🏠 Real Estate',
            'finance' => '💰 Finance',
            'education' => '🎓 Education',
            'nonprofit' => '💚 Non-Profit',
            'automotive' => '🚗 Automotive',
            'construction' => '🏗️ Construction',
            'blog' => '📝 Blog',
            'portfolio' => '🎯 Portfolio',
            'landing' => '🚀 Landing Page'
        ];
    }

    /**
     * Call AI API
     */
    private function callAI(string $prompt): string
    {
        $provider = $this->aiSettings['default_provider'] ?? 'openai';
        $config = $this->aiSettings['providers'][$provider] ?? [];
        
        if (empty($config['api_key'])) {
            throw new \Exception("AI provider not configured");
        }
        
        $model = $config['model'] ?? 'gpt-5.2';
        
        if ($provider === 'openai') {
            return $this->callOpenAI($config['api_key'], $model, $prompt);
        } elseif ($provider === 'anthropic') {
            return $this->callAnthropic($config['api_key'], $model, $prompt);
        }
        
        throw new \Exception("Unknown AI provider: {$provider}");
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $apiKey, string $model, string $prompt): string
    {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a design system expert. Output only valid JSON.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.7
            ])
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception("OpenAI API error: HTTP {$httpCode}");
        }
        
        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Call Anthropic API
     */
    private function callAnthropic(string $apiKey, string $model, string $prompt): string
    {
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'max_tokens' => 2000,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ])
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception("Anthropic API error: HTTP {$httpCode}");
        }
        
        $data = json_decode($response, true);
        return $data['content'][0]['text'] ?? '';
    }
}
