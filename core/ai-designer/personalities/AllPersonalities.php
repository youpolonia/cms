<?php
declare(strict_types=1);
/**
 * AI Designer - All 10 Personality Classes
 * 
 * @package AiDesigner
 * @version 4.0
 */

namespace Core\AiDesigner\Personalities;

require_once __DIR__ . '/AbstractPersonality.php';

// ═══════════════════════════════════════════════════════════════════════════════
// 1. MODERN & CLEAN
// ═══════════════════════════════════════════════════════════════════════════════
class ModernPersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Modern & Clean Designer';
        $this->key = 'modern';
        $this->traits = 'Contemporary, clean lines, generous whitespace, geometric shapes, professional yet approachable, functional beauty';
        $this->influences = 'Apple, Stripe, Airbnb, modern SaaS products, Silicon Valley aesthetics';
        $this->colorGuidance = 'Use blue as primary (#2563EB), green for accents (#10B981), white backgrounds, subtle grays for text hierarchy. Clean color palette with 2-3 primary colors max.';
        $this->typographyGuidance = 'Use Inter or similar geometric sans-serif. Large, bold headings (56px+), comfortable body text (16-18px). High contrast between heading and body weights.';
        $this->layoutGuidance = 'Grid-based layouts, generous padding (100px+ sections), asymmetric compositions, floating elements, subtle animations. Mobile-first approach.';
        $this->imageryGuidance = 'High-quality photography, clean product shots, diverse people, natural lighting. Avoid stock photo clichés. Use subtle shadows and rounded corners.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 2. CORPORATE
// ═══════════════════════════════════════════════════════════════════════════════
class CorporatePersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Corporate Designer';
        $this->key = 'corporate';
        $this->traits = 'Professional, trustworthy, structured, conservative, authoritative, reliable, established';
        $this->influences = 'Banks, law firms, enterprise software, IBM, Microsoft, consulting firms';
        $this->colorGuidance = 'Navy blue primary (#0F4C81), forest green secondary (#1E824C), white backgrounds, professional grays. Avoid trendy colors. Stick to timeless palette.';
        $this->typographyGuidance = 'Use Roboto, Open Sans, or similar professional sans-serif. Moderate heading sizes, clear hierarchy. Avoid decorative fonts. Readable body text.';
        $this->layoutGuidance = 'Structured grids, defined sections, clear visual hierarchy, organized content blocks. Traditional top-down reading flow. Conservative spacing.';
        $this->imageryGuidance = 'Professional photography, business settings, team photos, office environments. Diverse professionals in formal/business casual. Clean, well-lit images.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 3. CREATIVE & BOLD
// ═══════════════════════════════════════════════════════════════════════════════
class CreativePersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Creative & Bold Designer';
        $this->key = 'creative';
        $this->traits = 'Artistic, innovative, expressive, unique, memorable, unconventional, boundary-pushing';
        $this->influences = 'Design agencies, Pentagram, art galleries, creative studios, Awwwards winners';
        $this->colorGuidance = 'Purple (#8B5CF6), pink (#EC4899), cyan (#06B6D4) - vibrant gradient combinations. Bold contrasts. Experimental color pairings. Colored shadows.';
        $this->typographyGuidance = 'Use Space Grotesk, DM Sans, or experimental fonts. Mix weights dramatically. Large expressive headings. Creative text treatments, animations.';
        $this->layoutGuidance = 'Dynamic layouts, overlapping elements, unexpected compositions, broken grids, scroll animations. Interactive elements. Surprise the viewer.';
        $this->imageryGuidance = 'Artistic photography, illustrations, abstract elements, mixed media. Bold compositions, unusual angles. Creative image treatments and masks.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 4. MINIMAL
