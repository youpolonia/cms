<?php
/**
 * Template Library Seeder
 * Seeds professional premade templates from JTB_Layout_Library
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Library_Seeder
{
    /**
     * Seed premade templates from Layout Library
     * @return int Number of templates seeded
     */
    public static function seed(): int
    {
        // Check if already seeded
        $db = \core\Database::connection();
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM jtb_library_templates WHERE is_premade = 1");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ((int)$row['cnt'] > 0) {
            // Already seeded
            return 0;
        }

        // Get layouts from Layout Library
        $layouts = JTB_Layout_Library::getLayouts();
        $count = 0;

        // Seed page layouts
        foreach ($layouts['pages'] as $page) {
            $saved = JTB_Library::save([
                'name' => $page['name'],
                'slug' => $page['id'],
                'description' => self::getDescription($page['id']),
                'category_slug' => self::mapCategory($page['category']),
                'tags' => self::getTags($page['id']),
                'content' => $page['content'],
                'thumbnail' => $page['thumbnail'] ?? null,
                'is_premade' => 1,
                'is_featured' => self::isFeatured($page['id']),
                'template_type' => 'page',
                'author' => 'Jessie CMS'
            ]);

            if ($saved) {
                $count++;
            }
        }

        // Seed section layouts
        foreach ($layouts['sections'] as $section) {
            $saved = JTB_Library::save([
                'name' => $section['name'],
                'slug' => $section['id'],
                'description' => self::getDescription($section['id']),
                'category_slug' => 'sections',
                'tags' => self::getTags($section['id']),
                'content' => $section['content'],
                'thumbnail' => $section['thumbnail'] ?? null,
                'is_premade' => 1,
                'is_featured' => self::isFeatured($section['id']),
                'template_type' => 'section',
                'author' => 'Jessie CMS'
            ]);

            if ($saved) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Force reseed - delete all premade and seed again
     */
    public static function reseed(): int
    {
        $db = \core\Database::connection();
        $db->exec("DELETE FROM jtb_library_templates WHERE is_premade = 1");
        return self::seed();
    }

    /**
     * Map layout category to library category slug
     */
    private static function mapCategory(string $category): string
    {
        $map = [
            'business' => 'business',
            'landing' => 'landing-page',
            'portfolio' => 'portfolio',
            'blog' => 'blog',
            'ecommerce' => 'ecommerce',
            'services' => 'services',
            'coming-soon' => 'coming-soon',
            'contact' => 'contact',
            'about' => 'about',
        ];

        return $map[$category] ?? 'business';
    }

    /**
     * Get description for template
     */
    private static function getDescription(string $id): string
    {
        $descriptions = [
            // Page layouts - 5 new pages
            'page-home' => 'Complete home page with hero section, features grid, testimonials, and call-to-action. Perfect starting point for any business website.',
            'page-about' => 'Professional about page with company story, team members section, and call-to-action. Ideal for introducing your company.',
            'page-services' => 'Services page with service blurbs, pricing tables, and CTA. Great for showcasing what you offer.',
            'page-portfolio' => 'Portfolio page with project gallery, testimonials, and CTA. Perfect for showcasing your work.',
            'page-contact' => 'Contact page with contact information, contact form, and map. Everything needed to connect with visitors.',

            // Section layouts
            'section-hero-centered' => 'Centered hero section with heading, text, and CTA button. Clean and modern design.',
            'section-hero-split' => 'Split hero section with content on left and image on right. Engaging two-column layout.',
            'section-features-3col' => 'Features section with 3-column blurb grid. Highlight your key benefits.',
            'section-testimonials' => 'Testimonials section with 3 client reviews. Build trust with social proof.',
            'section-team' => 'Team section with 4 team member cards. Introduce your talented people.',
            'section-pricing' => 'Pricing section with 3 pricing tables. Compare plans at a glance.',
            'section-cta' => 'Call-to-action section with heading, text, and button. Drive conversions.',
            'section-contact' => 'Contact section with info and form. Make it easy to get in touch.',
        ];

        return $descriptions[$id] ?? 'Professional premade template for Jessie Theme Builder.';
    }

    /**
     * Get tags for template
     */
    private static function getTags(string $id): array
    {
        $tags = [
            // Page layouts
            'page-home' => ['home', 'landing', 'business', 'hero', 'features', 'testimonials', 'cta'],
            'page-about' => ['about', 'team', 'company', 'story', 'professional'],
            'page-services' => ['services', 'pricing', 'business', 'blurbs', 'plans'],
            'page-portfolio' => ['portfolio', 'gallery', 'creative', 'projects', 'showcase'],
            'page-contact' => ['contact', 'form', 'map', 'information', 'address'],

            // Section layouts
            'section-hero-centered' => ['hero', 'centered', 'modern', 'cta', 'heading'],
            'section-hero-split' => ['hero', 'split', 'image', 'two-column'],
            'section-features-3col' => ['features', 'grid', 'blurb', '3-column'],
            'section-testimonials' => ['testimonials', 'reviews', 'clients', 'social-proof'],
            'section-team' => ['team', 'members', 'people', 'staff'],
            'section-pricing' => ['pricing', 'tables', 'plans', 'comparison'],
            'section-cta' => ['cta', 'call-to-action', 'conversion', 'button'],
            'section-contact' => ['contact', 'form', 'info', 'address'],
        ];

        return $tags[$id] ?? ['template', 'premade'];
    }

    /**
     * Check if template should be featured
     */
    private static function isFeatured(string $id): bool
    {
        $featured = [
            'page-home',
            'page-services',
            'section-hero-centered',
            'section-pricing',
        ];

        return in_array($id, $featured);
    }
}
