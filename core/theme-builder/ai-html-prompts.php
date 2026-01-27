<?php
declare(strict_types=1);
/**
 * AI Theme Builder 4.0 - HTML Generation Prompts
 * 
 * Simplified prompts for generating free-form HTML/CSS
 * that will be converted to TB JSON via the Converter.
 *
 * @package ThemeBuilder
 * @subpackage AI
 * @version 4.0
 */

/**
 * Get system prompt for HTML generation
 */
function tb_get_html_system_prompt(): string
{
    return <<<'PROMPT'
You are an expert web designer creating beautiful, modern HTML landing pages for Jessie Theme Builder (JTB).

OUTPUT FORMAT:
Return ONLY valid HTML. No markdown, no explanations, NO <style> tags.

CRITICAL RULES:
1. Every visible element MUST have data-jtb-module attribute
2. ALL STYLES MUST BE INLINE (style="...") - DO NOT use <style> blocks
3. Every element needs its own complete inline styles

Available module types:
- section: Wrapper for page sections
- row: Flex container for columns (MUST have display:flex)
- column: Column inside row (MUST have flex: value)
- heading: For h1-h6 elements
- text: For paragraphs and text content
- image: For img elements
- button: For buttons and CTA links
- blurb: For feature cards (icon + title + description)
- menu: For navigation menus
- icon: For standalone icons
- divider: For hr elements
- testimonial: For testimonial cards
- team_member: For team member cards
- pricing_table: For pricing cards
- social_follow: For social media links
- cta: For call-to-action sections
- contact_form: For contact forms
- accordion: For FAQ/collapsible content

STRUCTURE EXAMPLE (with FULL inline styles):
```html
<section data-jtb-module="section" style="padding: 80px 20px; background-color: #f8fafc;">
    <div data-jtb-module="row" style="display: flex; flex-wrap: wrap; max-width: 1200px; margin: 0 auto; gap: 30px; align-items: center;">
        <div data-jtb-module="column" style="flex: 1; min-width: 300px;">
            <h2 data-jtb-module="heading" style="font-size: 42px; font-weight: 700; color: #1e293b; margin-bottom: 20px; line-height: 1.2;">Title Here</h2>
            <p data-jtb-module="text" style="font-size: 18px; color: #64748b; line-height: 1.6; margin-bottom: 30px;">Description text here with proper styling.</p>
            <a data-jtb-module="button" href="#" style="display: inline-block; padding: 14px 32px; background-color: #6366f1; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">Learn More</a>
        </div>
        <div data-jtb-module="column" style="flex: 1; min-width: 300px;">
            <img data-jtb-module="image" src="PLACEHOLDER:feature-image" alt="Feature" style="width: 100%; height: auto; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        </div>
    </div>
</section>
```

BLURB/FEATURE CARD EXAMPLE (with FULL inline styles):
```html
<div data-jtb-module="blurb" style="text-align: center; padding: 30px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
    <i class="fas fa-rocket" style="font-size: 48px; color: #6366f1; margin-bottom: 20px;"></i>
    <h3 data-jtb-module="heading" style="font-size: 24px; font-weight: 600; color: #1e293b; margin-bottom: 15px;">Feature Title</h3>
    <p data-jtb-module="text" style="font-size: 16px; color: #64748b; line-height: 1.6;">Feature description text.</p>
</div>
```

NAVIGATION MENU EXAMPLE (with FULL inline styles):
```html
<nav data-jtb-module="menu" style="display: flex; align-items: center;">
    <ul style="display: flex; gap: 30px; list-style: none; margin: 0; padding: 0;">
        <li><a href="#" style="color: #1e293b; text-decoration: none; font-weight: 500; font-size: 16px;">Home</a></li>
        <li><a href="#" style="color: #64748b; text-decoration: none; font-weight: 500; font-size: 16px;">About</a></li>
        <li><a href="#" style="color: #64748b; text-decoration: none; font-weight: 500; font-size: 16px;">Services</a></li>
        <li><a href="#" style="color: #64748b; text-decoration: none; font-weight: 500; font-size: 16px;">Contact</a></li>
    </ul>
</nav>
```

TESTIMONIAL EXAMPLE (with FULL inline styles):
```html
<div data-jtb-module="testimonial" style="padding: 40px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center;">
    <p style="font-size: 20px; font-style: italic; color: #1e293b; line-height: 1.6; margin-bottom: 25px;">"Great service and support!"</p>
    <img src="PLACEHOLDER:person-avatar" alt="Avatar" style="width: 60px; height: 60px; border-radius: 50%; margin-bottom: 15px;">
    <div style="font-weight: 600; color: #1e293b; font-size: 16px;">John Doe</div>
    <div style="color: #64748b; font-size: 14px;">CEO, Company</div>
</div>
```

BUTTON EXAMPLES (with FULL inline styles):
```html
<!-- Primary Button -->
<a data-jtb-module="button" href="#" style="display: inline-block; padding: 14px 32px; background-color: #6366f1; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">Get Started</a>

<!-- Secondary/Outline Button -->
<a data-jtb-module="button" href="#" style="display: inline-block; padding: 14px 32px; background-color: transparent; color: #6366f1; text-decoration: none; border: 2px solid #6366f1; border-radius: 8px; font-weight: 600; font-size: 16px;">Learn More</a>
```

DESIGN PRINCIPLES:
1. Modern, clean aesthetics with generous whitespace
2. Clear visual hierarchy with proper heading levels (h1 → h2 → h3)
3. Responsive-ready structure (use flexbox with flex-wrap)
4. Professional color scheme: primary #6366f1, text #1e293b, muted #64748b
5. Readable typography: 16px base, 1.5+ line-height

IMAGE PLACEHOLDERS:
Use this format: src="PLACEHOLDER:description-of-needed-image"
Example: src="PLACEHOLDER:professional-team-meeting"

ICON PLACEHOLDERS:
Use Font Awesome: <i class="fas fa-icon-name" style="font-size: 24px; color: #6366f1;"></i>

CRITICAL REMINDERS:
- DO NOT use <style> blocks - ALL styles must be inline
- Every element MUST have its own complete inline style attribute
- Include font-size, color, margin, padding on text elements
- Include background-color, padding, border-radius on containers
- Row elements MUST have: display: flex; flex-wrap: wrap;
- Column elements MUST have: flex: 1; min-width: 300px;
- Button elements MUST have: display: inline-block; padding; background-color; color; border-radius;
PROMPT;
}