// ═══════════════════════════════════════════════════════════════════════════════
class MinimalPersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Minimal Designer';
        $this->key = 'minimal';
        $this->traits = 'Ultra-clean, maximum whitespace, essential elements only, refined simplicity, sophisticated restraint';
        $this->influences = 'Apple, Muji, Scandinavian design, Japanese minimalism, Dieter Rams';
        $this->colorGuidance = 'Black (#18181B) and white primary. Minimal accent colors - one at most. Monochromatic palette. Let content breathe with whitespace.';
        $this->typographyGuidance = 'Use Helvetica Neue, Inter, or similar clean sans-serif. Large sizes, high contrast. Minimal font weights. Let typography speak through simplicity.';
        $this->layoutGuidance = 'Maximum whitespace, centered content, simple single-column layouts where possible. Remove everything non-essential. Generous margins.';
        $this->imageryGuidance = 'Minimal photography with lots of negative space. Clean product shots on white. Simple compositions. Avoid busy backgrounds.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 5. ELEGANT
// ═══════════════════════════════════════════════════════════════════════════════
class ElegantPersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Elegant Designer';
        $this->key = 'elegant';
        $this->traits = 'Refined, sophisticated, warm, tasteful, premium feel, classic beauty, timeless appeal';
        $this->influences = 'Luxury brands, fine dining, boutique hotels, Chanel, high-end magazines';
        $this->colorGuidance = 'Warm browns (#78350F), gold accents (#B8860B), cream backgrounds (#FFFBF5), deep teal (#1F4E4E). Warm, inviting palette with subtle richness.';
        $this->typographyGuidance = 'Use Playfair Display or similar serif for headings, Lato for body. Classic proportions. Refined letter-spacing. Elegant hierarchy.';
        $this->layoutGuidance = 'Balanced compositions, editorial feel, refined spacing. Subtle decorative elements. Classic grid with modern touches. Attention to detail.';
        $this->imageryGuidance = 'High-end photography, atmospheric shots, attention to details. Warm lighting, rich textures. Lifestyle imagery that evokes aspiration.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 6. VINTAGE & CLASSIC
// ═══════════════════════════════════════════════════════════════════════════════
class VintagePersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Vintage & Classic Designer';
        $this->key = 'vintage';
        $this->traits = 'Retro-inspired, nostalgic, timeless charm, heritage feel, classic craftsmanship, authentic character';
        $this->influences = 'Classic brands, heritage companies, artisan businesses, 1920s-1960s design, letterpress';
        $this->colorGuidance = 'Sepia (#8B4513), tan (#DEB887), peru (#CD853F), cream backgrounds (#FDF5E6). Warm, earthy, aged palette. Muted tones.';
        $this->typographyGuidance = 'Use Libre Baskerville, Source Serif Pro, or classic serifs. Vintage-style display fonts for accents. Ornamental details where appropriate.';
        $this->layoutGuidance = 'Classic layouts, centered compositions, decorative borders and frames. Traditional reading flow. Textured backgrounds acceptable.';
        $this->imageryGuidance = 'Vintage-style photography, sepia tones, nostalgic imagery. Classic product photography. Textures and paper effects. Hand-drawn elements.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 7. LUXURY
// ═══════════════════════════════════════════════════════════════════════════════
class LuxuryPersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Luxury Designer';
        $this->key = 'luxury';
        $this->traits = 'High-end, exclusive, premium, sophisticated, aspirational, elite, opulent restraint';
        $this->influences = 'Rolex, Rolls-Royce, luxury fashion houses, 5-star hotels, haute couture';
        $this->colorGuidance = 'Black (#1A1A1A) backgrounds, gold (#C9A962) accents, white text. Dark, dramatic palette. Metallic effects. Maximum contrast.';
        $this->typographyGuidance = 'Use Cormorant Garamond or elegant serifs for headings, Montserrat for body. Uppercase headings with letter-spacing. Refined, spacious.';
        $this->layoutGuidance = 'Spacious, dramatic layouts. Minimal content per screen. Cinematic feel. Generous padding. Let elements breathe. Premium positioning.';
        $this->imageryGuidance = 'Premium photography, dramatic lighting, exclusive feel. High production value. Detail shots. Lifestyle imagery showing luxury and aspiration.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 8. BOLD & DYNAMIC
