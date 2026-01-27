<?php
/**
 * JTB Component Recognizer
 * Recognizes complex component patterns (sliders, accordions, galleries, etc.)
 * that require analysis of DOM structure beyond simple element mapping
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Component_Recognizer
{
    /**
     * Component patterns with recognition rules
     */
    private static array $componentPatterns = [
        // =============================================
        // SLIDER PATTERNS
        // =============================================
        'slider' => [
            'weight' => 100,
            'rules' => [
                'classes' => ['slider', 'carousel', 'slideshow', 'swiper', 'slick', 'owl-carousel', 'splide'],
                'attributes' => ['data-slick', 'data-swiper', 'data-owl', 'data-slide', 'data-carousel'],
                'children_pattern' => 'repeated_similar_structure',
                'children_classes' => ['slide', 'swiper-slide', 'carousel-item', 'owl-item', 'slick-slide'],
            ],
            'child_type' => 'slider_item',
            'required_children' => 2,
        ],

        // =============================================
        // ACCORDION PATTERNS
        // =============================================
        'accordion' => [
            'weight' => 90,
            'rules' => [
                'classes' => ['accordion', 'faq', 'collapsible', 'expandable', 'collapse-container'],
                'attributes' => ['data-accordion', 'data-collapse', 'role=tablist'],
                'children_pattern' => 'header_content_pairs',
                'children_structure' => [
                    'header' => ['accordion-header', 'faq-question', 'collapse-header', 'panel-heading'],
                    'content' => ['accordion-content', 'faq-answer', 'collapse-content', 'panel-body'],
                ],
            ],
            'child_type' => 'accordion_item',
            'required_children' => 1,
        ],

        // =============================================
        // TABS PATTERNS
        // =============================================
        'tabs' => [
            'weight' => 90,
            'rules' => [
                'classes' => ['tabs', 'tab-container', 'tabbed', 'nav-tabs', 'tab-group'],
                'attributes' => ['data-tabs', 'role=tablist'],
                'has_nav_and_panels' => true,
                'nav_classes' => ['tab-nav', 'tabs-nav', 'nav-tabs', 'tab-list'],
                'panel_classes' => ['tab-content', 'tab-panels', 'tab-pane'],
            ],
            'child_type' => 'tabs_item',
            'required_children' => 2,
        ],

        // =============================================
        // GALLERY PATTERNS
        // =============================================
        'gallery' => [
            'weight' => 85,
            'rules' => [
                'classes' => ['gallery', 'image-gallery', 'photo-gallery', 'grid-gallery', 'lightbox-gallery', 'masonry'],
                'attributes' => ['data-gallery', 'data-lightbox', 'data-fancybox'],
                'children_pattern' => 'image_collection',
                'min_images' => 2,
            ],
            'extract_images' => true,
        ],

        // =============================================
        // TESTIMONIAL PATTERNS
        // =============================================
        'testimonial' => [
            'weight' => 80,
            'rules' => [
                'classes' => ['testimonial', 'review', 'quote', 'customer-review', 'client-review', 'feedback'],
                'has_quote' => true,
                'has_author' => true,
                'author_indicators' => ['author', 'name', 'cite', 'attribution', 'reviewer'],
                'quote_indicators' => ['blockquote', 'quote', 'content', 'text', 'review-text'],
            ],
        ],

        // =============================================
        // TEAM MEMBER PATTERNS
        // =============================================
        'team_member' => [
            'weight' => 80,
            'rules' => [
                'classes' => ['team-member', 'member', 'staff', 'person', 'profile', 'team-card', 'employee'],
                'has_image' => true,
                'has_name' => true,
                'has_position' => true,
                'name_indicators' => ['name', 'title', 'h2', 'h3', 'h4'],
                'position_indicators' => ['position', 'role', 'job', 'title', 'designation'],
            ],
        ],

        // =============================================
        // PRICING TABLE PATTERNS
        // =============================================
        'pricing_table' => [
            'weight' => 85,
            'rules' => [
                'classes' => ['pricing', 'pricing-table', 'pricing-card', 'price-box', 'plan', 'pricing-plan'],
                'has_price' => true,
                'has_features' => true,
                'has_button' => true,
                'price_indicators' => ['price', 'amount', 'cost', 'value'],
                'feature_indicators' => ['features', 'benefits', 'ul', 'list'],
            ],
        ],

        // =============================================
        // BLURB/FEATURE BOX PATTERNS
        // =============================================
        'blurb' => [
            'weight' => 70,
            'rules' => [
                'classes' => ['blurb', 'feature', 'feature-box', 'info-box', 'card', 'service', 'benefit'],
                'has_icon_or_image' => true,
                'has_title' => true,
                'has_description' => true,
            ],
        ],

        // =============================================
        // CTA PATTERNS
        // =============================================
        'cta' => [
            'weight' => 75,
            'rules' => [
                'classes' => ['cta', 'call-to-action', 'cta-section', 'cta-box', 'action-box', 'promo'],
                'has_heading' => true,
                'has_button' => true,
                'prominent_button' => true,
            ],
        ],

        // =============================================
        // HERO/HEADER PATTERNS
        // =============================================
        'fullwidth_header' => [
            'weight' => 95,
            'rules' => [
                'classes' => ['hero', 'hero-section', 'banner', 'jumbotron', 'masthead', 'fullwidth-header', 'page-header'],
                'has_large_heading' => true,
                'has_background' => true,
                'may_have_buttons' => true,
            ],
        ],

        // =============================================
        // SOCIAL ICONS PATTERNS
        // =============================================
        'social_follow' => [
            'weight' => 85,
            'rules' => [
                'classes' => ['social', 'social-icons', 'social-links', 'social-follow', 'social-media'],
                'has_social_links' => true,
                'social_indicators' => ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'pinterest', 'tiktok'],
            ],
            'extract_social_links' => true,
        ],

        // =============================================
        // COUNTDOWN PATTERNS
        // =============================================
        'countdown' => [
            'weight' => 90,
            'rules' => [
                'classes' => ['countdown', 'timer', 'count-down', 'countdown-timer', 'clock'],
                'attributes' => ['data-countdown', 'data-timer', 'data-end', 'data-date'],
                'has_time_units' => true,
                'time_indicators' => ['days', 'hours', 'minutes', 'seconds', 'day', 'hour', 'minute', 'second'],
            ],
        ],

        // =============================================
        // COUNTER/STAT PATTERNS
        // =============================================
        'number_counter' => [
            'weight' => 80,
            'rules' => [
                'classes' => ['counter', 'stat', 'statistic', 'number-counter', 'count-up', 'fact'],
                'attributes' => ['data-counter', 'data-count', 'data-target'],
                'has_number' => true,
                'has_label' => true,
            ],
        ],

        // =============================================
        // MAP PATTERNS
        // =============================================
        'map' => [
            'weight' => 95,
            'rules' => [
                'classes' => ['map', 'google-map', 'location-map', 'embed-map'],
                'has_iframe' => true,
                'iframe_src_contains' => ['google.com/maps', 'maps.google.com', 'openstreetmap'],
            ],
        ],

        // =============================================
        // VIDEO PATTERNS
        // =============================================
        'video' => [
            'weight' => 95,
            'rules' => [
                'classes' => ['video', 'video-player', 'video-embed', 'embed-responsive', 'video-container'],
                'has_video_element' => true,
                'video_src_contains' => ['youtube.com', 'youtu.be', 'vimeo.com', 'wistia.com', 'dailymotion'],
            ],
        ],

        // =============================================
        // BLOG/POSTS PATTERNS
        // =============================================
        'blog' => [
            'weight' => 75,
            'rules' => [
                'classes' => ['blog', 'posts', 'post-grid', 'articles', 'news', 'blog-posts', 'post-list'],
                'children_pattern' => 'article_cards',
                'has_multiple_articles' => true,
            ],
        ],

        // =============================================
        // PORTFOLIO PATTERNS
        // =============================================
        'portfolio' => [
            'weight' => 75,
            'rules' => [
                'classes' => ['portfolio', 'projects', 'work', 'works', 'portfolio-grid', 'case-studies'],
                'children_pattern' => 'project_cards',
                'has_images' => true,
            ],
        ],

        // =============================================
        // FORM PATTERNS
        // =============================================
        'contact_form' => [
            'weight' => 90,
            'rules' => [
                'tag' => 'form',
                'classes' => ['contact-form', 'form', 'form-container', 'cf7', 'wpforms', 'gform'],
                'has_text_inputs' => true,
                'has_submit' => true,
            ],
            'extract_fields' => true,
        ],
    ];

    /**
     * Recognize component type from element
     */
    public static function recognize(\DOMElement $element): ?array
    {
        $matches = [];

        foreach (self::$componentPatterns as $type => $pattern) {
            $score = self::calculateMatchScore($element, $pattern);
            if ($score > 0) {
                $matches[$type] = [
                    'score' => $score * ($pattern['weight'] ?? 50),
                    'pattern' => $pattern,
                ];
            }
        }

        if (empty($matches)) {
            return null;
        }

        // Sort by score descending
        uasort($matches, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Get best match
        $bestType = array_key_first($matches);
        $bestMatch = $matches[$bestType];

        // Extract component-specific data
        $data = self::extractComponentData($element, $bestType, $bestMatch['pattern']);

        return [
            'type' => $bestType,
            'confidence' => min(100, $bestMatch['score']),
            'data' => $data,
        ];
    }

    /**
     * Calculate match score for a pattern
     */
    private static function calculateMatchScore(\DOMElement $element, array $pattern): int
    {
        $score = 0;
        $rules = $pattern['rules'] ?? [];

        // Check tag name
        if (!empty($rules['tag'])) {
            if (strtolower($element->nodeName) === $rules['tag']) {
                $score += 20;
            } else {
                return 0; // Required tag doesn't match
            }
        }

        // Check classes
        if (!empty($rules['classes'])) {
            $elementClasses = self::getClassList($element);
            $matchedClasses = array_intersect($rules['classes'], $elementClasses);
            if (!empty($matchedClasses)) {
                $score += 30 + (count($matchedClasses) * 5);
            }
        }

        // Check attributes
        if (!empty($rules['attributes'])) {
            foreach ($rules['attributes'] as $attr) {
                if (strpos($attr, '=') !== false) {
                    list($attrName, $attrValue) = explode('=', $attr);
                    if ($element->getAttribute($attrName) === $attrValue) {
                        $score += 15;
                    }
                } else {
                    if ($element->hasAttribute($attr)) {
                        $score += 10;
                    }
                }
            }
        }

        // Check for required children pattern
        if (!empty($rules['children_pattern'])) {
            $childrenScore = self::checkChildrenPattern($element, $rules);
            if ($childrenScore > 0) {
                $score += $childrenScore;
            } elseif (!empty($pattern['required_children'])) {
                return 0; // Required children pattern not found
            }
        }

        // Check for specific structure requirements
        $score += self::checkStructureRequirements($element, $rules);

        return $score;
    }

    /**
     * Check children pattern
     */
    private static function checkChildrenPattern(\DOMElement $element, array $rules): int
    {
        $score = 0;
        $pattern = $rules['children_pattern'] ?? '';

        switch ($pattern) {
            case 'repeated_similar_structure':
                $score = self::checkRepeatedStructure($element, $rules);
                break;

            case 'header_content_pairs':
                $score = self::checkHeaderContentPairs($element, $rules);
                break;

            case 'image_collection':
                $score = self::checkImageCollection($element, $rules);
                break;

            case 'article_cards':
            case 'project_cards':
                $score = self::checkCardCollection($element, $rules);
                break;
        }

        return $score;
    }

    /**
     * Check for repeated similar structure (sliders, carousels)
     */
    private static function checkRepeatedStructure(\DOMElement $element, array $rules): int
    {
        $childClasses = $rules['children_classes'] ?? [];
        $matchingChildren = 0;

        foreach ($element->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;

            $childElementClasses = self::getClassList($child);

            // Check if child has any of the expected classes
            if (!empty($childClasses) && array_intersect($childClasses, $childElementClasses)) {
                $matchingChildren++;
                continue;
            }

            // Check for similar structure (div/section children)
            if (in_array(strtolower($child->nodeName), ['div', 'section', 'article', 'li'])) {
                $matchingChildren++;
            }
        }

        $minChildren = $rules['required_children'] ?? 2;
        if ($matchingChildren >= $minChildren) {
            return 20 + ($matchingChildren * 3);
        }

        return 0;
    }

    /**
     * Check for header/content pairs (accordions)
     */
    private static function checkHeaderContentPairs(\DOMElement $element, array $rules): int
    {
        $headerClasses = $rules['children_structure']['header'] ?? [];
        $contentClasses = $rules['children_structure']['content'] ?? [];
        $pairs = 0;

        $children = [];
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $children[] = $child;
            }
        }

        // Look for accordion items (wrappers containing header + content)
        foreach ($children as $child) {
            $childClasses = self::getClassList($child);

            // Check if it's an accordion item wrapper
            $hasHeader = false;
            $hasContent = false;

            foreach ($child->childNodes as $grandchild) {
                if ($grandchild->nodeType !== XML_ELEMENT_NODE) continue;

                $gcClasses = self::getClassList($grandchild);

                // Check for header element
                if (array_intersect($headerClasses, $gcClasses) ||
                    in_array(strtolower($grandchild->nodeName), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'button'])) {
                    $hasHeader = true;
                }

                // Check for content element
                if (array_intersect($contentClasses, $gcClasses) ||
                    (in_array(strtolower($grandchild->nodeName), ['div', 'section', 'p']) && !$hasContent)) {
                    $hasContent = true;
                }
            }

            if ($hasHeader && $hasContent) {
                $pairs++;
            }
        }

        if ($pairs >= 1) {
            return 25 + ($pairs * 5);
        }

        return 0;
    }

    /**
     * Check for image collection (galleries)
     */
    private static function checkImageCollection(\DOMElement $element, array $rules): int
    {
        $minImages = $rules['min_images'] ?? 2;
        $imageCount = 0;

        // Count direct img children
        foreach ($element->getElementsByTagName('img') as $img) {
            $imageCount++;
        }

        // Count figure children with images
        foreach ($element->getElementsByTagName('figure') as $figure) {
            if ($figure->getElementsByTagName('img')->length > 0) {
                $imageCount++;
            }
        }

        if ($imageCount >= $minImages) {
            return 20 + ($imageCount * 2);
        }

        return 0;
    }

    /**
     * Check for card collection (blog posts, portfolio items)
     */
    private static function checkCardCollection(\DOMElement $element, array $rules): int
    {
        $cardCount = 0;

        $cardTags = ['article', 'div', 'li'];
        $cardClasses = ['post', 'article', 'card', 'item', 'project', 'entry'];

        foreach ($element->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;

            $tagName = strtolower($child->nodeName);
            $childClasses = self::getClassList($child);

            // Check tag or classes
            if ($tagName === 'article' ||
                array_intersect($cardClasses, $childClasses)) {
                $cardCount++;
            } elseif (in_array($tagName, $cardTags)) {
                // Check if it has typical card structure (image + title + content)
                $hasImage = $child->getElementsByTagName('img')->length > 0;
                $hasHeading = false;
                foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $h) {
                    if ($child->getElementsByTagName($h)->length > 0) {
                        $hasHeading = true;
                        break;
                    }
                }

                if ($hasImage || $hasHeading) {
                    $cardCount++;
                }
            }
        }

        if ($cardCount >= 2) {
            return 15 + ($cardCount * 3);
        }

        return 0;
    }

    /**
     * Check structure requirements
     */
    private static function checkStructureRequirements(\DOMElement $element, array $rules): int
    {
        $score = 0;

        // Check for image presence
        if (!empty($rules['has_image']) || !empty($rules['has_icon_or_image'])) {
            if ($element->getElementsByTagName('img')->length > 0 ||
                self::hasIconElement($element)) {
                $score += 10;
            }
        }

        // Check for title/name
        if (!empty($rules['has_title']) || !empty($rules['has_name']) || !empty($rules['has_heading'])) {
            $headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
            foreach ($headings as $h) {
                if ($element->getElementsByTagName($h)->length > 0) {
                    $score += 10;
                    break;
                }
            }

            // Also check for title/name classes
            $indicators = array_merge(
                $rules['name_indicators'] ?? [],
                ['title', 'name', 'heading']
            );
            if (self::hasElementWithClass($element, $indicators)) {
                $score += 5;
            }
        }

        // Check for description/content
        if (!empty($rules['has_description'])) {
            if ($element->getElementsByTagName('p')->length > 0 ||
                self::hasElementWithClass($element, ['description', 'content', 'text', 'excerpt'])) {
                $score += 8;
            }
        }

        // Check for button
        if (!empty($rules['has_button'])) {
            if ($element->getElementsByTagName('button')->length > 0 ||
                self::hasElementWithClass($element, ['btn', 'button', 'cta'])) {
                $score += 10;
            }
        }

        // Check for quote
        if (!empty($rules['has_quote'])) {
            if ($element->getElementsByTagName('blockquote')->length > 0 ||
                self::hasElementWithClass($element, $rules['quote_indicators'] ?? ['quote', 'content'])) {
                $score += 10;
            }
        }

        // Check for author
        if (!empty($rules['has_author'])) {
            if (self::hasElementWithClass($element, $rules['author_indicators'] ?? ['author', 'name', 'cite'])) {
                $score += 10;
            }
        }

        // Check for price
        if (!empty($rules['has_price'])) {
            if (self::hasElementWithClass($element, $rules['price_indicators'] ?? ['price', 'amount']) ||
                self::containsPricePattern($element)) {
                $score += 15;
            }
        }

        // Check for features list
        if (!empty($rules['has_features'])) {
            if ($element->getElementsByTagName('ul')->length > 0 ||
                $element->getElementsByTagName('ol')->length > 0 ||
                self::hasElementWithClass($element, $rules['feature_indicators'] ?? ['features', 'list'])) {
                $score += 10;
            }
        }

        // Check for position (team member)
        if (!empty($rules['has_position'])) {
            if (self::hasElementWithClass($element, $rules['position_indicators'] ?? ['position', 'role', 'job'])) {
                $score += 8;
            }
        }

        // Check for social links
        if (!empty($rules['has_social_links'])) {
            $socialIndicators = $rules['social_indicators'] ?? [];
            if (self::hasSocialLinks($element, $socialIndicators)) {
                $score += 15;
            }
        }

        // Check for video element
        if (!empty($rules['has_video_element'])) {
            if ($element->getElementsByTagName('video')->length > 0 ||
                self::hasVideoIframe($element, $rules['video_src_contains'] ?? [])) {
                $score += 20;
            }
        }

        // Check for iframe (maps)
        if (!empty($rules['has_iframe'])) {
            $iframes = $element->getElementsByTagName('iframe');
            if ($iframes->length > 0) {
                $score += 10;

                // Check iframe src
                if (!empty($rules['iframe_src_contains'])) {
                    foreach ($iframes as $iframe) {
                        $src = $iframe->getAttribute('src');
                        foreach ($rules['iframe_src_contains'] as $pattern) {
                            if (stripos($src, $pattern) !== false) {
                                $score += 15;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // Check for form inputs
        if (!empty($rules['has_text_inputs'])) {
            $inputs = $element->getElementsByTagName('input');
            $textInputCount = 0;
            foreach ($inputs as $input) {
                $type = $input->getAttribute('type');
                if (in_array($type, ['text', 'email', 'tel', 'number', 'url', '']) || empty($type)) {
                    $textInputCount++;
                }
            }
            if ($textInputCount > 0 || $element->getElementsByTagName('textarea')->length > 0) {
                $score += 10;
            }
        }

        // Check for submit button
        if (!empty($rules['has_submit'])) {
            $buttons = $element->getElementsByTagName('button');
            foreach ($buttons as $btn) {
                if ($btn->getAttribute('type') === 'submit' || empty($btn->getAttribute('type'))) {
                    $score += 10;
                    break;
                }
            }

            $inputs = $element->getElementsByTagName('input');
            foreach ($inputs as $input) {
                if ($input->getAttribute('type') === 'submit') {
                    $score += 10;
                    break;
                }
            }
        }

        // Check for time units (countdown)
        if (!empty($rules['has_time_units'])) {
            $indicators = $rules['time_indicators'] ?? ['days', 'hours', 'minutes', 'seconds'];
            if (self::hasElementWithClass($element, $indicators) ||
                self::containsTextPatterns($element, $indicators)) {
                $score += 15;
            }
        }

        // Check for number (counters)
        if (!empty($rules['has_number'])) {
            if (self::hasElementWithClass($element, ['number', 'count', 'value', 'digit']) ||
                self::containsLargeNumber($element)) {
                $score += 10;
            }
        }

        // Check for label (counters)
        if (!empty($rules['has_label'])) {
            if (self::hasElementWithClass($element, ['label', 'title', 'text'])) {
                $score += 5;
            }
        }

        // Check for large heading (hero)
        if (!empty($rules['has_large_heading'])) {
            if ($element->getElementsByTagName('h1')->length > 0 ||
                $element->getElementsByTagName('h2')->length > 0) {
                $score += 15;
            }
        }

        // Check for background (hero)
        if (!empty($rules['has_background'])) {
            $style = $element->getAttribute('style');
            if (stripos($style, 'background') !== false ||
                $element->hasAttribute('data-background') ||
                $element->hasAttribute('data-bg')) {
                $score += 10;
            }
        }

        // Check for tabs structure
        if (!empty($rules['has_nav_and_panels'])) {
            $navClasses = $rules['nav_classes'] ?? [];
            $panelClasses = $rules['panel_classes'] ?? [];

            $hasNav = self::hasElementWithClass($element, $navClasses) ||
                      $element->getElementsByTagName('ul')->length > 0;
            $hasPanels = self::hasElementWithClass($element, $panelClasses);

            if ($hasNav && $hasPanels) {
                $score += 20;
            } elseif ($hasNav) {
                $score += 5;
            }
        }

        return $score;
    }

    /**
     * Extract component-specific data
     */
    private static function extractComponentData(\DOMElement $element, string $type, array $pattern): array
    {
        $data = [];

        switch ($type) {
            case 'slider':
                $data = self::extractSliderData($element, $pattern);
                break;

            case 'accordion':
                $data = self::extractAccordionData($element, $pattern);
                break;

            case 'tabs':
                $data = self::extractTabsData($element, $pattern);
                break;

            case 'gallery':
                $data = self::extractGalleryData($element, $pattern);
                break;

            case 'testimonial':
                $data = self::extractTestimonialData($element, $pattern);
                break;

            case 'team_member':
                $data = self::extractTeamMemberData($element, $pattern);
                break;

            case 'pricing_table':
                $data = self::extractPricingTableData($element, $pattern);
                break;

            case 'social_follow':
                $data = self::extractSocialData($element, $pattern);
                break;

            case 'contact_form':
                $data = self::extractFormData($element, $pattern);
                break;

            case 'countdown':
                $data = self::extractCountdownData($element, $pattern);
                break;

            case 'number_counter':
                $data = self::extractCounterData($element, $pattern);
                break;

            case 'map':
                $data = self::extractMapData($element, $pattern);
                break;

            case 'video':
                $data = self::extractVideoData($element, $pattern);
                break;

            default:
                // Generic extraction
                $data = self::extractGenericData($element);
        }

        return $data;
    }

    /**
     * Extract slider data with child items
     */
    private static function extractSliderData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'show_arrows' => true,
            'show_dots' => true,
            'auto' => false,
            'loop' => true,
            'children' => [],
        ];

        // Check data attributes for settings
        if ($element->hasAttribute('data-autoplay')) {
            $data['auto'] = $element->getAttribute('data-autoplay') !== 'false';
        }
        if ($element->hasAttribute('data-arrows')) {
            $data['show_arrows'] = $element->getAttribute('data-arrows') !== 'false';
        }
        if ($element->hasAttribute('data-dots') || $element->hasAttribute('data-pagination')) {
            $data['show_dots'] = ($element->getAttribute('data-dots') ?? $element->getAttribute('data-pagination')) !== 'false';
        }
        if ($element->hasAttribute('data-loop') || $element->hasAttribute('data-infinite')) {
            $data['loop'] = ($element->getAttribute('data-loop') ?? $element->getAttribute('data-infinite')) !== 'false';
        }
        if ($element->hasAttribute('data-speed') || $element->hasAttribute('data-autoplay-speed')) {
            $data['auto_speed'] = (int)($element->getAttribute('data-speed') ?? $element->getAttribute('data-autoplay-speed'));
        }

        // Extract child slides
        $slideClasses = $pattern['rules']['children_classes'] ?? ['slide', 'swiper-slide', 'carousel-item'];

        foreach ($element->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;

            $childClasses = self::getClassList($child);

            if (array_intersect($slideClasses, $childClasses) ||
                strtolower($child->nodeName) === 'div') {

                $slideData = [
                    'type' => 'slider_item',
                    'attrs' => [],
                ];

                // Extract slide content
                foreach (['h1', 'h2', 'h3', 'h4'] as $h) {
                    $headings = $child->getElementsByTagName($h);
                    if ($headings->length > 0) {
                        $slideData['attrs']['heading'] = trim($headings->item(0)->textContent);
                        break;
                    }
                }

                // Extract image
                $imgs = $child->getElementsByTagName('img');
                if ($imgs->length > 0) {
                    $slideData['attrs']['image'] = $imgs->item(0)->getAttribute('src');
                }

                // Check for background image
                $style = $child->getAttribute('style');
                if (preg_match('/background-image:\s*url\([\'"]?([^\'")\s]+)[\'"]?\)/i', $style, $m)) {
                    $slideData['attrs']['background_image'] = $m[1];
                }

                // Extract content
                $ps = $child->getElementsByTagName('p');
                if ($ps->length > 0) {
                    $slideData['attrs']['content'] = self::getInnerHtml($ps->item(0));
                }

                // Extract button
                $buttons = self::findButtons($child);
                if (!empty($buttons)) {
                    $slideData['attrs']['button_text'] = $buttons[0]['text'] ?? '';
                    $slideData['attrs']['button_url'] = $buttons[0]['url'] ?? '';
                }

                $data['children'][] = $slideData;
            }
        }

        return $data;
    }

    /**
     * Extract accordion data with child items
     */
    private static function extractAccordionData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'toggle_icon' => 'arrow',
            'toggle_icon_position' => 'right',
            'children' => [],
        ];

        // Find accordion items
        $headerClasses = $pattern['rules']['children_structure']['header'] ?? ['accordion-header', 'faq-question'];
        $contentClasses = $pattern['rules']['children_structure']['content'] ?? ['accordion-content', 'faq-answer'];

        foreach ($element->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;

            $itemData = [
                'type' => 'accordion_item',
                'attrs' => [
                    'open' => false,
                ],
            ];

            // Check if item is open
            $childClasses = self::getClassList($child);
            if (array_intersect(['open', 'active', 'expanded', 'show'], $childClasses)) {
                $itemData['attrs']['open'] = true;
            }

            // Find header
            foreach ($child->childNodes as $grandchild) {
                if ($grandchild->nodeType !== XML_ELEMENT_NODE) continue;

                $gcClasses = self::getClassList($grandchild);

                // Header element
                if (array_intersect($headerClasses, $gcClasses) ||
                    in_array(strtolower($grandchild->nodeName), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'button'])) {
                    $itemData['attrs']['title'] = trim($grandchild->textContent);
                }

                // Content element
                if (array_intersect($contentClasses, $gcClasses) ||
                    (in_array(strtolower($grandchild->nodeName), ['div', 'section', 'p']) &&
                     empty($itemData['attrs']['content']))) {
                    $itemData['attrs']['content'] = self::getInnerHtml($grandchild);
                }
            }

            if (!empty($itemData['attrs']['title'])) {
                $data['children'][] = $itemData;
            }
        }

        return $data;
    }

    /**
     * Extract tabs data with child items
     */
    private static function extractTabsData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'active_tab_idx' => 0,
            'children' => [],
        ];

        // Find tab navigation and panels
        $navClasses = $pattern['rules']['nav_classes'] ?? ['tab-nav', 'tabs-nav', 'nav-tabs'];
        $panelClasses = $pattern['rules']['panel_classes'] ?? ['tab-content', 'tab-panels', 'tab-pane'];

        $titles = [];
        $panels = [];

        // Extract titles from navigation
        foreach ($element->getElementsByTagName('*') as $el) {
            $elClasses = self::getClassList($el);

            // Check for nav element
            if (array_intersect($navClasses, $elClasses) ||
                (strtolower($el->nodeName) === 'ul' && $el->parentNode === $element)) {

                // Get tab titles from links/buttons
                foreach ($el->getElementsByTagName('a') as $a) {
                    $titles[] = trim($a->textContent);
                }
                foreach ($el->getElementsByTagName('button') as $btn) {
                    $titles[] = trim($btn->textContent);
                }
                if (empty($titles)) {
                    foreach ($el->getElementsByTagName('li') as $li) {
                        $titles[] = trim($li->textContent);
                    }
                }
                break;
            }
        }

        // Extract panels
        foreach ($element->getElementsByTagName('*') as $el) {
            $elClasses = self::getClassList($el);

            if (array_intersect($panelClasses, $elClasses)) {
                $panels[] = [
                    'content' => self::getInnerHtml($el),
                    'active' => (bool)array_intersect(['active', 'show', 'visible'], $elClasses),
                ];
            }
        }

        // Build children
        $count = max(count($titles), count($panels));
        for ($i = 0; $i < $count; $i++) {
            $itemData = [
                'type' => 'tabs_item',
                'attrs' => [
                    'title' => $titles[$i] ?? 'Tab ' . ($i + 1),
                    'content' => $panels[$i]['content'] ?? '',
                ],
            ];

            if (!empty($panels[$i]['active'])) {
                $data['active_tab_idx'] = $i;
            }

            $data['children'][] = $itemData;
        }

        return $data;
    }

    /**
     * Extract gallery data
     */
    private static function extractGalleryData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'gallery_ids' => [],
            'columns' => 3,
            'show_title_and_caption' => false,
        ];

        // Count columns from grid/flex styles
        $style = $element->getAttribute('style');
        if (preg_match('/grid-template-columns:\s*repeat\((\d+)/', $style, $m)) {
            $data['columns'] = (int)$m[1];
        }

        // Check data attribute
        if ($element->hasAttribute('data-columns')) {
            $data['columns'] = (int)$element->getAttribute('data-columns');
        }

        // Extract images
        $images = [];

        foreach ($element->getElementsByTagName('img') as $img) {
            $images[] = [
                'src' => $img->getAttribute('src'),
                'alt' => $img->getAttribute('alt'),
                'title' => $img->getAttribute('title'),
            ];
        }

        // Also check figure elements
        foreach ($element->getElementsByTagName('figure') as $figure) {
            $img = $figure->getElementsByTagName('img')->item(0);
            if ($img) {
                $caption = $figure->getElementsByTagName('figcaption')->item(0);
                $images[] = [
                    'src' => $img->getAttribute('src'),
                    'alt' => $img->getAttribute('alt'),
                    'caption' => $caption ? trim($caption->textContent) : '',
                ];

                if ($caption) {
                    $data['show_title_and_caption'] = true;
                }
            }
        }

        $data['gallery_ids'] = $images;

        return $data;
    }

    /**
     * Extract testimonial data
     */
    private static function extractTestimonialData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'author' => '',
            'content' => '',
            'portrait_url' => '',
            'job_title' => '',
            'company' => '',
        ];

        // Extract quote content
        $blockquote = $element->getElementsByTagName('blockquote')->item(0);
        if ($blockquote) {
            $data['content'] = self::getInnerHtml($blockquote);
        } else {
            // Look for content class
            foreach ($element->getElementsByTagName('*') as $el) {
                $classes = self::getClassList($el);
                if (array_intersect(['content', 'quote', 'text', 'review-text', 'testimonial-content'], $classes)) {
                    $data['content'] = self::getInnerHtml($el);
                    break;
                }
            }
        }

        // Extract author
        $authorIndicators = $pattern['rules']['author_indicators'] ?? ['author', 'name', 'cite'];
        foreach ($element->getElementsByTagName('*') as $el) {
            $classes = self::getClassList($el);
            if (array_intersect($authorIndicators, $classes)) {
                $data['author'] = trim($el->textContent);
                break;
            }
        }

        // Check cite element
        if (empty($data['author'])) {
            $cite = $element->getElementsByTagName('cite')->item(0);
            if ($cite) {
                $data['author'] = trim($cite->textContent);
            }
        }

        // Extract portrait
        $img = $element->getElementsByTagName('img')->item(0);
        if ($img) {
            $data['portrait_url'] = $img->getAttribute('src');
        }

        // Extract job title
        foreach ($element->getElementsByTagName('*') as $el) {
            $classes = self::getClassList($el);
            if (array_intersect(['position', 'role', 'job', 'title', 'job-title'], $classes)) {
                $data['job_title'] = trim($el->textContent);
                break;
            }
        }

        // Extract company
        foreach ($element->getElementsByTagName('*') as $el) {
            $classes = self::getClassList($el);
            if (array_intersect(['company', 'organization', 'org'], $classes)) {
                $data['company'] = trim($el->textContent);
                break;
            }
        }

        return $data;
    }

    /**
     * Extract team member data
     */
    private static function extractTeamMemberData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'name' => '',
            'position' => '',
            'image_url' => '',
            'content' => '',
        ];

        // Extract name from heading
        foreach (['h2', 'h3', 'h4', 'h5'] as $h) {
            $heading = $element->getElementsByTagName($h)->item(0);
            if ($heading) {
                $data['name'] = trim($heading->textContent);
                break;
            }
        }

        // Also check for name class
        if (empty($data['name'])) {
            foreach ($element->getElementsByTagName('*') as $el) {
                $classes = self::getClassList($el);
                if (array_intersect(['name', 'member-name', 'title'], $classes)) {
                    $data['name'] = trim($el->textContent);
                    break;
                }
            }
        }

        // Extract position
        $positionIndicators = $pattern['rules']['position_indicators'] ?? ['position', 'role', 'job'];
        foreach ($element->getElementsByTagName('*') as $el) {
            $classes = self::getClassList($el);
            if (array_intersect($positionIndicators, $classes)) {
                $data['position'] = trim($el->textContent);
                break;
            }
        }

        // Extract image
        $img = $element->getElementsByTagName('img')->item(0);
        if ($img) {
            $data['image_url'] = $img->getAttribute('src');
        }

        // Extract bio/content
        foreach ($element->getElementsByTagName('*') as $el) {
            $classes = self::getClassList($el);
            if (array_intersect(['bio', 'content', 'description', 'excerpt'], $classes)) {
                $data['content'] = self::getInnerHtml($el);
                break;
            }
        }

        // Extract social links
        $socialLinks = self::extractSocialLinks($element);
        foreach ($socialLinks as $network => $url) {
            $data[$network . '_url'] = $url;
        }

        return $data;
    }

    /**
     * Extract pricing table data
     */
    private static function extractPricingTableData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'title' => '',
            'price' => '',
            'currency' => '$',
            'per' => 'month',
            'content' => '',
            'button_text' => '',
            'button_url' => '',
            'featured' => false,
        ];

        // Check for featured class
        $classes = self::getClassList($element);
        if (array_intersect(['featured', 'popular', 'recommended', 'highlighted'], $classes)) {
            $data['featured'] = true;
        }

        // Extract title
        foreach (['h2', 'h3', 'h4'] as $h) {
            $heading = $element->getElementsByTagName($h)->item(0);
            if ($heading) {
                $data['title'] = trim($heading->textContent);
                break;
            }
        }

        // Extract price
        foreach ($element->getElementsByTagName('*') as $el) {
            $classes = self::getClassList($el);
            if (array_intersect(['price', 'amount', 'cost'], $classes)) {
                $priceText = trim($el->textContent);
                // Parse price
                if (preg_match('/([^\d]*)(\d+(?:[.,]\d+)?)(.*)/', $priceText, $m)) {
                    $data['currency'] = trim($m[1]) ?: '$';
                    $data['price'] = $m[2];
                    if (preg_match('/\/\s*(\w+)/', $m[3], $pm)) {
                        $data['per'] = $pm[1];
                    }
                }
                break;
            }
        }

        // Extract features list
        $ul = $element->getElementsByTagName('ul')->item(0);
        if ($ul) {
            $data['content'] = self::getOuterHtml($ul);
        }

        // Extract button
        $buttons = self::findButtons($element);
        if (!empty($buttons)) {
            $data['button_text'] = $buttons[0]['text'] ?? 'Sign Up';
            $data['button_url'] = $buttons[0]['url'] ?? '#';
        }

        // Extract badge text
        foreach ($element->getElementsByTagName('*') as $el) {
            $classes = self::getClassList($el);
            if (array_intersect(['badge', 'ribbon', 'label', 'tag'], $classes)) {
                $data['featured_text'] = trim($el->textContent);
                $data['featured'] = true;
                break;
            }
        }

        return $data;
    }

    /**
     * Extract social icons data
     */
    private static function extractSocialData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'children' => [],
        ];

        $socialLinks = self::extractSocialLinks($element);

        foreach ($socialLinks as $network => $url) {
            $data['children'][] = [
                'type' => 'social_follow_item',
                'attrs' => [
                    'social_network' => $network,
                    'url' => $url,
                ],
            ];
        }

        return $data;
    }

    /**
     * Extract form data
     */
    private static function extractFormData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'children' => [],
            'submit_button_text' => 'Submit',
        ];

        // Extract form fields
        $inputs = $element->getElementsByTagName('input');
        foreach ($inputs as $input) {
            $type = $input->getAttribute('type');
            if (in_array($type, ['submit', 'button', 'hidden'])) {
                if ($type === 'submit') {
                    $data['submit_button_text'] = $input->getAttribute('value') ?: 'Submit';
                }
                continue;
            }

            $fieldData = [
                'type' => 'contact_form_field',
                'attrs' => [
                    'field_type' => self::mapInputType($type),
                    'field_id' => $input->getAttribute('name'),
                    'placeholder' => $input->getAttribute('placeholder'),
                    'required_mark' => $input->hasAttribute('required'),
                ],
            ];

            // Try to find label
            $id = $input->getAttribute('id');
            if ($id) {
                foreach ($element->getElementsByTagName('label') as $label) {
                    if ($label->getAttribute('for') === $id) {
                        $fieldData['attrs']['field_title'] = trim($label->textContent);
                        break;
                    }
                }
            }

            $data['children'][] = $fieldData;
        }

        // Extract textareas
        foreach ($element->getElementsByTagName('textarea') as $textarea) {
            $fieldData = [
                'type' => 'contact_form_field',
                'attrs' => [
                    'field_type' => 'text',
                    'field_id' => $textarea->getAttribute('name'),
                    'placeholder' => $textarea->getAttribute('placeholder'),
                    'required_mark' => $textarea->hasAttribute('required'),
                ],
            ];

            $data['children'][] = $fieldData;
        }

        // Extract submit button text
        foreach ($element->getElementsByTagName('button') as $btn) {
            $type = $btn->getAttribute('type');
            if ($type === 'submit' || empty($type)) {
                $data['submit_button_text'] = trim($btn->textContent) ?: 'Submit';
                break;
            }
        }

        return $data;
    }

    /**
     * Extract countdown data
     */
    private static function extractCountdownData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'end_date' => '',
            'show_days' => true,
            'show_hours' => true,
            'show_minutes' => true,
            'show_seconds' => true,
        ];

        // Check data attributes for end date
        $dateAttrs = ['data-countdown', 'data-date', 'data-end', 'data-timer'];
        foreach ($dateAttrs as $attr) {
            if ($element->hasAttribute($attr)) {
                $data['end_date'] = $element->getAttribute($attr);
                break;
            }
        }

        return $data;
    }

    /**
     * Extract counter data
     */
    private static function extractCounterData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'number' => 0,
            'title' => '',
            'percent_sign' => false,
        ];

        // Check data attributes
        $numberAttrs = ['data-counter', 'data-count', 'data-target', 'data-number'];
        foreach ($numberAttrs as $attr) {
            if ($element->hasAttribute($attr)) {
                $data['number'] = $element->getAttribute($attr);
                break;
            }
        }

        // Find number in content
        if (empty($data['number'])) {
            foreach ($element->getElementsByTagName('*') as $el) {
                $classes = self::getClassList($el);
                if (array_intersect(['number', 'count', 'value', 'digit'], $classes)) {
                    $text = trim($el->textContent);
                    $data['number'] = preg_replace('/[^0-9.]/', '', $text);
                    if (strpos($text, '%') !== false) {
                        $data['percent_sign'] = true;
                    }
                    break;
                }
            }
        }

        // Find title/label
        foreach ($element->getElementsByTagName('*') as $el) {
            $classes = self::getClassList($el);
            if (array_intersect(['label', 'title', 'text'], $classes)) {
                $data['title'] = trim($el->textContent);
                break;
            }
        }

        return $data;
    }

    /**
     * Extract map data
     */
    private static function extractMapData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'address' => '',
            'zoom' => 14,
            'map_height' => 400,
        ];

        // Check data attributes
        if ($element->hasAttribute('data-address')) {
            $data['address'] = $element->getAttribute('data-address');
        }
        if ($element->hasAttribute('data-zoom')) {
            $data['zoom'] = (int)$element->getAttribute('data-zoom');
        }

        // Extract from iframe src
        $iframe = $element->getElementsByTagName('iframe')->item(0);
        if ($iframe) {
            $src = $iframe->getAttribute('src');

            // Parse Google Maps URL
            if (preg_match('/[?&]q=([^&]+)/', $src, $m)) {
                $data['address'] = urldecode($m[1]);
            }
            if (preg_match('/[?&]zoom=(\d+)/', $src, $m)) {
                $data['zoom'] = (int)$m[1];
            }

            // Get height from iframe
            $height = $iframe->getAttribute('height');
            if ($height) {
                $data['map_height'] = (int)preg_replace('/[^0-9]/', '', $height);
            }
        }

        return $data;
    }

    /**
     * Extract video data
     */
    private static function extractVideoData(\DOMElement $element, array $pattern): array
    {
        $data = [
            'src' => '',
        ];

        // Check for video element
        $video = $element->getElementsByTagName('video')->item(0);
        if ($video) {
            $data['src'] = $video->getAttribute('src');

            // Check source elements
            if (empty($data['src'])) {
                $source = $video->getElementsByTagName('source')->item(0);
                if ($source) {
                    $data['src'] = $source->getAttribute('src');
                }
            }
        }

        // Check for iframe embed
        $iframe = $element->getElementsByTagName('iframe')->item(0);
        if ($iframe) {
            $data['src'] = $iframe->getAttribute('src');
        }

        return $data;
    }

    /**
     * Extract generic data
     */
    private static function extractGenericData(\DOMElement $element): array
    {
        $data = [];

        // Extract first heading
        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $h) {
            $heading = $element->getElementsByTagName($h)->item(0);
            if ($heading) {
                $data['title'] = trim($heading->textContent);
                break;
            }
        }

        // Extract first paragraph/content
        $p = $element->getElementsByTagName('p')->item(0);
        if ($p) {
            $data['content'] = self::getInnerHtml($p);
        }

        // Extract first image
        $img = $element->getElementsByTagName('img')->item(0);
        if ($img) {
            $data['image'] = $img->getAttribute('src');
        }

        // Extract first button
        $buttons = self::findButtons($element);
        if (!empty($buttons)) {
            $data['button_text'] = $buttons[0]['text'] ?? '';
            $data['button_url'] = $buttons[0]['url'] ?? '';
        }

        return $data;
    }

    // =============================================
    // HELPER METHODS
    // =============================================

    /**
     * Get class list from element
     */
    private static function getClassList(\DOMElement $element): array
    {
        $classAttr = $element->getAttribute('class');
        if (empty($classAttr)) return [];
        return array_filter(array_map('trim', preg_split('/\s+/', $classAttr)));
    }

    /**
     * Get inner HTML of element
     */
    private static function getInnerHtml(\DOMElement $element): string
    {
        $innerHTML = '';
        foreach ($element->childNodes as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return trim($innerHTML);
    }

    /**
     * Get outer HTML of element
     */
    private static function getOuterHtml(\DOMElement $element): string
    {
        return $element->ownerDocument->saveHTML($element);
    }

    /**
     * Check if element has icon
     */
    private static function hasIconElement(\DOMElement $element): bool
    {
        // Check for i elements (font icons)
        if ($element->getElementsByTagName('i')->length > 0) {
            return true;
        }

        // Check for svg elements
        if ($element->getElementsByTagName('svg')->length > 0) {
            return true;
        }

        // Check for icon class
        return self::hasElementWithClass($element, ['icon', 'fa', 'feather', 'material-icons']);
    }

    /**
     * Check if element has child with specified classes
     */
    private static function hasElementWithClass(\DOMElement $element, array $classes): bool
    {
        foreach ($element->getElementsByTagName('*') as $el) {
            $elClasses = self::getClassList($el);
            if (array_intersect($classes, $elClasses)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if element contains price pattern
     */
    private static function containsPricePattern(\DOMElement $element): bool
    {
        $text = $element->textContent;
        // Look for currency symbols followed by numbers
        return (bool)preg_match('/[$€£¥₹]\s*\d+|\d+\s*[$€£¥₹]/', $text);
    }

    /**
     * Check if element contains text patterns
     */
    private static function containsTextPatterns(\DOMElement $element, array $patterns): bool
    {
        $text = strtolower($element->textContent);
        foreach ($patterns as $pattern) {
            if (stripos($text, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if element contains large number (for counters)
     */
    private static function containsLargeNumber(\DOMElement $element): bool
    {
        $text = $element->textContent;
        return (bool)preg_match('/\b\d{2,}\b/', $text);
    }

    /**
     * Check if element has social links
     */
    private static function hasSocialLinks(\DOMElement $element, array $indicators): bool
    {
        // Check href attributes
        foreach ($element->getElementsByTagName('a') as $a) {
            $href = strtolower($a->getAttribute('href'));
            foreach ($indicators as $network) {
                if (stripos($href, $network) !== false) {
                    return true;
                }
            }
        }

        // Check classes
        return self::hasElementWithClass($element, $indicators);
    }

    /**
     * Check if element has video iframe
     */
    private static function hasVideoIframe(\DOMElement $element, array $patterns): bool
    {
        foreach ($element->getElementsByTagName('iframe') as $iframe) {
            $src = $iframe->getAttribute('src');
            foreach ($patterns as $pattern) {
                if (stripos($src, $pattern) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Extract social links from element
     */
    private static function extractSocialLinks(\DOMElement $element): array
    {
        $links = [];
        $networks = [
            'facebook' => ['facebook.com', 'fb.com'],
            'twitter' => ['twitter.com', 'x.com'],
            'instagram' => ['instagram.com'],
            'linkedin' => ['linkedin.com'],
            'youtube' => ['youtube.com'],
            'pinterest' => ['pinterest.com'],
            'tiktok' => ['tiktok.com'],
            'github' => ['github.com'],
            'dribbble' => ['dribbble.com'],
            'behance' => ['behance.net'],
        ];

        foreach ($element->getElementsByTagName('a') as $a) {
            $href = $a->getAttribute('href');
            foreach ($networks as $network => $patterns) {
                foreach ($patterns as $pattern) {
                    if (stripos($href, $pattern) !== false) {
                        $links[$network] = $href;
                        break 2;
                    }
                }
            }
        }

        return $links;
    }

    /**
     * Find button elements
     */
    private static function findButtons(\DOMElement $element): array
    {
        $buttons = [];

        // Check for button elements
        foreach ($element->getElementsByTagName('button') as $btn) {
            $type = $btn->getAttribute('type');
            if ($type !== 'submit') {
                $buttons[] = [
                    'text' => trim($btn->textContent),
                    'url' => $btn->getAttribute('data-url') ?? '',
                ];
            }
        }

        // Check for anchor buttons
        foreach ($element->getElementsByTagName('a') as $a) {
            $classes = self::getClassList($a);
            if (array_intersect(['btn', 'button', 'cta', 'action'], $classes)) {
                $buttons[] = [
                    'text' => trim($a->textContent),
                    'url' => $a->getAttribute('href'),
                ];
            }
        }

        return $buttons;
    }

    /**
     * Map HTML input type to JTB field type
     */
    private static function mapInputType(string $type): string
    {
        $map = [
            'text' => 'input',
            'email' => 'email',
            'tel' => 'input',
            'number' => 'input',
            'url' => 'input',
            'password' => 'input',
            'checkbox' => 'checkbox',
            'radio' => 'radio',
            'date' => 'input',
            'file' => 'input',
        ];

        return $map[$type] ?? 'input';
    }
}
