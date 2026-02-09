<?php
/**
 * JTB AI Knowledge - Complete System Prompt Generator
 *
 * This class contains the complete knowledge base for AI to generate
 * perfect JTB JSON layouts directly, without post-processing.
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Knowledge
{
    /**
     * Get the complete system prompt for AI layout generation
     *
     * @param array $context Optional context (colors, industry, style)
     * @return string Complete system prompt
     */
    public static function getSystemPrompt(array $context = []): string
    {
        $prompt = '';

        // 1. Introduction and Role
        $prompt .= self::getIntroduction();

        // 2. JSON Structure
        $prompt .= self::getJsonStructure();

        // 3. Value Formats
        $prompt .= self::getValueFormats();

        // 4. Pattern Packages
        $prompt .= self::getPatternPackages();

        // 5. All Modules Documentation (will be added in Etap 2-7)
        $prompt .= self::getAllModulesDocumentation();

        // 6. Feather Icons (will be added in Etap 8)
        $prompt .= self::getFeatherIcons();

        // 7. Design Principles (will be added in Etap 8)
        $prompt .= self::getDesignPrinciples();

        // 8. Example Layouts (will be added in Etap 8)
        $prompt .= self::getExampleLayouts();

        // 9. Context-specific additions
        if (!empty($context)) {
            $prompt .= self::getContextSection($context);
        }

        return $prompt;
    }

    /**
     * Section 1: Introduction and Role
     */
    private static function getIntroduction(): string
    {
        return <<<'PROMPT'
# JTB AI Layout Generator - System Knowledge

You are an expert web designer and JTB (Jessie Theme Builder) specialist. Your task is to generate complete, production-ready JTB JSON layouts based on user prompts.

## Your Role
- You are a professional web designer with deep knowledge of visual hierarchy, typography, spacing, and color theory
- You know EVERY JTB module, EVERY attribute, and EVERY value format
- You generate pixel-perfect layouts that require NO post-processing
- Your JSON output is ready to be used directly in JTB builder

## Critical Rules
1. ALWAYS output valid JSON - no comments, no trailing commas
2. NEVER use placeholder text like "Lorem ipsum" - generate real, contextual content
3. ALWAYS include styling attributes (padding, margin, colors, typography)
4. Use responsive suffixes (__tablet, __phone) for mobile optimization
5. Follow the exact attribute names and value formats documented below


PROMPT;
    }

    /**
     * Section 2: JSON Structure
     */
    private static function getJsonStructure(): string
    {
        return <<<'PROMPT'
## JTB JSON Structure

JTB uses a hierarchical structure: sections → rows → columns → modules

```json
{
  "sections": [
    {
      "type": "section",
      "attrs": {
        "padding": {"top": 100, "right": 0, "bottom": 100, "left": 0},
        "background_color": "#ffffff"
      },
      "children": [
        {
          "type": "row",
          "attrs": {
            "columns": "1_2,1_2",
            "column_gap": 30
          },
          "children": [
            {
              "type": "column",
              "attrs": {},
              "children": [
                {
                  "type": "heading",
                  "attrs": {
                    "text": "Welcome",
                    "level": "h1",
                    "font_size": 48,
                    "text_color": "#111827"
                  }
                }
              ]
            },
            {
              "type": "column",
              "attrs": {},
              "children": [
                {
                  "type": "image",
                  "attrs": {
                    "src": "{{PEXELS:hero,technology}}",
                    "alt": "Hero image"
                  }
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

### Hierarchy Rules
- A layout MUST have a "sections" array at root
- Each section MUST contain rows
- Each row MUST contain columns (number matches the "columns" attribute pattern)
- Columns contain modules (heading, text, button, image, etc.)
- Some modules can contain child modules (accordion → accordion_item, tabs → tabs_item)


PROMPT;
    }

    /**
     * Section 3: Value Formats
     */
    private static function getValueFormats(): string
    {
        return <<<'PROMPT'
## Value Formats

### Spacing (padding, margin)
ALWAYS use object format with numeric values (no "px" suffix):
```json
{
  "padding": {"top": 100, "right": 0, "bottom": 100, "left": 0},
  "margin": {"top": 0, "right": 0, "bottom": 30, "left": 0}
}
```

Responsive variants:
```json
{
  "padding": {"top": 100, "right": 0, "bottom": 100, "left": 0},
  "padding__tablet": {"top": 60, "right": 0, "bottom": 60, "left": 0},
  "padding__phone": {"top": 40, "right": 20, "bottom": 40, "left": 20}
}
```

### Border Radius
ALWAYS use object format with numeric values:
```json
{
  "border_radius": {"top_left": 12, "top_right": 12, "bottom_right": 12, "bottom_left": 12}
}
```

### Font Size
ALWAYS use numeric values (no "px" suffix):
```json
{
  "font_size": 48,
  "font_size__tablet": 36,
  "font_size__phone": 28
}
```

### Colors
Use hex format:
```json
{
  "text_color": "#111827",
  "background_color": "#f9fafb",
  "button_bg_color": "#2563eb",
  "button_bg_color__hover": "#1d4ed8"
}
```

### Column Layouts (for row "columns" attribute)
Available patterns:
- "1" (full width)
- "1_2,1_2" (two equal)
- "1_3,1_3,1_3" (three equal)
- "1_4,1_4,1_4,1_4" (four equal)
- "2_3,1_3" (two-thirds + one-third)
- "1_3,2_3" (one-third + two-thirds)
- "1_4,3_4" (quarter + three-quarters)
- "3_4,1_4" (three-quarters + quarter)
- "1_4,1_2,1_4" (quarter + half + quarter)
- "1_5,1_5,1_5,1_5,1_5" (five equal)
- "1_6,1_6,1_6,1_6,1_6,1_6" (six equal)

### Hover States
Add __hover suffix for interactive states:
```json
{
  "background_color": "#2563eb",
  "background_color__hover": "#1d4ed8",
  "text_color": "#ffffff",
  "text_color__hover": "#f0f0f0"
}
```

### Image Placeholders
Use Pexels integration syntax:
```json
{
  "src": "{{PEXELS:keyword1,keyword2}}",
  "alt": "Descriptive alt text"
}
```
Examples:
- "{{PEXELS:hero,technology}}" - tech hero image
- "{{PEXELS:team,business,portrait}}" - team member photo
- "{{PEXELS:office,modern}}" - office background
- "{{PEXELS:fitness,gym}}" - fitness image


PROMPT;
    }

    /**
     * Section 4: Pattern Packages
     */
    private static function getPatternPackages(): string
    {
        return <<<'PROMPT'
## Pattern Packages

These are common section patterns you should use as building blocks:

### HERO Package
Purpose: First impression, value proposition, primary CTA
Typical structure:
- Section with large padding (120-140px top/bottom)
- Row with 1_2,1_2 or 2_3,1_3 columns
- Left column: heading (h1, 48-64px), text (18-20px), button
- Right column: hero image or illustration
Style: Bold heading, generous whitespace, high-contrast CTA button

### FEATURES Package
Purpose: Showcase product/service features or benefits
Typical structure:
- Section with medium padding (80-100px)
- Optional: centered heading + text intro
- Row with 1_3,1_3,1_3 or 1_4,1_4,1_4,1_4 columns
- Each column: blurb (icon + title + description)
Style: Consistent icons, clear hierarchy, balanced spacing

### SOCIAL_PROOF Package
Purpose: Build trust through testimonials, logos, stats
Typical structure:
- Section with medium padding (80-100px)
- Centered section heading
- Row with testimonial modules or logo images
- Optional: stats row with number_counter modules
Style: Subtle background, professional typography, trust indicators

### PRICING Package
Purpose: Display pricing plans for conversion
Typical structure:
- Section with medium padding (80-100px)
- Centered heading + optional subtext
- Row with 1_3,1_3,1_3 columns
- Each column: pricing_table module
- Middle column often "featured" (highlighted)
Style: Clear hierarchy, prominent CTA, feature comparison

### CTA Package
Purpose: Drive conversion, final call to action
Typical structure:
- Section with contrasting background (primary color or dark)
- Centered content: heading (h2), text, button(s)
- Often uses cta module or custom heading+button combo
Style: High contrast, urgent/compelling copy, prominent button

### FAQ Package
Purpose: Answer common questions, reduce friction
Typical structure:
- Section with light background
- Centered heading
- accordion module with accordion_item children
Style: Clean, readable, expandable items

### CONTACT Package
Purpose: Enable user communication
Typical structure:
- Section with light background
- Row with 1_2,1_2 columns
- Left: contact info (heading, text, social icons, map)
- Right: contact_form module
Style: Professional, accessible, clear labels

### TEAM Package
Purpose: Introduce team members, build personal connection
Typical structure:
- Section with light background
- Centered heading + optional text
- Row with 1_3,1_3,1_3 or 1_4,1_4,1_4,1_4 columns
- Each column: team_member module
Style: Professional photos, consistent sizing, social links

### GALLERY Package
Purpose: Showcase visual work, portfolio, products
Typical structure:
- Section with minimal padding
- gallery or slider module
- Optional: filter controls
Style: Focus on images, minimal text, hover effects

### BLOG Package
Purpose: Display recent posts, content marketing
Typical structure:
- Section with medium padding
- Centered heading
- blog module with grid layout
Style: Card-based, consistent thumbnails, clear metadata

### FOOTER Package
Purpose: Site-wide navigation, legal info, contact
Typical structure:
- Section with dark background
- Row with 1_4,1_4,1_4,1_4 columns
- Columns: logo/about, navigation links, contact info, social
- Optional: copyright bar below
Style: Dark theme, organized columns, subtle links


PROMPT;
    }

    /**
     * Section 5: All Modules Documentation
     * This will be populated in Etap 2-7
     */
    /**
     * DYNAMIC Module Documentation - reads ALL modules from Registry
     * Replaces 2000+ lines of hardcoded documentation with dynamic generation.
     * Every module, every field, every option - straight from the source.
     */
    private static function getAllModulesDocumentation(): string
    {
        $docs = "\n## Complete Module Reference\n\n";

        try {
            $registry = JTB_Registry::all();
            $categories = JTB_Registry::getCategories();

            // Group modules by category
            $grouped = [];
            foreach ($registry as $slug => $className) {
                $instance = JTB_Registry::get($slug);
                if (!$instance) continue;
                $category = $instance->category ?? 'other';
                $grouped[$category][$slug] = $instance;
            }

            // Output per category
            foreach ($categories as $catSlug => $catDef) {
                if (empty($grouped[$catSlug])) continue;

                $catLabel = strtoupper($catDef['label'] ?? $catSlug);
                $docs .= "### {$catLabel} MODULES\n\n";

                foreach ($grouped[$catSlug] as $slug => $instance) {
                    $docs .= self::formatModuleDocumentation($slug, $instance);
                }
            }

            // Output uncategorized modules
            foreach ($grouped as $catSlug => $modules) {
                if (isset($categories[$catSlug])) continue;
                $docs .= "### " . strtoupper($catSlug) . " MODULES\n\n";
                foreach ($modules as $slug => $instance) {
                    $docs .= self::formatModuleDocumentation($slug, $instance);
                }
            }
        } catch (\Exception $e) {
            // Minimal fallback - use compact schemas
            $docs .= JTB_AI_Schema::getCompactSchemasForAI();
        }

        return $docs;
    }

    /**
     * Format documentation for a single module from its instance
     */
    private static function formatModuleDocumentation(string $slug, JTB_Element $instance): string
    {
        $doc = "#### {$slug}\n";
        $doc .= $instance->getName() . "\n```\nAttributes:\n";

        // Content fields
        $contentFields = $instance->getFields();
        foreach ($contentFields as $fieldName => $fieldDef) {
            $type = $fieldDef['type'] ?? 'text';
            $desc = self::formatFieldDoc($fieldName, $fieldDef, $type);
            $doc .= "- {$fieldName}: {$desc}\n";
        }

        // Design fields (from feature flags)
        try {
            $designFields = $instance->getDesignFields();
            if (!empty($designFields)) {
                $doc .= "\nDesign attributes (common):\n";
                foreach ($designFields as $groupName => $groupDef) {
                    if (is_array($groupDef) && isset($groupDef['fields'])) {
                        foreach ($groupDef['fields'] as $dfName => $dfDef) {
                            $dfType = $dfDef['type'] ?? 'text';
                            $doc .= "- {$dfName}: {$dfType}\n";
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Design fields are optional, skip on error
        }

        // Child modules
        if ($instance->child_slug) {
            $doc .= "\nChild module: {$instance->child_slug} (add as children array)\n";
        }

        $doc .= "```\n\n";
        return $doc;
    }

    /**
     * Format a single field for documentation
     */
    private static function formatFieldDoc(string $name, array $def, string $type): string
    {
        $desc = $type;

        if ($type === 'select' && !empty($def['options'])) {
            $opts = array_keys($def['options']);
            $desc .= ' - ' . implode('|', array_slice($opts, 0, 8));
            if (count($opts) > 8) $desc .= '|...';
        }
        if ($type === 'toggle') {
            $desc = 'boolean (true|false)';
        }
        if ($type === 'range' || $type === 'number') {
            $parts = [$type];
            if (isset($def['min'])) $parts[] = "min: {$def['min']}";
            if (isset($def['max'])) $parts[] = "max: {$def['max']}";
            $desc = implode(', ', $parts);
        }
        if ($type === 'color') {
            $desc = 'string - hex color';
        }
        if ($type === 'upload') {
            $desc = 'string - image URL';
        }
        if ($type === 'repeater') {
            $desc = 'array of objects';
        }
        if (!empty($def['default']) && is_scalar($def['default'])) {
            $desc .= " (default: {$def['default']})";
        }

        return $desc;
    }

    /**
     * Structure Modules: section, row, column
     */
    private static function getStructureModulesDocs(): string
    {
        return <<<'PROMPT'
### STRUCTURE MODULES

#### section
Container for rows. Every layout starts with sections.
```
Attributes:
- padding: {top, right, bottom, left} - Section inner spacing (default: 100px top/bottom)
- padding__tablet: {top, right, bottom, left} - Tablet padding
- padding__phone: {top, right, bottom, left} - Phone padding
- margin: {top, right, bottom, left} - Section outer spacing
- background_color: string - Background color hex
- background_image: string - Background image URL
- background_size: "cover"|"contain"|"auto" - Background size
- background_position: "center"|"top"|"bottom"|"left"|"right" - Position
- background_attachment: "scroll"|"fixed" - Parallax effect
- background_overlay_color: string - Overlay color with alpha
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - CSS box-shadow value
- custom_css_class: string - Additional CSS class
- animation: "none"|"fade"|"slide"|"zoom"|"bounce" - Entrance animation
- animation_delay: number - Delay in ms
```

#### row
Container for columns within a section.
```
Attributes:
- columns: string - Column layout pattern (e.g., "1_2,1_2", "1_3,1_3,1_3")
- column_gap: number - Gap between columns in px (default: 30)
- row_gap: number - Gap between rows in px
- vertical_align: "top"|"center"|"bottom"|"stretch" - Column alignment
- horizontal_align: "left"|"center"|"right"|"space-between" - Horizontal alignment
- padding: {top, right, bottom, left} - Row padding
- margin: {top, right, bottom, left} - Row margin
- max_width: number - Maximum row width in px
- background_color: string - Row background
- custom_css_class: string - Additional CSS class
```

#### column
Container for modules within a row.
```
Attributes:
- padding: {top, right, bottom, left} - Column padding
- margin: {top, right, bottom, left} - Column margin
- background_color: string - Column background
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - CSS box-shadow
- vertical_align: "top"|"center"|"bottom" - Content alignment
- custom_css_class: string - Additional CSS class
```


PROMPT;
    }

    /**
     * Content Modules Part 1
     * Placeholder - will be implemented in Etap 2
     */
    private static function getContentModulesPart1Docs(): string
    {
        return <<<'PROMPT'
### CONTENT MODULES (Part 1)

#### heading
Text heading with typography controls.
```
Attributes:
- text: string - Heading text content (REQUIRED)
- level: "h1"|"h2"|"h3"|"h4"|"h5"|"h6" - Heading level (default: "h2")
- font_family: string - Font family name
- font_size: number - Font size in px (no "px" suffix!)
- font_size__tablet: number - Tablet font size
- font_size__phone: number - Phone font size
- font_weight: "100"|"200"|"300"|"400"|"500"|"600"|"700"|"800"|"900" - Font weight
- font_style: "normal"|"italic" - Font style
- text_color: string - Text color hex
- text_align: "left"|"center"|"right"|"justify" - Text alignment
- text_align__tablet: string - Tablet alignment
- text_align__phone: string - Phone alignment
- line_height: number - Line height (e.g., 1.2, 1.5)
- letter_spacing: number - Letter spacing in px
- text_transform: "none"|"uppercase"|"lowercase"|"capitalize" - Text transform
- text_decoration: "none"|"underline"|"line-through" - Text decoration
- margin: {top, right, bottom, left} - Outer spacing
- padding: {top, right, bottom, left} - Inner spacing
- animation: string - Entrance animation
- custom_css_class: string - Additional CSS class
```

#### text
Rich text / paragraph content.
```
Attributes:
- content: string - HTML content (REQUIRED) - can include <p>, <strong>, <em>, <a>, <ul>, <li>
- font_family: string - Font family
- font_size: number - Font size in px
- font_size__tablet: number - Tablet font size
- font_size__phone: number - Phone font size
- font_weight: string - Font weight
- text_color: string - Text color hex
- text_align: "left"|"center"|"right"|"justify" - Alignment
- line_height: number - Line height
- margin: {top, right, bottom, left} - Outer spacing
- padding: {top, right, bottom, left} - Inner spacing
- animation: string - Entrance animation
- custom_css_class: string - Additional CSS class
```

#### image
Image with sizing and effects.
```
Attributes:
- src: string - Image URL or {{PEXELS:keywords}} placeholder (REQUIRED)
- alt: string - Alt text for accessibility (REQUIRED)
- title: string - Image title
- width: number|"auto" - Image width
- height: number|"auto" - Image height
- max_width: number - Maximum width in px
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - CSS box-shadow
- opacity: number - Opacity 0-1
- object_fit: "cover"|"contain"|"fill"|"none" - Object fit
- link_url: string - Link URL when clicked
- link_target: boolean - Open in new tab
- hover_effect: "none"|"zoom"|"grayscale"|"brightness" - Hover effect
- margin: {top, right, bottom, left} - Outer spacing
- animation: string - Entrance animation
- custom_css_class: string - Additional CSS class
```

#### button
Clickable button with styling.
```
Attributes:
- text: string - Button text (REQUIRED)
- link_url: string - Link URL (REQUIRED)
- link_target: boolean - Open in new tab (default: false)
- button_style: "solid"|"outline"|"text" - Button style
- background_color: string - Background color
- background_color__hover: string - Hover background
- text_color: string - Text color
- text_color__hover: string - Hover text color
- border_color: string - Border color
- border_color__hover: string - Hover border color
- border_width: number - Border width in px
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- padding: {top, right, bottom, left} - Inner padding
- font_size: number - Font size
- font_weight: string - Font weight
- text_transform: "none"|"uppercase"|"lowercase" - Text transform
- icon: string - Feather icon name (e.g., "arrow-right")
- icon_position: "left"|"right" - Icon position
- width: "auto"|"full" - Button width
- margin: {top, right, bottom, left} - Outer spacing
- animation: string - Entrance animation
- custom_css_class: string - Additional CSS class
```

#### divider
Horizontal line separator.
```
Attributes:
- style: "solid"|"dashed"|"dotted"|"double" - Line style
- color: string - Line color hex
- width: number|"100%" - Divider width
- height: number - Line thickness in px
- margin: {top, right, bottom, left} - Spacing around divider
- alignment: "left"|"center"|"right" - Horizontal alignment
- custom_css_class: string - Additional CSS class
```

#### icon
Single icon display.
```
Attributes:
- icon: string - Feather icon name (REQUIRED) (e.g., "check", "star", "heart")
- icon_color: string - Icon color hex
- icon_size: number - Icon size in px
- background_color: string - Background color (for circle/square)
- background_shape: "none"|"circle"|"square" - Background shape
- background_padding: number - Padding inside shape
- border_radius: {top_left, top_right, bottom_right, bottom_left} - For square shape
- link_url: string - Optional link
- margin: {top, right, bottom, left} - Outer spacing
- animation: string - Entrance animation
- custom_css_class: string - Additional CSS class
```

#### code
Code block with syntax highlighting.
```
Attributes:
- content: string - Code content (REQUIRED)
- language: string - Programming language (e.g., "javascript", "php", "css")
- show_line_numbers: boolean - Show line numbers
- theme: "light"|"dark" - Code theme
- font_size: number - Font size
- max_height: number - Maximum height with scroll
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- margin: {top, right, bottom, left} - Outer spacing
- custom_css_class: string - Additional CSS class
```


PROMPT;
    }

    /**
     * Content Modules Part 2
     * blurb, cta, testimonial, team_member, pricing_table, counters, social_follow, countdown
     */
    private static function getContentModulesPart2Docs(): string
    {
        return <<<'PROMPT'
### CONTENT MODULES (Part 2)

#### blurb
Feature box with icon/image, title and description. Perfect for features, services, benefits.
```
Attributes:
- title: string - Blurb title (REQUIRED)
- content: string - HTML description content
- use_icon: boolean - Use icon instead of image (default: false)
- font_icon: string - Feather icon name (when use_icon=true)
- icon_color: string - Icon color hex
- icon_color__hover: string - Icon hover color
- use_circle: boolean - Circle background for icon (default: false)
- circle_color: string - Circle background color
- circle_color__hover: string - Circle hover background
- circle_border_color: string - Circle border color
- image: string - Image URL (when use_icon=false)
- alt: string - Image alt text
- image_max_width: number - Max image width in px
- icon_font_size: number - Icon size in px (default: 96)
- icon_font_size__tablet: number - Tablet icon size
- icon_font_size__phone: number - Phone icon size
- image_placement: "top"|"left" - Media position (default: "top")
- link_url: string - Optional link URL
- link_target: boolean - Open in new tab
- header_level: "h1"|"h2"|"h3"|"h4"|"h5"|"h6" - Title heading level (default: "h4")
- content_max_width: number - Max content width in px
- text_orientation: "left"|"center"|"right" - Alignment (default: "center")
- title_font_size: number - Title font size in px
- title_color: string - Title color hex
- content_font_size: number - Content font size
- content_color: string - Content color hex
- margin: {top, right, bottom, left} - Outer spacing
- padding: {top, right, bottom, left} - Inner spacing
- background_color: string - Background color
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - CSS box-shadow
- animation: string - Entrance animation
```

#### cta
Call to action box with title, description and button.
```
Attributes:
- title: string - CTA title (REQUIRED)
- content: string - HTML body content
- button_text: string - Button text (default: "Click Here")
- link_url: string - Button link URL
- link_target: boolean - Open in new tab
- header_level: "h1"|"h2"|"h3"|"h4"|"h5"|"h6" - Title level (default: "h2")
- text_orientation: "left"|"center"|"right" - Alignment (default: "center")
- text_orientation__tablet: string - Tablet alignment
- text_orientation__phone: string - Phone alignment
- use_background_color: boolean - Enable background (default: true)
- promo_color: string - Background color (default: "#7ebec5")
- promo_color__hover: string - Hover background color
- button_bg_color: string - Button background (default: "#2ea3f2")
- button_bg_color__hover: string - Button hover background
- button_text_color: string - Button text color (default: "#ffffff")
- button_text_color__hover: string - Button hover text
- button_border_width: number - Button border width in px
- button_border_color: string - Button border color
- button_border_color__hover: string - Button hover border
- button_border_radius: number - Button corner radius in px
- button_icon: string - Feather icon for button
- button_icon_placement: "left"|"right" - Icon position (default: "right")
- title_font_size: number - Title font size
- title_font_size__tablet: number - Tablet title size
- title_font_size__phone: number - Phone title size
- title_color: string - Title color
- content_color: string - Content text color
- padding: {top, right, bottom, left} - Section padding
- margin: {top, right, bottom, left} - Section margin
- animation: string - Entrance animation
```

#### testimonial
Customer testimonial with avatar, quote and author info.
```
Attributes:
- author: string - Author name (REQUIRED, default: "John Doe")
- job_title: string - Author job title (default: "CEO")
- company: string - Company name
- link_url: string - Author/company URL
- link_target: boolean - Open in new tab
- portrait_url: string - Portrait image URL or {{PEXELS:portrait,business}}
- quote_icon: "on"|"off" - Show quote icon (default: "on")
- content: string - Testimonial text HTML (REQUIRED)
- text_orientation: "left"|"center"|"right" - Alignment (default: "left")
- portrait_width: number - Portrait width in px (default: 90)
- portrait_height: number - Portrait height in px (default: 90)
- portrait_border_radius: number - Portrait border radius in % (default: 50 for circle)
- quote_icon_color: string - Quote icon color (default: "#2ea3f2")
- quote_icon_size: number - Quote icon size in px (default: 32)
- author_name_color: string - Author name color
- position_color: string - Job title color
- company_color: string - Company name color
- body_color: string - Testimonial text color
- padding: {top, right, bottom, left} - Card padding
- margin: {top, right, bottom, left} - Card margin
- background_color: string - Card background
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - Card shadow
- animation: string - Entrance animation
```

#### team_member
Team member profile with photo, name, position and social links.
```
Attributes:
- name: string - Member name (REQUIRED, default: "John Doe")
- position: string - Job position (default: "CEO")
- image_url: string - Photo URL or {{PEXELS:portrait,professional}}
- content: string - Biography HTML
- facebook_url: string - Facebook profile URL
- twitter_url: string - Twitter/X profile URL
- linkedin_url: string - LinkedIn profile URL
- instagram_url: string - Instagram profile URL
- email: string - Email address
- text_orientation: "left"|"center"|"right" - Alignment (default: "center")
- header_level: "h1"|"h2"|"h3"|"h4"|"h5"|"h6" - Name heading level (default: "h4")
- image_border_radius: number - Image border radius in % (default: 0, use 50 for circle)
- name_font_size: number - Name font size in px
- name_color: string - Name color hex
- position_font_size: number - Position font size
- position_color: string - Position color
- bio_color: string - Biography text color
- icon_color: string - Social icons color (default: "#2ea3f2")
- icon_color__hover: string - Social icons hover color
- icon_size: number - Social icons size in px (default: 20)
- padding: {top, right, bottom, left} - Card padding
- margin: {top, right, bottom, left} - Card margin
- background_color: string - Card background
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - Card shadow
- animation: string - Entrance animation
```

#### pricing_table
Pricing plan card with features list and CTA button.
```
Attributes:
- title: string - Plan name (REQUIRED, default: "Basic Plan")
- subtitle: string - Plan description (default: "For individuals")
- currency: string - Currency symbol (default: "$")
- price: string - Price value (default: "29")
- per: string - Billing period (default: "month")
- content: string - Features list HTML with <ul><li> (REQUIRED)
- button_text: string - CTA button text (default: "Sign Up")
- link_url: string - Button link URL
- link_target: boolean - Open in new tab
- featured: boolean - Highlight as featured plan (default: false)
- featured_text: string - Featured badge text (default: "Most Popular")
- header_bg_color: string - Header background (default: "#2ea3f2")
- header_text_color: string - Header text color (default: "#ffffff")
- price_color: string - Price display color (default: "#2ea3f2")
- body_bg_color: string - Body background (default: "#ffffff")
- bullet_color: string - Feature bullet/check color (default: "#2ea3f2")
- button_bg_color: string - Button background (default: "#2ea3f2")
- button_bg_color__hover: string - Button hover background
- button_text_color: string - Button text color (default: "#ffffff")
- button_text_color__hover: string - Button hover text
- featured_badge_bg: string - Badge background (default: "#ff6b35")
- featured_badge_text: string - Badge text color (default: "#ffffff")
- padding: {top, right, bottom, left} - Card padding
- margin: {top, right, bottom, left} - Card margin
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - Card shadow
- animation: string - Entrance animation
```

#### number_counter
Animated number counter with title. Great for statistics.
```
Attributes:
- title: string - Counter title/label (REQUIRED)
- number: string - Target number (default: "100")
- percent_sign: boolean - Show % after number (default: false)
- counter_prefix: string - Text before number (e.g., "$")
- counter_suffix: string - Text after number (e.g., "+", "k", "M")
- text_orientation: "left"|"center"|"right" - Alignment (default: "center")
- number_color: string - Number color (default: "#2ea3f2")
- number_font_size: number - Number font size in px (default: 60)
- number_font_size__tablet: number - Tablet number size
- number_font_size__phone: number - Phone number size
- title_color: string - Title color
- title_font_size: number - Title font size in px
- title_font_size__tablet: number - Tablet title size
- title_font_size__phone: number - Phone title size
- animation_duration: number - Counter animation duration in ms (default: 2000)
- padding: {top, right, bottom, left} - Counter padding
- margin: {top, right, bottom, left} - Counter margin
- animation: string - Entrance animation
```

#### circle_counter
Animated circular progress indicator with percentage.
```
Attributes:
- title: string - Counter title (REQUIRED)
- number: number - Percentage 0-100 (default: 50)
- bar_bg_color: string - Circle track color (default: "#dddddd")
- circle_color: string - Progress circle color (default: "#2ea3f2")
- circle_color_alpha: number - Circle opacity 0-100 (default: 100)
- number_color: string - Center number color (default: "#000000")
- title_color: string - Title color
- circle_size: number - Circle diameter in px (default: 200)
- circle_size__tablet: number - Tablet circle size
- circle_size__phone: number - Phone circle size
- circle_stroke_width: number - Circle stroke width in px (default: 10)
- text_orientation: "left"|"center"|"right" - Alignment (default: "center")
- number_font_size: number - Number font size (default: 46)
- number_font_size__tablet: number - Tablet number size
- number_font_size__phone: number - Phone number size
- padding: {top, right, bottom, left} - Counter padding
- margin: {top, right, bottom, left} - Counter margin
- animation: string - Entrance animation
```

#### bar_counter
Animated horizontal progress bar with label.
```
Attributes:
- content: string - Progress bar title/label (REQUIRED, default: "Progress Bar")
- percent: number - Percentage 0-100 (default: 50)
- bar_background_color: string - Track color (default: "#dddddd")
- bar_color: string - Progress bar color (default: "#2ea3f2")
- use_percentages: boolean - Show percentage text (default: true)
- label_color: string - Label text color (default: "#666666")
- percent_color: string - Percentage text color (default: "#666666")
- bar_height: number - Bar height in px (default: 20)
- bar_border_radius: number - Bar corner radius in px (default: 0)
- use_stripes: boolean - Show stripe pattern (default: false)
- stripe_animate: boolean - Animate stripes (default: false)
- padding: {top, right, bottom, left} - Counter padding
- margin: {top, right, bottom, left} - Counter margin
- animation: string - Entrance animation
```

#### social_follow
Social media follow icons/buttons.
```
Attributes:
- facebook_url: string - Facebook page URL
- twitter_url: string - Twitter/X profile URL
- instagram_url: string - Instagram profile URL
- linkedin_url: string - LinkedIn page URL
- youtube_url: string - YouTube channel URL
- pinterest_url: string - Pinterest profile URL
- tiktok_url: string - TikTok profile URL
- github_url: string - GitHub profile URL
- dribbble_url: string - Dribbble profile URL
- behance_url: string - Behance profile URL
- email: string - Contact email address
- icon_style: "icons_only"|"circle"|"rounded"|"square" - Icon style (default: "icons_only")
- icon_color: string - Icon color (default: "#666666")
- icon_color__hover: string - Icon hover color
- use_brand_colors: boolean - Use brand colors on hover (default: false)
- icon_bg_color: string - Icon background (for circle/rounded/square)
- icon_bg_color__hover: string - Icon hover background
- icon_size: number - Icon size in px (default: 24)
- icon_size__tablet: number - Tablet icon size
- icon_size__phone: number - Phone icon size
- icon_spacing: number - Gap between icons in px (default: 10)
- alignment: "left"|"center"|"right" - Icons alignment (default: "center")
- alignment__tablet: string - Tablet alignment
- alignment__phone: string - Phone alignment
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```

#### countdown
Countdown timer to a specific date/time.
```
Attributes:
- date: string - Target date in YYYY-MM-DD format (REQUIRED)
- title: string - Countdown title
- show_labels: boolean - Show unit labels (default: true)
- show_separator: boolean - Show ":" separators (default: true)
- layout: "inline"|"block"|"circle" - Display layout (default: "inline")
- number_color: string - Number color (default: "#333333")
- label_color: string - Label color (default: "#666666")
- separator_color: string - Separator color (default: "#cccccc")
- box_bg_color: string - Unit box background (default: "#f9f9f9")
- number_font_size: number - Number font size in px (default: 48)
- number_font_size__tablet: number - Tablet number size
- number_font_size__phone: number - Phone number size
- label_font_size: number - Label font size in px (default: 14)
- label_font_size__tablet: number - Tablet label size
- label_font_size__phone: number - Phone label size
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```


PROMPT;
    }

    /**
     * Interactive Modules
     * accordion, accordion_item, tabs, tabs_item, toggle
     */
    private static function getInteractiveModulesDocs(): string
    {
        return <<<'PROMPT'
### INTERACTIVE MODULES

#### accordion
Collapsible content sections (parent container for accordion_item).
```
Attributes:
- open_toggle_text_color: string - Open state title color
- open_toggle_text_color__hover: string - Open state title hover color
- open_toggle_bg_color: string - Open state header background
- open_toggle_bg_color__hover: string - Open state header hover background
- closed_toggle_text_color: string - Closed state title color
- closed_toggle_text_color__hover: string - Closed state title hover color
- closed_toggle_bg_color: string - Closed state header background
- closed_toggle_bg_color__hover: string - Closed state header hover background
- icon_color: string - Toggle icon color (default: "#666666")
- icon_color__hover: string - Toggle icon hover color
- icon_size: number - Toggle icon size in px (default: 16)
- toggle_header_level: "h1"|"h2"|"h3"|"h4"|"h5"|"h6" - Title heading level (default: "h5")
- toggle_icon: "arrow"|"plus"|"none" - Toggle icon style (default: "arrow")
- toggle_icon_position: "left"|"right" - Icon position (default: "right")
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation

Children: accordion_item (REQUIRED - at least 2-3 items)
```

#### accordion_item
Single accordion panel (child of accordion).
```
Attributes:
- title: string - Panel title (REQUIRED)
- content: string - Panel content HTML (REQUIRED)
- open: boolean - Open by default (default: false, usually first item is open)
- title_tag: "h2"|"h3"|"h4"|"h5"|"h6"|"div" - Title HTML tag (default: "h4")
- title_color: string - Title color (default: "#333333")
- title_color__hover: string - Title hover color
- title_font_size: number - Title font size in px (default: 16)
- icon_color: string - Icon color (default: "#666666")
- header_bg_color: string - Header background (default: "#f8f9fa")
- header_bg_color__hover: string - Header hover background
- content_bg_color: string - Content background (default: "#ffffff")
```

#### tabs
Tabbed content container (parent for tabs_item).
```
Attributes:
- active_tab_bg_color: string - Active tab background (default: "#ffffff")
- active_tab_text_color: string - Active tab text color (default: "#2ea3f2")
- inactive_tab_bg_color: string - Inactive tab background (default: "#f4f4f4")
- inactive_tab_text_color: string - Inactive tab text color (default: "#666666")
- inactive_tab_text_color__hover: string - Inactive tab hover color
- tab_border_color: string - Tab border color (default: "#d9d9d9")
- body_bg_color: string - Content body background (default: "#ffffff")
- tab_font_size: number - Tab text font size in px (default: 14)
- tab_font_size__tablet: number - Tablet tab font size
- tab_font_size__phone: number - Phone tab font size
- nav_position: "top"|"left"|"bottom"|"right" - Tab navigation position (default: "top")
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation

Children: tabs_item (REQUIRED - at least 2-3 items)
```

#### tabs_item
Single tab panel (child of tabs).
```
Attributes:
- title: string - Tab title shown in navigation (REQUIRED)
- content: string - Tab content HTML (REQUIRED)
```

#### toggle
Single collapsible content block (standalone, not grouped like accordion).
```
Attributes:
- title: string - Toggle title (REQUIRED, default: "Toggle Title")
- content: string - Toggle content HTML (REQUIRED)
- open: boolean - Open by default (default: false)
- header_level: "h1"|"h2"|"h3"|"h4"|"h5"|"h6" - Title heading level (default: "h5")
- toggle_icon: "arrow"|"plus"|"none" - Icon style (default: "arrow")
- icon_position: "left"|"right" - Icon position (default: "right")
- open_title_color: string - Open state title color
- open_title_color__hover: string - Open state title hover
- open_bg_color: string - Open state header background
- open_bg_color__hover: string - Open state header hover background
- closed_title_color: string - Closed state title color
- closed_title_color__hover: string - Closed state title hover
- closed_bg_color: string - Closed state header background
- closed_bg_color__hover: string - Closed state header hover background
- icon_color: string - Icon color (default: "#666666")
- icon_color__hover: string - Icon hover color
- icon_size: number - Icon size in px (default: 16)
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```


PROMPT;
    }

    /**
     * Media Modules
     * audio, video, gallery, slider, map
     */
    private static function getMediaModulesDocs(): string
    {
        return <<<'PROMPT'
### MEDIA MODULES

#### audio
HTML5 audio player with album art and metadata.
```
Attributes:
- audio: string - Audio file URL (REQUIRED)
- title: string - Track title
- artist: string - Artist name
- album: string - Album name
- image_url: string - Album art image URL
- autoplay: boolean - Autoplay audio (default: false)
- loop: boolean - Loop audio (default: false)
- player_bg_color: string - Player background color (default: "#222222")
- player_text_color: string - Player text color (default: "#ffffff")
- progress_color: string - Progress bar color (default: "#2ea3f2")
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- animation: string - Entrance animation
```

#### video
HTML5/YouTube/Vimeo video player.
```
Attributes:
- src: string - Video URL (REQUIRED) - YouTube URL, Vimeo URL, or direct file path
- src_webm: string - WebM fallback file URL (for HTML5)
- image_src: string - Poster/thumbnail image URL
- play_icon_color: string - Play icon color (default: "#ffffff")
- autoplay: boolean - Autoplay video (default: false)
- loop: boolean - Loop video (default: false)
- muted: boolean - Muted video (default: false)
- controls: boolean - Show video controls (default: true)
- aspect_ratio: "16:9"|"4:3"|"21:9"|"1:1"|"9:16" - Video aspect ratio (default: "16:9")
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - Container shadow
- animation: string - Entrance animation
```

#### gallery
Image gallery with various layouts and lightbox.
```
Attributes:
- gallery_source: "custom"|"cms_gallery" - Image source (default: "custom")
- cms_gallery_id: number - CMS gallery ID (when gallery_source=cms_gallery)
- gallery_images: array - Array of image objects (when gallery_source=custom)
  Each image: {url: string, title: string, caption: string, alt: string}
- gallery_layout: "grid"|"masonry"|"slider" - Layout type (default: "grid")
- columns: "1"|"2"|"3"|"4"|"5"|"6" - Number of columns (default: "3")
- columns__tablet: string - Tablet columns (default: "2")
- columns__phone: string - Phone columns (default: "1")
- gutter: number - Gap between images in px (default: 10)
- show_title_caption: boolean - Show image titles/captions (default: true)
- overlay_on_hover: boolean - Show overlay on hover (default: true)
- overlay_color: string - Overlay background color (default: "rgba(0,0,0,0.5)")
- overlay_icon_color: string - Overlay icon color (default: "#ffffff")
- lightbox: boolean - Enable lightbox on click (default: true)
- image_border_radius: number - Image corner radius in px (default: 0)
- title_font_size: number - Title font size in px (default: 14)
- caption_font_size: number - Caption font size in px (default: 12)
- title_color: string - Title text color
- caption_color: string - Caption text color
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```

#### slider
Image/content slider carousel (parent for slider_item or images).
```
Attributes:
- show_arrows: boolean - Show navigation arrows (default: true)
- show_dots: boolean - Show pagination dots (default: true)
- auto: boolean - Auto rotate slides (default: false)
- auto_speed: number - Auto rotation speed in ms (default: 5000)
- auto_ignore_hover: boolean - Continue rotation on hover (default: false)
- loop: boolean - Infinite loop (default: true)
- arrow_color: string - Arrow icon color (default: "#ffffff")
- arrow_color__hover: string - Arrow hover color
- arrow_bg_color: string - Arrow background (default: "rgba(0,0,0,0.3)")
- arrow_bg_color__hover: string - Arrow hover background
- dot_color: string - Inactive dot color (default: "rgba(255,255,255,0.5)")
- dot_active_color: string - Active dot color (default: "#ffffff")
- slider_height: number - Slider height in px (default: 500)
- slider_height__tablet: number - Tablet slider height
- slider_height__phone: number - Phone slider height
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - Container shadow
- animation: string - Entrance animation

Children: slider_item (for custom slides)
```

#### map
OpenStreetMap embed.
```
Attributes:
- address: string - Map address/location (REQUIRED, default: "New York, NY, USA")
- zoom: number - Zoom level 1-22 (default: 14)
- map_height: number - Map height in px (default: 400)
- map_height__tablet: number - Tablet map height
- map_height__phone: number - Phone map height
- grayscale: boolean - Apply grayscale filter (default: false)
- mouse_wheel: boolean - Enable mouse wheel zoom (default: true)
- mobile_dragging: boolean - Enable mobile dragging (default: true)
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - Container shadow
- animation: string - Entrance animation
```


PROMPT;
    }

    /**
     * Forms Modules
     * contact_form, login, signup, search
     */
    private static function getFormsModulesDocs(): string
    {
        return <<<'PROMPT'
### FORMS MODULES

#### contact_form
Customizable contact form with email submission.
```
Attributes:
- email: string - Email address for submissions (REQUIRED)
- title: string - Form title (default: "Contact Us")
- success_message: string - Message shown after submission
- submit_button_text: string - Submit button text (default: "Submit")
- use_redirect: boolean - Redirect after submit (default: false)
- redirect_url: string - URL to redirect to
- use_name: boolean - Show name field (default: true)
- use_email: boolean - Show email field (default: true)
- use_message: boolean - Show message field (default: true)
- use_captcha: boolean - Enable captcha (default: false)
- form_field_bg_color: string - Field background (default: "#ffffff")
- form_field_text_color: string - Field text color (default: "#666666")
- form_field_border_color: string - Field border color (default: "#bbb")
- form_field_border_color__hover: string - Field focus border
- form_field_border_width: number - Field border width in px (default: 1)
- form_field_border_radius: number - Field corner radius in px (default: 0)
- button_bg_color: string - Button background (default: "#2ea3f2")
- button_bg_color__hover: string - Button hover background
- button_text_color: string - Button text color (default: "#ffffff")
- button_text_color__hover: string - Button hover text
- button_border_radius: number - Button corner radius in px (default: 3)
- padding: {top, right, bottom, left} - Form padding
- margin: {top, right, bottom, left} - Form margin
- animation: string - Entrance animation
```

#### login
User login form with remember me and forgot password.
```
Attributes:
- title: string - Form title (default: "Login")
- current_page_redirect: boolean - Redirect to current page after login (default: true)
- redirect_url: string - Custom redirect URL (when current_page_redirect=false)
- username_label: string - Username field label (default: "Username")
- password_label: string - Password field label (default: "Password")
- button_text: string - Login button text (default: "Login")
- form_field_bg_color: string - Field background (default: "#ffffff")
- form_field_text_color: string - Field text color (default: "#666666")
- form_field_border_color: string - Field border color (default: "#bbb")
- form_field_border_color__hover: string - Field focus border
- button_bg_color: string - Button background (default: "#2ea3f2")
- button_bg_color__hover: string - Button hover background
- button_text_color: string - Button text color (default: "#ffffff")
- button_text_color__hover: string - Button hover text
- padding: {top, right, bottom, left} - Form padding
- margin: {top, right, bottom, left} - Form margin
- background_color: string - Form background
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - Form shadow
- animation: string - Entrance animation
```

#### signup
User registration form.
```
Attributes:
- title: string - Form title (default: "Sign Up")
- redirect_url: string - Redirect URL after registration
- button_text: string - Submit button text (default: "Register")
- form_field_bg_color: string - Field background
- form_field_text_color: string - Field text color
- form_field_border_color: string - Field border color
- button_bg_color: string - Button background
- button_text_color: string - Button text color
- padding: {top, right, bottom, left} - Form padding
- margin: {top, right, bottom, left} - Form margin
- animation: string - Entrance animation
```

#### search
Site search form with optional icon button.
```
Attributes:
- placeholder: string - Input placeholder (default: "Search...")
- button_text: string - Button text (default: "Search")
- show_button: boolean - Show search button (default: true)
- use_icon: boolean - Use icon instead of text (default: false)
- field_bg_color: string - Field background (default: "#ffffff")
- field_text_color: string - Field text color (default: "#666666")
- field_border_color: string - Field border color (default: "#bbb")
- field_border_color__hover: string - Field focus border
- field_border_width: number - Field border width in px (default: 1)
- field_border_radius: number - Field corner radius in px (default: 0)
- button_bg_color: string - Button background (default: "#2ea3f2")
- button_bg_color__hover: string - Button hover background
- button_text_color: string - Button/icon color (default: "#ffffff")
- button_text_color__hover: string - Button hover color
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```


PROMPT;
    }

    /**
     * Blog Modules
     * blog, portfolio, post_slider
     */
    private static function getBlogModulesDocs(): string
    {
        return <<<'PROMPT'
### BLOG MODULES

#### blog
Blog posts grid/list with filtering and pagination.
```
Attributes:
- fullwidth: boolean - Fullwidth grid layout (default: false)
- posts_number: number - Number of posts to show (default: 10)
- include_categories: string - Comma-separated category IDs to include
- meta_date: boolean - Show post date (default: true)
- meta_author: boolean - Show author name (default: true)
- meta_categories: boolean - Show categories (default: true)
- meta_comments: boolean - Show comments count (default: true)
- show_thumbnail: boolean - Show featured image (default: true)
- show_content: boolean - Show excerpt (default: true)
- show_more: boolean - Show read more link (default: true)
- show_pagination: boolean - Show pagination (default: true)
- offset_number: number - Skip first N posts (default: 0)
- use_overlay: boolean - Image overlay on hover (default: true)
- overlay_color: string - Overlay color (default: "rgba(0,0,0,0.3)")
- header_level: "h1"|"h2"|"h3"|"h4"|"h5"|"h6" - Title heading level (default: "h2")
- masonry: boolean - Use masonry layout (default: false)
- columns: "1"|"2"|"3"|"4" - Number of columns (default: "3")
- columns__tablet: string - Tablet columns (default: "2")
- columns__phone: string - Phone columns (default: "1")
- gap: number - Gap between posts in px
- card_background: string - Post card background
- card_padding: string - Card content padding
- card_border_radius: number - Card corner radius in px
- title_font_size: number - Title font size in px
- title_color: string - Title text color
- title_hover_color: string - Title hover color
- meta_font_size: number - Meta text font size in px
- meta_color: string - Meta text color
- excerpt_font_size: number - Excerpt font size in px
- excerpt_color: string - Excerpt text color
- read_more_color: string - Read more link color
- read_more_hover_color: string - Read more hover color
- pagination_color: string - Pagination link color
- pagination_active_bg: string - Active page background
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```

#### portfolio
Portfolio/projects grid with filtering and hover overlay.
```
Attributes:
- fullwidth: boolean - Fullwidth layout (default: false)
- posts_number: number - Number of projects (default: 8)
- include_categories: string - Comma-separated category IDs
- show_title: boolean - Show project title (default: true)
- show_categories: boolean - Show categories (default: true)
- show_pagination: boolean - Show pagination (default: false)
- zoom_icon_color: string - Zoom icon color (default: "#ffffff")
- hover_overlay_color: string - Overlay color (default: "rgba(0,0,0,0.7)")
- columns: "2"|"3"|"4"|"5"|"6" - Number of columns (default: "4")
- columns__tablet: string - Tablet columns (default: "3")
- columns__phone: string - Phone columns (default: "2")
- gutter: number - Gap between items in px (default: 10)
- title_font_size: number - Title font size in px (default: 18)
- title_font_size__tablet: number - Tablet title size
- title_font_size__phone: number - Phone title size
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```

#### post_slider
Blog post carousel/slider with featured posts.
```
Attributes:
- posts_number: number - Number of posts (default: 5)
- include_categories: string - Comma-separated category IDs
- show_arrows: boolean - Show navigation arrows (default: true)
- show_pagination: boolean - Show dots (default: true)
- auto: boolean - Auto rotate slides (default: true)
- auto_speed: number - Rotation speed in ms (default: 5000)
- show_meta: boolean - Show post meta (default: true)
- show_excerpt: boolean - Show excerpt (default: true)
- show_read_more: boolean - Show read more button (default: true)
- image_placement: "background"|"left"|"right"|"top" - Image position (default: "background")
- content_text_color: string - Text color (default: "#ffffff")
- overlay_color: string - Overlay color (default: "rgba(0,0,0,0.5)")
- slider_height: number - Slider height in px (default: 500)
- slider_height__tablet: number - Tablet slider height
- slider_height__phone: number - Phone slider height
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```


PROMPT;
    }

    /**
     * Fullwidth Modules
     * fullwidth_header, fullwidth_image, fullwidth_menu, fullwidth_slider,
     * fullwidth_portfolio, fullwidth_code, fullwidth_map, fullwidth_post_slider, fullwidth_post_title
     */
    private static function getFullwidthModulesDocs(): string
    {
        return <<<'PROMPT'
### FULLWIDTH MODULES

These modules span the full width of the viewport and are typically used for hero sections, sliders, and immersive content.

#### fullwidth_header
Hero header section with title, subtitle, buttons and background.
```
Attributes:
- title: string - Main headline (REQUIRED, default: "Your Title Goes Here")
- subhead: string - Secondary headline text
- content: string - Body content HTML
- button_one_text: string - First button text (default: "Click Here")
- button_one_url: string - First button URL
- button_one_target: boolean - Open in new tab
- button_two_text: string - Second button text
- button_two_url: string - Second button URL
- button_two_target: boolean - Open in new tab
- header_level: "h1"|"h2"|"h3"|"h4"|"h5"|"h6" - Title level (default: "h1")
- text_orientation: "left"|"center"|"right" - Text alignment (default: "center")
- text_orientation__tablet: string - Tablet alignment
- text_orientation__phone: string - Phone alignment
- logo_image_url: string - Logo image URL
- logo_max_width: number - Logo max width in px (default: 100)
- content_orientation: "center"|"bottom" - Vertical content position (default: "center")
- header_fullscreen: boolean - Fullscreen height (default: false)
- header_height: number - Custom height in px (default: 500)
- header_height__tablet: number - Tablet height
- header_height__phone: number - Phone height
- background_color: string - Background color
- background_image: string - Background image URL or {{PEXELS:keywords}}
- background_overlay_color: string - Overlay color (default: "rgba(0,0,0,0.3)")
- parallax: boolean - Parallax effect (default: false)
- title_font_size: number - Title font size in px (default: 60)
- title_font_size__tablet: number - Tablet title size
- title_font_size__phone: number - Phone title size
- title_text_color: string - Title color (default: "#ffffff")
- content_font_size: number - Content font size in px (default: 18)
- content_text_color: string - Content color (default: "#ffffff")
- subhead_text_color: string - Subhead color
- button_one_bg_color: string - First button background (default: "#2ea3f2")
- button_one_bg_color__hover: string - First button hover background
- button_one_text_color: string - First button text color (default: "#ffffff")
- button_two_bg_color: string - Second button background (default: "transparent")
- button_two_text_color: string - Second button text color (default: "#ffffff")
- button_border_radius: number - Button corner radius in px (default: 3)
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```

#### fullwidth_image
Full-width image with optional overlay effect.
```
Attributes:
- src: string - Image URL or {{PEXELS:keywords}} (REQUIRED)
- alt: string - Alt text for accessibility
- title_text: string - Image title attribute
- link_url: string - Optional link URL
- link_target: boolean - Open in new tab
- show_in_lightbox: boolean - Open in lightbox on click (default: false)
- use_overlay: boolean - Show hover overlay (default: false)
- overlay_color: string - Overlay color (default: "rgba(0,0,0,0.3)")
- overlay_on_hover: boolean - Show overlay only on hover (default: true)
- overlay_icon_color: string - Overlay icon color (default: "#ffffff")
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- border_radius: {top_left, top_right, bottom_right, bottom_left} - Corner radius
- box_shadow: string - Container shadow
- animation: string - Entrance animation
```

#### fullwidth_menu
Full-width navigation menu with logo and responsive mobile support.
```
Attributes:
- menu_id: "primary"|"secondary"|"footer" - Menu selection (default: "primary")
- menu_style: "left"|"centered"|"inline_centered_logo"|"fullwidth" - Layout style (default: "left")
- submenu_direction: "downward"|"upward" - Dropdown direction (default: "downward")
- fullwidth_menu: boolean - Full width menu items (default: false)
- logo: string - Logo image URL
- logo_max_height: number - Logo max height in px (default: 54)
- menu_link_color: string - Menu link color (default: "#666666")
- menu_link_color__hover: string - Menu link hover color
- active_link_color: string - Active link color (default: "#2ea3f2")
- dropdown_menu_bg_color: string - Dropdown background (default: "#ffffff")
- dropdown_menu_text_color: string - Dropdown text color (default: "#666666")
- dropdown_menu_line_color: string - Dropdown border color (default: "#e5e5e5")
- mobile_menu_bg_color: string - Mobile menu background (default: "#ffffff")
- mobile_menu_text_color: string - Mobile menu text color (default: "#666666")
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- background_color: string - Menu background
- box_shadow: string - Menu shadow
```

#### fullwidth_slider
Full-width image/content slider carousel.
```
Attributes:
- show_arrows: boolean - Show navigation arrows (default: true)
- show_pagination: boolean - Show dots pagination (default: true)
- auto_play: boolean - Auto rotate slides (default: true)
- auto_speed: number - Rotation speed in ms (default: 5000)
- loop: boolean - Infinite loop slides (default: true)
- parallax: boolean - Parallax effect on images (default: false)
- slider_height: "auto"|"fullscreen"|"custom" - Height mode (default: "auto")
- custom_height: number - Custom height in px (default: 500)
- custom_height__tablet: number - Tablet custom height
- custom_height__phone: number - Phone custom height
- arrows_color: string - Arrow icon color (default: "#ffffff")
- arrows_bg_color: string - Arrow background (default: "rgba(0,0,0,0.3)")
- dot_color: string - Inactive dot color (default: "rgba(255,255,255,0.5)")
- dot_active_color: string - Active dot color (default: "#ffffff")
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- box_shadow: string - Container shadow
- animation: string - Entrance animation

Children: fullwidth_slider_item (slides with background/content)
```

#### fullwidth_portfolio
Full-width portfolio grid with overlay effects.
```
Attributes:
- posts_per_page: number - Number of projects (default: 8)
- columns: "2"|"3"|"4"|"5"|"6" - Grid columns (default: "4")
- layout: "grid"|"masonry" - Grid layout type (default: "grid")
- show_title: boolean - Show project title (default: true)
- show_categories: boolean - Show categories (default: true)
- show_pagination: boolean - Show pagination (default: false)
- category_filter: string - Filter by category (default: "all")
- overlay_style: "overlay"|"slide_up"|"zoom" - Hover effect (default: "overlay")
- gap_width: number - Gap between items in px (default: 0)
- overlay_color: string - Overlay color (default: "rgba(0,0,0,0.7)")
- title_color: string - Title color (default: "#ffffff")
- category_color: string - Category color (default: "rgba(255,255,255,0.8)")
- icon_color: string - Overlay icon color (default: "#ffffff")
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```

#### fullwidth_code
Full-width raw HTML/CSS/JavaScript code block.
```
Attributes:
- raw_content: string - Raw HTML content (REQUIRED)
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- background_color: string - Container background
```

#### fullwidth_map
Full-width map embed with address overlay.
```
Attributes:
- address: string - Map address/location (REQUIRED, default: "New York, NY")
- zoom: number - Zoom level 1-20 (default: 14)
- map_height: number - Map height in px (default: 400)
- map_height__tablet: number - Tablet map height
- map_height__phone: number - Phone map height
- grayscale: boolean - Apply grayscale filter (default: false)
- mouse_wheel: boolean - Enable mouse wheel zoom (default: false)
- draggable: boolean - Enable map dragging (default: true)
- show_info_window: boolean - Show info popup (default: false)
- info_window_content: string - Info popup HTML content
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```

#### fullwidth_post_slider
Full-width blog post carousel.
```
Attributes:
- posts_number: number - Number of posts (default: 5)
- category: string - Category filter (default: "all")
- orderby: "date"|"title"|"random"|"comment_count" - Sort order (default: "date")
- show_arrows: boolean - Show navigation arrows (default: true)
- show_pagination: boolean - Show dots (default: true)
- auto_play: boolean - Auto rotate slides (default: true)
- auto_speed: number - Rotation speed in ms (default: 5000)
- show_meta: boolean - Show post meta (default: true)
- show_excerpt: boolean - Show excerpt (default: true)
- use_overlay: boolean - Use overlay on images (default: true)
- slider_height: number - Slider height in px (default: 500)
- slider_height__tablet: number - Tablet slider height
- slider_height__phone: number - Phone slider height
- overlay_color: string - Overlay color (default: "rgba(0,0,0,0.4)")
- title_color: string - Title color (default: "#ffffff")
- meta_color: string - Meta text color (default: "rgba(255,255,255,0.8)")
- excerpt_color: string - Excerpt color (default: "rgba(255,255,255,0.9)")
- arrows_color: string - Arrow icon color (default: "#ffffff")
- dots_color: string - Inactive dot color (default: "rgba(255,255,255,0.5)")
- dots_active_color: string - Active dot color (default: "#ffffff")
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- animation: string - Entrance animation
```

#### fullwidth_post_title
Full-width post/page title header with background.
```
Attributes:
- title: string - Title text (default: "Page Title", leave empty to use page title)
- show_meta: boolean - Show post meta (default: true)
- show_author: boolean - Show author name (default: true)
- show_date: boolean - Show date (default: true)
- show_categories: boolean - Show categories (default: true)
- show_comments: boolean - Show comments count (default: false)
- text_alignment: "left"|"center"|"right" - Text alignment (default: "center")
- featured_placement: "background"|"below"|"none" - Featured image position (default: "background")
- module_height: number - Module height in px (default: 300)
- module_height__tablet: number - Tablet module height
- module_height__phone: number - Phone module height
- overlay_color: string - Overlay color (default: "rgba(0,0,0,0.4)")
- title_color: string - Title color (default: "#ffffff")
- meta_color: string - Meta text color (default: "rgba(255,255,255,0.8)")
- title_font_size: number - Title font size in px (default: 42)
- title_font_size__tablet: number - Tablet title size
- title_font_size__phone: number - Phone title size
- padding: {top, right, bottom, left} - Container padding
- margin: {top, right, bottom, left} - Container margin
- background_color: string - Background color
- background_image: string - Background image URL
- animation: string - Entrance animation
```


PROMPT;
    }

    /**
     * Section 5g: Theme Modules Documentation
     * Complete documentation for all 19 theme modules
     */
    private static function getThemeModulesDocs(): string
    {
        return <<<'PROMPT'

### Category: Theme (19 modules)

Theme modules are used in Theme Builder templates for headers, footers, blog posts, and archives.

---

#### Module: `post_title`
**Purpose:** Displays the current post/page title dynamically

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| level | select | h1 | Tag: h1, h2, h3, h4, h5, h6 |
| show_link | toggle | false | Make title clickable |
| show_meta | toggle | false | Show date/author below |
| meta_items | multiSelect | ['date','author'] | Which meta to show |
| date_format | text | "F j, Y" | PHP date format |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| text_color | color | #1f2937 | No | Yes |
| font_size | range(16-80) | 42 | Yes | No |
| font_weight | select | 700 | No | No |
| line_height | range(1.0-2.0) | 1.2 | No | No |
| text_align | select | left | Yes | No |
| letter_spacing | range(-2 to 5) | 0 | No | No |
| meta_color | color | #6b7280 | No | No |

**Example:**
```json
{
  "type": "post_title",
  "attrs": {
    "level": "h1",
    "font_size": 48,
    "font_size__tablet": 36,
    "font_size__phone": 28,
    "font_weight": "700",
    "text_color": "#111827",
    "text_align": "center",
    "show_meta": true,
    "meta_items": ["date", "author"],
    "meta_color": "#6b7280"
  }
}
```

---

#### Module: `post_content`
**Purpose:** Displays the main post/page content dynamically

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| show_drop_cap | toggle | false | Large first letter |
| drop_cap_style | select | square | square, circle, none |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| text_color | color | #374151 | No | No |
| font_size | range(14-22) | 16 | Yes | No |
| line_height | range(1.4-2.2) | 1.75 | No | No |
| heading_color | color | #111827 | No | No |
| link_color | color | #7c3aed | No | Yes |
| max_width | range(600-1200) | 800 | No | No |
| paragraph_spacing | range(0-40) | 24 | No | No |

**Example:**
```json
{
  "type": "post_content",
  "attrs": {
    "text_color": "#374151",
    "font_size": 18,
    "line_height": 1.8,
    "link_color": "#7c3aed",
    "link_color__hover": "#5b21b6",
    "max_width": 750,
    "show_drop_cap": true,
    "drop_cap_style": "square"
  }
}
```

---

#### Module: `post_meta`
**Purpose:** Displays post metadata (author, date, categories, tags, reading time)

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| show_author | toggle | true | Show author name |
| show_avatar | toggle | true | Show author avatar |
| show_date | toggle | true | Show publish date |
| date_format | text | "F j, Y" | PHP date format |
| show_categories | toggle | true | Show categories |
| show_tags | toggle | false | Show tags |
| show_comments_count | toggle | false | Show comment count |
| show_reading_time | toggle | false | Show estimated time |
| separator | select | • | Separator: •, |, /, - |
| layout | select | inline | inline, stacked |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| text_color | color | #6b7280 | No | No |
| link_color | color | #7c3aed | No | Yes |
| font_size | range(12-18) | 14 | Yes | No |
| avatar_size | range(24-48) | 32 | No | No |
| text_alignment | select | left | Yes | No |

**Example:**
```json
{
  "type": "post_meta",
  "attrs": {
    "show_author": true,
    "show_avatar": true,
    "show_date": true,
    "show_categories": true,
    "show_reading_time": true,
    "layout": "inline",
    "separator": "•",
    "text_color": "#6b7280",
    "link_color": "#7c3aed",
    "font_size": 14
  }
}
```

---

#### Module: `post_excerpt`
**Purpose:** Displays post excerpt with optional read more link

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| excerpt_length | range(10-100) | 55 | Number of words |
| show_read_more | toggle | true | Show read more link |
| read_more_text | text | "Read More →" | Link text |
| strip_html | toggle | true | Remove HTML tags |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| text_color | color | #4b5563 | No | No |
| font_size | range(14-20) | 16 | Yes | No |
| line_height | range(1.4-2.0) | 1.7 | No | No |
| link_color | color | #7c3aed | No | Yes |
| text_alignment | select | left | Yes | No |

**Example:**
```json
{
  "type": "post_excerpt",
  "attrs": {
    "excerpt_length": 30,
    "show_read_more": true,
    "read_more_text": "Continue Reading →",
    "text_color": "#6b7280",
    "link_color": "#7c3aed",
    "font_size": 16,
    "line_height": 1.7
  }
}
```

---

#### Module: `featured_image`
**Purpose:** Displays the post's featured/thumbnail image

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| size | select | full | full, large, medium, thumbnail |
| show_caption | toggle | false | Display image caption |
| enable_lightbox | toggle | false | Click to open fullsize |
| link_to_post | toggle | false | Click links to post |
| fallback_image | upload | "" | Image if none set |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| border_radius | range(0-30) | 8 | No | No |
| opacity | range(0-100) | 100 | No | Yes |
| max_height | range(200-800) | 0 | No | No |
| object_fit | select | cover | cover, contain, fill |
| caption_color | color | #6b7280 | No | No |

**Example:**
```json
{
  "type": "featured_image",
  "attrs": {
    "size": "large",
    "border_radius": 12,
    "opacity": 100,
    "opacity__hover": 90,
    "show_caption": true,
    "caption_color": "#6b7280",
    "link_to_post": true
  }
}
```

---

#### Module: `author_box`
**Purpose:** Displays author info with avatar, bio, and social links

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| layout | select | horizontal | horizontal, vertical |
| show_avatar | toggle | true | Display author avatar |
| avatar_size | range(48-120) | 80 | Avatar size in px |
| avatar_style | select | circle | circle, square, rounded |
| show_title | toggle | true | Show "About the Author" |
| title_text | text | "About the Author" | Title text |
| show_bio | toggle | true | Show author biography |
| show_role | toggle | false | Show author role |
| show_social | toggle | true | Show social links |
| show_website | toggle | true | Show website link |
| show_post_count | toggle | false | Show total posts |
| link_to_archive | toggle | true | Link to author page |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| title_color | color | #1f2937 | No | No |
| name_color | color | #111827 | No | Yes |
| bio_color | color | #6b7280 | No | No |
| link_color | color | #7c3aed | No | Yes |
| box_background | color | #f9fafb | No | No |
| border_radius | range(0-24) | 12 | No | No |

**Example:**
```json
{
  "type": "author_box",
  "attrs": {
    "layout": "horizontal",
    "show_avatar": true,
    "avatar_size": 80,
    "avatar_style": "circle",
    "show_bio": true,
    "show_social": true,
    "box_background": "#f9fafb",
    "border_radius": 12,
    "name_color": "#111827",
    "bio_color": "#6b7280"
  }
}
```

---

#### Module: `related_posts`
**Purpose:** Displays related posts based on category/tags

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| title | text | "Related Posts" | Section title |
| show_title | toggle | true | Show section title |
| posts_count | range(2-8) | 3 | Number of posts |
| columns | select | 3 | 2, 3, 4 columns (responsive) |
| relation_type | select | category | category, tag, both, author |
| show_image | toggle | true | Show featured image |
| image_aspect | select | 16/9 | 16/9, 4/3, 1/1, 3/4 |
| show_category | toggle | true | Show category badge |
| show_date | toggle | true | Show publish date |
| show_excerpt | toggle | false | Show excerpt |
| excerpt_length | range(10-50) | 20 | Words in excerpt |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| title_color | color | #1f2937 | No | Yes |
| category_color | color | #7c3aed | No | No |
| meta_color | color | #6b7280 | No | No |
| card_background | color | #ffffff | No | No |
| card_border_radius | range(0-24) | 8 | No | No |
| card_shadow | toggle | true | Show shadow |
| gap | range(10-40) | 24 | No | No |

**Example:**
```json
{
  "type": "related_posts",
  "attrs": {
    "title": "You Might Also Like",
    "posts_count": 3,
    "columns": "3",
    "columns__tablet": "2",
    "columns__phone": "1",
    "relation_type": "category",
    "show_image": true,
    "image_aspect": "16/9",
    "card_background": "#ffffff",
    "card_border_radius": 12,
    "card_shadow": true,
    "gap": 24
  }
}
```

---

#### Module: `breadcrumbs`
**Purpose:** Navigation breadcrumbs with schema markup

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| home_text | text | "Home" | Home link text |
| home_icon | toggle | true | Show home icon |
| separator | select | chevron | /, >, >>, |, -, chevron |
| show_current | toggle | true | Show current page |
| link_current | toggle | false | Make current clickable |
| show_category | toggle | true | Show category in trail |
| schema_markup | toggle | true | Add SEO schema |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| text_color | color | #6b7280 | No | No |
| link_color | color | #7c3aed | No | Yes |
| separator_color | color | #9ca3af | No | No |
| current_color | color | #374151 | No | No |
| font_size | range(11-18) | 14 | Yes | No |
| text_alignment | select | left | Yes | No |

**Example:**
```json
{
  "type": "breadcrumbs",
  "attrs": {
    "home_text": "Home",
    "home_icon": true,
    "separator": "chevron",
    "show_current": true,
    "link_color": "#7c3aed",
    "link_color__hover": "#5b21b6",
    "font_size": 14,
    "schema_markup": true
  }
}
```

---

#### Module: `archive_title`
**Purpose:** Displays archive page title (Category: X, Tag: X, etc.)

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| show_prefix | toggle | true | Show "Category:", etc. |
| custom_prefix | text | "" | Override auto prefix |
| show_description | toggle | true | Show archive description |
| show_post_count | toggle | false | Show number of posts |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| title_color | color | #1f2937 | No | No |
| prefix_color | color | #7c3aed | No | No |
| description_color | color | #6b7280 | No | No |
| title_size | range(24-64) | 42 | Yes | No |
| prefix_size | range(14-24) | 16 | No | No |
| text_alignment | select | center | Yes | No |

**Example:**
```json
{
  "type": "archive_title",
  "attrs": {
    "show_prefix": true,
    "show_description": true,
    "show_post_count": true,
    "title_color": "#1f2937",
    "prefix_color": "#7c3aed",
    "title_size": 42,
    "title_size__tablet": 32,
    "text_alignment": "center"
  }
}
```

---

#### Module: `archive_posts`
**Purpose:** Displays posts grid/list for archive pages

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| layout | select | grid | grid, list, masonry |
| columns | select | 3 | 1, 2, 3, 4 (responsive) |
| posts_per_page | range(3-24) | 9 | Posts to show |
| show_image | toggle | true | Show featured image |
| image_position | select | top | top, left, right, background |
| image_aspect | select | 16/9 | 16/9, 4/3, 1/1, 3/4 |
| show_title | toggle | true | Show post title |
| show_excerpt | toggle | true | Show excerpt |
| excerpt_length | range(10-50) | 20 | Words |
| show_author | toggle | true | Show author |
| show_date | toggle | true | Show date |
| show_category | toggle | true | Show category |
| show_read_more | toggle | true | Show link |
| read_more_text | text | "Read More" | Link text |
| show_pagination | toggle | true | Show pagination |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| card_background | color | #ffffff | No | No |
| card_border_radius | range(0-24) | 8 | No | No |
| card_shadow | select | md | none, sm, md, lg |
| gap | range(16-48) | 24 | Yes | No |
| title_color | color | #1f2937 | No | Yes |
| excerpt_color | color | #6b7280 | No | No |
| meta_color | color | #9ca3af | No | No |
| category_color | color | #7c3aed | No | No |

**Example:**
```json
{
  "type": "archive_posts",
  "attrs": {
    "layout": "grid",
    "columns": "3",
    "columns__tablet": "2",
    "columns__phone": "1",
    "posts_per_page": 9,
    "show_image": true,
    "image_aspect": "16/9",
    "show_excerpt": true,
    "excerpt_length": 20,
    "card_background": "#ffffff",
    "card_border_radius": 12,
    "card_shadow": "md",
    "gap": 24,
    "show_pagination": true
  }
}
```

---

#### Module: `site_logo`
**Purpose:** Displays the site logo in header

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| logo | upload | "" | Logo image URL |
| logo_url | text | "/" | Link URL (homepage) |
| logo_alt | text | "Site Logo" | Alt text |
| open_in_new_tab | toggle | false | New tab for link |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| logo_width | range(20-600) | 150 | Yes | No |
| logo_max_height | range(0-300) | 0 | No | No |
| logo_opacity | range(0-100) | 100 | No | Yes |
| alignment | select | left | Yes | No |

**Example:**
```json
{
  "type": "site_logo",
  "attrs": {
    "logo": "{{PEXELS:company,logo}}",
    "logo_url": "/",
    "logo_alt": "Company Logo",
    "logo_width": 180,
    "logo_width__tablet": 150,
    "logo_width__phone": 120,
    "logo_opacity": 100,
    "logo_opacity__hover": 85,
    "alignment": "left"
  }
}
```

---

#### Module: `menu`
**Purpose:** Navigation menu with logo for headers

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| logo | upload | "" | Logo image |
| logo_url | text | "/" | Logo link |
| logo_alt | text | "Site Logo" | Logo alt text |
| menu_style | select | left_aligned | left_aligned, centered_logo, inline_centered, stacked |
| show_cart_icon | toggle | false | E-commerce cart |
| show_search_icon | toggle | true | Search toggle |
| sticky | toggle | false | Sticky header |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| logo_width | range(50-400) | 150 | Yes | No |
| menu_text_color | color | #333333 | No | Yes |
| icon_color | color | #333333 | No | Yes |
| menu_font_size | range(12-20) | 15 | Yes | No |
| menu_font_weight | select | 500 | No | No |
| menu_item_spacing | range(10-40) | 24 | No | No |
| icon_size | range(16-32) | 20 | No | No |

**Example:**
```json
{
  "type": "menu",
  "attrs": {
    "logo": "{{PEXELS:logo,brand}}",
    "menu_style": "left_aligned",
    "show_search_icon": true,
    "sticky": true,
    "logo_width": 160,
    "menu_text_color": "#333333",
    "menu_text_color__hover": "#7c3aed",
    "menu_font_size": 15,
    "menu_font_weight": "500",
    "menu_item_spacing": 28
  }
}
```

---

#### Module: `social_icons`
**Purpose:** Social media icon links

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| platforms | array | [...] | List of platforms with URLs |
| icon_style | select | default | default, circle, rounded, square |
| open_new_tab | toggle | true | Open in new tab |
| show_labels | toggle | false | Show platform names |
| alignment | select | center | left, center, right |

Available platforms: facebook, twitter, instagram, linkedin, youtube, pinterest, tiktok, github, dribbble, behance, medium, discord, telegram, whatsapp, snapchat, reddit, tumblr, vimeo, twitch, spotify

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| icon_color | color | #4b5563 | No | Yes |
| icon_bg_color | color | transparent | No | Yes |
| icon_size | range(16-48) | 24 | No | No |
| icon_spacing | range(4-24) | 12 | No | No |
| use_brand_colors | toggle | false | Use official colors |

**Example:**
```json
{
  "type": "social_icons",
  "attrs": {
    "platforms": [
      {"platform": "facebook", "url": "https://facebook.com/company"},
      {"platform": "twitter", "url": "https://twitter.com/company"},
      {"platform": "instagram", "url": "https://instagram.com/company"},
      {"platform": "linkedin", "url": "https://linkedin.com/company/company"}
    ],
    "icon_style": "circle",
    "icon_color": "#ffffff",
    "icon_bg_color": "#374151",
    "icon_bg_color__hover": "#7c3aed",
    "icon_size": 20,
    "icon_spacing": 12,
    "alignment": "center"
  }
}
```

---

#### Module: `search_form`
**Purpose:** Search form or icon for headers

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| display_type | select | full_form | icon_only, full_form, minimal |
| placeholder | text | "Search..." | Input placeholder |
| show_button | toggle | true | Show submit button |
| button_style | select | icon | icon, text, both |
| button_text | text | "Search" | Button text |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| button_bg_color | color | #2ea3f2 | No | Yes |
| button_text_color | color | #ffffff | No | No |
| input_bg_color | color | #f5f5f5 | No | No |
| input_text_color | color | #333333 | No | No |
| input_border_color | color | #e5e5e5 | No | No |
| input_focus_border_color | color | #2ea3f2 | No | No |
| border_radius | range(0-30) | 4 | No | No |
| input_height | range(32-56) | 44 | No | No |
| form_width | range(200-600) | 300 | Yes | No |
| icon_color | color | #333333 | No | Yes |

**Example:**
```json
{
  "type": "search_form",
  "attrs": {
    "display_type": "full_form",
    "placeholder": "Search articles...",
    "show_button": true,
    "button_style": "icon",
    "button_bg_color": "#7c3aed",
    "input_bg_color": "#f9fafb",
    "input_border_color": "#e5e7eb",
    "input_focus_border_color": "#7c3aed",
    "border_radius": 8,
    "form_width": 320
  }
}
```

---

#### Module: `cart_icon`
**Purpose:** Shopping cart icon with count badge for e-commerce

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| cart_url | text | "/cart" | Cart page URL |
| icon_style | select | cart | cart, bag, basket |
| show_badge | toggle | true | Show item count |
| demo_count | number | 3 | Preview count |
| show_total | toggle | false | Show cart total |
| demo_total | text | "$99.00" | Preview total |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| icon_color | color | #333333 | No | Yes |
| icon_size | range(18-40) | 24 | No | No |
| badge_bg_color | color | #e74c3c | No | No |
| badge_text_color | color | #ffffff | No | No |
| badge_size | range(14-24) | 18 | No | No |
| total_color | color | #333333 | No | No |

**Example:**
```json
{
  "type": "cart_icon",
  "attrs": {
    "cart_url": "/cart",
    "icon_style": "bag",
    "show_badge": true,
    "icon_color": "#1f2937",
    "icon_color__hover": "#7c3aed",
    "icon_size": 24,
    "badge_bg_color": "#ef4444",
    "badge_text_color": "#ffffff"
  }
}
```

---

#### Module: `header_button`
**Purpose:** CTA button for header (Get Started, Contact, etc.)

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| button_text | text | "Get Started" | Button label |
| link_url | text | "#" | Button link |
| button_style | select | filled | filled, outline, text |
| button_size | select | medium | small, medium, large |
| show_icon | toggle | false | Show arrow icon |
| icon_position | select | right | left, right |
| open_new_tab | toggle | false | Open in new tab |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| button_bg_color | color | #2ea3f2 | No | Yes |
| button_text_color | color | #ffffff | No | Yes |
| border_color | color | #2ea3f2 | No | Yes |
| border_radius | range(0-50) | 6 | No | No |
| font_size | range(12-20) | 14 | Yes | No |
| font_weight | select | 600 | No | No |

**Example:**
```json
{
  "type": "header_button",
  "attrs": {
    "button_text": "Get Started",
    "link_url": "/signup",
    "button_style": "filled",
    "button_size": "medium",
    "show_icon": true,
    "icon_position": "right",
    "button_bg_color": "#7c3aed",
    "button_bg_color__hover": "#5b21b6",
    "button_text_color": "#ffffff",
    "border_radius": 8,
    "font_weight": "600"
  }
}
```

---

#### Module: `footer_info`
**Purpose:** Contact info, address, or business hours with icons

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| title | text | "Contact Info" | Section title |
| show_title | toggle | true | Show title |
| info_type | select | contact | contact, address, hours, custom |
| show_icons | toggle | true | Show icons |

*For info_type: contact:*
| phone | text | "+1 (555) 123-4567" | Phone number |
| email | text | "info@example.com" | Email address |
| fax | text | "" | Fax number |

*For info_type: address:*
| address_line1 | text | "123 Main Street" | Address |
| address_line2 | text | "Suite 100" | Address line 2 |
| city_state_zip | text | "New York, NY 10001" | City, State ZIP |
| country | text | "United States" | Country |
| map_url | text | "" | Google Maps URL |

*For info_type: hours:*
| weekday_hours | text | "Mon - Fri: 9AM - 5PM" | Weekday hours |
| weekend_hours | text | "Sat - Sun: Closed" | Weekend hours |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| title_color | color | #ffffff | No | No |
| text_color | color | #cccccc | No | No |
| icon_color | color | #2ea3f2 | No | No |
| link_color | color | #2ea3f2 | No | Yes |
| icon_size | range(14-28) | 18 | No | No |
| item_spacing | range(8-24) | 12 | No | No |

**Example:**
```json
{
  "type": "footer_info",
  "attrs": {
    "title": "Contact Us",
    "info_type": "contact",
    "show_icons": true,
    "phone": "+1 (555) 123-4567",
    "email": "hello@company.com",
    "title_color": "#ffffff",
    "text_color": "#9ca3af",
    "icon_color": "#7c3aed",
    "link_color": "#7c3aed",
    "link_color__hover": "#a78bfa"
  }
}
```

---

#### Module: `footer_menu`
**Purpose:** Simple navigation links for footer (Privacy, Terms, etc.)

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| title | text | "Quick Links" | Section title |
| show_title | toggle | true | Show title |
| layout | select | vertical | vertical, horizontal |
| menu_items | textarea | "..." | Links: "Label \| URL" per line |
| show_bullet | toggle | false | Show bullet/arrow |
| bullet_style | select | arrow | arrow, chevron, dot, dash |
| open_new_tab | toggle | false | Open in new tab |

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| title_color | color | #ffffff | No | No |
| link_color | color | #cccccc | No | Yes |
| font_size | range(12-18) | 14 | Yes | No |
| item_spacing | range(4-20) | 10 | No | No |

**Example:**
```json
{
  "type": "footer_menu",
  "attrs": {
    "title": "Company",
    "layout": "vertical",
    "menu_items": "About Us | /about\nCareers | /careers\nPress | /press\nContact | /contact",
    "show_bullet": true,
    "bullet_style": "chevron",
    "title_color": "#ffffff",
    "link_color": "#9ca3af",
    "link_color__hover": "#ffffff",
    "font_size": 14,
    "item_spacing": 10
  }
}
```

---

#### Module: `copyright`
**Purpose:** Copyright text with dynamic year

**Content Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| copyright_text | text | "© {year} {site_name}. All rights reserved." | Text with placeholders |
| site_name | text | "Your Company" | Company name |
| start_year | text | "" | For year range (2020-2024) |
| show_powered_by | toggle | false | Show "Powered by" |
| powered_by_text | text | "Powered by Jessie CMS" | Powered by text |
| powered_by_url | text | "https://jessiecms.com" | Powered by URL |

Placeholders: {year} = current year, {site_name} = company name

**Style Attributes:**
| Attribute | Type | Default | Responsive | Hover |
|-----------|------|---------|------------|-------|
| text_color | color | #666666 | No | Yes |
| link_color | color | #2ea3f2 | No | Yes |
| font_size | range(10-20) | 14 | Yes | No |
| alignment | select | center | Yes | No |

**Example:**
```json
{
  "type": "copyright",
  "attrs": {
    "copyright_text": "© {year} {site_name}. All rights reserved.",
    "site_name": "TechStartup Inc",
    "start_year": "2020",
    "alignment": "center",
    "text_color": "#9ca3af",
    "link_color": "#7c3aed",
    "font_size": 14
  }
}
```

---

PROMPT;
    }

    /**
     * Section 6: Feather Icons
     * Complete list of available icons for blurb, icon modules
     */
    private static function getFeatherIcons(): string
    {
        return <<<'PROMPT'

## Feather Icons (Available for font_icon attribute)

Use these icon names in the `font_icon` attribute for blurb, icon, and other modules that support icons.

### Interface & Navigation
activity, alert-circle, alert-octagon, alert-triangle, archive, arrow-down, arrow-down-circle, arrow-down-left, arrow-down-right, arrow-left, arrow-left-circle, arrow-right, arrow-right-circle, arrow-up, arrow-up-circle, arrow-up-left, arrow-up-right, at-sign, award, bar-chart, bar-chart-2, battery, battery-charging, bell, bell-off, bluetooth, book, book-open, bookmark, box, briefcase, calendar, camera, camera-off, cast, check, check-circle, check-square, chevron-down, chevron-left, chevron-right, chevron-up, chevrons-down, chevrons-left, chevrons-right, chevrons-up, chrome, circle, clipboard, clock, cloud, cloud-drizzle, cloud-lightning, cloud-off, cloud-rain, cloud-snow, code, codepen, codesandbox, coffee, columns, command, compass, copy, corner-down-left, corner-down-right, corner-left-down, corner-left-up, corner-right-down, corner-right-up, corner-up-left, corner-up-right, cpu, credit-card, crop, crosshair, database, delete, disc, divide, divide-circle, divide-square, dollar-sign, download, download-cloud, dribbble, droplet, edit, edit-2, edit-3, external-link, eye, eye-off, facebook, fast-forward, feather, figma, file, file-minus, file-plus, file-text, film, filter, flag, folder, folder-minus, folder-plus, framer, frown, gift, git-branch, git-commit, git-merge, git-pull-request, github, gitlab, globe, grid, hard-drive, hash, headphones, heart, help-circle, hexagon, home, image, inbox, info, instagram, italic, key, layers, layout, life-buoy, link, link-2, linkedin, list, loader, lock, log-in, log-out, mail, map, map-pin, maximize, maximize-2, meh, menu, message-circle, message-square, mic, mic-off, minimize, minimize-2, minus, minus-circle, minus-square, monitor, moon, more-horizontal, more-vertical, mouse-pointer, move, music, navigation, navigation-2, octagon, package, paperclip, pause, pause-circle, pen-tool, percent, phone, phone-call, phone-forwarded, phone-incoming, phone-missed, phone-off, phone-outgoing, pie-chart, play, play-circle, plus, plus-circle, plus-square, pocket, power, printer, radio, refresh-ccw, refresh-cw, repeat, rewind, rotate-ccw, rotate-cw, rss, save, scissors, search, send, server, settings, share, share-2, shield, shield-off, shopping-bag, shopping-cart, shuffle, sidebar, skip-back, skip-forward, slack, slash, sliders, smartphone, smile, speaker, square, star, stop-circle, sun, sunrise, sunset, tablet, tag, target, terminal, thermometer, thumbs-down, thumbs-up, toggle-left, toggle-right, tool, trash, trash-2, trello, trending-down, trending-up, triangle, truck, tv, twitch, twitter, type, umbrella, underline, unlock, upload, upload-cloud, user, user-check, user-minus, user-plus, user-x, users, video, video-off, voicemail, volume, volume-1, volume-2, volume-x, watch, wifi, wifi-off, wind, x, x-circle, x-octagon, x-square, youtube, zap, zap-off, zoom-in, zoom-out

### Commonly Used by Category

**Business/Corporate:**
briefcase, building, chart-bar, chart-pie, clipboard, database, file-text, folder, globe, layers, settings, target, trending-up, users

**Communication:**
at-sign, bell, mail, message-circle, message-square, phone, send, share-2, video

**E-commerce/Shopping:**
credit-card, dollar-sign, gift, package, percent, shopping-bag, shopping-cart, tag, truck

**Technology/Development:**
code, codepen, cpu, database, github, gitlab, monitor, server, smartphone, tablet, terminal, tool

**Social Media:**
dribbble, facebook, github, instagram, linkedin, twitter, youtube

**Security/Privacy:**
eye, eye-off, key, lock, shield, unlock, user-check

**Media/Creative:**
camera, edit, film, headphones, image, mic, music, pen-tool, play, video, volume

**Navigation/Location:**
compass, home, map, map-pin, navigation, navigation-2

**Weather/Nature:**
cloud, droplet, moon, sun, sunrise, sunset, thermometer, umbrella, wind

**Health/Wellness:**
activity, heart, smile, thermometer

**Time/Calendar:**
calendar, clock, watch

### Icon Usage Example
```json
{
  "type": "blurb",
  "attrs": {
    "use_icon": true,
    "font_icon": "shield",
    "icon_color": "#7c3aed",
    "icon_font_size": 48,
    "title": "Enterprise Security",
    "content": "Bank-level encryption protects your data."
  }
}
```

PROMPT;
    }

    /**
     * Section 7: Design Principles
     * Professional web design guidelines for AI
     */
    private static function getDesignPrinciples(): string
    {
        return <<<'PROMPT'

## Design Principles for Professional Layouts

### 1. Visual Hierarchy
- **Hero sections**: Largest text (48-72px), most padding (100-140px top/bottom)
- **Section headings**: 32-42px, always heavier weight than body
- **Body text**: 16-18px, 1.6-1.8 line height for readability
- **Subtext/meta**: 14px, lighter color (#6b7280)
- **Create contrast**: Not everything can be important - choose focal points

### 2. Spacing System (Consistent Scale)
Use these spacing values consistently:
- **xs**: 8px (tight spacing, between related items)
- **sm**: 16px (compact spacing, within components)
- **md**: 24px (default gap between elements)
- **lg**: 32px (section internal spacing)
- **xl**: 48px (between distinct sections)
- **2xl**: 64px (major section breaks)
- **3xl**: 96px (hero/large sections)
- **4xl**: 128px (dramatic spacing)

**Section Padding Guidelines:**
- Hero sections: 100-140px vertical
- Regular sections: 80-100px vertical
- Compact sections: 60-80px vertical
- Footer: 60-80px vertical

### 3. Color Usage
- **Primary color**: CTAs, links, key highlights (use sparingly - 10-20% of page)
- **Dark colors**: Headings, important text (#111827, #1f2937)
- **Medium colors**: Body text (#374151, #4b5563)
- **Light colors**: Secondary text, meta (#6b7280, #9ca3af)
- **Background variety**: Alternate between white and light gray (#f9fafb, #f3f4f6) sections
- **Dark sections**: Use for trust/authority sections (testimonials, CTAs)

### 4. Typography Combinations
**Modern Tech:**
- Headings: Inter, 700 weight
- Body: Inter, 400 weight

**Professional/Corporate:**
- Headings: Playfair Display, 700 weight
- Body: Open Sans, 400 weight

**Clean/Minimal:**
- Headings: Poppins, 600 weight
- Body: Poppins, 400 weight

### 5. Section Flow (Typical Landing Page)
1. **Hero** (DOMINANT) - Capture attention, value proposition
2. **Social Proof** - Logos, trust badges (brief)
3. **Features/Benefits** - What you offer (3-4 items)
4. **How It Works** - Simple 3-step process
5. **Testimonials** - Build trust
6. **Pricing** (if applicable)
7. **FAQ** - Address objections
8. **CTA** - Final conversion push
9. **Footer** - Navigation, contact, legal

### 6. Column Layouts
- **Hero**: Usually 1/2,1/2 or full-width centered
- **Features**: 1/3,1/3,1/3 or 1/4,1/4,1/4,1/4
- **Testimonials**: 1/3,1/3,1/3 for cards or full-width for single
- **CTA**: Centered single column
- **Stats**: 1/4,1/4,1/4,1/4

### 7. Image Guidelines
- **Hero images**: High-quality, relevant to message
- **Feature icons**: Consistent size (40-64px), same color family
- **Testimonial photos**: Square or circle, consistent size
- **Use {{PEXELS:keyword1,keyword2}}** for contextual images

### 8. Button Hierarchy
- **Primary CTA**: Filled, primary color, prominent size
- **Secondary CTA**: Outline or ghost style
- **Only ONE primary CTA per viewport** - don't compete for attention

### 9. Whitespace
- **Don't fear whitespace** - it creates breathing room
- **Group related items** - closer spacing
- **Separate distinct concepts** - larger gaps
- **Margins > Padding** for section separation

### 10. Mobile Considerations
- Always set __tablet and __phone suffixes for:
  - font_size (reduce by 20-30%)
  - padding (reduce vertical by 30-40%)
  - columns (stack on mobile: "1" or "1/2,1/2")
- Minimum tap target: 44px for buttons/links

PROMPT;
    }

    /**
     * Section 8: Example Layouts
     * Complete production-ready examples
     */
    private static function getExampleLayouts(): string
    {
        return <<<'PROMPT'

## Complete Example Layouts

### Example 1: SaaS Landing Page

```json
{
  "sections": [
    {
      "type": "section",
      "attrs": {
        "background_color": "#ffffff",
        "padding": {"top": 120, "right": 0, "bottom": 120, "left": 0},
        "padding__tablet": {"top": 80, "right": 0, "bottom": 80, "left": 0},
        "padding__phone": {"top": 60, "right": 0, "bottom": 60, "left": 0}
      },
      "children": [
        {
          "type": "row",
          "attrs": {
            "columns": "1/2,1/2",
            "columns__phone": "1",
            "column_gap": 48,
            "vertical_align": "center"
          },
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "heading",
                  "attrs": {
                    "text": "Streamline Your Workflow with AI-Powered Tools",
                    "level": "h1",
                    "font_size": 56,
                    "font_size__tablet": 42,
                    "font_size__phone": 32,
                    "font_weight": "700",
                    "text_color": "#111827",
                    "line_height": 1.15,
                    "margin": {"top": 0, "right": 0, "bottom": 24, "left": 0}
                  }
                },
                {
                  "type": "text",
                  "attrs": {
                    "content": "<p>Save 10+ hours every week with intelligent automation. Join 5,000+ teams already transforming how they work.</p>",
                    "font_size": 18,
                    "font_size__phone": 16,
                    "text_color": "#6b7280",
                    "line_height": 1.7,
                    "margin": {"top": 0, "right": 0, "bottom": 32, "left": 0}
                  }
                },
                {
                  "type": "button",
                  "attrs": {
                    "text": "Start Free Trial",
                    "link_url": "/signup",
                    "background_color": "#7c3aed",
                    "background_color__hover": "#5b21b6",
                    "text_color": "#ffffff",
                    "font_size": 16,
                    "font_weight": "600",
                    "padding": {"top": 16, "right": 32, "bottom": 16, "left": 32},
                    "border_radius": {"top_left": 8, "top_right": 8, "bottom_right": 8, "bottom_left": 8}
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "image",
                  "attrs": {
                    "src": "{{PEXELS:dashboard,software,technology}}",
                    "alt": "Platform Dashboard",
                    "border_radius": {"top_left": 12, "top_right": 12, "bottom_right": 12, "bottom_left": 12},
                    "box_shadow": "0 25px 50px -12px rgba(0, 0, 0, 0.15)"
                  }
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "type": "section",
      "attrs": {
        "background_color": "#f9fafb",
        "padding": {"top": 80, "right": 0, "bottom": 80, "left": 0}
      },
      "children": [
        {
          "type": "row",
          "attrs": {"columns": "1"},
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "text",
                  "attrs": {
                    "content": "<p style=\"text-align: center;\">Trusted by innovative teams at</p>",
                    "font_size": 14,
                    "text_color": "#9ca3af",
                    "text_align": "center",
                    "margin": {"top": 0, "right": 0, "bottom": 24, "left": 0}
                  }
                }
              ]
            }
          ]
        },
        {
          "type": "row",
          "attrs": {
            "columns": "1/5,1/5,1/5,1/5,1/5",
            "columns__phone": "1/2,1/2",
            "column_gap": 48
          },
          "children": [
            {"type": "column", "children": [{"type": "image", "attrs": {"src": "{{PEXELS:company,logo}}", "alt": "Company 1", "max_width": 120, "opacity": 60}}]},
            {"type": "column", "children": [{"type": "image", "attrs": {"src": "{{PEXELS:brand,logo}}", "alt": "Company 2", "max_width": 120, "opacity": 60}}]},
            {"type": "column", "children": [{"type": "image", "attrs": {"src": "{{PEXELS:business,logo}}", "alt": "Company 3", "max_width": 120, "opacity": 60}}]},
            {"type": "column", "children": [{"type": "image", "attrs": {"src": "{{PEXELS:tech,logo}}", "alt": "Company 4", "max_width": 120, "opacity": 60}}]},
            {"type": "column", "children": [{"type": "image", "attrs": {"src": "{{PEXELS:startup,logo}}", "alt": "Company 5", "max_width": 120, "opacity": 60}}]}
          ]
        }
      ]
    },
    {
      "type": "section",
      "attrs": {
        "background_color": "#ffffff",
        "padding": {"top": 100, "right": 0, "bottom": 100, "left": 0},
        "padding__tablet": {"top": 80, "right": 0, "bottom": 80, "left": 0}
      },
      "children": [
        {
          "type": "row",
          "attrs": {"columns": "1"},
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "heading",
                  "attrs": {
                    "text": "Everything You Need to Succeed",
                    "level": "h2",
                    "font_size": 42,
                    "font_size__tablet": 32,
                    "font_size__phone": 28,
                    "font_weight": "700",
                    "text_color": "#111827",
                    "text_align": "center",
                    "margin": {"top": 0, "right": 0, "bottom": 16, "left": 0}
                  }
                },
                {
                  "type": "text",
                  "attrs": {
                    "content": "<p>Powerful features designed to help your team work smarter, not harder.</p>",
                    "font_size": 18,
                    "text_color": "#6b7280",
                    "text_align": "center",
                    "margin": {"top": 0, "right": 0, "bottom": 48, "left": 0}
                  }
                }
              ]
            }
          ]
        },
        {
          "type": "row",
          "attrs": {
            "columns": "1/3,1/3,1/3",
            "columns__phone": "1",
            "column_gap": 32,
            "row_gap": 32
          },
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "blurb",
                  "attrs": {
                    "use_icon": true,
                    "font_icon": "zap",
                    "icon_color": "#7c3aed",
                    "icon_font_size": 48,
                    "title": "Lightning Fast",
                    "title_font_size": 20,
                    "title_font_weight": "600",
                    "title_color": "#111827",
                    "content": "Process thousands of tasks in seconds with our optimized infrastructure.",
                    "body_font_size": 16,
                    "body_color": "#6b7280",
                    "text_align": "center",
                    "padding": {"top": 32, "right": 24, "bottom": 32, "left": 24},
                    "background_color": "#f9fafb",
                    "border_radius": {"top_left": 12, "top_right": 12, "bottom_right": 12, "bottom_left": 12}
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "blurb",
                  "attrs": {
                    "use_icon": true,
                    "font_icon": "shield",
                    "icon_color": "#7c3aed",
                    "icon_font_size": 48,
                    "title": "Enterprise Security",
                    "title_font_size": 20,
                    "title_font_weight": "600",
                    "title_color": "#111827",
                    "content": "Bank-level encryption and SOC 2 compliance keep your data safe.",
                    "body_font_size": 16,
                    "body_color": "#6b7280",
                    "text_align": "center",
                    "padding": {"top": 32, "right": 24, "bottom": 32, "left": 24},
                    "background_color": "#f9fafb",
                    "border_radius": {"top_left": 12, "top_right": 12, "bottom_right": 12, "bottom_left": 12}
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "blurb",
                  "attrs": {
                    "use_icon": true,
                    "font_icon": "users",
                    "icon_color": "#7c3aed",
                    "icon_font_size": 48,
                    "title": "Team Collaboration",
                    "title_font_size": 20,
                    "title_font_weight": "600",
                    "title_color": "#111827",
                    "content": "Real-time collaboration tools that keep everyone on the same page.",
                    "body_font_size": 16,
                    "body_color": "#6b7280",
                    "text_align": "center",
                    "padding": {"top": 32, "right": 24, "bottom": 32, "left": 24},
                    "background_color": "#f9fafb",
                    "border_radius": {"top_left": 12, "top_right": 12, "bottom_right": 12, "bottom_left": 12}
                  }
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "type": "section",
      "attrs": {
        "background_color": "#111827",
        "padding": {"top": 100, "right": 0, "bottom": 100, "left": 0}
      },
      "children": [
        {
          "type": "row",
          "attrs": {
            "columns": "1/3,1/3,1/3",
            "columns__phone": "1",
            "column_gap": 32
          },
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "testimonial",
                  "attrs": {
                    "content": "This tool has completely transformed how our team operates. We've cut our processing time by 60%.",
                    "author": "Sarah Chen",
                    "job_title": "CTO at TechFlow",
                    "portrait_url": "{{PEXELS:woman,professional,portrait}}",
                    "quote_color": "#ffffff",
                    "author_color": "#ffffff",
                    "job_title_color": "#9ca3af",
                    "background_color": "rgba(255,255,255,0.05)",
                    "border_radius": {"top_left": 12, "top_right": 12, "bottom_right": 12, "bottom_left": 12},
                    "padding": {"top": 32, "right": 32, "bottom": 32, "left": 32}
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "testimonial",
                  "attrs": {
                    "content": "The best investment we've made this year. Our team loves it and adoption was instant.",
                    "author": "Michael Torres",
                    "job_title": "VP Operations at Scale",
                    "portrait_url": "{{PEXELS:man,business,portrait}}",
                    "quote_color": "#ffffff",
                    "author_color": "#ffffff",
                    "job_title_color": "#9ca3af",
                    "background_color": "rgba(255,255,255,0.05)",
                    "border_radius": {"top_left": 12, "top_right": 12, "bottom_right": 12, "bottom_left": 12},
                    "padding": {"top": 32, "right": 32, "bottom": 32, "left": 32}
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "testimonial",
                  "attrs": {
                    "content": "Support is incredible and the product just keeps getting better. Highly recommended.",
                    "author": "Emily Watson",
                    "job_title": "CEO at GrowthLabs",
                    "portrait_url": "{{PEXELS:woman,ceo,portrait}}",
                    "quote_color": "#ffffff",
                    "author_color": "#ffffff",
                    "job_title_color": "#9ca3af",
                    "background_color": "rgba(255,255,255,0.05)",
                    "border_radius": {"top_left": 12, "top_right": 12, "bottom_right": 12, "bottom_left": 12},
                    "padding": {"top": 32, "right": 32, "bottom": 32, "left": 32}
                  }
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "type": "section",
      "attrs": {
        "background_color": "#7c3aed",
        "padding": {"top": 100, "right": 0, "bottom": 100, "left": 0}
      },
      "children": [
        {
          "type": "row",
          "attrs": {"columns": "1"},
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "heading",
                  "attrs": {
                    "text": "Ready to Transform Your Workflow?",
                    "level": "h2",
                    "font_size": 42,
                    "font_size__tablet": 32,
                    "font_weight": "700",
                    "text_color": "#ffffff",
                    "text_align": "center",
                    "margin": {"top": 0, "right": 0, "bottom": 16, "left": 0}
                  }
                },
                {
                  "type": "text",
                  "attrs": {
                    "content": "<p>Start your 14-day free trial today. No credit card required.</p>",
                    "font_size": 18,
                    "text_color": "rgba(255,255,255,0.9)",
                    "text_align": "center",
                    "margin": {"top": 0, "right": 0, "bottom": 32, "left": 0}
                  }
                },
                {
                  "type": "button",
                  "attrs": {
                    "text": "Get Started Free",
                    "link_url": "/signup",
                    "background_color": "#ffffff",
                    "background_color__hover": "#f9fafb",
                    "text_color": "#7c3aed",
                    "font_size": 16,
                    "font_weight": "600",
                    "padding": {"top": 16, "right": 32, "bottom": 16, "left": 32},
                    "border_radius": {"top_left": 8, "top_right": 8, "bottom_right": 8, "bottom_left": 8},
                    "alignment": "center"
                  }
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

---

### Example 2: Agency Services Page

```json
{
  "sections": [
    {
      "type": "section",
      "attrs": {
        "background_color": "#0f172a",
        "padding": {"top": 140, "right": 0, "bottom": 140, "left": 0},
        "padding__tablet": {"top": 100, "right": 0, "bottom": 100, "left": 0}
      },
      "children": [
        {
          "type": "row",
          "attrs": {"columns": "1"},
          "children": [
            {
              "type": "column",
              "attrs": {"max_width": 800, "alignment": "center"},
              "children": [
                {
                  "type": "text",
                  "attrs": {
                    "content": "<p>CREATIVE AGENCY</p>",
                    "font_size": 14,
                    "font_weight": "600",
                    "text_color": "#a78bfa",
                    "text_align": "center",
                    "letter_spacing": 3,
                    "margin": {"top": 0, "right": 0, "bottom": 16, "left": 0}
                  }
                },
                {
                  "type": "heading",
                  "attrs": {
                    "text": "We Build Digital Experiences That Matter",
                    "level": "h1",
                    "font_size": 64,
                    "font_size__tablet": 48,
                    "font_size__phone": 36,
                    "font_weight": "700",
                    "text_color": "#ffffff",
                    "text_align": "center",
                    "line_height": 1.1,
                    "margin": {"top": 0, "right": 0, "bottom": 24, "left": 0}
                  }
                },
                {
                  "type": "text",
                  "attrs": {
                    "content": "<p>Award-winning design studio specializing in brand identity, web design, and digital marketing for ambitious companies.</p>",
                    "font_size": 20,
                    "font_size__phone": 18,
                    "text_color": "#94a3b8",
                    "text_align": "center",
                    "line_height": 1.7,
                    "margin": {"top": 0, "right": 0, "bottom": 40, "left": 0}
                  }
                },
                {
                  "type": "row",
                  "attrs": {
                    "columns": "1/2,1/2",
                    "column_gap": 16,
                    "alignment": "center"
                  },
                  "children": [
                    {
                      "type": "column",
                      "children": [
                        {
                          "type": "button",
                          "attrs": {
                            "text": "View Our Work",
                            "link_url": "/portfolio",
                            "background_color": "#a78bfa",
                            "background_color__hover": "#8b5cf6",
                            "text_color": "#ffffff",
                            "font_size": 16,
                            "font_weight": "600",
                            "padding": {"top": 16, "right": 28, "bottom": 16, "left": 28},
                            "border_radius": {"top_left": 6, "top_right": 6, "bottom_right": 6, "bottom_left": 6},
                            "alignment": "right"
                          }
                        }
                      ]
                    },
                    {
                      "type": "column",
                      "children": [
                        {
                          "type": "button",
                          "attrs": {
                            "text": "Get in Touch",
                            "link_url": "/contact",
                            "background_color": "transparent",
                            "text_color": "#ffffff",
                            "font_size": 16,
                            "font_weight": "600",
                            "padding": {"top": 16, "right": 28, "bottom": 16, "left": 28},
                            "border_radius": {"top_left": 6, "top_right": 6, "bottom_right": 6, "bottom_left": 6},
                            "border_width": {"top": 2, "right": 2, "bottom": 2, "left": 2},
                            "border_color": "#475569",
                            "alignment": "left"
                          }
                        }
                      ]
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "type": "section",
      "attrs": {
        "background_color": "#ffffff",
        "padding": {"top": 100, "right": 0, "bottom": 100, "left": 0}
      },
      "children": [
        {
          "type": "row",
          "attrs": {"columns": "1"},
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "heading",
                  "attrs": {
                    "text": "Our Services",
                    "level": "h2",
                    "font_size": 42,
                    "font_size__tablet": 32,
                    "font_weight": "700",
                    "text_color": "#0f172a",
                    "text_align": "center",
                    "margin": {"top": 0, "right": 0, "bottom": 60, "left": 0}
                  }
                }
              ]
            }
          ]
        },
        {
          "type": "row",
          "attrs": {
            "columns": "1/2,1/2",
            "columns__phone": "1",
            "column_gap": 48,
            "row_gap": 48
          },
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "blurb",
                  "attrs": {
                    "use_icon": true,
                    "font_icon": "pen-tool",
                    "icon_color": "#a78bfa",
                    "icon_font_size": 40,
                    "icon_placement": "left",
                    "title": "Brand Identity",
                    "title_font_size": 24,
                    "title_font_weight": "600",
                    "title_color": "#0f172a",
                    "content": "Strategic brand development that tells your story and connects with your audience. Logos, guidelines, and visual systems.",
                    "body_font_size": 16,
                    "body_color": "#64748b",
                    "content_max_width": 400
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "blurb",
                  "attrs": {
                    "use_icon": true,
                    "font_icon": "monitor",
                    "icon_color": "#a78bfa",
                    "icon_font_size": 40,
                    "icon_placement": "left",
                    "title": "Web Design",
                    "title_font_size": 24,
                    "title_font_weight": "600",
                    "title_color": "#0f172a",
                    "content": "Beautiful, responsive websites that convert visitors into customers. UX-focused design with seamless development.",
                    "body_font_size": 16,
                    "body_color": "#64748b"
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "blurb",
                  "attrs": {
                    "use_icon": true,
                    "font_icon": "trending-up",
                    "icon_color": "#a78bfa",
                    "icon_font_size": 40,
                    "icon_placement": "left",
                    "title": "Digital Marketing",
                    "title_font_size": 24,
                    "title_font_weight": "600",
                    "title_color": "#0f172a",
                    "content": "Data-driven campaigns that grow your reach. SEO, content marketing, and paid advertising strategies.",
                    "body_font_size": 16,
                    "body_color": "#64748b"
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "blurb",
                  "attrs": {
                    "use_icon": true,
                    "font_icon": "smartphone",
                    "icon_color": "#a78bfa",
                    "icon_font_size": 40,
                    "icon_placement": "left",
                    "title": "App Development",
                    "title_font_size": 24,
                    "title_font_weight": "600",
                    "title_color": "#0f172a",
                    "content": "Native and cross-platform mobile apps that users love. From concept to launch and beyond.",
                    "body_font_size": 16,
                    "body_color": "#64748b"
                  }
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "type": "section",
      "attrs": {
        "background_color": "#f8fafc",
        "padding": {"top": 80, "right": 0, "bottom": 80, "left": 0}
      },
      "children": [
        {
          "type": "row",
          "attrs": {
            "columns": "1/4,1/4,1/4,1/4",
            "columns__tablet": "1/2,1/2",
            "columns__phone": "1",
            "column_gap": 32
          },
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "number_counter",
                  "attrs": {
                    "number": 150,
                    "title": "Projects Completed",
                    "number_prefix": "",
                    "number_suffix": "+",
                    "number_color": "#0f172a",
                    "number_font_size": 56,
                    "number_font_weight": "700",
                    "title_color": "#64748b",
                    "title_font_size": 16,
                    "text_align": "center"
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "number_counter",
                  "attrs": {
                    "number": 12,
                    "title": "Years Experience",
                    "number_color": "#0f172a",
                    "number_font_size": 56,
                    "number_font_weight": "700",
                    "title_color": "#64748b",
                    "title_font_size": 16,
                    "text_align": "center"
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "number_counter",
                  "attrs": {
                    "number": 98,
                    "title": "Client Satisfaction",
                    "number_suffix": "%",
                    "number_color": "#0f172a",
                    "number_font_size": 56,
                    "number_font_weight": "700",
                    "title_color": "#64748b",
                    "title_font_size": 16,
                    "text_align": "center"
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "number_counter",
                  "attrs": {
                    "number": 24,
                    "title": "Team Members",
                    "number_color": "#0f172a",
                    "number_font_size": 56,
                    "number_font_weight": "700",
                    "title_color": "#64748b",
                    "title_font_size": 16,
                    "text_align": "center"
                  }
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "type": "section",
      "attrs": {
        "background_color": "#0f172a",
        "padding": {"top": 100, "right": 0, "bottom": 100, "left": 0}
      },
      "children": [
        {
          "type": "row",
          "attrs": {
            "columns": "1/2,1/2",
            "columns__phone": "1",
            "column_gap": 64,
            "vertical_align": "center"
          },
          "children": [
            {
              "type": "column",
              "children": [
                {
                  "type": "text",
                  "attrs": {
                    "content": "<p>LET'S WORK TOGETHER</p>",
                    "font_size": 14,
                    "font_weight": "600",
                    "text_color": "#a78bfa",
                    "letter_spacing": 3,
                    "margin": {"top": 0, "right": 0, "bottom": 16, "left": 0}
                  }
                },
                {
                  "type": "heading",
                  "attrs": {
                    "text": "Have a Project in Mind?",
                    "level": "h2",
                    "font_size": 48,
                    "font_size__tablet": 36,
                    "font_weight": "700",
                    "text_color": "#ffffff",
                    "line_height": 1.2,
                    "margin": {"top": 0, "right": 0, "bottom": 24, "left": 0}
                  }
                },
                {
                  "type": "text",
                  "attrs": {
                    "content": "<p>We'd love to hear about your project and discuss how we can help bring your vision to life.</p>",
                    "font_size": 18,
                    "text_color": "#94a3b8",
                    "line_height": 1.7,
                    "margin": {"top": 0, "right": 0, "bottom": 32, "left": 0}
                  }
                },
                {
                  "type": "button",
                  "attrs": {
                    "text": "Start a Conversation",
                    "link_url": "/contact",
                    "background_color": "#a78bfa",
                    "background_color__hover": "#8b5cf6",
                    "text_color": "#ffffff",
                    "font_size": 16,
                    "font_weight": "600",
                    "padding": {"top": 16, "right": 32, "bottom": 16, "left": 32},
                    "border_radius": {"top_left": 6, "top_right": 6, "bottom_right": 6, "bottom_left": 6}
                  }
                }
              ]
            },
            {
              "type": "column",
              "children": [
                {
                  "type": "image",
                  "attrs": {
                    "src": "{{PEXELS:team,meeting,creative}}",
                    "alt": "Our team collaborating",
                    "border_radius": {"top_left": 12, "top_right": 12, "bottom_right": 12, "bottom_left": 12}
                  }
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

---

## Important Notes for AI

1. **Always use exact attribute names** as documented above
2. **Include responsive suffixes** (__tablet, __phone) for key styling attributes
3. **Use {{PEXELS:keyword1,keyword2}}** syntax for images - they will be replaced with real stock photos
4. **Maintain consistent spacing** using the spacing scale
5. **Vary section backgrounds** to create visual rhythm
6. **Include hover states** where documented (color__hover, background_color__hover)
7. **Generate real, contextual content** - never use "Lorem ipsum"
8. **Follow visual hierarchy** - hero headings largest, diminishing scale

PROMPT;
    }

    /**
     * Section 9: Context-specific additions
     */
    private static function getContextSection(array $context): string
    {
        $section = "\n## Context for This Generation\n\n";

        // Color palette
        if (!empty($context['colors'])) {
            $section .= "### Color Palette\n";
            $section .= "Use these colors consistently throughout the layout:\n";
            foreach ($context['colors'] as $name => $value) {
                $section .= "- {$name}: {$value}\n";
            }
            $section .= "\n";
        }

        // Color hints from industry detection
        if (!empty($context['color_hints'])) {
            $section .= "### Recommended Industry Colors\n";
            $section .= $context['color_hints']['industry_colors'] ?? '';
            $section .= "\n\n";
        }

        // Industry
        if (!empty($context['industry'])) {
            $section .= "### Industry: " . ucfirst($context['industry']) . "\n";
            $section .= "Tailor imagery, language, and style to this industry.\n\n";
        }

        // Style preference
        if (!empty($context['style'])) {
            $section .= "### Style: " . ucfirst($context['style']) . "\n";
            $styleDescriptions = [
                'modern' => 'Clean lines, generous whitespace, sans-serif fonts, subtle shadows',
                'minimal' => 'Maximum whitespace, limited colors, typography-focused',
                'bold' => 'Strong colors, large typography, dramatic contrasts',
                'elegant' => 'Serif accents, refined spacing, sophisticated palette',
                'playful' => 'Vibrant colors, rounded shapes, friendly tone',
                'corporate' => 'Professional, structured, trustworthy, conservative colors'
            ];
            $section .= ($styleDescriptions[$context['style']] ?? 'Apply this style throughout.') . "\n\n";
        }

        // Page type
        if (!empty($context['page_type'])) {
            $section .= "### Page Type: " . ucfirst($context['page_type']) . "\n";
            $pageGuidelines = [
                'landing' => 'Focus on conversion: strong hero, clear value proposition, prominent CTAs, social proof',
                'saas_landing' => 'SaaS landing page: Hero with product screenshot, features grid, pricing table, testimonials, FAQ, final CTA',
                'homepage' => 'Balance information: introduce brand, highlight key services, multiple pathways',
                'about' => 'Tell the story: team, mission, values, history, build trust',
                'contact' => 'Enable connection: contact form, address, map, multiple contact methods',
                'services' => 'Showcase offerings: clear descriptions, pricing hints, benefits, CTAs',
                'service_showcase' => 'Services page: Hero, services grid, process steps, testimonials, team, contact form',
                'portfolio' => 'Display work: visual gallery, case studies, client logos',
                'pricing' => 'Drive conversion: clear plans, feature comparison, prominent CTAs',
                'product_launch' => 'Product launch: Hero with product, key features, specifications, reviews, pricing, urgency CTA',
                'agency' => 'Agency page: Bold hero, case studies, services grid, team showcase, client logos, contact form',
                'brand_story' => 'About/Brand story: Full-width hero, timeline, team members, values, testimonials, CTA'
            ];
            $section .= ($pageGuidelines[$context['page_type']] ?? 'Design appropriately for this page type.') . "\n\n";
        }

        return $section;
    }

    /**
     * Get system prompt for content generation (Step 4 in pipeline)
     * This is a condensed version focused on content fields for each module type
     *
     * @param array $context Business context (industry, prompt, etc.)
     * @return string System prompt for content generation
     */
    public static function getContentGenerationPrompt(array $context = []): string
    {
        $industry = $context['industry'] ?? 'business';
        $businessDescription = $context['prompt'] ?? '';

        return <<<PROMPT
# JTB Content Generator

You are a professional copywriter generating content for website modules. Your content must be SPECIFIC to the business, using their terminology and industry language.

## BUSINESS CONTEXT
Industry: {$industry}
Description: {$businessDescription}

## OUTPUT FORMAT
Return JSON only:
```json
{
  "modules": {
    "module_id": {
      "field_name": "value",
      ...
    }
  }
}
```

## MODULE CONTENT FIELDS

### heading
```json
{"text": "Benefit-focused headline (not generic)"}
```
- NEVER use generic text like "Welcome" or "About Us"
- Focus on VALUE PROPOSITION or BENEFIT

### text
```json
{"content": "<p>Well-written paragraph with proper HTML. Can include <strong>bold</strong> and <em>italic</em>.</p>"}
```
- ALWAYS wrap in <p> tags
- Write 2-3 sentences that are SPECIFIC to the business
- Mention business-specific terms, benefits, or features

### button
```json
{"text": "Action-oriented label", "link_url": "#contact"}
```
- Use action verbs: "Get Started", "Learn More", "Book Now", "Contact Us"
- Match the section context (hero CTA vs footer link)

### blurb (icon card)
```json
{
  "title": "Short descriptive title (3-5 words)",
  "content": "<p>One or two sentences explaining this feature or benefit.</p>",
  "font_icon": "star|shield|zap|heart|check|users|clock|award|target|trending-up|layers|code|settings|briefcase|globe|mail"
}
```
- Icon should MATCH the content (security → shield, speed → zap, support → users)
- Content should explain HOW this benefits the customer

### number_counter
```json
{
  "number": "500",
  "title": "What this number represents",
  "suffix": "+|%|K|M"
}
```
- Use realistic, impressive numbers for the industry
- Examples: clients served, success rate %, years experience, projects completed

### testimonial
```json
{
  "content": "<p>Specific, believable quote about their experience with this business.</p>",
  "author": "Realistic Full Name",
  "job_title": "Relevant Job Title",
  "company": "Industry-Appropriate Company Name"
}
```
- Quote should mention SPECIFIC benefits or results
- Avoid generic praise like "Great service!"
- Include measurable outcomes when possible

### pricing_table
```json
{
  "title": "Plan Name (Basic/Pro/Enterprise)",
  "price": "99",
  "currency": "$",
  "period": "/month",
  "features": ["Feature 1 with detail", "Feature 2 with detail", "Feature 3 with detail"],
  "button_text": "Get Started"
}
```
- Features should be SPECIFIC, not "Feature 1"
- Price should be realistic for the industry

### team_member
```json
{
  "name": "Full Name",
  "position": "Job Title"
}
```

### accordion (FAQ)
```json
{
  "children": [
    {"title": "Specific question customers actually ask?", "content": "<p>Detailed, helpful answer.</p>"},
    {"title": "Another relevant question?", "content": "<p>Clear explanation.</p>"}
  ]
}
```
- Questions should be REAL questions customers ask
- Answers should be helpful and complete

### contact_form
```json
{
  "form_title": "Get In Touch",
  "submit_text": "Send Message",
  "success_message": "Thank you! We'll respond within 24 hours."
}
```

### cta (call to action)
```json
{
  "title": "Compelling CTA headline",
  "content": "<p>Why they should act NOW.</p>",
  "button_text": "Take Action",
  "button_url": "#"
}
```

## CRITICAL RULES
1. NEVER use placeholder text like "Lorem ipsum", "Feature 1", "Description here"
2. ALL content must be SPECIFIC to the business described
3. Use industry-appropriate terminology
4. Headlines should state BENEFITS, not features
5. Keep it professional but not boring
6. Each module ID maps to specific content - match them exactly

Generate content for the modules provided. Output ONLY valid JSON.
PROMPT;
    }

    /**
     * Get prompt length for debugging
     */
    public static function getPromptLength(array $context = []): int
    {
        return strlen(self::getSystemPrompt($context));
    }

    /**
     * Get prompt sections breakdown for debugging
     */
    public static function getPromptBreakdown(): array
    {
        return [
            'introduction' => strlen(self::getIntroduction()),
            'json_structure' => strlen(self::getJsonStructure()),
            'value_formats' => strlen(self::getValueFormats()),
            'pattern_packages' => strlen(self::getPatternPackages()),
            'modules_docs' => strlen(self::getAllModulesDocumentation()),
            'feather_icons' => strlen(self::getFeatherIcons()),
            'design_principles' => strlen(self::getDesignPrinciples()),
            'example_layouts' => strlen(self::getExampleLayouts()),
        ];
    }
}
