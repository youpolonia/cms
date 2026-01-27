<?php
/**
 * AI Landing Page Import - Create CMS Pages from AI Landing Specs
 * Converts AI-generated landing page specifications into CMS page records
 * NO classes, uses centralized Database helper, FTP-deployable
 */

// Guard against multiple includes
if (defined('AI_LANDING_IMPORT_LOADED')) {
    return;
}
define('AI_LANDING_IMPORT_LOADED', true);

// Detect CMS_ROOT if needed
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

// Load required dependencies
require_once CMS_ROOT . '/core/database.php';

if (!function_exists('ai_landing_import_create_page')) {
    /**
     * Create a CMS page record from AI landing spec
     *
     * @param array $spec Landing page specification with meta, hero, sections, faq
     * @return array Result with keys:
     *   - 'ok': bool - Success status
     *   - 'page_id': int - Inserted page ID (on success)
     *   - 'slug': string - Final slug used (on success)
     *   - 'title': string - Page title (on success)
     *   - 'created': bool - Whether page was created (on success)
     *   - 'error': string - Error type (on failure)
     *   - 'message': string - Human-readable error message (on failure)
     */
    function ai_landing_import_create_page(array $spec): array
    {
        try {
            // Validate spec structure
            if (!isset($spec['meta']) || !is_array($spec['meta'])) {
                return [
                    'ok' => false,
                    'error' => 'invalid_spec',
                    'message' => 'Invalid spec: missing or invalid meta section',
                ];
            }

            // Extract and normalize fields
            $meta = $spec['meta'];
            $hero = $spec['hero'] ?? [];
            $sections = $spec['sections'] ?? [];
            $faq = $spec['faq'] ?? [];

            // Build title
            $title = isset($meta['title']) ? trim((string)$meta['title']) : '';
            if ($title === '') {
                $title = isset($hero['headline']) ? trim((string)$hero['headline']) : '';
            }
            if ($title === '') {
                $title = 'AI Landing';
            }

            // Build slug
            $slug = isset($meta['slug']) ? trim((string)$meta['slug']) : '';
            if ($slug === '') {
                $slug = ai_landing_import_sanitize_slug($title);
            } else {
                $slug = ai_landing_import_sanitize_slug($slug);
            }

            // Meta fields
            $metaTitle = isset($meta['meta_title']) ? trim((string)$meta['meta_title']) : mb_substr($title, 0, 60);
            $metaDescription = isset($meta['meta_description']) ? trim((string)$meta['meta_description']) : mb_substr($title, 0, 160);

            // Build content HTML from sections and FAQ
            $contentHtml = ai_landing_import_build_content_html($hero, $sections, $faq);

            // Connect to database
            $db = \core\Database::connection();

            // Check for slug collision and find unique slug
            $finalSlug = ai_landing_import_find_unique_slug($db, $slug);

            // Build page data
            $now = date('Y-m-d H:i:s');
            $pageData = [
                'slug' => $finalSlug,
                'title' => $title,
                'content' => $contentHtml,
                'status' => 'draft',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Insert page record
            $stmt = $db->prepare(
                "INSERT INTO `pages` (slug, title, content, status, created_at, updated_at)
                 VALUES (:slug, :title, :content, :status, :created_at, :updated_at)"
            );

            $success = $stmt->execute($pageData);

            if (!$success) {
                error_log('[AI_LANDING_IMPORT] Failed to insert page record');
                return [
                    'ok' => false,
                    'error' => 'db_error',
                    'message' => 'Failed to create page record',
                ];
            }

            $pageId = (int)$db->lastInsertId();

            return [
                'ok' => true,
                'page_id' => $pageId,
                'slug' => $finalSlug,
                'title' => $title,
                'created' => true,
            ];

        } catch (\PDOException $e) {
            error_log('[AI_LANDING_IMPORT] Database error: ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'db_error',
                'message' => 'Database error occurred while creating page',
            ];
        } catch (\Throwable $e) {
            error_log('[AI_LANDING_IMPORT] Unexpected error: ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'unknown',
                'message' => 'An unexpected error occurred',
            ];
        }
    }
}

if (!function_exists('ai_landing_import_find_unique_slug')) {
    /**
     * Find a unique slug by checking database and appending numeric suffix if needed
     *
     * @param \PDO $db Database connection
     * @param string $slug Desired slug
     * @return string Unique slug
     */
    function ai_landing_import_find_unique_slug(\PDO $db, string $slug): string
    {
        $originalSlug = $slug;
        $counter = 2;

        while (true) {
            // Check if slug exists
            $stmt = $db->prepare("SELECT COUNT(*) FROM `pages` WHERE slug = :slug");
            $stmt->execute(['slug' => $slug]);
            $count = (int)$stmt->fetchColumn();

            if ($count === 0) {
                return $slug;
            }

            // Slug exists, try next suffix
            $slug = $originalSlug . '-' . $counter;
            $counter++;

            // Safety limit to prevent infinite loop
            if ($counter > 1000) {
                $slug = $originalSlug . '-' . time();
                break;
            }
        }

        return $slug;
    }
}

if (!function_exists('ai_landing_import_sanitize_slug')) {
    /**
     * Sanitize string to kebab-case slug
     *
     * @param string $str Input string
     * @return string Kebab-case slug
     */
    function ai_landing_import_sanitize_slug(string $str): string
    {
        $str = mb_strtolower($str, 'UTF-8');
        $str = preg_replace('/[^a-z0-9\s\-]+/', '', $str);
        $str = preg_replace('/[\s\-]+/', '-', $str);
        $str = trim($str, '-');

        if ($str === '') {
            $str = 'landing-' . date('YmdHis');
        }

        return $str;
    }
}

if (!function_exists('ai_landing_import_build_content_html')) {
    /**
     * Build HTML content from hero, sections, and FAQ
     *
     * @param array $hero Hero section data
     * @param array $sections Content sections array
     * @param array $faq FAQ entries array
     * @return string HTML content
     */
    function ai_landing_import_build_content_html(array $hero, array $sections, array $faq): string
    {
        $html = '';

        // Hero section
        if (!empty($hero)) {
            $headline = isset($hero['headline']) ? htmlspecialchars((string)$hero['headline'], ENT_QUOTES, 'UTF-8') : '';
            $subheadline = isset($hero['subheadline']) ? htmlspecialchars((string)$hero['subheadline'], ENT_QUOTES, 'UTF-8') : '';

            if ($headline !== '' || $subheadline !== '') {
                $html .= "<section class=\"hero\">\n";
                if ($headline !== '') {
                    $html .= "  <h1>{$headline}</h1>\n";
                }
                if ($subheadline !== '') {
                    $html .= "  <p class=\"subheadline\">{$subheadline}</p>\n";
                }
                $html .= "</section>\n\n";
            }
        }

        // Content sections
        if (!empty($sections) && is_array($sections)) {
            foreach ($sections as $section) {
                if (!is_array($section)) {
                    continue;
                }

                $type = isset($section['type']) ? htmlspecialchars((string)$section['type'], ENT_QUOTES, 'UTF-8') : 'text';
                $heading = isset($section['heading']) ? htmlspecialchars((string)$section['heading'], ENT_QUOTES, 'UTF-8') : '';
                $body = isset($section['body']) ? htmlspecialchars((string)$section['body'], ENT_QUOTES, 'UTF-8') : '';

                if ($heading === '' && $body === '') {
                    continue;
                }

                $html .= "<section class=\"content-section section-{$type}\">\n";
                if ($heading !== '') {
                    $html .= "  <h2>{$heading}</h2>\n";
                }
                if ($body !== '') {
                    $html .= "  <p>{$body}</p>\n";
                }

                // CTA button if present
                if (!empty($section['cta_label'])) {
                    $ctaLabel = htmlspecialchars((string)$section['cta_label'], ENT_QUOTES, 'UTF-8');
                    $ctaUrl = !empty($section['cta_url_placeholder']) ? htmlspecialchars((string)$section['cta_url_placeholder'], ENT_QUOTES, 'UTF-8') : '#';
                    $html .= "  <p><a href=\"{$ctaUrl}\" class=\"cta-button\">{$ctaLabel}</a></p>\n";
                }

                $html .= "</section>\n\n";
            }
        }

        // FAQ section
        if (!empty($faq) && is_array($faq)) {
            $html .= "<section class=\"faq\">\n";
            $html .= "  <h2>Frequently Asked Questions</h2>\n";

            foreach ($faq as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $question = isset($item['question']) ? htmlspecialchars((string)$item['question'], ENT_QUOTES, 'UTF-8') : '';
                $answer = isset($item['answer']) ? htmlspecialchars((string)$item['answer'], ENT_QUOTES, 'UTF-8') : '';

                if ($question === '' || $answer === '') {
                    continue;
                }

                $html .= "  <div class=\"faq-item\">\n";
                $html .= "    <h3>{$question}</h3>\n";
                $html .= "    <p>{$answer}</p>\n";
                $html .= "  </div>\n";
            }

            $html .= "</section>\n";
        }

        return $html;
    }
}
