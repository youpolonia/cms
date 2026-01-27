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
                'thumbnail' => self::getThumbnail($page['id']),
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
                'thumbnail' => self::getThumbnail($section['id']),
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
            'landing' => 'landing-page',
            'business' => 'business',
            'portfolio' => 'portfolio',
            'blog' => 'blog',
            'ecommerce' => 'ecommerce',
            'services' => 'services',
            'coming-soon' => 'coming-soon',
            'contact' => 'contact',
            'about' => 'about',
            'hero' => 'sections',
            'features' => 'sections',
            'cta' => 'sections',
            'testimonials' => 'sections',
        ];

        return $map[$category] ?? 'landing-page';
    }

    /**
     * Get description for template
     */
    private static function getDescription(string $id): string
    {
        $descriptions = [
            'page-agency-landing' => 'Modern agency landing page with hero section, features grid, and call-to-action. Perfect for digital agencies and creative studios.',
            'page-insurance-landing' => 'Professional insurance landing page with hero, coverage plans, methodology steps, and testimonials. Designed for insurance companies and financial services.',
            'section-hero-modern' => 'Modern split-layout hero section with heading, description, CTA button, and featured image. Dark theme with accent colors.',
            'section-insurance-hero' => 'Insurance-themed hero section with dual buttons, customer trust badges, and professional imagery. Clean light design.',
        ];

        return $descriptions[$id] ?? 'Professional premade template for Jessie Theme Builder.';
    }

    /**
     * Get tags for template
     */
    private static function getTags(string $id): array
    {
        $tags = [
            'page-agency-landing' => ['agency', 'landing', 'modern', 'dark', 'creative'],
            'page-insurance-landing' => ['insurance', 'landing', 'business', 'professional', 'finance', 'green'],
            'section-hero-modern' => ['hero', 'dark', 'modern', 'cta'],
            'section-insurance-hero' => ['hero', 'light', 'insurance', 'professional'],
        ];

        return $tags[$id] ?? ['template', 'premade'];
    }

    /**
     * Get thumbnail URL for template
     */
    private static function getThumbnail(string $id): ?string
    {
        // For now, return null - thumbnails can be generated later
        return null;
    }

    /**
     * Check if template should be featured
     */
    private static function isFeatured(string $id): bool
    {
        $featured = [
            'page-insurance-landing',
            'page-agency-landing',
        ];

        return in_array($id, $featured);
    }
}
