<?php
/**
 * JTB AI Prompts
 * Prompt templates for AI content generation
 * Contains system prompts and user prompt builders for all generation types
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Prompts
{
    // ========================================
    // System Prompts
    // ========================================

    /**
     * Get main system prompt for JTB AI
     * @return string System prompt
     */
    public static function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert web designer and content strategist. You create professional, modern website content using the Jessie Theme Builder (JTB) page builder system.

Your expertise includes:
- Creating compelling headlines and copy that converts
- Designing layouts that guide user attention
- Understanding UX best practices
- Writing for different industries and audiences
- Optimizing content for readability and engagement

Key principles:
1. Write concise, benefit-focused content
2. Use action verbs and active voice
3. Create clear visual hierarchy
4. Match the brand voice and industry
5. Include compelling calls-to-action
6. Keep paragraphs short (2-3 sentences max)
7. Use power words that create emotion
8. Focus on customer benefits, not features

Output format:
- Provide clean, valid JSON when requested
- Use proper escaping for special characters in strings
- Do not include comments in JSON output
- Follow the exact schema structure provided
PROMPT;
    }

    /**
     * Get system prompt for layout generation
     * @param array $moduleSchema Available modules schema
     * @return string System prompt for layout generation
     */
    public static function getLayoutSystemPrompt(array $moduleSchema = []): string
    {
        $moduleList = '';
        if (!empty($moduleSchema)) {
            $moduleList = "\n\nAvailable modules:\n";
            foreach ($moduleSchema as $slug => $module) {
                $desc = $module['description'] ?? '';
                $moduleList .= "- {$slug}: {$desc}\n";
            }
        }

        return <<<PROMPT
You are a web layout architect specializing in the Jessie Theme Builder (JTB) system.

Your task is to create complete page layouts in JTB JSON format.

Layout structure rules:
1. Pages contain SECTIONS (vertical stacks)
2. Sections contain ROWS (horizontal containers)
3. Rows contain COLUMNS (with width ratios like 1_2, 1_3, 2_3)
4. Columns contain MODULES (actual content)

Available column widths:
- 1_1: Full width (100%)
- 1_2: Half (50%)
- 1_3: One third (33%)
- 2_3: Two thirds (66%)
- 1_4: Quarter (25%)
- 3_4: Three quarters (75%)

Layout best practices:
1. Hero sections should be impactful with clear CTA
2. Use visual hierarchy - larger/bolder for important elements
3. Alternate section backgrounds for visual rhythm
4. Group related content in the same section
5. Use whitespace effectively with section padding
6. Limit to 5-8 sections for most pages
7. End with a clear call-to-action section
{$moduleList}

Output valid JSON only, no explanations.
PROMPT;
    }

    /**
     * Get system prompt for content generation
     * @return string System prompt for content
     */
    public static function getContentSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert copywriter creating website content. Your writing is:
- Concise and scannable
- Benefit-focused
- Action-oriented
- Professional yet approachable
- SEO-friendly without keyword stuffing

Content guidelines:
1. Headlines: 5-10 words, power words, benefit-focused
2. Subheadlines: Expand on headline, 10-15 words
3. Body text: Short paragraphs, bullet points when appropriate
4. CTAs: Action verbs, urgency, clear value
5. Testimonials: Specific, credible, emotional

Avoid:
- Generic phrases ("Welcome to our website")
- Passive voice
- Jargon without explanation
- Long, complex sentences
- Excessive punctuation (!!! or ???)