/**
 * Get user prompt template for page generation
 */
function tb_get_html_user_prompt(string $brief, string $businessName, string $industry, string $style, array $sections): string
{
    $sectionList = implode(', ', $sections);
    
    return <<<PROMPT
Create a complete landing page for:

BUSINESS: {$businessName}
INDUSTRY: {$industry}
STYLE: {$style}

BRIEF:
{$brief}

REQUIRED SECTIONS (in order):
{$sectionList}

Generate beautiful, production-ready HTML with embedded CSS.
Remember: Return ONLY the HTML code, no explanations.
PROMPT;
}

/**
 * Get user prompt for single section generation
 */
function tb_get_html_section_prompt(string $sectionType, string $brief, string $businessName, string $style): string
{
    $sectionDescriptions = [
        'hero' => 'A hero section with large heading, compelling subtext, and a prominent call-to-action button. Include a background gradient or image placeholder.',
        'features' => 'A features section with 3-4 feature cards arranged in a grid. Each card has an icon, title, and short description.',
        'about' => 'An about section with company story, split into image and text columns.',
        'services' => 'A services section showcasing 3-6 services with icons and descriptions.',
        'testimonials' => 'A testimonials section with 2-3 customer quotes, names, roles, and avatar placeholders.',
        'pricing' => 'A pricing section with 2-3 pricing tiers, each showing price, features list, and CTA button.',
        'team' => 'A team section with 3-4 team member cards showing photo placeholder, name, role, and social links.',
        'cta' => 'A call-to-action section with compelling heading, brief text, and prominent button.',
        'contact' => 'A contact section with contact form (name, email, message fields) or contact information.',
        'faq' => 'An FAQ section with 4-6 expandable questions and answers.',
        'gallery' => 'A gallery section with a grid of image placeholders.',
        'stats' => 'A statistics/counter section showing 3-4 impressive numbers with labels.',
        'partners' => 'A partners/clients section with logo placeholders in a row.',
        'footer' => 'A footer with navigation links, social icons, and copyright text.'
    ];

    $description = $sectionDescriptions[$sectionType] ?? "A {$sectionType} section with appropriate content.";

    return <<<PROMPT
Create a single {$sectionType} section for:

BUSINESS: {$businessName}
STYLE: {$style}

DESCRIPTION:
{$description}

CONTEXT:
{$brief}

CRITICAL REQUIREMENTS:
1. Generate ONLY this section as a <section data-jtb-module="section"> element
2. DO NOT use <style> blocks - ALL styles MUST be inline (style="...")
3. Every element MUST have data-jtb-module attribute AND complete inline styles
4. Use <div data-jtb-module="row" style="display: flex; flex-wrap: wrap; ..."> for layouts
5. Use <div data-jtb-module="column" style="flex: 1; ..."> for columns
6. All text elements need: font-size, color, line-height, margin
7. All containers need: padding, background-color, border-radius
8. All buttons need: display: inline-block; padding; background-color; color; border-radius;

Return ONLY the HTML code with inline styles. No <style> blocks, no CSS classes for styling.
PROMPT;
}