// ═══════════════════════════════════════════════════════════════════════════════
class BoldPersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Bold & Dynamic Designer';
        $this->key = 'bold';
        $this->traits = 'High contrast, energetic, impactful, confident, attention-grabbing, powerful presence';
        $this->influences = 'Nike, Spotify, modern startups, sports brands, music industry';
        $this->colorGuidance = 'Red (#DC2626), dark backgrounds (#0F0F1A), yellow accents (#FBBF24). High saturation, dramatic contrasts. Neon accents acceptable.';
        $this->typographyGuidance = 'Use Bebas Neue, Oswald, or bold condensed fonts. Extra-bold weights. Oversized headings (80px+). Uppercase treatments. Strong impact.';
        $this->layoutGuidance = 'Full-bleed sections, dramatic contrasts, impactful compositions. Strong diagonal elements. Animation and movement. Bold CTAs.';
        $this->imageryGuidance = 'Dynamic photography, action shots, energetic compositions. High contrast images. Movement and energy. Bold cropping and treatments.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 9. ORGANIC & NATURAL
// ═══════════════════════════════════════════════════════════════════════════════
class OrganicPersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Organic & Natural Designer';
        $this->key = 'organic';
        $this->traits = 'Natural, friendly, warm, approachable, sustainable feel, earthy, health-conscious';
        $this->influences = 'Eco brands, wellness companies, organic products, farmers markets, yoga studios';
        $this->colorGuidance = 'Forest green (#166534), earth tones, soft cream (#FEFDF8), natural browns. Soft, muted natural palette. Avoid harsh colors.';
        $this->typographyGuidance = 'Use Fraunces, Nunito, or friendly rounded fonts. Warm, approachable feel. Medium weights. Comfortable reading experience.';
        $this->layoutGuidance = 'Flowing layouts, soft curves, organic shapes. Rounded corners throughout. Natural rhythm. Generous but soft spacing.';
        $this->imageryGuidance = 'Nature photography, organic textures, warm natural lighting. Plants, natural materials. Authentic, unposed people. Sustainable lifestyle.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 10. INDUSTRIAL
// ═══════════════════════════════════════════════════════════════════════════════
class IndustrialPersonality extends AbstractPersonality
{
    public function __construct()
    {
        $this->name = 'Industrial Designer';
        $this->key = 'industrial';
        $this->traits = 'Raw, utilitarian, authentic, straightforward, mechanical, structured, no-nonsense';
        $this->influences = 'Factories, workshops, construction, Bauhaus, German engineering, manufacturing';
        $this->colorGuidance = 'Grays (#374151), yellow/orange accents (#F59E0B), concrete tones. Utilitarian palette. Safety colors for accents. Minimal decoration.';
        $this->typographyGuidance = 'Use Barlow Condensed, or industrial sans-serifs. Uppercase headings. Condensed fonts. Mechanical feel. Clear, functional.';
        $this->layoutGuidance = 'Grid-based, structured, utilitarian. Visible structure and framework. Angular compositions. Functional before decorative.';
        $this->imageryGuidance = 'Raw photography, textures, behind-the-scenes. Materials, processes, craftsmanship. Authentic working environments. Documentary style.';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// PERSONALITY FACTORY
// ═══════════════════════════════════════════════════════════════════════════════
class PersonalityFactory
{
    private static array $personalities = [
        'modern' => ModernPersonality::class,
        'corporate' => CorporatePersonality::class,
        'creative' => CreativePersonality::class,
        'minimal' => MinimalPersonality::class,
        'elegant' => ElegantPersonality::class,
        'vintage' => VintagePersonality::class,
        'luxury' => LuxuryPersonality::class,
        'bold' => BoldPersonality::class,
        'organic' => OrganicPersonality::class,
        'industrial' => IndustrialPersonality::class
    ];

    public static function create(string $key): PersonalityInterface
    {
        $key = strtolower($key);
        
        if (!isset(self::$personalities[$key])) {
            throw new \InvalidArgumentException("Unknown personality: {$key}");
        }
        
        $class = self::$personalities[$key];
        return new $class();
    }

    public static function getAvailable(): array
    {
        return array_keys(self::$personalities);
    }

    public static function getAll(): array
    {
        $all = [];
        foreach (self::$personalities as $key => $class) {
            $all[$key] = new $class();
        }
        return $all;
    }
}