Return content as clean JSON matching the requested field structure.
PROMPT;
    }

    /**
     * Get system prompt for image generation
     * @return string System prompt for images
     */
    public static function getImageSystemPrompt(): string
    {
        return <<<'PROMPT'
You create detailed prompts for AI image generation. Your prompts produce:
- Professional, high-quality images
- Consistent style across a website
- Appropriate mood and atmosphere
- Clean, modern aesthetics

Image prompt structure:
1. Subject: Main focus of the image
2. Style: Photography style, art direction
3. Composition: Framing, perspective
4. Lighting: Type and quality of light
5. Colors: Color palette alignment
6. Mood: Emotional tone
7. Details: Specific elements to include

Format prompts as descriptive sentences, not keywords.
PROMPT;
    }

    // ========================================
    // Layout Prompt Builders
    // ========================================

    /**
     * Build prompt for complete page layout
     * @param string $userPrompt User's request
     * @param array $context Page/site context
     * @return string Complete prompt
     */
    public static function buildLayoutPrompt(string $userPrompt, array $context): string
    {
        $contextStr = '';
        if (!empty($context['site_name'])) {
            $contextStr .= "Site: {$context['site_name']}\n";
        }
        if (!empty($context['industry'])) {
            $contextStr .= "Industry: {$context['industry']}\n";
        }
        if (!empty($context['page_title'])) {
            $contextStr .= "Page: {$context['page_title']}\n";
        }
        if (!empty($context['brand_voice'])) {
            $contextStr .= "Voice: {$context['brand_voice']}\n";
        }

        $colors = '';
        if (!empty($context['colors'])) {
            $colors = "Brand colors: Primary {$context['colors']['primary']}, Secondary {$context['colors']['secondary']}, Accent {$context['colors']['accent']}\n";
        }

        return <<<PROMPT
Create a complete page layout based on this request:

"{$userPrompt}"

Context:
{$contextStr}{$colors}

Generate a complete JTB layout with:
1. Appropriate sections for the page type
2. Logical content flow
3. Engaging hero section
4. Clear call-to-action
5. All required module attributes with realistic content

Return ONLY valid JSON in this exact structure:
{
  "sections": [
    {
      "id": "section_unique_id",
      "type": "section",
      "attrs": {},
      "children": [
        {
          "id": "row_unique_id",
          "type": "row",
          "attrs": {"columns": "1_2_1_2"},
          "children": [
            {
              "id": "column_unique_id",
              "type": "column",
              "attrs": {"width": "1_2"},
              "children": [
                {
                  "id": "module_unique_id",
                  "type": "heading",
                  "attrs": {"text": "Your Headline", "level": "h2"}
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}
PROMPT;
    }

    /**
     * Build prompt for section generation
     * @param string $sectionType Type of section (hero, features, etc.)
     * @param array $context Context information
     * @return string Section generation prompt
     */
    public static function buildSectionPrompt(string $sectionType, array $context = []): string
    {
        $sectionPrompts = self::getSectionTypePrompts();
        $basePrompt = $sectionPrompts[$sectionType] ?? $sectionPrompts['generic'];

        $contextStr = self::formatContextForPrompt($context);

        return <<<PROMPT
Create a {$sectionType} section for a website.

{$basePrompt}

{$contextStr}

Return ONLY valid JSON with this structure:
{
  "id": "section_[random]",
  "type": "section",
  "attrs": {
    "background_color": "#FFFFFF"
  },
  "children": [rows with columns and modules]
}

Include all module attributes with realistic content.
PROMPT;
    }

    /**
     * Build prompt for module content
     * @param string $moduleType Module type
     * @param array $context Context information
     * @return string Module content prompt
     */
    public static function buildModuleContentPrompt(string $moduleType, array $context = []): string
    {
        $modulePrompt = self::getModulePrompt($moduleType, $context);
        $contextStr = self::formatContextForPrompt($context);

        // Build dynamic field reference from Registry
        $fieldRef = '';
        try {
            $instance = JTB_Registry::get($moduleType);
            if ($instance) {
                $fields = $instance->getFields();
                $fieldParts = [];
                foreach ($fields as $fieldName => $fieldDef) {
                    $type = $fieldDef['type'] ?? 'text';
                    $fieldParts[] = "  \"{$fieldName}\": \"<{$type}>\"";
                }
                $fieldRef = "{\n" . implode(",\n", $fieldParts) . "\n}";
            }
        } catch (\Exception $e) {
            $fieldRef = "{ ...module-specific fields }";
        }

        return <<<PROMPT
Generate content for a "{$moduleType}" module.

{$modulePrompt}

{$contextStr}

Return ONLY valid JSON with the module attributes:
{$fieldRef}
PROMPT;
    }

    // ========================================
    // Section Type Prompts
    // ========================================

    /**
     * Get prompts for each section type
     * @return array Section type prompts
     */
    private static function getSectionTypePrompts(): array
    {
        return [
            'hero' => <<<'PROMPT'
Create a compelling hero section that:
- Has a powerful headline that grabs attention
- Includes a supporting subheadline with value proposition
- Features a prominent call-to-action button
- May include a hero image or background
- Creates immediate interest and encourages scrolling

Typical layout: Full-width with centered or left-aligned content
PROMPT,

            'features' => <<<'PROMPT'
Create a features section that:
- Showcases 3-4 key features or benefits
- Uses icons or images for visual appeal
- Has concise, benefit-focused descriptions
- Highlights what makes the product/service unique
- Uses consistent formatting for all items

Typical layout: 3 or 4 columns with blurb modules
PROMPT,

            'testimonials' => <<<'PROMPT'
Create a testimonials section that:
- Features 2-3 customer testimonials
- Includes customer names and positions
- Uses authentic-sounding quotes
- Builds trust and social proof
- May include customer photos

Typical layout: 2-3 columns or a slider
PROMPT,

            'cta' => <<<'PROMPT'
Create a call-to-action section that:
- Has a compelling headline
- Creates urgency or excitement
- Features a prominent button
- May include secondary text or benefits
- Uses contrasting background color

Typical layout: Centered content, full-width background
PROMPT,

            'pricing' => <<<'PROMPT'
Create a pricing section that:
- Shows 3 pricing tiers clearly
- Highlights a recommended plan
- Lists features for each tier
- Has clear CTA buttons for each plan
- Uses visual hierarchy to guide choice

Typical layout: 3 columns with pricing tables
PROMPT,

            'faq' => <<<'PROMPT'
Create a FAQ section that:
- Addresses common customer questions
- Uses accordion format for easy reading
- Has clear, helpful answers
- Anticipates objections
- Builds confidence in the product/service

Typical layout: Single column with accordion
PROMPT,

            'contact' => <<<'PROMPT'
Create a contact section that:
- Includes a contact form
- Shows contact information (email, phone, address)
- May include a map
- Has clear instructions
- Makes it easy to get in touch

Typical layout: 2 columns (form + info) or form with map
PROMPT,

            'about' => <<<'PROMPT'
Create an about section that:
- Tells the company story briefly
- Highlights key values or mission
- May include company stats
- Builds trust and connection
- Uses a friendly, authentic tone

Typical layout: 2 columns (image + text) or full-width with image
PROMPT,

            'team' => <<<'PROMPT'
Create a team section that:
- Introduces 3-4 team members
- Includes photos and titles
- Has brief, personable bios
- May include social links
- Shows the human side of the business

Typical layout: 3-4 columns with team member modules
PROMPT,

            'portfolio' => <<<'PROMPT'
Create a portfolio section that:
- Showcases 4-6 work samples
- Uses high-quality images
- Has project titles and brief descriptions
- May include category filters
- Demonstrates expertise and quality

Typical layout: Grid of 3-4 columns with image galleries
PROMPT,

            'blog' => <<<'PROMPT'
Create a blog section that:
- Shows 3-4 recent posts
- Includes post images
- Has titles and excerpts
- Includes dates and categories
- Encourages reading more

Typical layout: 3 columns with blog post cards
PROMPT,

            'newsletter' => <<<'PROMPT'
Create a newsletter signup section that:
- Has a compelling headline
- Explains the value of subscribing
- Features an email input and button
- Builds trust (privacy assurance)
- Creates incentive to subscribe

Typical layout: Centered content, contrasting background
PROMPT,

            'stats' => <<<'PROMPT'
Create a statistics section that:
- Shows 3-4 impressive numbers
- Uses animated counters
- Has labels for each stat
- Demonstrates credibility
- Uses visual emphasis

Typical layout: 3-4 columns with number counters
PROMPT,

            'partners' => <<<'PROMPT'
Create a partners/logos section that:
- Displays 4-6 partner/client logos
- Uses consistent image sizing
- May have a section title
- Builds trust through association
- Uses subtle styling

Typical layout: Single row with logo images
PROMPT,

            'services' => <<<'PROMPT'
Create a services section that:
- Showcases 3-6 services
- Uses icons and clear titles
- Has benefit-focused descriptions
- May include pricing hints
- Has CTAs for each service

Typical layout: 3 columns with blurb modules
PROMPT,

            'generic' => <<<'PROMPT'
Create a content section that:
- Serves the page's overall purpose
- Uses appropriate modules
- Has clear visual hierarchy
- Includes compelling content
- Guides users toward action

Typical layout: Based on content needs
PROMPT
        ];
    }

    // ========================================
    // Module-Specific Prompts
    // ========================================

    /**
     * Get prompt for specific module type
     * @param string $moduleType Module type
     * @param array $context Context data
     * @return string Module prompt
     */
    private static function getModulePrompt(string $moduleType, array $context): string
    {
        $industry = $context['industry'] ?? 'general business';
        $tone = $context['tone'] ?? 'professional';
        $siteName = $context['site_name'] ?? 'the company';

        $prompts = [
            // Content modules
            'heading' => "Create a compelling headline for {$industry}. Make it benefit-focused, concise (5-10 words), and attention-grabbing.",

            'text' => "Write engaging body text for {$industry}. Keep paragraphs short (2-3 sentences). Use benefit-focused language. Tone: {$tone}.",

            'blurb' => "Create a feature/service blurb for {$industry}. Include: catchy title (3-5 words), descriptive content (2-3 sentences), and suggest an icon name.",

            'button' => "Create a call-to-action button. Use action verbs, create urgency, and be specific about the action (e.g., 'Get Your Free Quote' not 'Submit').",

            'cta' => "Create a call-to-action section for {$industry}. Include: attention-grabbing headline, supporting text (1-2 sentences), and button text.",

            'testimonial' => "Create a realistic customer testimonial for {$industry}. Include: authentic quote (2-3 sentences), customer name, position/company. Make it specific and credible.",

            'team_member' => "Create a team member profile for {$industry}. Include: realistic name, professional title, brief bio (2-3 sentences), and suggest social links.",

            'pricing_table' => "Create a pricing plan for {$industry}. Include: plan name, price, billing period, 4-6 features, and CTA text. Make the value clear.",

            'accordion' => "Create FAQ content for {$industry}. Include 3-5 common questions with helpful, comprehensive answers.",

            'accordion_item' => "Create a single FAQ item for {$industry}. Include: clear question and comprehensive answer (2-4 sentences).",

            'tabs' => "Create tabbed content for {$industry}. Include 3-4 tabs with titles and content that logically groups information.",

            'tabs_item' => "Create a single tab content panel for {$industry}. Include: tab title and relevant content.",

            'toggle' => "Create a collapsible content item for {$industry}. Include: toggle title and hidden content.",

            'countdown' => "Create countdown content for {$industry}. Include: event date, countdown title, and supporting text about what's coming.",

            'number_counter' => "Create an impressive statistic for {$industry}. Include: the number, title/label, and optional suffix (like '+' or '%').",

            'circle_counter' => "Create a circular progress statistic for {$industry}. Include: percentage, title, and description.",

            'bar_counter' => "Create a skill/progress bar for {$industry}. Include: skill name, percentage level, and optional description.",

            'divider' => "Set divider style. Suggest whether to show a line and appropriate styling.",

            'icon' => "Choose an appropriate icon for {$industry} from the Feather icons set.",

            'image' => "Describe an appropriate image for {$industry}. Include: image concept, alt text, and optional caption.",

            'code' => "Create a code example if relevant to {$industry}, or suggest alternative content.",

            'social_follow' => "List social media platforms appropriate for {$industry} with profile URLs.",

            // Media modules
            'gallery' => "Describe a gallery concept for {$industry}. Include 4-8 image descriptions with alt texts.",

            'slider' => "Create slider content for {$industry}. Include 3-5 slides with titles, content, and image descriptions.",

            'slider_item' => "Create a single slide for {$industry}. Include: headline, supporting text, and image description.",

            'video' => "Describe an appropriate video for {$industry}. Include: video concept, title, and description.",

            'audio' => "Describe audio content for {$industry}. Include: audio type, title, and description.",

            'map' => "Describe map content for {$industry}. Include: location purpose and any markers needed.",

            // Form modules
            'contact_form' => "Create a contact form for {$industry}. Include: form title, description, submit button text, and success message.",

            'login' => "Create login form content. Include: form title, placeholder texts, forgot password text, and register link text.",

            'signup' => "Create registration form content. Include: form title, description, terms text, and submit button text.",

            'search' => "Create search form content. Include: placeholder text and any filtering options relevant to {$industry}.",

            // Blog modules
            'blog' => "Configure blog display settings for {$industry}. Include: posts per page, layout style, and category filters.",

            'portfolio' => "Configure portfolio display for {$industry}. Include: layout style, number of items, and category filtering.",

            'post_slider' => "Configure post slider for {$industry}. Include: number of posts, autoplay settings, and display options.",

            // Fullwidth modules
            'fullwidth_header' => "Create a full-width hero for {$industry}. Include: powerful headline, subheadline, CTA button, and background image description.",

            'fullwidth_image' => "Describe a full-width image for {$industry}. Include: image concept, alt text, and parallax settings.",

            'fullwidth_slider' => "Create a full-width slider for {$industry}. Include 3-5 slides with headlines, content, and backgrounds.",

            'fullwidth_map' => "Describe a full-width map for {$industry}. Include: location and any styling preferences.",

            'fullwidth_menu' => "Configure a full-width menu for {$industry}. Include: menu style and alignment.",

            'fullwidth_code' => "Create full-width code display content if relevant to {$industry}.",

            'fullwidth_portfolio' => "Configure full-width portfolio display for {$industry}.",

            'fullwidth_post_slider' => "Configure full-width post slider for {$industry}.",

            'fullwidth_post_title' => "Configure post title display settings.",

            // Theme modules
            'menu' => "Configure navigation menu for {$siteName}. Include: menu style, layout, and mobile behavior.",

            'site_logo' => "Configure logo display for {$siteName}. Include: logo image URL or text, size, and alignment.",

            'breadcrumbs' => "Configure breadcrumbs navigation. Include: separator style and home link text.",

            'post_title' => "Configure dynamic post title display. Include: typography and alignment.",

            'post_content' => "Configure dynamic post content display settings.",

            'post_meta' => "Configure post meta display. Include: which elements to show (date, author, categories).",

            'post_excerpt' => "Configure post excerpt display. Include: length and read more text.",

            'featured_image' => "Configure featured image display. Include: size, aspect ratio, and overlay options.",

            'author_box' => "Configure author box display. Include: which elements to show and styling.",

            'related_posts' => "Configure related posts display. Include: number of posts and layout style.",

            'archive_posts' => "Configure archive posts display. Include: layout and pagination.",

            'archive_title' => "Configure archive title display settings.",

            'search_form' => "Configure search form. Include: placeholder and button text.",

            'footer_info' => "Create footer information for {$siteName}. Include: company info, description, and contact details.",

            'footer_menu' => "Configure footer menu. Include: menu style and columns.",

            'copyright' => "Create copyright text for {$siteName}. Include: current year and company name.",

            'header_button' => "Create a header CTA button for {$siteName}. Include: button text and link.",

            'cart_icon' => "Configure shopping cart icon display settings.",

            'social_icons' => "Configure social media icons. Include: platforms and style."
        ];

        return $prompts[$moduleType] ?? "Create appropriate content for a {$moduleType} module for {$industry}. Use a {$tone} tone.";
    }

    // ========================================
    // Section-Specific Prompt Methods
    // ========================================

    /**
     * Get hero section prompt
     * @param array $context Context data
     * @return string Hero prompt
     */
    public static function getHeroSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('hero', $context);
    }

    /**
     * Get features section prompt
     * @param array $context Context data
     * @return string Features prompt
     */
    public static function getFeaturesSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('features', $context);
    }

    /**
     * Get testimonials section prompt
     * @param array $context Context data
     * @return string Testimonials prompt
     */
    public static function getTestimonialsSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('testimonials', $context);
    }

    /**
     * Get CTA section prompt
     * @param array $context Context data
     * @return string CTA prompt
     */
    public static function getCTASectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('cta', $context);
    }

    /**
     * Get pricing section prompt
     * @param array $context Context data
     * @return string Pricing prompt
     */
    public static function getPricingSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('pricing', $context);
    }

    /**
     * Get FAQ section prompt
     * @param array $context Context data
     * @return string FAQ prompt
     */
    public static function getFAQSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('faq', $context);
    }

    /**
     * Get contact section prompt
     * @param array $context Context data
     * @return string Contact prompt
     */
    public static function getContactSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('contact', $context);
    }

    /**
     * Get about section prompt
     * @param array $context Context data
     * @return string About prompt
     */
    public static function getAboutSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('about', $context);
    }

    /**
     * Get portfolio section prompt
     * @param array $context Context data
     * @return string Portfolio prompt
     */
    public static function getPortfolioSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('portfolio', $context);
    }

    /**
     * Get blog section prompt
     * @param array $context Context data
     * @return string Blog prompt
     */
    public static function getBlogSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('blog', $context);
    }

    /**
     * Get team section prompt
     * @param array $context Context data
     * @return string Team prompt
     */
    public static function getTeamSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('team', $context);
    }

    /**
     * Get services section prompt
     * @param array $context Context data
     * @return string Services prompt
     */
    public static function getServicesSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('services', $context);
    }

    /**
     * Get newsletter section prompt
     * @param array $context Context data
     * @return string Newsletter prompt
     */
    public static function getNewsletterSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('newsletter', $context);
    }

    /**
     * Get stats section prompt
     * @param array $context Context data
     * @return string Stats prompt
     */
    public static function getStatsSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('stats', $context);
    }

    /**
     * Get partners section prompt
     * @param array $context Context data
     * @return string Partners prompt
     */
    public static function getPartnersSectionPrompt(array $context): string
    {
        return self::buildSectionPrompt('partners', $context);
    }

    // ========================================
    // Module Content Prompt Methods
    // ========================================

    public static function getBlurbPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('blurb', $context);
    }

    public static function getCTAPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('cta', $context);
    }

    public static function getHeadingPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('heading', $context);
    }

    public static function getTextPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('text', $context);
    }

    public static function getTestimonialPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('testimonial', $context);
    }

    public static function getButtonPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('button', $context);
    }

    public static function getPricingPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('pricing_table', $context);
    }

    public static function getTeamMemberPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('team_member', $context);
    }

    public static function getAccordionPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('accordion', $context);
    }

    public static function getAccordionItemPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('accordion_item', $context);
    }

    public static function getTabsPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('tabs', $context);
    }

    public static function getTabsItemPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('tabs_item', $context);
    }

    public static function getTogglePrompt(array $context): string
    {
        return self::buildModuleContentPrompt('toggle', $context);
    }

    public static function getCountdownPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('countdown', $context);
    }

    public static function getNumberCounterPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('number_counter', $context);
    }

    public static function getCircleCounterPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('circle_counter', $context);
    }

    public static function getBarCounterPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('bar_counter', $context);
    }

    public static function getDividerPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('divider', $context);
    }

    public static function getIconPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('icon', $context);
    }

    public static function getImagePrompt(array $context): string
    {
        return self::buildModuleContentPrompt('image', $context);
    }

    public static function getGalleryPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('gallery', $context);
    }

    public static function getVideoPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('video', $context);
    }

    public static function getSliderPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('slider', $context);
    }

    public static function getSliderItemPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('slider_item', $context);
    }

    public static function getMapPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('map', $context);
    }

    public static function getAudioPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('audio', $context);
    }

    public static function getContactFormPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('contact_form', $context);
    }

    public static function getLoginPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('login', $context);
    }

    public static function getSignupPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('signup', $context);
    }

    public static function getSearchPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('search', $context);
    }

    public static function getBlogPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('blog', $context);
    }

    public static function getPortfolioPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('portfolio', $context);
    }

    public static function getPostSliderPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('post_slider', $context);
    }

    public static function getSocialFollowPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('social_follow', $context);
    }

    public static function getCodePrompt(array $context): string
    {
        return self::buildModuleContentPrompt('code', $context);
    }

    public static function getSidebarPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('sidebar', $context);
    }

    public static function getCommentsPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('comments', $context);
    }

    public static function getPostNavigationPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('post_navigation', $context);
    }

    public static function getShopPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('shop', $context);
    }

    // Fullwidth modules
    public static function getFullwidthHeaderPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('fullwidth_header', $context);
    }

    public static function getFullwidthImagePrompt(array $context): string
    {
        return self::buildModuleContentPrompt('fullwidth_image', $context);
    }

    public static function getFullwidthSliderPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('fullwidth_slider', $context);
    }

    public static function getFullwidthMapPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('fullwidth_map', $context);
    }

    public static function getFullwidthMenuPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('fullwidth_menu', $context);
    }

    public static function getFullwidthCodePrompt(array $context): string
    {
        return self::buildModuleContentPrompt('fullwidth_code', $context);
    }

    public static function getFullwidthPortfolioPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('fullwidth_portfolio', $context);
    }

    public static function getFullwidthPostSliderPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('fullwidth_post_slider', $context);
    }

    public static function getFullwidthPostTitlePrompt(array $context): string
    {
        return self::buildModuleContentPrompt('fullwidth_post_title', $context);
    }

    // Theme modules
    public static function getMenuPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('menu', $context);
    }

    public static function getSiteLogoPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('site_logo', $context);
    }

    public static function getBreadcrumbsPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('breadcrumbs', $context);
    }

    public static function getPostTitlePrompt(array $context): string
    {
        return self::buildModuleContentPrompt('post_title', $context);
    }

    public static function getPostContentPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('post_content', $context);
    }

    public static function getPostMetaPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('post_meta', $context);
    }

    public static function getPostExcerptPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('post_excerpt', $context);
    }

    public static function getFeaturedImagePrompt(array $context): string
    {
        return self::buildModuleContentPrompt('featured_image', $context);
    }

    public static function getAuthorBoxPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('author_box', $context);
    }

    public static function getRelatedPostsPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('related_posts', $context);
    }

    public static function getArchivePostsPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('archive_posts', $context);
    }

    public static function getArchiveTitlePrompt(array $context): string
    {
        return self::buildModuleContentPrompt('archive_title', $context);
    }

    public static function getSearchFormPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('search_form', $context);
    }

    public static function getFooterInfoPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('footer_info', $context);
    }

    public static function getFooterMenuPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('footer_menu', $context);
    }

    public static function getCopyrightPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('copyright', $context);
    }

    public static function getHeaderButtonPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('header_button', $context);
    }

    public static function getCartIconPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('cart_icon', $context);
    }

    public static function getSocialIconsPrompt(array $context): string
    {
        return self::buildModuleContentPrompt('social_icons', $context);
    }

    // ========================================
    // Utility Methods
    // ========================================

    /**
     * Format context data for prompt inclusion
     * @param array $context Context data
     * @return string Formatted context
     */
    private static function formatContextForPrompt(array $context): string
    {
        $lines = [];

        if (!empty($context['site_name'])) {
            $lines[] = "Site: {$context['site_name']}";
        }
        if (!empty($context['industry'])) {
            $lines[] = "Industry: {$context['industry']}";
        }
        if (!empty($context['tone'])) {
            $lines[] = "Tone: {$context['tone']}";
        }
        if (!empty($context['page_type'])) {
            $lines[] = "Page Type: {$context['page_type']}";
        }
        if (!empty($context['target_audience'])) {
            $lines[] = "Audience: {$context['target_audience']}";
        }
        if (!empty($context['colors'])) {
            $lines[] = "Colors: Primary {$context['colors']['primary']}, Accent {$context['colors']['accent']}";
        }

        if (empty($lines)) {
            return '';
        }

        return "Context:\n" . implode("\n", $lines);
    }

    /**
     * Apply tone adjustment instructions
     * @param string $tone Target tone
     * @return string Tone instructions
     */
    public static function getToneInstructions(string $tone): string
    {
        $tones = [
            'professional' => 'Use formal, confident language. Avoid slang. Focus on expertise and credibility.',
            'friendly' => 'Be warm and approachable. Use conversational language. Include light humor where appropriate.',
            'casual' => 'Keep it relaxed and informal. Use contractions freely. Be conversational.',
            'formal' => 'Use proper, formal language. Avoid contractions. Maintain a serious, authoritative tone.',
            'playful' => 'Be fun and energetic. Use exclamations and wordplay. Keep things light and entertaining.',
            'luxury' => 'Use sophisticated, elegant language. Emphasize quality and exclusivity. Be refined.',
            'technical' => 'Use precise, accurate terminology. Be detailed and specific. Focus on specifications.',
            'inspiring' => 'Use motivational language. Appeal to emotions. Focus on possibilities and transformation.',
            'urgent' => 'Create a sense of urgency. Use action words. Emphasize time-sensitivity.',
            'trustworthy' => 'Build credibility. Use reassuring language. Include proof points and guarantees.'
        ];

        return $tones[$tone] ?? $tones['professional'];
    }

    /**
     * Get length adjustment instructions
     * @param string $length Target length (short, medium, long)
     * @return string Length instructions
     */
    public static function getLengthInstructions(string $length): string
    {
        $lengths = [
            'short' => 'Keep all content extremely concise. Headlines: 3-5 words. Paragraphs: 1 sentence. Bullet points only.',
            'medium' => 'Use moderate content length. Headlines: 5-8 words. Paragraphs: 2-3 sentences. Balance detail with brevity.',
            'long' => 'Provide detailed content. Headlines: 8-12 words. Paragraphs: 3-5 sentences. Include comprehensive information.'
        ];

        return $lengths[$length] ?? $lengths['medium'];
    }

    // ========================================
    // QUALITY FEEDBACK SYSTEM
    // ========================================

    /**
     * Build quality feedback prompt for retry attempts
     * @param array $quality Quality validation result from validateLayout()
     * @param int $attempt Current attempt number (2 or 3)
     * @return string Feedback block to append to system prompt
     */
    public static function buildQualityFeedbackPrompt(array $quality, int $attempt): string
    {
        if ($attempt < 2) {
            return '';
        }

        $violations = $quality['violations'] ?? [];
        $warnings = $quality['warnings'] ?? [];
        $meta = $quality['meta'] ?? [];

        $issues = self::formatIssuesForFeedback($violations, $warnings);
        $corrections = self::formatCorrectionsForFeedback($violations, $meta);
        $doNotRepeat = self::formatDoNotRepeatForFeedback($violations);

        $tone = $attempt === 2 ? 'corrective' : 'imperative';
        $header = $attempt === 2
            ? "QUALITY FEEDBACK (ATTEMPT 2)"
            : "QUALITY FEEDBACK (FINAL ATTEMPT 3)";

        $lastAttemptWarning = '';
        if ($attempt === 3) {
            $lastAttemptWarning = "\n\n⚠️ THIS IS YOUR LAST ATTEMPT. FOLLOW ALL RULES STRICTLY. ⚠️";
        }

        $feedback = <<<FEEDBACK

---
{$header}

Issues detected:
{$issues}

Mandatory corrections:
{$corrections}

Do NOT repeat:
{$doNotRepeat}
---{$lastAttemptWarning}
FEEDBACK;

        return $feedback;
    }

    /**
     * Format violations and warnings as issues list
     */
    private static function formatIssuesForFeedback(array $violations, array $warnings): string
    {
        $lines = [];

        foreach ($violations as $violation) {
            $lines[] = "- ❌ " . self::humanizeViolation($violation);
        }

        foreach ($warnings as $warning) {
            $lines[] = "- ⚠️ " . self::humanizeWarning($warning);
        }

        return empty($lines) ? "- None detected" : implode("\n", $lines);
    }

    /**
     * Format mandatory corrections based on violations
     */
    private static function formatCorrectionsForFeedback(array $violations, array $meta): string
    {
        $corrections = [];

        foreach ($violations as $violation) {
            if (str_contains($violation, 'MISSING_FINAL_CTA')) {
                $corrections[] = "- Final CTA section is MANDATORY and MUST be the last section on the page.";
            }
            if (str_contains($violation, 'FINAL_CTA_NOT_LAST')) {
                $corrections[] = "- Move final_cta to be the LAST section. No sections after final_cta.";
            }
            if (str_contains($violation, 'PRIMARY_COUNT_INVALID')) {
                $corrections[] = "- Only ONE section can have PRIMARY visual_context, and it MUST be final_cta.";
            }
            if (str_contains($violation, 'PRIMARY_MISUSE')) {
                $corrections[] = "- PRIMARY visual_context is ONLY allowed for final_cta. Use LIGHT or DARK for other sections.";
            }
            if (str_contains($violation, 'DARK_OVERFLOW')) {
                $corrections[] = "- Maximum 2 DARK sections allowed. Reduce DARK sections to trust_metrics and optionally hero.";
            }
            if (str_contains($violation, 'DARK_FORBIDDEN')) {
                $corrections[] = "- DARK visual_context is forbidden for content grids (features, testimonials, pricing, team). Use LIGHT.";
            }
            if (str_contains($violation, 'GRID_SEQUENCE')) {
                $corrections[] = "- Never place two grid patterns consecutively. Insert breathing_space or zigzag_narrative between grids.";
            }
            if (str_contains($violation, 'CTA_DUPLICATE')) {
                $corrections[] = "- Hero CTA and Final CTA must be DIFFERENT. Hero = low commitment, Final = high commitment.";
            }
        }

        if (($meta['breathing_space_count'] ?? 0) === 0 && ($meta['total_sections'] ?? 0) > 7) {
            $corrections[] = "- Long pages require at least one breathing_space section between dense content.";
        }

        return empty($corrections) ? "- Follow all Golden Rules" : implode("\n", array_unique($corrections));
    }

    /**
     * Format do-not-repeat rules based on violations
     */
    private static function formatDoNotRepeatForFeedback(array $violations): string
    {
        $doNotRepeat = [];

        foreach ($violations as $violation) {
            if (str_contains($violation, 'MISSING_FINAL_CTA')) {
                $doNotRepeat[] = "- Omitting final_cta section";
            }
            if (str_contains($violation, 'FINAL_CTA_NOT_LAST')) {
                $doNotRepeat[] = "- Placing sections after final_cta";
            }
            if (str_contains($violation, 'PRIMARY_COUNT_INVALID') || str_contains($violation, 'PRIMARY_MISUSE')) {
                $doNotRepeat[] = "- Using PRIMARY for sections other than final_cta";
            }
            if (str_contains($violation, 'DARK_OVERFLOW') || str_contains($violation, 'DARK_FORBIDDEN')) {
                $doNotRepeat[] = "- Using DARK for content grids or exceeding 2 DARK sections";
            }
            if (str_contains($violation, 'GRID_SEQUENCE')) {
                $doNotRepeat[] = "- Placing grid_density or grid_featured back-to-back";
            }
            if (str_contains($violation, 'CTA_DUPLICATE')) {
                $doNotRepeat[] = "- Using identical CTA text in hero and final_cta";
            }
        }

        return empty($doNotRepeat) ? "- All previous mistakes" : implode("\n", array_unique($doNotRepeat));
    }

    /**
     * Convert violation code to human-readable message
     */
    private static function humanizeViolation(string $violation): string
    {
        $parts = explode(':', $violation, 2);
        $code = trim($parts[0] ?? '');
        $detail = trim($parts[1] ?? '');

        $messages = [
            'MISSING_FINAL_CTA' => 'No final_cta section found',
            'FINAL_CTA_NOT_LAST' => 'final_cta is not the last section',
            'PRIMARY_COUNT_INVALID' => 'Wrong number of PRIMARY sections',
            'PRIMARY_MISUSE' => 'PRIMARY used in wrong section',
            'DARK_OVERFLOW' => 'Too many DARK sections',
            'DARK_FORBIDDEN' => 'DARK used in forbidden section type',
            'GRID_SEQUENCE' => 'Two grid sections placed consecutively',
            'CTA_DUPLICATE' => 'Hero and Final CTA are identical',
        ];

        $humanized = $messages[$code] ?? $code;
        return $detail ? "{$humanized}: {$detail}" : $humanized;
    }

    /**
     * Convert warning code to human-readable message
     */
    private static function humanizeWarning(string $warning): string
    {
        $parts = explode(':', $warning, 2);
        $code = trim($parts[0] ?? '');
        $detail = trim($parts[1] ?? '');

        $messages = [
            'SHORT_PAGE' => 'Page has too few sections',
            'LONG_PAGE' => 'Page has too many sections',
            'NO_BREATHING_SPACE' => 'No breathing_space sections in long page',
            'LIGHT_MONOTONY' => 'Too many consecutive LIGHT sections without contrast',
        ];

        $humanized = $messages[$code] ?? $code;
        return $detail ? "{$humanized}: {$detail}" : $humanized;
    }
}