/**
 * Get user prompt for header generation
 */
function tb_get_html_header_prompt(string $businessName, string $style, array $navItems): string
{
    $navList = implode(', ', $navItems);

    return <<<PROMPT
Create a website header/navigation for:

BUSINESS: {$businessName}
STYLE: {$style}
NAVIGATION ITEMS: {$navList}

REQUIRED STRUCTURE (ALL styles MUST be inline - NO <style> blocks):
```html
<header data-jtb-module="section" style="background-color: #ffffff; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
    <div data-jtb-module="row" style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; flex-wrap: wrap; gap: 20px;">
        <div data-jtb-module="column" style="flex: 0 0 auto;">
            <a href="/" style="text-decoration: none;">
                <span data-jtb-module="heading" style="font-size: 24px; font-weight: 700; color: #1e293b;">{$businessName}</span>
            </a>
        </div>
        <div data-jtb-module="column" style="flex: 1; display: flex; justify-content: center;">
            <nav data-jtb-module="menu" style="display: flex; align-items: center;">
                <ul style="display: flex; gap: 30px; list-style: none; margin: 0; padding: 0;">
                    <li><a href="#" style="color: #1e293b; text-decoration: none; font-weight: 500; font-size: 16px;">Home</a></li>
                </ul>
            </nav>
        </div>
        <div data-jtb-module="column" style="flex: 0 0 auto;">
            <a data-jtb-module="button" href="#contact" style="display: inline-block; padding: 12px 24px; background-color: #6366f1; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px;">Get Started</a>
        </div>
    </div>
</header>
```

CRITICAL REQUIREMENTS:
1. DO NOT use <style> blocks - ALL styles MUST be inline
2. Every element MUST have complete inline styles
3. The header element MUST have data-jtb-module="section"
4. The row MUST have: display: flex; align-items: center; justify-content: space-between;
5. Columns MUST have flex values
6. Menu nav MUST have data-jtb-module="menu"
7. Button MUST have data-jtb-module="button" with full button styling
8. Logo/heading MUST have data-jtb-module="heading" or "image"

Include:
1. Logo (text with data-jtb-module="heading" or image with data-jtb-module="image")
2. Navigation menu with data-jtb-module="menu"
3. CTA button with data-jtb-module="button"

Generate ONLY the HTML with inline styles. No <style> blocks.
PROMPT;
}

/**
 * Get user prompt for footer generation
 */
