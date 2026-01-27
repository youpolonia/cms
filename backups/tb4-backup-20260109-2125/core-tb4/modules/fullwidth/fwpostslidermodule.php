<?php
namespace Core\TB4\Modules\Fullwidth;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Fullwidth Post Slider Module
 *
 * Full-width blog post slider with featured images and content overlay.
 * Displays posts with category badges, meta info, and navigation controls.
 */
class FwPostSliderModule extends Module
{
    protected array $content_fields = [];
    protected array $design_fields_custom = [];

    public function __construct()
    {
        $this->name = 'Fullwidth Post Slider';
        $this->slug = 'fw_post_slider';
        $this->icon = 'newspaper';
        $this->category = 'fullwidth';

        $this->elements = [
            'main' => '.tb4-fw-post-slider',
            'container' => '.tb4-fw-post-slider-container',
            'track' => '.tb4-fw-post-slider-track',
            'slide' => '.tb4-fw-post-slider-slide',
            'background' => '.tb4-fw-post-slider-bg',
            'overlay' => '.tb4-fw-post-slider-overlay',
            'content' => '.tb4-fw-post-slider-content',
            'category' => '.tb4-fw-post-slider-category',
            'title' => '.tb4-fw-post-slider-title',
            'excerpt' => '.tb4-fw-post-slider-excerpt',
            'meta' => '.tb4-fw-post-slider-meta',
            'button' => '.tb4-fw-post-slider-btn',
            'arrows' => '.tb4-fw-post-slider-arrows',
            'arrow' => '.tb4-fw-post-slider-arrow',
            'dots' => '.tb4-fw-post-slider-dots',
            'dot' => '.tb4-fw-post-slider-dot'
        ];

        // Content fields
        $this->content_fields = [
            'posts_count' => ['type' => 'select', 'label' => 'Number of Posts', 'options' => ['3' => '3 Posts', '4' => '4 Posts', '5' => '5 Posts', '6' => '6 Posts', '8' => '8 Posts'], 'default' => '5'],
            'category' => ['type' => 'text', 'label' => 'Category (slug or ID)', 'default' => ''],
            'order_by' => ['type' => 'select', 'label' => 'Order By', 'options' => ['date_desc' => 'Date (Newest)', 'date_asc' => 'Date (Oldest)', 'title_asc' => 'Title (A-Z)', 'title_desc' => 'Title (Z-A)', 'random' => 'Random'], 'default' => 'date_desc'],
            'show_title' => ['type' => 'select', 'label' => 'Show Title', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'show_excerpt' => ['type' => 'select', 'label' => 'Show Excerpt', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'excerpt_length' => ['type' => 'select', 'label' => 'Excerpt Length', 'options' => ['50' => 'Short (50 chars)', '100' => 'Medium (100 chars)', '150' => 'Long (150 chars)'], 'default' => '100'],
            'show_date' => ['type' => 'select', 'label' => 'Show Date', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'show_author' => ['type' => 'select', 'label' => 'Show Author', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'show_category' => ['type' => 'select', 'label' => 'Show Category', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'show_read_more' => ['type' => 'select', 'label' => 'Show Read More', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'read_more_text' => ['type' => 'text', 'label' => 'Read More Text', 'default' => 'Read Article'],
            'show_arrows' => ['type' => 'select', 'label' => 'Show Arrows', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'show_dots' => ['type' => 'select', 'label' => 'Show Dots', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'autoplay' => ['type' => 'select', 'label' => 'Autoplay', 'options' => ['no' => 'No', 'yes' => 'Yes'], 'default' => 'no'],
            'autoplay_speed' => ['type' => 'select', 'label' => 'Autoplay Speed', 'options' => ['3000' => '3 seconds', '5000' => '5 seconds', '7000' => '7 seconds'], 'default' => '5000'],
            'loop' => ['type' => 'select', 'label' => 'Loop', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes']
        ];

        // Design fields
        $this->design_fields_custom = [
            'slider_height' => ['type' => 'text', 'label' => 'Slider Height', 'default' => '600px'],
            'content_position' => ['type' => 'select', 'label' => 'Content Position', 'options' => ['center' => 'Center', 'left' => 'Left', 'right' => 'Right', 'bottom-left' => 'Bottom Left', 'bottom-center' => 'Bottom Center'], 'default' => 'center'],
            'content_width' => ['type' => 'text', 'label' => 'Content Max Width', 'default' => '700px'],
            'overlay_type' => ['type' => 'select', 'label' => 'Overlay Type', 'options' => ['gradient' => 'Gradient', 'solid' => 'Solid Color', 'none' => 'None'], 'default' => 'gradient'],
            'overlay_color' => ['type' => 'color', 'label' => 'Overlay Color', 'default' => 'rgba(0,0,0,0.5)'],
            'gradient_direction' => ['type' => 'select', 'label' => 'Gradient Direction', 'options' => ['to-top' => 'Bottom to Top', 'to-bottom' => 'Top to Bottom', 'to-right' => 'Left to Right', 'radial' => 'Radial (Center)'], 'default' => 'to-top'],
            'title_color' => ['type' => 'color', 'label' => 'Title Color', 'default' => '#ffffff'],
            'title_font_size' => ['type' => 'text', 'label' => 'Title Font Size', 'default' => '42px'],
            'title_font_weight' => ['type' => 'select', 'label' => 'Title Font Weight', 'options' => ['400' => 'Normal', '500' => 'Medium', '600' => 'Semi Bold', '700' => 'Bold', '800' => 'Extra Bold'], 'default' => '700'],
            'title_line_height' => ['type' => 'text', 'label' => 'Title Line Height', 'default' => '1.2'],
            'excerpt_color' => ['type' => 'color', 'label' => 'Excerpt Color', 'default' => 'rgba(255,255,255,0.9)'],
            'excerpt_font_size' => ['type' => 'text', 'label' => 'Excerpt Font Size', 'default' => '18px'],
            'meta_color' => ['type' => 'color', 'label' => 'Meta Color', 'default' => 'rgba(255,255,255,0.8)'],
            'meta_font_size' => ['type' => 'text', 'label' => 'Meta Font Size', 'default' => '14px'],
            'category_bg_color' => ['type' => 'color', 'label' => 'Category Badge Background', 'default' => '#2563eb'],
            'category_text_color' => ['type' => 'color', 'label' => 'Category Badge Text', 'default' => '#ffffff'],
            'button_bg_color' => ['type' => 'color', 'label' => 'Button Background', 'default' => '#ffffff'],
            'button_text_color' => ['type' => 'color', 'label' => 'Button Text Color', 'default' => '#111827'],
            'button_border_radius' => ['type' => 'text', 'label' => 'Button Border Radius', 'default' => '8px'],
            'button_padding' => ['type' => 'text', 'label' => 'Button Padding', 'default' => '14px 28px'],
            'arrow_color' => ['type' => 'color', 'label' => 'Arrow Color', 'default' => '#ffffff'],
            'arrow_bg_color' => ['type' => 'color', 'label' => 'Arrow Background', 'default' => 'rgba(255,255,255,0.2)'],
            'arrow_size' => ['type' => 'text', 'label' => 'Arrow Size', 'default' => '50px'],
            'dot_color' => ['type' => 'color', 'label' => 'Dot Color', 'default' => 'rgba(255,255,255,0.5)'],
            'dot_active_color' => ['type' => 'color', 'label' => 'Active Dot Color', 'default' => '#ffffff'],
            'content_padding' => ['type' => 'text', 'label' => 'Content Padding', 'default' => '60px']
        ];

        // Advanced fields
        $this->advanced_fields = array_merge($this->advanced_fields, [
            'css_id' => ['type' => 'text', 'label' => 'CSS ID', 'default' => ''],
            'css_class' => ['type' => 'text', 'label' => 'CSS Class', 'default' => ''],
            'custom_css' => ['type' => 'textarea', 'label' => 'Custom CSS', 'default' => '']
        ]);
    }

    public function get_content_fields(): array
    {
        return $this->content_fields;
    }

    public function get_design_fields(): array
    {
        return array_merge(parent::get_design_fields(), $this->design_fields_custom);
    }

    public function render(array $attrs): string
    {
        $postsCount = (int)($attrs['posts_count'] ?? 5);
        $showTitle = ($attrs['show_title'] ?? 'yes') === 'yes';
        $showExcerpt = ($attrs['show_excerpt'] ?? 'yes') === 'yes';
        $excerptLength = (int)($attrs['excerpt_length'] ?? 100);
        $showDate = ($attrs['show_date'] ?? 'yes') === 'yes';
        $showAuthor = ($attrs['show_author'] ?? 'yes') === 'yes';
        $showCategory = ($attrs['show_category'] ?? 'yes') === 'yes';
        $showReadMore = ($attrs['show_read_more'] ?? 'yes') === 'yes';
        $readMoreText = $attrs['read_more_text'] ?? 'Read Article';
        $showArrows = ($attrs['show_arrows'] ?? 'yes') === 'yes';
        $showDots = ($attrs['show_dots'] ?? 'yes') === 'yes';
        $autoplay = ($attrs['autoplay'] ?? 'no') === 'yes';
        $autoplaySpeed = (int)($attrs['autoplay_speed'] ?? 5000);
        $loop = ($attrs['loop'] ?? 'yes') === 'yes';

        $sliderHeight = $attrs['slider_height'] ?? '600px';
        $contentPosition = $attrs['content_position'] ?? 'center';
        $contentWidth = $attrs['content_width'] ?? '700px';
        $overlayType = $attrs['overlay_type'] ?? 'gradient';
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.5)';
        $gradientDirection = $attrs['gradient_direction'] ?? 'to-top';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $titleFontSize = $attrs['title_font_size'] ?? '42px';
        $titleFontWeight = $attrs['title_font_weight'] ?? '700';
        $titleLineHeight = $attrs['title_line_height'] ?? '1.2';
        $excerptColor = $attrs['excerpt_color'] ?? 'rgba(255,255,255,0.9)';
        $excerptFontSize = $attrs['excerpt_font_size'] ?? '18px';
        $metaColor = $attrs['meta_color'] ?? 'rgba(255,255,255,0.8)';
        $metaFontSize = $attrs['meta_font_size'] ?? '14px';
        $categoryBgColor = $attrs['category_bg_color'] ?? '#2563eb';
        $categoryTextColor = $attrs['category_text_color'] ?? '#ffffff';
        $buttonBgColor = $attrs['button_bg_color'] ?? '#ffffff';
        $buttonTextColor = $attrs['button_text_color'] ?? '#111827';
        $buttonBorderRadius = $attrs['button_border_radius'] ?? '8px';
        $buttonPadding = $attrs['button_padding'] ?? '14px 28px';
        $arrowColor = $attrs['arrow_color'] ?? '#ffffff';
        $arrowBgColor = $attrs['arrow_bg_color'] ?? 'rgba(255,255,255,0.2)';
        $arrowSize = $attrs['arrow_size'] ?? '50px';
        $dotColor = $attrs['dot_color'] ?? 'rgba(255,255,255,0.5)';
        $dotActiveColor = $attrs['dot_active_color'] ?? '#ffffff';
        $contentPadding = $attrs['content_padding'] ?? '60px';
        $cssId = $attrs['css_id'] ?? '';
        $cssClass = $attrs['css_class'] ?? '';

        $alignItems = 'center';
        $justifyContent = 'center';
        $textAlign = 'center';

        if ($contentPosition === 'left' || $contentPosition === 'bottom-left') {
            $alignItems = 'flex-start';
            $textAlign = 'left';
        } elseif ($contentPosition === 'right') {
            $alignItems = 'flex-end';
            $textAlign = 'right';
        }

        if ($contentPosition === 'bottom-left' || $contentPosition === 'bottom-center') {
            $justifyContent = 'flex-end';
        }

        $overlayStyle = '';
        if ($overlayType === 'gradient') {
            $gradDir = 'to top';
            if ($gradientDirection === 'to-bottom') $gradDir = 'to bottom';
            elseif ($gradientDirection === 'to-right') $gradDir = 'to right';
            elseif ($gradientDirection === 'radial') $gradDir = 'circle at center';

            if ($gradientDirection === 'radial') {
                $overlayStyle = 'background:radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.7) 100%);';
            } else {
                $overlayStyle = 'background:linear-gradient(' . $gradDir . ', transparent 0%, rgba(0,0,0,0.7) 100%);';
            }
        } elseif ($overlayType === 'solid') {
            $overlayStyle = 'background:' . htmlspecialchars($overlayColor, ENT_QUOTES, 'UTF-8') . ';';
        }

        $samplePosts = [
            ['title' => 'The Ultimate Guide to Modern Web Design Trends in 2026', 'excerpt' => 'Discover the latest design patterns and techniques that are shaping the future of web development and user experience.', 'category' => 'Design', 'author' => 'John Smith', 'date' => 'January 5, 2026', 'gradient' => 'linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%)'],
            ['title' => 'Building Scalable Applications with Cloud Architecture', 'excerpt' => 'Learn how to design and implement cloud-native applications that can handle millions of users with ease.', 'category' => 'Technology', 'author' => 'Sarah Johnson', 'date' => 'January 3, 2026', 'gradient' => 'linear-gradient(135deg, #065f46 0%, #10b981 100%)'],
            ['title' => 'The Art of Minimalist Photography', 'excerpt' => 'Explore the principles of minimalist composition and how simplicity can create powerful visual stories.', 'category' => 'Photography', 'author' => 'Mike Chen', 'date' => 'December 28, 2025', 'gradient' => 'linear-gradient(135deg, #9f1239 0%, #f43f5e 100%)'],
            ['title' => 'Artificial Intelligence in Creative Industries', 'excerpt' => 'How AI is transforming the way we create art, music, and content in the digital age.', 'category' => 'AI', 'author' => 'Emily Davis', 'date' => 'December 22, 2025', 'gradient' => 'linear-gradient(135deg, #0c4a6e 0%, #0ea5e9 100%)'],
            ['title' => 'Sustainable Business Practices for the Future', 'excerpt' => 'A comprehensive look at how companies are adapting to meet environmental challenges while staying profitable.', 'category' => 'Business', 'author' => 'Alex Turner', 'date' => 'December 18, 2025', 'gradient' => 'linear-gradient(135deg, #365314 0%, #84cc16 100%)'],
            ['title' => 'The Psychology of Color in Marketing', 'excerpt' => 'Understanding how color choices influence consumer behavior and brand perception.', 'category' => 'Marketing', 'author' => 'Lisa Wang', 'date' => 'December 15, 2025', 'gradient' => 'linear-gradient(135deg, #7c2d12 0%, #f97316 100%)'],
            ['title' => 'Remote Work Revolution: Building Effective Teams', 'excerpt' => 'Strategies for managing distributed teams and maintaining productivity across time zones.', 'category' => 'Business', 'author' => 'David Miller', 'date' => 'December 10, 2025', 'gradient' => 'linear-gradient(135deg, #581c87 0%, #a855f7 100%)'],
            ['title' => 'The Future of Electric Vehicles', 'excerpt' => 'Exploring advancements in EV technology and the road to sustainable transportation.', 'category' => 'Technology', 'author' => 'Rachel Green', 'date' => 'December 5, 2025', 'gradient' => 'linear-gradient(135deg, #164e63 0%, #06b6d4 100%)']
        ];

        $posts = array_slice($samplePosts, 0, $postsCount);
        $idAttr = $cssId ? ' id="' . htmlspecialchars($cssId, ENT_QUOTES, 'UTF-8') . '"' : '';
        $classAttr = 'tb4-fw-post-slider' . ($cssClass ? ' ' . htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8') : '');

        $html = '<div' . $idAttr . ' class="' . $classAttr . '" data-autoplay="' . ($autoplay ? 'true' : 'false') . '" data-speed="' . $autoplaySpeed . '" data-loop="' . ($loop ? 'true' : 'false') . '">';
        $html .= '<div class="tb4-fw-post-slider-container" style="position:relative;width:100%;overflow:hidden;">';
        $html .= '<div class="tb4-fw-post-slider-track" style="display:flex;transition:transform 0.5s ease;">';

        foreach ($posts as $index => $post) {
            $html .= '<div class="tb4-fw-post-slider-slide" data-slide-index="' . $index . '" style="flex:0 0 100%;position:relative;min-height:' . htmlspecialchars($sliderHeight, ENT_QUOTES, 'UTF-8') . ';">';
            $html .= '<div class="tb4-fw-post-slider-bg" style="position:absolute;inset:0;' . $post['gradient'] . ';background-size:cover;background-position:center;"></div>';

            if ($overlayType !== 'none') {
                $html .= '<div class="tb4-fw-post-slider-overlay" style="position:absolute;inset:0;' . $overlayStyle . '"></div>';
            }

            $html .= '<div class="tb4-fw-post-slider-content" style="position:relative;z-index:2;display:flex;flex-direction:column;align-items:' . $alignItems . ';justify-content:' . $justifyContent . ';text-align:' . $textAlign . ';height:100%;min-height:' . htmlspecialchars($sliderHeight, ENT_QUOTES, 'UTF-8') . ';padding:' . htmlspecialchars($contentPadding, ENT_QUOTES, 'UTF-8') . ';box-sizing:border-box;">';
            $html .= '<div style="max-width:' . htmlspecialchars($contentWidth, ENT_QUOTES, 'UTF-8') . ';width:100%;">';

            if ($showCategory) {
                $html .= '<span class="tb4-fw-post-slider-category" style="display:inline-block;padding:6px 14px;background:' . htmlspecialchars($categoryBgColor, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($categoryTextColor, ENT_QUOTES, 'UTF-8') . ';font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;border-radius:4px;margin-bottom:20px;">' . htmlspecialchars($post['category'], ENT_QUOTES, 'UTF-8') . '</span>';
            }

            if ($showTitle) {
                $html .= '<h2 class="tb4-fw-post-slider-title" style="font-size:' . htmlspecialchars($titleFontSize, ENT_QUOTES, 'UTF-8') . ';font-weight:' . htmlspecialchars($titleFontWeight, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($titleColor, ENT_QUOTES, 'UTF-8') . ';line-height:' . htmlspecialchars($titleLineHeight, ENT_QUOTES, 'UTF-8') . ';margin:0 0 20px 0;">' . htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') . '</h2>';
            }

            if ($showExcerpt) {
                $excerpt = $post['excerpt'];
                if (strlen($excerpt) > $excerptLength) $excerpt = substr($excerpt, 0, $excerptLength) . '...';
                $html .= '<p class="tb4-fw-post-slider-excerpt" style="font-size:' . htmlspecialchars($excerptFontSize, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($excerptColor, ENT_QUOTES, 'UTF-8') . ';line-height:1.6;margin:0 0 24px 0;">' . htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') . '</p>';
            }

            if ($showDate || $showAuthor) {
                $metaStyle = 'display:flex;align-items:center;gap:16px;font-size:' . htmlspecialchars($metaFontSize, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($metaColor, ENT_QUOTES, 'UTF-8') . ';margin-bottom:32px;flex-wrap:wrap;';
                if ($textAlign === 'center') $metaStyle .= 'justify-content:center;';
                $html .= '<div class="tb4-fw-post-slider-meta" style="' . $metaStyle . '">';
                if ($showAuthor) {
                    $html .= '<span class="tb4-fw-post-slider-meta-item" style="display:flex;align-items:center;gap:6px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>' . htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8') . '</span>';
                }
                if ($showDate) {
                    $html .= '<span class="tb4-fw-post-slider-meta-item" style="display:flex;align-items:center;gap:6px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>' . htmlspecialchars($post['date'], ENT_QUOTES, 'UTF-8') . '</span>';
                }
                $html .= '</div>';
            }

            if ($showReadMore) {
                $html .= '<a href="#" class="tb4-fw-post-slider-btn" style="display:inline-block;padding:' . htmlspecialchars($buttonPadding, ENT_QUOTES, 'UTF-8') . ';background:' . htmlspecialchars($buttonBgColor, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($buttonTextColor, ENT_QUOTES, 'UTF-8') . ';text-decoration:none;font-size:15px;font-weight:600;border-radius:' . htmlspecialchars($buttonBorderRadius, ENT_QUOTES, 'UTF-8') . ';transition:all 0.2s;">' . htmlspecialchars($readMoreText, ENT_QUOTES, 'UTF-8') . '</a>';
            }

            $html .= '</div></div></div>';
        }

        $html .= '</div>';

        if ($showArrows) {
            $html .= '<div class="tb4-fw-post-slider-arrows" style="position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);display:flex;justify-content:space-between;padding:0 24px;pointer-events:none;z-index:10;">';
            $html .= '<button class="tb4-fw-post-slider-arrow tb4-fw-post-slider-prev" style="width:' . htmlspecialchars($arrowSize, ENT_QUOTES, 'UTF-8') . ';height:' . htmlspecialchars($arrowSize, ENT_QUOTES, 'UTF-8') . ';border-radius:50%;background:' . htmlspecialchars($arrowBgColor, ENT_QUOTES, 'UTF-8') . ';border:none;color:' . htmlspecialchars($arrowColor, ENT_QUOTES, 'UTF-8') . ';cursor:pointer;display:flex;align-items:center;justify-content:center;pointer-events:auto;transition:all 0.2s;backdrop-filter:blur(4px);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg></button>';
            $html .= '<button class="tb4-fw-post-slider-arrow tb4-fw-post-slider-next" style="width:' . htmlspecialchars($arrowSize, ENT_QUOTES, 'UTF-8') . ';height:' . htmlspecialchars($arrowSize, ENT_QUOTES, 'UTF-8') . ';border-radius:50%;background:' . htmlspecialchars($arrowBgColor, ENT_QUOTES, 'UTF-8') . ';border:none;color:' . htmlspecialchars($arrowColor, ENT_QUOTES, 'UTF-8') . ';cursor:pointer;display:flex;align-items:center;justify-content:center;pointer-events:auto;transition:all 0.2s;backdrop-filter:blur(4px);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></button>';
            $html .= '</div>';
        }

        if ($showDots) {
            $html .= '<div class="tb4-fw-post-slider-dots" style="position:absolute;bottom:32px;left:50%;transform:translateX(-50%);display:flex;gap:10px;z-index:10;">';
            foreach ($posts as $index => $post) {
                $dotBg = $index === 0 ? $dotActiveColor : $dotColor;
                $dotScale = $index === 0 ? 'transform:scale(1.2);' : '';
                $activeClass = $index === 0 ? ' active' : '';
                $html .= '<button class="tb4-fw-post-slider-dot' . $activeClass . '" data-slide="' . $index . '" style="width:10px;height:10px;border-radius:50%;background:' . htmlspecialchars($dotBg, ENT_QUOTES, 'UTF-8') . ';border:none;cursor:pointer;transition:all 0.2s;' . $dotScale . '"></button>';
            }
            $html .= '</div>';
        }

        $html .= '</div></div>';
        $html .= '<style>.tb4-fw-post-slider-arrow:hover{background:rgba(255,255,255,0.3)!important}.tb4-fw-post-slider-btn:hover{filter:brightness(0.95)!important}</style>';

        return $html;
    }
}