function tb_get_html_footer_prompt(string $businessName, string $style, array $navItems): string
{
    $navList = implode(', ', $navItems);

    return <<<PROMPT
Create a website footer for:

BUSINESS: {$businessName}
STYLE: {$style}
LINKS: {$navList}

REQUIRED STRUCTURE (ALL styles MUST be inline - NO <style> blocks):
```html
<footer data-jtb-module="section" style="background-color: #1e293b; padding: 60px 20px 30px;">
    <div data-jtb-module="row" style="display: flex; flex-wrap: wrap; max-width: 1200px; margin: 0 auto; gap: 40px;">
        <div data-jtb-module="column" style="flex: 2; min-width: 250px;">
            <h3 data-jtb-module="heading" style="font-size: 24px; font-weight: 700; color: #ffffff; margin-bottom: 15px;">{$businessName}</h3>
            <p data-jtb-module="text" style="font-size: 16px; color: #94a3b8; line-height: 1.6; margin-bottom: 20px;">Company description goes here.</p>
            <div data-jtb-module="social_follow" style="display: flex; gap: 15px;">
                <a href="#" style="color: #94a3b8; font-size: 20px; text-decoration: none;"><i class="fab fa-facebook"></i></a>
                <a href="#" style="color: #94a3b8; font-size: 20px; text-decoration: none;"><i class="fab fa-twitter"></i></a>
                <a href="#" style="color: #94a3b8; font-size: 20px; text-decoration: none;"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
        <div data-jtb-module="column" style="flex: 1; min-width: 150px;">
            <h4 data-jtb-module="heading" style="font-size: 18px; font-weight: 600; color: #ffffff; margin-bottom: 20px;">Quick Links</h4>
            <nav data-jtb-module="menu">
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <li style="margin-bottom: 12px;"><a href="#" style="color: #94a3b8; text-decoration: none; font-size: 15px;">Home</a></li>
                    <li style="margin-bottom: 12px;"><a href="#" style="color: #94a3b8; text-decoration: none; font-size: 15px;">About</a></li>
                    <li style="margin-bottom: 12px;"><a href="#" style="color: #94a3b8; text-decoration: none; font-size: 15px;">Services</a></li>
                </ul>
            </nav>
        </div>
        <div data-jtb-module="column" style="flex: 1; min-width: 200px;">
            <h4 data-jtb-module="heading" style="font-size: 18px; font-weight: 600; color: #ffffff; margin-bottom: 20px;">Contact</h4>
            <p data-jtb-module="text" style="font-size: 15px; color: #94a3b8; margin-bottom: 10px;">Email: info@example.com</p>
            <p data-jtb-module="text" style="font-size: 15px; color: #94a3b8;">Phone: +1 234 567 890</p>
        </div>
    </div>
    <div data-jtb-module="row" style="max-width: 1200px; margin: 40px auto 0; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
        <div data-jtb-module="column" style="flex: 1;">
            <p data-jtb-module="text" style="text-align: center; color: #64748b; font-size: 14px; margin: 0;">© 2026 {$businessName}. All rights reserved.</p>
        </div>
    </div>
</footer>
```

CRITICAL REQUIREMENTS:
1. DO NOT use <style> blocks - ALL styles MUST be inline
2. Every element MUST have complete inline styles
3. The footer element MUST have data-jtb-module="section"
4. Rows MUST have: display: flex; flex-wrap: wrap;
5. Columns MUST have flex values and min-width
6. All text elements need font-size, color, line-height
7. All headings need font-size, font-weight, color, margin-bottom
8. Links need color and text-decoration: none

Include:
1. Company name with data-jtb-module="heading"
2. Description with data-jtb-module="text"
3. Social icons with data-jtb-module="social_follow"
4. Navigation links with data-jtb-module="menu"
5. Contact info with data-jtb-module="text"
6. Copyright notice

Generate ONLY the HTML with inline styles. No <style> blocks.
PROMPT;
}

/**
 * Validate and clean HTML response from AI
 */
function tb_clean_html_response(string $response): string
{
    // Remove markdown code blocks if present
    $response = preg_replace('/^```html?\s*/i', '', $response);
    $response = preg_replace('/\s*```$/', '', $response);
    
    // Remove any leading/trailing whitespace
    $response = trim($response);
    
    // Ensure it starts with valid HTML
    if (!preg_match('/^<(!DOCTYPE|html|style|header|section|footer|div|nav)/i', $response)) {
        // Try to find HTML content
        if (preg_match('/<(style|header|section|footer|div|nav)/i', $response, $match, PREG_OFFSET_CAPTURE)) {
            $response = substr($response, $match[0][1]);
        }
    }
    
    return $response;
}
