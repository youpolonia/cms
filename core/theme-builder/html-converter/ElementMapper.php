<?php
declare(strict_types=1);
/**
 * Element Mapper - Maps HTML elements to TB modules
 * 
 * @package ThemeBuilder
 * @subpackage HtmlConverter
 * @version 4.0.3
 */

namespace Core\ThemeBuilder\HtmlConverter;

require_once __DIR__ . '/ModuleDefaults.php';

class ElementMapper
{
    private StyleExtractor $styleExtractor;
    
    public function __construct()
    {
        $this->styleExtractor = new StyleExtractor();
    }
    
    /**
     * Map HTML element to TB module
     */
    public function mapElement(\DOMElement $element, array $globalStyles, StyleExtractor $styleExtractor): ?array
    {
        $this->styleExtractor = $styleExtractor;
        $tag = strtolower($element->tagName);
        
        // Skip non-content elements
        if (in_array($tag, ['script', 'style', 'link', 'meta', 'noscript', 'template', 'br'])) {
            return null;
        }
        
        // ═══════════════════════════════════════════════════════════════
        // SIMPLE ELEMENT MAPPINGS
        // ═══════════════════════════════════════════════════════════════
        
        // Headings
        if (preg_match('/^h[1-6]$/', $tag)) {
            return $this->mapHeading($element, $globalStyles);
        }
        
        // Paragraph
        if ($tag === 'p') {
            return $this->mapText($element, $globalStyles);
        }
        
        // Image
        if ($tag === 'img') {
            return $this->mapImage($element, $globalStyles);
        }
        
        // Video
        if ($tag === 'video') {
            return $this->mapVideo($element, $globalStyles);
        }
        
        // Audio
        if ($tag === 'audio') {
            return $this->mapAudio($element, $globalStyles);
        }
        
        // Embed/iframe
        if ($tag === 'iframe') {
            return $this->mapEmbed($element, $globalStyles);
        }
        
        // Divider
        if ($tag === 'hr') {
            return $this->mapDivider($element, $globalStyles);
        }
        
        // Lists
        if ($tag === 'ul' || $tag === 'ol') {
            return $this->mapList($element, $globalStyles);
        }
        
        // Quote
        if ($tag === 'blockquote') {
            return $this->mapQuote($element, $globalStyles);
        }
        
        // Code
        if ($tag === 'pre' || $tag === 'code') {
            return $this->mapCode($element, $globalStyles);
        }
        
        // Table
        if ($tag === 'table') {
            return $this->mapTable($element, $globalStyles);
        }
        
        // Search (check before form)
        if ($this->isSearch($element)) {
            return $this->mapSearch($element, $globalStyles);
        }
        
        // Form
        if ($tag === 'form' || $this->isForm($element)) {
            return $this->mapForm($element, $globalStyles);
        }
        
        // Navigation
        if ($tag === 'nav' || $this->isMenu($element)) {
            return $this->mapMenu($element, $globalStyles);
        }
        
        // ═══════════════════════════════════════════════════════════════
        // COMPLEX PATTERN DETECTION
        // ═══════════════════════════════════════════════════════════════
        
        // Button
        if ($this->isButton($element)) {
            return $this->mapButton($element, $globalStyles);
        }
        
        // Icon
        if ($this->isIcon($element)) {
            return $this->mapIcon($element, $globalStyles);
        }
        
        // Logo
        if ($this->isLogo($element)) {
            return $this->mapLogo($element, $globalStyles);
        }
        

        
        // Spacer
        if ($this->isSpacer($element)) {
            return $this->mapSpacer($element, $globalStyles);
        }
        
        // Toggle (single collapsible)
        if ($this->isToggle($element)) {
            return $this->mapToggle($element, $globalStyles);
        }
        
        // Accordion
        if ($this->isAccordion($element)) {
            return $this->mapAccordion($element, $globalStyles);
        }
        
        // Tabs
        if ($this->isTabs($element)) {
            return $this->mapTabs($element, $globalStyles);
        }
        
        // Hero
        if ($this->isHero($element)) {
            return $this->mapHero($element, $globalStyles);
        }
        
        // Slider
        if ($this->isSlider($element)) {
            return $this->mapSlider($element, $globalStyles);
        }
        
        // Gallery
        if ($this->isGallery($element)) {
            return $this->mapGallery($element, $globalStyles);
        }
        
        // Countdown
        if ($this->isCountdown($element)) {
            return $this->mapCountdown($element, $globalStyles);
        }
        
        // Counter/Stats
        if ($this->isCounter($element)) {
            return $this->mapCounter($element, $globalStyles);
        }
        
        // Testimonial
        if ($this->isTestimonial($element)) {
            return $this->mapTestimonial($element, $globalStyles);
        }
        
        // Team member
        if ($this->isTeamMember($element)) {
            return $this->mapTeamMember($element, $globalStyles);
        }
        
        // Team section
        if ($this->isTeamSection($element)) {
            return $this->mapTeamSection($element, $globalStyles);
        }
        
        // Pricing
        if ($this->isPricing($element)) {
            return $this->mapPricing($element, $globalStyles);
        }
        
        // CTA
        if ($this->isCta($element)) {
            return $this->mapCta($element, $globalStyles);
        }
        
        // Blurb (card with icon/image + text)
        if ($this->isBlurb($element)) {
            return $this->mapBlurb($element, $globalStyles);
        }
        
        // Social icons
        if ($this->isSocialIcons($element)) {
            return $this->mapSocial($element, $globalStyles);
        }
        
        // Map (Google Maps)
        if ($this->isMap($element)) {
            return $this->mapMap($element, $globalStyles);
        }
        
        // Bar Counters
        if ($this->isBarCounters($element)) {
            return $this->mapBarCounters($element, $globalStyles);
        }
        
        // Circle Counter
        if ($this->isCircleCounter($element)) {
            return $this->mapCircleCounter($element, $globalStyles);
        }
        
        // Portfolio
        if ($this->isPortfolio($element)) {
            return $this->mapPortfolio($element, $globalStyles);
        }
        
        // Sidebar
        if ($this->isSidebar($element)) {
            return $this->mapSidebar($element, $globalStyles);
        }
        
        // Video Slider
        if ($this->isVideoSlider($element)) {
            return $this->mapVideoSlider($element, $globalStyles);
        }
        
        // Blog
        if ($this->isBlog($element)) {
            return $this->mapBlog($element, $globalStyles);
        }
        
        // Login form
        if ($this->isLogin($element)) {
            return $this->mapLogin($element, $globalStyles);
        }
        
        // Signup form
        if ($this->isSignup($element)) {
            return $this->mapSignup($element, $globalStyles);
        }
        
        // Post title
        if ($this->isPostTitle($element)) {
            return $this->mapPostTitle($element, $globalStyles);
        }
        
        // Post content
        if ($this->isPostContent($element)) {
            return $this->mapPostContent($element, $globalStyles);
        }
        
        // Posts navigation
        if ($this->isPostsNavigation($element)) {
            return $this->mapPostsNavigation($element, $globalStyles);
        }
        
        // Comments
        if ($this->isComments($element)) {
            return $this->mapComments($element, $globalStyles);
        }
        
        // Post slider
        if ($this->isPostSlider($element)) {
            return $this->mapPostSlider($element, $globalStyles);
        }
        
        // Fullwidth modules
        if ($this->isFullwidth($element)) {
            return $this->mapFullwidth($element, $globalStyles);
        }
        
        return null;
    }

    // ═══════════════════════════════════════════════════════════════
    // DETECTION METHODS
    // ═══════════════════════════════════════════════════════════════
    
    private function isButton(\DOMElement $el): bool
    {
        $tag = strtolower($el->tagName);
        if ($tag === 'button') return true;
        if ($tag === 'a' && preg_match('/btn|button|cta/i', $el->getAttribute('class'))) return true;
        return false;
    }
    
    private function isIcon(\DOMElement $el): bool
    {
        $tag = strtolower($el->tagName);
        $class = strtolower($el->getAttribute('class'));
        if ($tag === 'i' || $tag === 'svg') return true;
        if (preg_match('/\b(fa|fas|far|fab|fal|fad|material-icons|bi|bx|ri-|ti-)\b/', $class)) return true;
        return false;
    }
    
    private function isLogo(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class') . ' ' . $el->getAttribute('id'));
        return (bool) preg_match('/logo|brand|site-logo/', $class);
    }
    
    private function isMenu(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        $role = strtolower($el->getAttribute('role'));
        if ($role === 'navigation') return true;
        if (preg_match('/nav|menu|navigation|navbar/', $class)) return true;
        
        // Check if it's a footer column with links (h4 + multiple links)
        $tag = strtolower($el->tagName);
        if ($tag === 'div') {
            $links = $el->getElementsByTagName('a');
            $headings = $el->getElementsByTagName('h4');
            if ($links->length >= 2 && $headings->length === 1) {
                // This is a footer link column - let mapMenu handle it
                return true;
            }
        }
        return false;
    }
    
    private function isSearch(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        $role = strtolower($el->getAttribute('role'));
        if ($role === 'search') return true;
        return (bool) preg_match('/search|site-search/', $class);
    }
    
    private function isSpacer(\DOMElement $el): bool
    {
        $tag = strtolower($el->tagName);
        if ($tag !== 'div' && $tag !== 'span') return false;
        if (strlen(trim($el->textContent)) > 0) return false;
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/spacer|spacing|gap|separator/', $class);
    }
    
    private function isToggle(\DOMElement $el): bool
    {
        if (strtolower($el->tagName) === 'details') return true;
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/toggle|collapsible|expandable/', $class);
    }
    
    private function isAccordion(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/accordion|collapse|faq/', $class);
    }
    
    private function isTabs(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/tabs|tabbed|tab-container/', $class);
    }
    
    private function isHero(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class') . ' ' . $el->getAttribute('id'));
        return (bool) preg_match('/hero|banner|jumbotron|masthead|intro/', $class);
    }
    
    private function isSlider(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/slider|carousel|swiper|slick|owl|splide/', $class);
    }
    
    private function isGallery(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        if (preg_match('/gallery|image-grid|photo-grid|lightbox|masonry/', $class)) return true;
        return $el->getElementsByTagName('img')->length >= 3;
    }
    
    private function isCountdown(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/countdown|timer|coming-soon/', $class);
    }
    
    private function isCounter(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/counter|stat|number|achievement/', $class);
    }
    
    private function isTestimonial(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/testimonial|review|quote|feedback/', $class);
    }
    
    private function isTeamMember(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/team-member|member|staff|person/', $class);
    }
    
    private function isTeamSection(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/team-section|our-team|team-grid/', $class);
    }
    
    private function isPricing(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/pricing|price|plan|package/', $class);
    }
    
    private function isCta(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/cta|call-to-action|action-box/', $class);
    }
    
    private function isBlurb(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        if (preg_match('/blurb|feature|service|card|box/', $class)) {
            $hasIcon = $this->findIcon($el) !== null;
            $hasImg = $el->getElementsByTagName('img')->length > 0;
            $hasHeading = $el->getElementsByTagName('h3')->length > 0 || $el->getElementsByTagName('h4')->length > 0;
            return ($hasIcon || $hasImg) && $hasHeading;
        }
        return false;
    }
    
    private function isSocialIcons(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/social|share|follow/', $class);
    }
    
    private function isMap(\DOMElement $el): bool
    {
        if (strtolower($el->tagName) === 'iframe') {
            $src = strtolower($el->getAttribute('src'));
            return strpos($src, 'google.com/maps') !== false || strpos($src, 'maps.google') !== false;
        }
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/map|google-map/', $class);
    }
    
    private function isForm(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        if (preg_match('/contact-form|newsletter|subscribe/', $class)) return true;
        return $el->getElementsByTagName('form')->length > 0;
    }

    // ═══════════════════════════════════════════════════════════════
    // SIMPLE MAPPING METHODS
    // ═══════════════════════════════════════════════════════════════
    
    private function mapHeading(\DOMElement $el, array $gs): array
    {
        $extracted = [
            'content' => [
                'text' => $this->getTextContent($el),
                'level' => $el->tagName
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('heading', $extracted);
    }
    
    private function mapText(\DOMElement $el, array $gs): array
    {
        $extracted = [
            'content' => [
                'text' => $this->getHtmlContent($el)
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('text', $extracted);
    }
    
    private function mapImage(\DOMElement $el, array $gs): array
    {
        // Check if image has a parent link
        $link = '';
        $parent = $el->parentNode;
        if ($parent && $parent->nodeName === 'a') {
            $link = $parent->getAttribute('href');
        }
        // Check for data-link attribute
        if (!$link) {
            $link = $el->getAttribute('data-link') ?: $el->getAttribute('data-href');
        }
        $extracted = [
            'content' => [
                'src' => $el->getAttribute('src'),
                'alt' => $el->getAttribute('alt'),
                'title' => $el->getAttribute('title'),
                'link' => $link
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id'),
                'lazy_load' => $el->hasAttribute('loading') && $el->getAttribute('loading') === 'lazy'
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('image', $extracted);
    }
    
    private function mapVideo(\DOMElement $el, array $gs): array
    {
        $src = $el->getAttribute('src');
        if (!$src) {
            $source = $el->getElementsByTagName('source')->item(0);
            if ($source) $src = $source->getAttribute('src');
        }
        // Detect video source type
        $sourceType = 'url';
        if (stripos($src, 'youtube.com') !== false || stripos($src, 'youtu.be') !== false) {
            $sourceType = 'youtube';
        } elseif (stripos($src, 'vimeo.com') !== false) {
            $sourceType = 'vimeo';
        }
        $extracted = [
            'content' => [
                'url' => $src,
                'source' => $sourceType,
                'autoplay' => $el->hasAttribute('autoplay'),
                'loop' => $el->hasAttribute('loop'),
                'muted' => $el->hasAttribute('muted'),
                'controls' => $el->hasAttribute('controls'),
                'poster' => $el->getAttribute('poster')
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('video', $extracted);
    }
    
    private function mapAudio(\DOMElement $el, array $gs): array
    {
        $src = $el->getAttribute('src');
        if (!$src) {
            $source = $el->getElementsByTagName('source')->item(0);
            if ($source) $src = $source->getAttribute('src');
        }
        $extracted = [
            'content' => [
                'url' => $src,
                'autoplay' => $el->hasAttribute('autoplay'),
                'loop' => $el->hasAttribute('loop'),
                'controls' => $el->hasAttribute('controls') || !$el->hasAttribute('autoplay')
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('audio', $extracted);
    }
    
    private function mapEmbed(\DOMElement $el, array $gs): array
    {
        return [
            'type' => 'embed',
            'content' => [
                'url' => $el->getAttribute('src'),
                'width' => $el->getAttribute('width') ?: '100%',
                'height' => $el->getAttribute('height') ?: '400'
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs)
        ];
    }
    
    private function mapDivider(\DOMElement $el, array $gs): array
    {
        $extracted = [
            'content' => [
                'show_divider' => true
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('divider', $extracted);
    }
    
    private function mapList(\DOMElement $el, array $gs): array
    {
        $items = [];
        $icon = '';
        foreach ($el->getElementsByTagName('li') as $li) {
            $items[] = $this->getTextContent($li);
            // Check for icon in list item
            if (!$icon) {
                $iconEl = $this->findIcon($li);
                if ($iconEl) {
                    $icon = $this->extractIconName($iconEl->getAttribute('class'));
                }
            }
        }
        $extracted = [
            'content' => [
                'items' => $items,
                'ordered' => strtolower($el->tagName) === 'ol'
            ],
            'design' => array_merge($this->styleExtractor->extractDesign($el, $gs), [
                'icon' => $icon
            ]),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('list', $extracted);
    }
    
    private function mapQuote(\DOMElement $el, array $gs): array
    {
        $author = '';
        $source = '';
        $citeEl = $el->getElementsByTagName('cite')->item(0);
        if ($citeEl) {
            $author = $this->getTextContent($citeEl);
        }
        // Look for footer or figcaption for source
        $footer = $el->getElementsByTagName('footer')->item(0);
        if ($footer) {
            $source = $this->getTextContent($footer);
        }
        // Get quote text without cite/footer
        $text = '';
        $p = $el->getElementsByTagName('p')->item(0);
        if ($p) {
            $text = $this->getTextContent($p);
        } else {
            $text = $this->getTextContent($el);
        }
        $extracted = [
            'content' => [
                'text' => $text,
                'author' => $author,
                'source' => $source
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('quote', $extracted);
    }
    
    private function mapCode(\DOMElement $el, array $gs): array
    {
        $lang = '';
        $class = $el->getAttribute('class');
        if (preg_match('/language-(\w+)|lang-(\w+)/', $class, $m)) {
            $lang = $m[1] ?: $m[2];
        }
        $extracted = [
            'content' => [
                'code' => $el->textContent,
                'language' => $lang
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $class,
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('code', $extracted);
    }
    
    private function mapTable(\DOMElement $el, array $gs): array
    {
        $headers = [];
        $rows = [];
        $thead = $el->getElementsByTagName('thead')->item(0);
        if ($thead) {
            foreach ($thead->getElementsByTagName('th') as $th) {
                $headers[] = $this->getTextContent($th);
            }
        }
        $tbody = $el->getElementsByTagName('tbody')->item(0) ?: $el;
        foreach ($tbody->getElementsByTagName('tr') as $tr) {
            $row = [];
            foreach ($tr->getElementsByTagName('td') as $td) {
                $row[] = $this->getTextContent($td);
            }
            if (!empty($row)) $rows[] = $row;
        }
        return [
            'type' => 'table',
            'content' => ['headers' => $headers, 'rows' => $rows],
            'design' => $this->styleExtractor->extractDesign($el, $gs)
        ];
    }
    
    private function mapButton(\DOMElement $el, array $gs): array
    {
        // Extract icon from button
        $icon = '';
        $iconEl = $this->findIcon($el);
        if ($iconEl) {
            $icon = $this->extractIconName($iconEl->getAttribute('class'));
        }
        // Get text without icon
        $text = $this->getTextContent($el);
        $extracted = [
            'content' => [
                'text' => $text,
                'url' => $el->getAttribute('href') ?: '#',
                'target' => $el->getAttribute('target') ?: '_self',
                'icon' => $icon
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('button', $extracted);
    }
    
    private function mapIcon(\DOMElement $el, array $gs): array
    {
        $class = $el->getAttribute('class');
        $icon = $this->extractIconName($class);
        // Check if icon has a parent link
        $link = '';
        $parent = $el->parentNode;
        if ($parent && $parent->nodeName === 'a') {
            $link = $parent->getAttribute('href');
        }
        $extracted = [
            'content' => [
                'icon' => $icon,
                'library' => $this->detectIconLibrary($class),
                'link' => $link
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $class,
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('icon', $extracted);
    }
    
    private function mapLogo(\DOMElement $el, array $gs): array
    {
        $src = '';
        $text = '';
        $alt = '';
        $url = '/';
        
        $img = $el->getElementsByTagName('img')->item(0);
        if ($img) {
            $src = $img->getAttribute('src');
            $alt = $img->getAttribute('alt');
        }
        
        // Text logo (if no image)
        if (!$src) {
            $text = trim($this->getTextContent($el));
            // Remove any link text duplication
            $link = $el->getElementsByTagName('a')->item(0);
            if ($link && !$text) {
                $text = trim($this->getTextContent($link));
            }
        }
        
        $link = $el->getElementsByTagName('a')->item(0);
        if ($link) {
            $url = $link->getAttribute('href') ?: '/';
        } elseif (strtolower($el->tagName) === 'a') {
            $url = $el->getAttribute('href') ?: '/';
        }
        
        $extracted = [
            'content' => [
                'image' => $src,
                'text' => $text,
                'url' => $url,
                'alt' => $alt
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('logo', $extracted);
    }
    
    private function mapMenu(\DOMElement $el, array $gs): array
    {
        $items = [];
        $title = '';
        
        // Check for heading in menu/footer column
        foreach (['h4', 'h3', 'h5'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) {
                $title = $this->getTextContent($h);
                break;
            }
        }
        
        // Extract menu items with children (dropdown support)
        $processedLinks = [];
        foreach ($el->getElementsByTagName('li') as $li) {
            $a = $li->getElementsByTagName('a')->item(0);
            if (!$a) continue;
            
            $linkId = spl_object_id($a);
            if (in_array($linkId, $processedLinks)) continue;
            $processedLinks[] = $linkId;
            
            $text = trim($this->getTextContent($a));
            $href = $a->getAttribute('href');
            $target = $a->getAttribute('target') ?: '_self';
            
            if ($text && $href) {
                $item = [
                    'text' => $text,
                    'url' => $href,
                    'target' => $target,
                    'children' => []
                ];
                
                // Check for submenu
                $submenu = $li->getElementsByTagName('ul')->item(0);
                if ($submenu) {
                    foreach ($submenu->getElementsByTagName('a') as $subA) {
                        $subLinkId = spl_object_id($subA);
                        if (in_array($subLinkId, $processedLinks)) continue;
                        $processedLinks[] = $subLinkId;
                        
                        $subText = trim($this->getTextContent($subA));
                        $subHref = $subA->getAttribute('href');
                        if ($subText && $subHref) {
                            $item['children'][] = [
                                'text' => $subText,
                                'url' => $subHref,
                                'target' => $subA->getAttribute('target') ?: '_self'
                            ];
                        }
                    }
                }
                
                $items[] = $item;
            }
        }
        
        // Fallback: direct links without li
        if (empty($items)) {
            foreach ($el->getElementsByTagName('a') as $a) {
                $text = trim($this->getTextContent($a));
                $href = $a->getAttribute('href');
                if ($text && $href) {
                    $items[] = [
                        'text' => $text,
                        'url' => $href,
                        'target' => $a->getAttribute('target') ?: '_self',
                        'children' => []
                    ];
                }
            }
        }
        
        $extracted = [
            'content' => [
                'items' => $items,
                'title' => $title
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('menu', $extracted);
    }
    
    private function mapSearch(\DOMElement $el, array $gs): array
    {
        $placeholder = 'Search...';
        $action = '/search';
        $buttonText = 'Search';
        $showButton = false;
        foreach ($el->getElementsByTagName('input') as $input) {
            $p = $input->getAttribute('placeholder');
            if ($p) { $placeholder = $p; break; }
        }
        // Check for form action
        $form = $el->getElementsByTagName('form')->item(0);
        if ($form) {
            $action = $form->getAttribute('action') ?: '/search';
        } elseif (strtolower($el->tagName) === 'form') {
            $action = $el->getAttribute('action') ?: '/search';
        }
        // Check for submit button
        $btn = $this->findButton($el);
        if ($btn) {
            $showButton = true;
            $buttonText = $this->getTextContent($btn) ?: 'Search';
        }
        $extracted = [
            'content' => [
                'placeholder' => $placeholder,
                'action' => $action,
                'button_text' => $buttonText,
                'show_button' => $showButton
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('search', $extracted);
    }
    
    private function mapSpacer(\DOMElement $el, array $gs): array
    {
        $design = $this->styleExtractor->extractDesign($el, $gs);
        $extracted = [
            'content' => [],
            'design' => array_merge($design, [
                'height' => $design['height'] ?? '40px'
            ]),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('spacer', $extracted);
    }
    
    private function mapMap(\DOMElement $el, array $gs): array
    {
        $embedUrl = '';
        $address = '';
        $lat = '';
        $lng = '';
        $zoom = 14;
        $markerTitle = '';
        
        if (strtolower($el->tagName) === 'iframe') {
            $embedUrl = $el->getAttribute('src');
        } else {
            $iframe = $el->getElementsByTagName('iframe')->item(0);
            if ($iframe) $embedUrl = $iframe->getAttribute('src');
        }
        
        // Extract coordinates from embed URL
        if ($embedUrl && preg_match('/@(-?[\d.]+),(-?[\d.]+),(\d+)z/', $embedUrl, $m)) {
            $lat = $m[1];
            $lng = $m[2];
            $zoom = (int)$m[3];
        }
        
        // Extract from data attributes
        $address = $el->getAttribute('data-address') ?: '';
        if ($el->hasAttribute('data-lat')) $lat = $el->getAttribute('data-lat');
        if ($el->hasAttribute('data-lng')) $lng = $el->getAttribute('data-lng');
        if ($el->hasAttribute('data-zoom')) $zoom = (int)$el->getAttribute('data-zoom');
        $markerTitle = $el->getAttribute('data-marker') ?: $el->getAttribute('data-title') ?: '';
        
        $extracted = [
            'content' => [
                'address' => $address,
                'lat' => $lat,
                'lng' => $lng,
                'zoom' => $zoom,
                'embed_url' => $embedUrl,
                'marker_title' => $markerTitle
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('map', $extracted);
    }

    // ═══════════════════════════════════════════════════════════════
    // COMPLEX MAPPING METHODS
    // ═══════════════════════════════════════════════════════════════
    
    private function mapToggle(\DOMElement $el, array $gs): array
    {
        $title = '';
        $toggleContent = '';
        $open = false;
        if (strtolower($el->tagName) === 'details') {
            $summary = $el->getElementsByTagName('summary')->item(0);
            if ($summary) $title = $this->getTextContent($summary);
            foreach ($el->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->tagName) !== 'summary') {
                    $toggleContent .= $this->getHtmlContent($child);
                }
            }
            $open = $el->hasAttribute('open');
        } else {
            // Non-details toggle (div-based)
            $header = null;
            foreach (['header', 'title', 'toggle-header', 'toggle-title'] as $cls) {
                $header = $this->findByClass($el, $cls);
                if ($header) break;
            }
            if ($header) $title = $this->getTextContent($header);
            $body = $this->findByClass($el, 'content') ?: $this->findByClass($el, 'body');
            if ($body) $toggleContent = $this->getHtmlContent($body);
            $open = stripos($el->getAttribute('class'), 'open') !== false ||
                    stripos($el->getAttribute('class'), 'active') !== false;
        }
        $extracted = [
            'content' => [
                'title' => $title,
                'content' => $toggleContent,
                'open' => $open
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('toggle', $extracted);
    }
    
    private function mapAccordion(\DOMElement $el, array $gs): array
    {
        $items = [];
        foreach ($el->getElementsByTagName('details') as $det) {
            $title = '';
            $itemContent = '';
            $summary = $det->getElementsByTagName('summary')->item(0);
            if ($summary) $title = $this->getTextContent($summary);
            foreach ($det->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->tagName) !== 'summary') {
                    $itemContent .= $this->getHtmlContent($child);
                }
            }
            if ($title) {
                $items[] = [
                    'title' => $title,
                    'content' => $itemContent,
                    'open' => $det->hasAttribute('open')
                ];
            }
        }
        // Check for multiple_open setting
        $multipleOpen = $el->hasAttribute('data-multiple') || 
                        stripos($el->getAttribute('class'), 'multiple') !== false;
        $extracted = [
            'content' => [
                'items' => $items,
                'multiple_open' => $multipleOpen
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('accordion', $extracted);
    }
    
    private function mapTabs(\DOMElement $el, array $gs): array
    {
        $tabs = [];
        $defaultTab = 0;
        // Find tab buttons/links
        $tabButtons = [];
        foreach ($el->getElementsByTagName('button') as $btn) {
            $tabButtons[] = $btn;
        }
        if (empty($tabButtons)) {
            // Try links with tab-related classes
            foreach ($el->getElementsByTagName('a') as $a) {
                if (preg_match('/tab|nav-link/', $a->getAttribute('class'))) {
                    $tabButtons[] = $a;
                }
            }
        }
        // Find tab content panels
        $tabPanels = [];
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $class = strtolower($child->getAttribute('class'));
            if (preg_match('/tab-pane|tab-content|panel/', $class)) {
                $tabPanels[] = $child;
            }
        }
        foreach ($tabButtons as $i => $btn) {
            $icon = '';
            $iconEl = $this->findIcon($btn);
            if ($iconEl) {
                $icon = $this->extractIconName($iconEl->getAttribute('class'));
            }
            $tabContent = '';
            if (isset($tabPanels[$i])) {
                $tabContent = $this->getHtmlContent($tabPanels[$i]);
            }
            // Check if this tab is active/default
            if (stripos($btn->getAttribute('class'), 'active') !== false) {
                $defaultTab = $i;
            }
            $tabs[] = [
                'title' => $this->getTextContent($btn),
                'content' => $tabContent,
                'icon' => $icon
            ];
        }
        $extracted = [
            'content' => [
                'tabs' => $tabs,
                'default_tab' => $defaultTab
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('tabs', $extracted);
    }
    
    private function mapHero(\DOMElement $el, array $gs): array
    {
        $heading = '';
        $subheading = '';
        $text = '';
        $buttonText = '';
        $buttonUrl = '';
        $secondaryButtonText = '';
        $secondaryButtonUrl = '';
        $backgroundImage = '';
        $videoUrl = '';
        
        foreach (['h1', 'h2'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) { $heading = $this->getTextContent($h); break; }
        }
        
        // Subheading
        $subheading = $this->findTextByClass($el, ['subheading', 'subtitle', 'sub-title', 'tagline', 'hero-subtitle']);
        if (!$subheading) {
            $h3 = $el->getElementsByTagName('h3')->item(0);
            if ($h3) $subheading = $this->getTextContent($h3);
        }
        
        $p = $el->getElementsByTagName('p')->item(0);
        if ($p) $text = $this->getTextContent($p);
        
        // Find buttons
        $buttons = [];
        foreach ($el->getElementsByTagName('a') as $a) {
            $class = strtolower($a->getAttribute('class'));
            if (preg_match('/btn|button|cta/', $class)) {
                $buttons[] = $a;
            }
        }
        foreach ($el->getElementsByTagName('button') as $btn) {
            $buttons[] = $btn;
        }
        
        if (count($buttons) >= 1) {
            $buttonText = $this->getTextContent($buttons[0]);
            $buttonUrl = $buttons[0]->getAttribute('href') ?: '#';
        }
        if (count($buttons) >= 2) {
            $secondaryButtonText = $this->getTextContent($buttons[1]);
            $secondaryButtonUrl = $buttons[1]->getAttribute('href') ?: '#';
        }
        
        // Background image from style or data attribute
        $style = $el->getAttribute('style');
        if (preg_match('/background(?:-image)?\s*:\s*url\([\'"]?([^\'")]+)[\'"]?\)/', $style, $m)) {
            $backgroundImage = $m[1];
        }
        if (!$backgroundImage) {
            $backgroundImage = $el->getAttribute('data-background') ?: $el->getAttribute('data-bg');
        }
        // Check for img inside hero
        if (!$backgroundImage) {
            $img = $el->getElementsByTagName('img')->item(0);
            if ($img && preg_match('/background|hero-bg|bg-image/', strtolower($img->getAttribute('class')))) {
                $backgroundImage = $img->getAttribute('src');
            }
        }
        
        // Video background
        $video = $el->getElementsByTagName('video')->item(0);
        if ($video) {
            $videoUrl = $video->getAttribute('src');
            if (!$videoUrl) {
                $source = $video->getElementsByTagName('source')->item(0);
                if ($source) $videoUrl = $source->getAttribute('src');
            }
        }
        // Check for iframe (youtube/vimeo background)
        if (!$videoUrl) {
            $iframe = $el->getElementsByTagName('iframe')->item(0);
            if ($iframe) {
                $src = $iframe->getAttribute('src');
                if (stripos($src, 'youtube') !== false || stripos($src, 'vimeo') !== false) {
                    $videoUrl = $src;
                }
            }
        }
        
        $extracted = [
            'content' => [
                'heading' => $heading,
                'subheading' => $subheading,
                'text' => $text,
                'button_text' => $buttonText,
                'button_url' => $buttonUrl,
                'secondary_button_text' => $secondaryButtonText,
                'secondary_button_url' => $secondaryButtonUrl,
                'background_image' => $backgroundImage,
                'video_url' => $videoUrl
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('hero', $extracted);
    }
    
    private function mapSlider(\DOMElement $el, array $gs): array
    {
        $slides = [];
        $patterns = ['slide', 'swiper-slide', 'carousel-item', 'slick-slide'];
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $class = strtolower($child->getAttribute('class'));
            foreach ($patterns as $p) {
                if (strpos($class, $p) !== false) {
                    $img = $child->getElementsByTagName('img')->item(0);
                    $title = '';
                    $text = '';
                    $buttonText = '';
                    $buttonUrl = '';
                    foreach (['h2', 'h3'] as $hTag) {
                        $h = $child->getElementsByTagName($hTag)->item(0);
                        if ($h) { $title = $this->getTextContent($h); break; }
                    }
                    $pEl = $child->getElementsByTagName('p')->item(0);
                    if ($pEl) $text = $this->getTextContent($pEl);
                    $btn = $this->findButton($child);
                    if ($btn) {
                        $buttonText = $this->getTextContent($btn);
                        $buttonUrl = $btn->getAttribute('href') ?: '';
                    }
                    $slides[] = [
                        'image' => $img ? $img->getAttribute('src') : '',
                        'title' => $title,
                        'text' => $text,
                        'button_text' => $buttonText,
                        'button_url' => $buttonUrl
                    ];
                    break;
                }
            }
        }
        if (empty($slides)) {
            foreach ($el->getElementsByTagName('img') as $img) {
                $slides[] = [
                    'image' => $img->getAttribute('src'),
                    'title' => $img->getAttribute('alt'),
                    'text' => '',
                    'button_text' => '',
                    'button_url' => ''
                ];
            }
        }
        // Extract slider settings from data attributes
        $autoplay = $el->hasAttribute('data-autoplay') ? $el->getAttribute('data-autoplay') !== 'false' : true;
        $loop = $el->hasAttribute('data-loop') ? $el->getAttribute('data-loop') !== 'false' : true;
        $speed = (int)($el->getAttribute('data-speed') ?: $el->getAttribute('data-interval') ?: 5000);
        $extracted = [
            'content' => [
                'slides' => $slides,
                'autoplay' => $autoplay,
                'loop' => $loop,
                'speed' => $speed
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('slider', $extracted);
    }
    
    private function mapGallery(\DOMElement $el, array $gs): array
    {
        $images = [];
        $lightbox = false;
        foreach ($el->getElementsByTagName('img') as $img) {
            // Check for link wrapper
            $link = '';
            $parent = $img->parentNode;
            if ($parent && $parent->nodeName === 'a') {
                $link = $parent->getAttribute('href');
                // Check for lightbox attributes
                if ($parent->hasAttribute('data-lightbox') || 
                    $parent->hasAttribute('data-fancybox') ||
                    stripos($parent->getAttribute('class'), 'lightbox') !== false) {
                    $lightbox = true;
                }
            }
            $images[] = [
                'src' => $img->getAttribute('src'),
                'alt' => $img->getAttribute('alt'),
                'title' => $img->getAttribute('title') ?: $img->getAttribute('alt'),
                'link' => $link
            ];
        }
        // Check gallery element for lightbox
        if ($el->hasAttribute('data-lightbox') || stripos($el->getAttribute('class'), 'lightbox') !== false) {
            $lightbox = true;
        }
        // Detect columns from class
        $columns = min(4, count($images));
        if (preg_match('/columns?-(\d+)|col-(\d+)|grid-(\d+)/', $el->getAttribute('class'), $m)) {
            $columns = (int)($m[1] ?: $m[2] ?: $m[3]);
        }
        $extracted = [
            'content' => [
                'images' => $images,
                'lightbox' => $lightbox
            ],
            'design' => array_merge($this->styleExtractor->extractDesign($el, $gs), [
                'columns' => $columns,
                'columns_tablet' => min(2, $columns),
                'columns_mobile' => 1
            ]),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('gallery', $extracted);
    }
    
    private function mapCountdown(\DOMElement $el, array $gs): array
    {
        $targetDate = $el->getAttribute('data-date') 
            ?: $el->getAttribute('data-countdown')
            ?: $el->getAttribute('data-target')
            ?: date('Y-m-d H:i:s', strtotime('+30 days'));
        $title = '';
        $expiredMessage = 'Event has ended';
        
        foreach (['h2', 'h3'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) { $title = $this->getTextContent($h); break; }
        }
        
        // Check for expired message
        if ($el->hasAttribute('data-expired')) {
            $expiredMessage = $el->getAttribute('data-expired');
        }
        $expiredEl = $this->findByClass($el, 'expired-message');
        if ($expiredEl) {
            $expiredMessage = $this->getTextContent($expiredEl);
        }
        
        // Detect which units to show
        $showDays = true;
        $showHours = true;
        $showMinutes = true;
        $showSeconds = true;
        
        // Check for hidden units via class or data attributes
        $class = strtolower($el->getAttribute('class'));
        if (preg_match('/hide-days|no-days/', $class) || $el->getAttribute('data-show-days') === 'false') {
            $showDays = false;
        }
        if (preg_match('/hide-hours|no-hours/', $class) || $el->getAttribute('data-show-hours') === 'false') {
            $showHours = false;
        }
        if (preg_match('/hide-minutes|no-minutes/', $class) || $el->getAttribute('data-show-minutes') === 'false') {
            $showMinutes = false;
        }
        if (preg_match('/hide-seconds|no-seconds/', $class) || $el->getAttribute('data-show-seconds') === 'false') {
            $showSeconds = false;
        }
        
        $extracted = [
            'content' => [
                'target_date' => $targetDate,
                'title' => $title,
                'expired_message' => $expiredMessage,
                'show_days' => $showDays,
                'show_hours' => $showHours,
                'show_minutes' => $showMinutes,
                'show_seconds' => $showSeconds
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('countdown', $extracted);
    }
    
    private function mapCounter(\DOMElement $el, array $gs): array
    {
        $number = '';
        $prefix = '';
        $suffix = '';
        $label = '';
        $startValue = 0;
        
        $numberText = $this->findTextByClass($el, ['number', 'count', 'value', 'stat-number']);
        if (!$numberText && preg_match('/([\d,]+[+%]?|\d+[kKmM]+)/', $el->textContent, $m)) {
            $numberText = $m[1];
        }
        
        // Parse prefix, number, suffix
        if ($numberText) {
            if (preg_match('/^([\$\x{20AC}\x{00A3}\x{00A5}#]*)([\d,\.]+)([+%kKmMbB]*)$/u', trim($numberText), $m)) {
                $prefix = $m[1];
                $number = $m[2];
                $suffix = $m[3];
            } else {
                $number = preg_replace('/[^\d,\.]/', '', $numberText);
                if (preg_match('/^([^\d]+)/', $numberText, $m)) {
                    $prefix = trim($m[1]);
                }
                if (preg_match('/[\d,\.]+(.+)$/', $numberText, $m)) {
                    $suffix = trim($m[1]);
                }
            }
        }
        
        $label = $this->findTextByClass($el, ['label', 'title', 'text', 'stat-label', 'counter-label']);
        
        $startValue = (int)($el->getAttribute('data-start') ?: $el->getAttribute('data-from') ?: 0);
        if ($el->hasAttribute('data-prefix')) $prefix = $el->getAttribute('data-prefix');
        if ($el->hasAttribute('data-suffix')) $suffix = $el->getAttribute('data-suffix');
        
        $extracted = [
            'content' => [
                'number' => $number,
                'prefix' => $prefix,
                'suffix' => $suffix,
                'label' => $label,
                'start_value' => $startValue
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('counter', $extracted);
    }
    
    private function mapTestimonial(\DOMElement $el, array $gs): array
    {
        $quote = '';
        $name = '';
        $role = '';
        $company = '';
        $image = '';
        $rating = 5;
        
        $bq = $el->getElementsByTagName('blockquote')->item(0);
        if ($bq) $quote = $this->getTextContent($bq);
        else {
            $p = $el->getElementsByTagName('p')->item(0);
            if ($p) $quote = $this->getTextContent($p);
        }
        $name = $this->findTextByClass($el, ['author', 'name', 'client-name']);
        $role = $this->findTextByClass($el, ['role', 'position', 'title', 'job-title']);
        $company = $this->findTextByClass($el, ['company', 'organization', 'firm']);
        
        $img = $el->getElementsByTagName('img')->item(0);
        if ($img) $image = $img->getAttribute('src');
        
        // Extract rating from data attribute or stars count
        if ($el->hasAttribute('data-rating')) {
            $rating = (int)$el->getAttribute('data-rating');
        } else {
            // Count filled stars
            $stars = 0;
            foreach ($el->getElementsByTagName('i') as $i) {
                $class = strtolower($i->getAttribute('class'));
                if (preg_match('/star|rating/', $class) && !preg_match('/empty|outline|half/', $class)) {
                    $stars++;
                }
            }
            if ($stars > 0) $rating = min(5, $stars);
        }
        
        $extracted = [
            'content' => [
                'quote' => $quote,
                'name' => $name,
                'role' => $role,
                'company' => $company,
                'image' => $image,
                'rating' => $rating
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('testimonial', $extracted);
    }
    
    private function mapTeamMember(\DOMElement $el, array $gs): array
    {
        $image = '';
        $name = '';
        $role = '';
        $bio = '';
        $social = [];
        
        $img = $el->getElementsByTagName('img')->item(0);
        if ($img) $image = $img->getAttribute('src');
        
        foreach (['h3', 'h4'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) { $name = $this->getTextContent($h); break; }
        }
        $role = $this->findTextByClass($el, ['role', 'position', 'title', 'job']);
        
        // Extract bio
        $p = $el->getElementsByTagName('p')->item(0);
        if ($p) $bio = $this->getTextContent($p);
        
        $social = $this->extractSocialLinks($el);
        
        $extracted = [
            'content' => [
                'image' => $image,
                'name' => $name,
                'role' => $role,
                'bio' => $bio,
                'social' => $social
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('team_member', $extracted);
    }
    
    private function mapTeamSection(\DOMElement $el, array $gs): array
    {
        $members = [];
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $hasImg = $child->getElementsByTagName('img')->length > 0;
            $hasH = $child->getElementsByTagName('h3')->length > 0 || $child->getElementsByTagName('h4')->length > 0;
            if ($hasImg && $hasH) {
                $img = $child->getElementsByTagName('img')->item(0);
                $name = '';
                $bio = '';
                foreach (['h3', 'h4'] as $hTag) {
                    $h = $child->getElementsByTagName($hTag)->item(0);
                    if ($h) { $name = $this->getTextContent($h); break; }
                }
                $role = $this->findTextByClass($child, ['role', 'position', 'title', 'job']);
                
                // Extract bio from paragraph
                $p = $child->getElementsByTagName('p')->item(0);
                if ($p) $bio = $this->getTextContent($p);
                
                // Extract social links
                $social = $this->extractSocialLinks($child);
                
                $members[] = [
                    'image' => $img->getAttribute('src'),
                    'name' => $name,
                    'role' => $role,
                    'bio' => $bio,
                    'social' => $social
                ];
            }
        }
        
        // Detect columns
        $columns = min(4, max(1, count($members)));
        $class = strtolower($el->getAttribute('class'));
        if (preg_match('/col(?:umn)?s?-(\d+)|grid-(\d+)/', $class, $m)) {
            $columns = (int)($m[1] ?: $m[2]);
        }
        
        $extracted = [
            'content' => [
                'members' => $members,
                'columns' => $columns
            ],
            'design' => array_merge($this->styleExtractor->extractDesign($el, $gs), [
                'columns_tablet' => min(2, $columns),
                'columns_mobile' => 1,
                'show_social' => !empty($members) && !empty($members[0]['social'])
            ]),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('team', $extracted);
    }
    
    private function mapPricing(\DOMElement $el, array $gs): array
    {
        $title = '';
        $subtitle = '';
        $price = '';
        $currency = '$';
        $period = '/month';
        $features = [];
        $buttonText = '';
        $buttonUrl = '';
        $featured = (bool) preg_match('/featured|popular|recommended/', strtolower($el->getAttribute('class')));
        $badge = '';
        
        foreach (['h3', 'h2', 'h4'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) { $title = $this->getTextContent($h); break; }
        }
        if (!$title) {
            $title = $this->findTextByClass($el, ['plan-name', 'plan-title', 'package-name', 'name']);
        }
        $subtitle = $this->findTextByClass($el, ['subtitle', 'description', 'plan-description']);
        
        // Extract price with currency
        $priceText = $this->findTextByClass($el, ['price', 'amount', 'cost']);
        if ($priceText) {
            if (preg_match('/([\$\x{20AC}\x{00A3}\x{00A5}])\s*([\d,\.]+)/u', $priceText, $m)) {
                $currency = $m[1];
                $price = $m[2];
            } else {
                $price = $priceText;
            }
        } elseif (preg_match('/([\$\x{20AC}\x{00A3}\x{00A5}])\s*([\d,\.]+)/u', $el->textContent, $m)) {
            $currency = $m[1];
            $price = $m[2];
        }
        
        // Period extraction
        if (preg_match('/\/(month|year|mo|yr|week|day)/i', $el->textContent, $m)) {
            $period = '/' . strtolower($m[1]);
        }
        $periodText = $this->findTextByClass($el, ['period', 'billing', 'duration']);
        if ($periodText) $period = $periodText;
        
        // Features with included/excluded status
        $ul = $el->getElementsByTagName('ul')->item(0);
        if ($ul) {
            foreach ($ul->getElementsByTagName('li') as $li) {
                $featureText = $this->getTextContent($li);
                $included = true;
                $class = strtolower($li->getAttribute('class'));
                if (preg_match('/excluded|disabled|unavailable|no/', $class)) {
                    $included = false;
                }
                if ($li->getElementsByTagName('del')->length > 0 || $li->getElementsByTagName('s')->length > 0) {
                    $included = false;
                }
                $features[] = ['text' => $featureText, 'included' => $included];
            }
        }
        
        // Badge
        $badge = $this->findTextByClass($el, ['badge', 'ribbon', 'tag', 'label']);
        if (!$badge && $featured) {
            $badge = 'Popular';
        }
        
        $btn = $this->findButton($el);
        if ($btn) {
            $buttonText = $this->getTextContent($btn);
            $buttonUrl = $btn->getAttribute('href') ?: '#';
        }
        
        $extracted = [
            'content' => [
                'title' => $title,
                'subtitle' => $subtitle,
                'price' => $price,
                'currency' => $currency,
                'period' => $period,
                'features' => $features,
                'button_text' => $buttonText,
                'button_url' => $buttonUrl,
                'featured' => $featured,
                'badge' => $badge
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('pricing', $extracted);
    }
    
    private function mapCta(\DOMElement $el, array $gs): array
    {
        $heading = '';
        $subheading = '';
        $text = '';
        $buttonText = '';
        $buttonUrl = '';
        $secondaryButtonText = '';
        $secondaryButtonUrl = '';
        
        foreach (['h2', 'h3'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) { $heading = $this->getTextContent($h); break; }
        }
        // Subheading
        $subheading = $this->findTextByClass($el, ['subheading', 'subtitle', 'sub-title', 'tagline']);
        if (!$subheading) {
            $h4 = $el->getElementsByTagName('h4')->item(0);
            if ($h4) $subheading = $this->getTextContent($h4);
        }
        
        $p = $el->getElementsByTagName('p')->item(0);
        if ($p) $text = $this->getTextContent($p);
        
        // Find all buttons (primary and secondary)
        $buttons = [];
        foreach ($el->getElementsByTagName('a') as $a) {
            $class = strtolower($a->getAttribute('class'));
            if (preg_match('/btn|button|cta/', $class)) {
                $buttons[] = $a;
            }
        }
        foreach ($el->getElementsByTagName('button') as $btn) {
            $buttons[] = $btn;
        }
        
        if (count($buttons) >= 1) {
            $buttonText = $this->getTextContent($buttons[0]);
            $buttonUrl = $buttons[0]->getAttribute('href') ?: '#';
        }
        if (count($buttons) >= 2) {
            $secondaryButtonText = $this->getTextContent($buttons[1]);
            $secondaryButtonUrl = $buttons[1]->getAttribute('href') ?: '#';
        }
        
        $extracted = [
            'content' => [
                'heading' => $heading,
                'subheading' => $subheading,
                'text' => $text,
                'button_text' => $buttonText,
                'button_url' => $buttonUrl,
                'secondary_button_text' => $secondaryButtonText,
                'secondary_button_url' => $secondaryButtonUrl
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('cta', $extracted);
    }
    
    private function mapBlurb(\DOMElement $el, array $gs): array
    {
        $icon = '';
        $image = '';
        $heading = '';
        $text = '';
        $link = '';
        $linkText = '';
        
        $iconEl = $this->findIcon($el);
        if ($iconEl) $icon = $this->extractIconName($iconEl->getAttribute('class'));
        
        $img = $el->getElementsByTagName('img')->item(0);
        if ($img) $image = $img->getAttribute('src');
        
        foreach (['h3', 'h4', 'h5'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) { $heading = $this->getTextContent($h); break; }
        }
        
        $p = $el->getElementsByTagName('p')->item(0);
        if ($p) $text = $this->getTextContent($p);
        
        // Find link (read more, learn more, etc.)
        foreach ($el->getElementsByTagName('a') as $a) {
            $class = strtolower($a->getAttribute('class'));
            $aText = strtolower($this->getTextContent($a));
            if (preg_match('/read|learn|more|link|btn/', $class . ' ' . $aText)) {
                $link = $a->getAttribute('href');
                $linkText = $this->getTextContent($a);
                break;
            }
        }
        
        $extracted = [
            'content' => [
                'icon' => $icon,
                'image' => $image,
                'heading' => $heading,
                'text' => $text,
                'link' => $link,
                'link_text' => $linkText
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('blurb', $extracted);
    }
    
    private function mapSocial(\DOMElement $el, array $gs): array
    {
        $links = $this->extractSocialLinks($el);
        
        // Detect if labels are shown
        $showLabels = false;
        foreach ($el->getElementsByTagName('a') as $a) {
            $text = trim($this->getTextContent($a));
            // If link has text beyond just icon
            if ($text && !preg_match('/^(fa|bi|bx|icon)/i', $text)) {
                $showLabels = true;
                break;
            }
            // Check for span with text
            $span = $a->getElementsByTagName('span')->item(0);
            if ($span && trim($this->getTextContent($span))) {
                $showLabels = true;
                break;
            }
        }
        
        $extracted = [
            'content' => [
                'links' => $links,
                'show_labels' => $showLabels
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('social', $extracted);
    }
    
    private function mapForm(\DOMElement $el, array $gs): array
    {
        $fields = [];
        $submitText = 'Submit';
        $title = '';
        $action = '';
        $method = 'POST';
        $form = (strtolower($el->tagName) === 'form') ? $el : $el->getElementsByTagName('form')->item(0);
        $formEl = $form ?: $el;
        // Get form attributes
        if ($form) {
            $action = $form->getAttribute('action');
            $method = strtoupper($form->getAttribute('method') ?: 'POST');
        }
        // Look for form title
        foreach (['h2', 'h3', 'h4', 'legend'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) {
                $title = $this->getTextContent($h);
                break;
            }
        }
        // Extract fields with labels
        foreach ($formEl->getElementsByTagName('input') as $input) {
            $type = strtolower($input->getAttribute('type') ?: 'text');
            if ($type === 'submit') {
                $submitText = $input->getAttribute('value') ?: 'Submit';
                continue;
            }
            if ($type === 'hidden') continue;
            // Find associated label
            $label = '';
            $inputId = $input->getAttribute('id');
            if ($inputId) {
                foreach ($formEl->getElementsByTagName('label') as $lbl) {
                    if ($lbl->getAttribute('for') === $inputId) {
                        $label = $this->getTextContent($lbl);
                        break;
                    }
                }
            }
            $fields[] = [
                'type' => $type,
                'name' => $input->getAttribute('name'),
                'label' => $label,
                'placeholder' => $input->getAttribute('placeholder'),
                'required' => $input->hasAttribute('required'),
                'options' => []
            ];
        }
        // Textareas
        foreach ($formEl->getElementsByTagName('textarea') as $ta) {
            $label = '';
            $taId = $ta->getAttribute('id');
            if ($taId) {
                foreach ($formEl->getElementsByTagName('label') as $lbl) {
                    if ($lbl->getAttribute('for') === $taId) {
                        $label = $this->getTextContent($lbl);
                        break;
                    }
                }
            }
            $fields[] = [
                'type' => 'textarea',
                'name' => $ta->getAttribute('name'),
                'label' => $label,
                'placeholder' => $ta->getAttribute('placeholder'),
                'required' => $ta->hasAttribute('required'),
                'options' => []
            ];
        }
        // Selects
        foreach ($formEl->getElementsByTagName('select') as $sel) {
            $options = [];
            foreach ($sel->getElementsByTagName('option') as $opt) {
                $options[] = [
                    'value' => $opt->getAttribute('value'),
                    'label' => $this->getTextContent($opt)
                ];
            }
            $label = '';
            $selId = $sel->getAttribute('id');
            if ($selId) {
                foreach ($formEl->getElementsByTagName('label') as $lbl) {
                    if ($lbl->getAttribute('for') === $selId) {
                        $label = $this->getTextContent($lbl);
                        break;
                    }
                }
            }
            $fields[] = [
                'type' => 'select',
                'name' => $sel->getAttribute('name'),
                'label' => $label,
                'placeholder' => '',
                'required' => $sel->hasAttribute('required'),
                'options' => $options
            ];
        }
        // Submit button
        foreach ($formEl->getElementsByTagName('button') as $btn) {
            if (strtolower($btn->getAttribute('type') ?: 'submit') === 'submit') {
                $submitText = $this->getTextContent($btn) ?: 'Submit';
                break;
            }
        }
        // Success/error messages from data attributes
        $successMessage = $el->getAttribute('data-success') ?: 'Thank you for your submission!';
        $errorMessage = $el->getAttribute('data-error') ?: 'Please check your input and try again.';
        $extracted = [
            'content' => [
                'title' => $title,
                'fields' => $fields,
                'submit_text' => $submitText,
                'action' => $action,
                'method' => $method,
                'success_message' => $successMessage,
                'error_message' => $errorMessage
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('form', $extracted);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════════
    
    private function getTextContent(\DOMElement $el): string
    {
        return trim($el->textContent);
    }
    
    private function getHtmlContent(\DOMElement $el): string
    {
        $html = '';
        foreach ($el->childNodes as $child) {
            $html .= $el->ownerDocument->saveHTML($child);
        }
        return trim($html);
    }
    
    private function findTextByClass(\DOMElement $el, array $patterns): string
    {
        $xpath = new \DOMXPath($el->ownerDocument);
        foreach ($patterns as $pattern) {
            $nodes = $xpath->query(".//*[contains(@class, '{$pattern}')]", $el);
            if ($nodes->length > 0) {
                return trim($nodes->item(0)->textContent);
            }
        }
        return '';
    }
    
    private function findByClass(\DOMElement $el, string $className): ?\DOMElement
    {
        $xpath = new \DOMXPath($el->ownerDocument);
        $nodes = $xpath->query(".//*[contains(@class, '{$className}')]", $el);
        if ($nodes->length > 0) {
            return $nodes->item(0);
        }
        return null;
    }
    
    private function findButton(\DOMElement $el): ?\DOMElement
    {
        // Check for button tag
        $buttons = $el->getElementsByTagName('button');
        if ($buttons->length > 0) return $buttons->item(0);
        
        // Check for anchor with button class
        foreach ($el->getElementsByTagName('a') as $a) {
            $class = strtolower($a->getAttribute('class'));
            if (preg_match('/btn|button|cta/', $class)) {
                return $a;
            }
        }
        return null;
    }
    
    private function findIcon(\DOMElement $el): ?\DOMElement
    {
        // Check for i tag with icon class
        foreach ($el->getElementsByTagName('i') as $i) {
            $class = strtolower($i->getAttribute('class'));
            if (preg_match('/\b(fa|fas|far|fab|material-icons|bi|bx)\b/', $class)) {
                return $i;
            }
        }
        // Check for svg
        $svgs = $el->getElementsByTagName('svg');
        if ($svgs->length > 0) return $svgs->item(0);
        
        return null;
    }
    
    private function extractIconName(string $class): string
    {
        // Font Awesome
        if (preg_match('/fa-([a-z0-9-]+)/', $class, $m)) {
            return $m[1];
        }
        // Bootstrap Icons
        if (preg_match('/bi-([a-z0-9-]+)/', $class, $m)) {
            return $m[1];
        }
        // Boxicons
        if (preg_match('/bx[sl]?-([a-z0-9-]+)/', $class, $m)) {
            return $m[1];
        }
        return $class;
    }
    
    private function detectIconLibrary(string $class): string
    {
        if (preg_match('/\b(fas|far|fab|fal|fad)\b/', $class)) return 'fontawesome';
        if (strpos($class, 'material-icons') !== false) return 'material';
        if (preg_match('/\bbi\b/', $class)) return 'bootstrap';
        if (preg_match('/\bbx\b/', $class)) return 'boxicons';
        return 'fontawesome';
    }
    
    private function extractSocialLinks(\DOMElement $el): array
    {
        $links = [];
        $platforms = [
            'facebook' => 'facebook',
            'twitter' => 'twitter',
            'x.com' => 'twitter',
            'linkedin' => 'linkedin',
            'instagram' => 'instagram',
            'youtube' => 'youtube',
            'tiktok' => 'tiktok',
            'pinterest' => 'pinterest',
            'github' => 'github',
            'dribbble' => 'dribbble',
            'behance' => 'behance'
        ];
        
        foreach ($el->getElementsByTagName('a') as $a) {
            $href = strtolower($a->getAttribute('href'));
            foreach ($platforms as $key => $platform) {
                if (strpos($href, $key) !== false) {
                    $links[$platform] = $a->getAttribute('href');
                    break;
                }
            }
        }
        return $links;
    }

    // ═══════════════════════════════════════════════════════════════
    // PART 3: Additional Detection Methods
    // ═══════════════════════════════════════════════════════════════
    
    private function isBarCounters(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/progress|bar-counter|skill-bar|progress-bar/', $class);
    }
    
    private function isCircleCounter(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/circle-counter|circular-progress|pie-chart|donut/', $class);
    }
    
    private function isPortfolio(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/portfolio|projects|works|case-studies/', $class);
    }
    
    private function isSidebar(\DOMElement $el): bool
    {
        if (strtolower($el->tagName) === 'aside') return true;
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/sidebar|aside|widget-area/', $class);
    }
    
    private function isVideoSlider(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        if (!preg_match('/slider|carousel|swiper/', $class)) return false;
        return $el->getElementsByTagName('video')->length > 0;
    }
    
    private function isBlog(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/blog|posts|articles|news/', $class);
    }
    
    // ═══════════════════════════════════════════════════════════════
    // PART 3: Additional Mapping Methods
    // ═══════════════════════════════════════════════════════════════
    
    private function mapBarCounters(\DOMElement $el, array $gs): array
    {
        $bars = [];
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $label = '';
            $value = 0;
            $color = '';
            
            // Try to find label element
            $labelEl = $this->findByClass($child, 'label') ?: $this->findByClass($child, 'title');
            if ($labelEl) {
                $label = $this->getTextContent($labelEl);
            }
            
            // Try to find progress bar element for value and color
            $progressEl = $this->findByClass($child, 'progress') ?: $this->findByClass($child, 'bar');
            if ($progressEl) {
                // Get value from width style or data attribute
                $style = $progressEl->getAttribute('style');
                if (preg_match('/width:\s*(\d+)%/', $style, $m)) {
                    $value = (int)$m[1];
                }
                $value = (int)($progressEl->getAttribute('data-value') ?: $progressEl->getAttribute('data-percent') ?: $value);
                
                // Get color from style
                if (preg_match('/background(?:-color)?:\s*([^;]+)/', $style, $m)) {
                    $color = trim($m[1]);
                }
            }
            
            // Fallback: parse from text
            if (!$value) {
                $text = trim($child->textContent);
                if (preg_match('/(.+?)\s*(\d+)\s*%/', $text, $m)) {
                    if (!$label) $label = trim($m[1]);
                    $value = (int)$m[2];
                } elseif (preg_match('/(\d+)\s*%/', $text, $m)) {
                    $value = (int)$m[1];
                }
            }
            
            // Get value from data attributes on child
            if (!$value) {
                $value = (int)($child->getAttribute('data-value') ?: $child->getAttribute('data-percent') ?: 0);
            }
            
            if ($label || $value) {
                $bars[] = [
                    'label' => $label,
                    'value' => $value,
                    'color' => $color
                ];
            }
        }
        
        $extracted = [
            'content' => [
                'bars' => $bars
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('bar_counters', $extracted);
    }
    
    private function mapCircleCounter(\DOMElement $el, array $gs): array
    {
        $value = (int)($el->getAttribute('data-value') ?: $el->getAttribute('data-percent') ?: 0);
        $maxValue = (int)($el->getAttribute('data-max') ?: 100);
        $suffix = '%';
        $label = '';
        
        if (!$value && preg_match('/(\d+)\s*(%|\+|k|m)?/i', $el->textContent, $m)) {
            $value = (int)$m[1];
            if (isset($m[2])) $suffix = $m[2];
        }
        
        // Get suffix from data attribute
        if ($el->hasAttribute('data-suffix')) {
            $suffix = $el->getAttribute('data-suffix');
        }
        
        // Find label
        $labelEl = $this->findByClass($el, 'label') ?: $this->findByClass($el, 'title');
        if ($labelEl) {
            $label = $this->getTextContent($labelEl);
        } else {
            foreach (['h4', 'h5', 'span', 'p'] as $tag) {
                $h = $el->getElementsByTagName($tag)->item(0);
                if ($h) {
                    $text = $this->getTextContent($h);
                    // Skip if it's just the number
                    if (!preg_match('/^\d+\s*(%|\+|k|m)?$/i', $text)) {
                        $label = $text;
                        break;
                    }
                }
            }
        }
        
        $extracted = [
            'content' => [
                'value' => $value,
                'max_value' => $maxValue,
                'label' => $label,
                'suffix' => $suffix
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('circle_counter', $extracted);
    }
    
    private function mapPortfolio(\DOMElement $el, array $gs): array
    {
        $items = [];
        $categories = [];
        $hasFilter = false;
        
        // Check for filter buttons/tabs
        $filterEl = $this->findByClass($el, 'filter') ?: $this->findByClass($el, 'filters');
        if ($filterEl) {
            $hasFilter = true;
            foreach ($filterEl->getElementsByTagName('button') as $btn) {
                $cat = trim($this->getTextContent($btn));
                if ($cat && strtolower($cat) !== 'all') {
                    $categories[] = $cat;
                }
            }
            foreach ($filterEl->getElementsByTagName('a') as $a) {
                $cat = trim($this->getTextContent($a));
                if ($cat && strtolower($cat) !== 'all') {
                    $categories[] = $cat;
                }
            }
        }
        
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            
            $class = strtolower($child->getAttribute('class'));
            // Skip filter element
            if (preg_match('/filter/', $class)) continue;
            
            $img = $child->getElementsByTagName('img')->item(0);
            $title = '';
            $description = '';
            $link = '';
            $category = '';
            
            foreach (['h3', 'h4'] as $hTag) {
                $h = $child->getElementsByTagName($hTag)->item(0);
                if ($h) { $title = $this->getTextContent($h); break; }
            }
            
            // Description from paragraph
            $p = $child->getElementsByTagName('p')->item(0);
            if ($p) $description = $this->getTextContent($p);
            
            // Category from class or text
            $category = $this->findTextByClass($child, ['category', 'tag', 'cat']);
            if (!$category) {
                // Try data-category attribute
                $category = $child->getAttribute('data-category') ?: $child->getAttribute('data-filter');
            }
            if ($category && !in_array($category, $categories)) {
                $categories[] = $category;
            }
            
            // Link
            $a = $child->getElementsByTagName('a')->item(0);
            if ($a) $link = $a->getAttribute('href');
            
            if ($img || $title) {
                $items[] = [
                    'image' => $img ? $img->getAttribute('src') : '',
                    'title' => $title,
                    'category' => $category,
                    'link' => $link,
                    'description' => $description
                ];
            }
        }
        
        // Detect columns
        $columns = min(4, max(1, count($items)));
        $class = strtolower($el->getAttribute('class'));
        if (preg_match('/col(?:umn)?s?-(\d+)|grid-(\d+)/', $class, $m)) {
            $columns = (int)($m[1] ?: $m[2]);
        }
        
        $extracted = [
            'content' => [
                'items' => $items,
                'filter' => $hasFilter || count($categories) > 1,
                'categories' => array_unique($categories)
            ],
            'design' => array_merge($this->styleExtractor->extractDesign($el, $gs), [
                'columns' => $columns,
                'columns_tablet' => min(2, $columns),
                'columns_mobile' => 1
            ]),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('portfolio', $extracted);
    }
    
    private function mapSidebar(\DOMElement $el, array $gs): array
    {
        $widgets = [];
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            
            $title = '';
            $widgetType = 'custom';
            $widgetContent = '';
            
            foreach (['h3', 'h4', 'h5'] as $hTag) {
                $h = $child->getElementsByTagName($hTag)->item(0);
                if ($h) { $title = $this->getTextContent($h); break; }
            }
            
            // Detect widget type
            $class = strtolower($child->getAttribute('class'));
            if (preg_match('/search/', $class) || $child->getElementsByTagName('input')->length > 0) {
                $widgetType = 'search';
            } elseif (preg_match('/categor/', $class)) {
                $widgetType = 'categories';
            } elseif (preg_match('/tag/', $class)) {
                $widgetType = 'tags';
            } elseif (preg_match('/recent|latest/', $class)) {
                $widgetType = 'recent_posts';
            } elseif (preg_match('/archive/', $class)) {
                $widgetType = 'archives';
            } elseif (preg_match('/social/', $class)) {
                $widgetType = 'social';
            } elseif (preg_match('/newsletter|subscribe/', $class)) {
                $widgetType = 'newsletter';
            } elseif ($child->getElementsByTagName('ul')->length > 0) {
                $widgetType = 'list';
            }
            
            $widgetContent = $this->getHtmlContent($child);
            
            if ($title || $widgetContent) {
                $widgets[] = [
                    'title' => $title,
                    'type' => $widgetType,
                    'content' => $widgetContent
                ];
            }
        }
        
        $extracted = [
            'content' => [
                'widgets' => $widgets
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('sidebar', $extracted);
    }
    
    private function mapVideoSlider(\DOMElement $el, array $gs): array
    {
        $videos = [];
        $slidePatterns = ['slide', 'swiper-slide', 'carousel-item'];
        
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $class = strtolower($child->getAttribute('class'));
            $isSlide = false;
            foreach ($slidePatterns as $pattern) {
                if (strpos($class, $pattern) !== false) { $isSlide = true; break; }
            }
            if ($isSlide || strtolower($child->tagName) === 'video' || strtolower($child->tagName) === 'iframe') {
                $url = '';
                $title = '';
                $poster = '';
                $source = 'url';
                
                $videoEl = $child->getElementsByTagName('video')->item(0) ?: $child->getElementsByTagName('iframe')->item(0);
                if (!$videoEl && in_array(strtolower($child->tagName), ['video', 'iframe'])) {
                    $videoEl = $child;
                }
                if ($videoEl) {
                    $url = $videoEl->getAttribute('src');
                    if (!$url) {
                        $sourceEl = $videoEl->getElementsByTagName('source')->item(0);
                        if ($sourceEl) $url = $sourceEl->getAttribute('src');
                    }
                    $poster = $videoEl->getAttribute('poster');
                    
                    // Detect source type
                    if (stripos($url, 'youtube') !== false || stripos($url, 'youtu.be') !== false) {
                        $source = 'youtube';
                    } elseif (stripos($url, 'vimeo') !== false) {
                        $source = 'vimeo';
                    }
                }
                
                foreach (['h3', 'h4'] as $hTag) {
                    $h = $child->getElementsByTagName($hTag)->item(0);
                    if ($h) { $title = $this->getTextContent($h); break; }
                }
                
                if ($url) {
                    $videos[] = [
                        'url' => $url,
                        'title' => $title,
                        'poster' => $poster,
                        'source' => $source
                    ];
                }
            }
        }
        
        // Check for autoplay and loop settings
        $autoplay = $el->hasAttribute('data-autoplay') ? $el->getAttribute('data-autoplay') !== 'false' : false;
        $loop = $el->hasAttribute('data-loop') ? $el->getAttribute('data-loop') !== 'false' : true;
        
        $extracted = [
            'content' => [
                'videos' => $videos,
                'autoplay' => $autoplay,
                'loop' => $loop
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('video_slider', $extracted);
    }
    
    private function mapBlog(\DOMElement $el, array $gs): array
    {
        $posts = [];
        $showExcerpt = true;
        $showDate = false;
        $showAuthor = false;
        $showImage = true;
        $showCategory = false;
        
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $tag = strtolower($child->tagName);
            $class = strtolower($child->getAttribute('class'));
            if ($tag === 'article' || preg_match('/post|blog-item|entry|card/', $class)) {
                $title = '';
                $excerpt = '';
                $image = '';
                $link = '';
                $date = '';
                $author = '';
                $category = '';
                
                foreach (['h2', 'h3', 'h4'] as $hTag) {
                    $h = $child->getElementsByTagName($hTag)->item(0);
                    if ($h) { 
                        $title = $this->getTextContent($h);
                        // Check if heading has link
                        $titleLink = $h->getElementsByTagName('a')->item(0);
                        if ($titleLink) $link = $titleLink->getAttribute('href');
                        break; 
                    }
                }
                
                $p = $child->getElementsByTagName('p')->item(0);
                if ($p) $excerpt = $this->getTextContent($p);
                
                $img = $child->getElementsByTagName('img')->item(0);
                if ($img) $image = $img->getAttribute('src');
                
                // Extract date
                $dateEl = $this->findByClass($child, 'date') ?: $this->findByClass($child, 'time') ?: $this->findByClass($child, 'posted');
                if ($dateEl) {
                    $date = $this->getTextContent($dateEl);
                    $showDate = true;
                }
                $timeEl = $child->getElementsByTagName('time')->item(0);
                if ($timeEl) {
                    $date = $timeEl->getAttribute('datetime') ?: $this->getTextContent($timeEl);
                    $showDate = true;
                }
                
                // Extract author
                $authorEl = $this->findByClass($child, 'author') ?: $this->findByClass($child, 'writer');
                if ($authorEl) {
                    $author = $this->getTextContent($authorEl);
                    $showAuthor = true;
                }
                
                // Extract category
                $catEl = $this->findByClass($child, 'category') ?: $this->findByClass($child, 'cat');
                if ($catEl) {
                    $category = $this->getTextContent($catEl);
                    $showCategory = true;
                }
                
                // Get link from read more or card link
                if (!$link) {
                    foreach ($child->getElementsByTagName('a') as $a) {
                        $aClass = strtolower($a->getAttribute('class'));
                        $aText = strtolower($this->getTextContent($a));
                        if (preg_match('/read|more|continue|link/', $aClass . ' ' . $aText)) {
                            $link = $a->getAttribute('href');
                            break;
                        }
                    }
                }
                
                if ($title) {
                    $posts[] = [
                        'title' => $title,
                        'excerpt' => $excerpt,
                        'image' => $image,
                        'link' => $link,
                        'date' => $date,
                        'author' => $author,
                        'category' => $category
                    ];
                }
            }
        }
        
        // Detect columns from grid classes
        $columns = min(3, max(1, count($posts)));
        $class = strtolower($el->getAttribute('class'));
        if (preg_match('/col(?:umn)?s?-(\d+)|grid-(\d+)/', $class, $m)) {
            $columns = (int)($m[1] ?: $m[2]);
        }
        
        $extracted = [
            'content' => [
                'posts' => $posts,
                'posts_count' => count($posts),
                'category' => '',
                'show_excerpt' => $showExcerpt,
                'excerpt_length' => 150,
                'show_date' => $showDate,
                'show_author' => $showAuthor,
                'show_image' => $showImage,
                'show_category' => $showCategory
            ],
            'design' => array_merge($this->styleExtractor->extractDesign($el, $gs), [
                'columns' => $columns,
                'columns_tablet' => min(2, $columns),
                'columns_mobile' => 1
            ]),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('blog', $extracted);
    }

    // ═══════════════════════════════════════════════════════════════
    // PART 4: Auth & Post Detection Methods
    // ═══════════════════════════════════════════════════════════════
    
    private function isLogin(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        $id = strtolower($el->getAttribute('id'));
        if (preg_match('/login|signin|sign-in/', $class . ' ' . $id)) return true;
        // Check for password field in form
        $inputs = $el->getElementsByTagName('input');
        $hasPassword = false;
        $hasEmail = false;
        foreach ($inputs as $input) {
            $type = strtolower($input->getAttribute('type'));
            if ($type === 'password') $hasPassword = true;
            if ($type === 'email' || $input->getAttribute('name') === 'email') $hasEmail = true;
        }
        return $hasPassword && $hasEmail && $inputs->length <= 4;
    }
    
    private function isSignup(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        $id = strtolower($el->getAttribute('id'));
        if (preg_match('/signup|sign-up|register|registration/', $class . ' ' . $id)) return true;
        // Check for password confirm field
        $inputs = $el->getElementsByTagName('input');
        $passwordCount = 0;
        foreach ($inputs as $input) {
            if (strtolower($input->getAttribute('type')) === 'password') $passwordCount++;
        }
        return $passwordCount >= 2;
    }
    
    private function isPostTitle(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/post-title|entry-title|article-title/', $class);
    }
    
    private function isPostContent(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/post-content|entry-content|article-content|post-body/', $class);
    }
    
    private function isPostsNavigation(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/post-navigation|posts-navigation|pagination|nav-links/', $class);
    }
    
    private function isComments(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        $id = strtolower($el->getAttribute('id'));
        return (bool) preg_match('/comments|comment-section|disqus/', $class . ' ' . $id);
    }
    
    private function isPostSlider(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        if (!preg_match('/slider|carousel|swiper/', $class)) return false;
        return (bool) preg_match('/post|article|blog/', $class);
    }
    
    private function isFullwidth(\DOMElement $el): bool
    {
        $class = strtolower($el->getAttribute('class'));
        return (bool) preg_match('/fullwidth|full-width|container-fluid|w-100|vw-100/', $class);
    }
    
    // ═══════════════════════════════════════════════════════════════
    // PART 4: Auth & Post Mapping Methods
    // ═══════════════════════════════════════════════════════════════
    
    private function mapLogin(\DOMElement $el, array $gs): array
    {
        $title = '';
        $forgotUrl = '/forgot-password';
        $registerUrl = '/register';
        $registerText = '';
        
        foreach (['h2', 'h3', 'h4'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) { $title = $this->getTextContent($h); break; }
        }
        
        $form = $el->getElementsByTagName('form')->item(0) ?: $el;
        $action = $form instanceof \DOMElement ? $form->getAttribute('action') : '';
        
        $submitText = 'Login';
        foreach ($el->getElementsByTagName('button') as $btn) {
            $type = strtolower($btn->getAttribute('type') ?: 'submit');
            if ($type === 'submit') {
                $submitText = $this->getTextContent($btn) ?: 'Login';
                break;
            }
        }
        
        // Find forgot password link
        foreach ($el->getElementsByTagName('a') as $a) {
            $text = strtolower($this->getTextContent($a));
            $href = $a->getAttribute('href');
            if (preg_match('/forgot|reset|password/', $text)) {
                $forgotUrl = $href;
            } elseif (preg_match('/register|sign.?up|create|account/', $text)) {
                $registerUrl = $href;
                $registerText = $this->getTextContent($a);
            }
        }
        
        $html = $el->ownerDocument->saveHTML($el);
        $showRemember = stripos($html, 'remember') !== false;
        $showForgot = stripos($html, 'forgot') !== false;
        
        if (!$registerText) {
            $registerText = 'Don\'t have an account? Sign up';
        }
        
        $extracted = [
            'content' => [
                'title' => $title ?: 'Login',
                'action' => $action,
                'submit_text' => $submitText,
                'show_remember' => $showRemember,
                'show_forgot' => $showForgot,
                'forgot_url' => $forgotUrl,
                'register_url' => $registerUrl,
                'register_text' => $registerText
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('login', $extracted);
    }
    
    private function mapSignup(\DOMElement $el, array $gs): array
    {
        $title = '';
        $action = '';
        $loginUrl = '/login';
        $loginText = '';
        $termsText = '';
        $termsUrl = '';
        
        foreach (['h2', 'h3', 'h4'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) { $title = $this->getTextContent($h); break; }
        }
        
        // Get form action
        $form = $el->getElementsByTagName('form')->item(0);
        if ($form) {
            $action = $form->getAttribute('action');
        } elseif (strtolower($el->tagName) === 'form') {
            $action = $el->getAttribute('action');
        }
        
        $fields = [];
        $formEl = $form ?: $el;
        
        foreach ($formEl->getElementsByTagName('input') as $input) {
            $type = strtolower($input->getAttribute('type') ?: 'text');
            if ($type === 'submit' || $type === 'hidden') continue;
            
            // Find label
            $label = '';
            $inputId = $input->getAttribute('id');
            if ($inputId) {
                foreach ($formEl->getElementsByTagName('label') as $lbl) {
                    if ($lbl->getAttribute('for') === $inputId) {
                        $label = $this->getTextContent($lbl);
                        break;
                    }
                }
            }
            
            $fields[] = [
                'type' => $type,
                'name' => $input->getAttribute('name'),
                'label' => $label,
                'placeholder' => $input->getAttribute('placeholder'),
                'required' => $input->hasAttribute('required')
            ];
        }
        
        $submitText = 'Sign Up';
        foreach ($el->getElementsByTagName('button') as $btn) {
            $type = strtolower($btn->getAttribute('type') ?: 'submit');
            if ($type === 'submit') {
                $submitText = $this->getTextContent($btn) ?: 'Sign Up';
                break;
            }
        }
        
        // Find login and terms links
        foreach ($el->getElementsByTagName('a') as $a) {
            $text = strtolower($this->getTextContent($a));
            $href = $a->getAttribute('href');
            if (preg_match('/login|sign.?in|already/', $text)) {
                $loginUrl = $href;
                $loginText = $this->getTextContent($a);
            } elseif (preg_match('/terms|conditions|privacy|policy/', $text)) {
                $termsUrl = $href;
                $termsText = $this->getTextContent($a);
            }
        }
        
        if (!$loginText) {
            $loginText = 'Already have an account? Sign in';
        }
        
        $extracted = [
            'content' => [
                'title' => $title ?: 'Create Account',
                'action' => $action,
                'fields' => $fields,
                'submit_text' => $submitText,
                'login_url' => $loginUrl,
                'login_text' => $loginText,
                'terms_text' => $termsText,
                'terms_url' => $termsUrl
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('signup', $extracted);
    }
    
    private function mapPostTitle(\DOMElement $el, array $gs): array
    {
        $title = '';
        foreach (['h1', 'h2'] as $hTag) {
            $h = $el->getElementsByTagName($hTag)->item(0);
            if ($h) { $title = $this->getTextContent($h); break; }
        }
        if (!$title) $title = $this->getTextContent($el);
        
        $extracted = [
            'content' => [
                'title' => $title
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('post_title', $extracted);
    }
    
    private function mapPostContent(\DOMElement $el, array $gs): array
    {
        $extracted = [
            'content' => [
                'content' => $this->getHtmlContent($el)
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('post_content', $extracted);
    }
    
    private function mapPostsNavigation(\DOMElement $el, array $gs): array
    {
        $prev = '';
        $next = '';
        $prevUrl = '';
        $nextUrl = '';
        
        foreach ($el->getElementsByTagName('a') as $a) {
            $class = strtolower($a->getAttribute('class'));
            $rel = strtolower($a->getAttribute('rel'));
            $text = $this->getTextContent($a);
            $href = $a->getAttribute('href');
            if (preg_match('/prev|previous/', $class . ' ' . $rel)) {
                $prev = $text;
                $prevUrl = $href;
            } elseif (preg_match('/next/', $class . ' ' . $rel)) {
                $next = $text;
                $nextUrl = $href;
            }
        }
        
        // Fallback: first and last link
        if (!$prevUrl && !$nextUrl) {
            $links = $el->getElementsByTagName('a');
            if ($links->length >= 2) {
                $prev = $this->getTextContent($links->item(0));
                $prevUrl = $links->item(0)->getAttribute('href');
                $next = $this->getTextContent($links->item($links->length - 1));
                $nextUrl = $links->item($links->length - 1)->getAttribute('href');
            }
        }
        
        $extracted = [
            'content' => [
                'prev_text' => $prev ?: '← Previous',
                'prev_url' => $prevUrl,
                'next_text' => $next ?: 'Next →',
                'next_url' => $nextUrl
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('posts_navigation', $extracted);
    }
    
    private function mapComments(\DOMElement $el, array $gs): array
    {
        $comments = [];
        $commentPatterns = ['comment', 'review'];
        
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $class = strtolower($child->getAttribute('class'));
            foreach ($commentPatterns as $pattern) {
                if (strpos($class, $pattern) !== false) {
                    $author = $this->findTextByClass($child, ['author', 'name', 'commenter']);
                    $commentContent = '';
                    $avatar = '';
                    
                    $p = $child->getElementsByTagName('p')->item(0);
                    if ($p) $commentContent = $this->getTextContent($p);
                    
                    $date = $this->findTextByClass($child, ['date', 'time', 'posted']);
                    
                    // Extract avatar
                    $img = $child->getElementsByTagName('img')->item(0);
                    if ($img) $avatar = $img->getAttribute('src');
                    
                    if ($author || $commentContent) {
                        $comments[] = [
                            'author' => $author,
                            'content' => $commentContent,
                            'date' => $date,
                            'avatar' => $avatar
                        ];
                    }
                    break;
                }
            }
        }
        
        $showForm = $el->getElementsByTagName('form')->length > 0 || 
                    $el->getElementsByTagName('textarea')->length > 0;
        
        // Check for login requirement
        $requireLogin = false;
        $html = strtolower($el->ownerDocument->saveHTML($el));
        if (preg_match('/login.*(comment|reply)|must.*log.*in/', $html)) {
            $requireLogin = true;
        }
        
        $extracted = [
            'content' => [
                'comments' => $comments,
                'show_form' => $showForm,
                'require_login' => $requireLogin
            ],
            'design' => $this->styleExtractor->extractDesign($el, $gs),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('comments', $extracted);
    }
    
    private function mapPostSlider(\DOMElement $el, array $gs): array
    {
        $posts = [];
        $showExcerpt = false;
        $showDate = false;
        $slidePatterns = ['slide', 'swiper-slide', 'carousel-item'];
        
        foreach ($el->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $class = strtolower($child->getAttribute('class'));
            $isSlide = false;
            foreach ($slidePatterns as $pattern) {
                if (strpos($class, $pattern) !== false) { $isSlide = true; break; }
            }
            if ($isSlide) {
                $title = '';
                $link = '';
                $date = '';
                
                foreach (['h3', 'h4'] as $hTag) {
                    $h = $child->getElementsByTagName($hTag)->item(0);
                    if ($h) { 
                        $title = $this->getTextContent($h);
                        $titleLink = $h->getElementsByTagName('a')->item(0);
                        if ($titleLink) $link = $titleLink->getAttribute('href');
                        break; 
                    }
                }
                
                $img = $child->getElementsByTagName('img')->item(0);
                $image = $img ? $img->getAttribute('src') : '';
                
                $excerpt = '';
                $p = $child->getElementsByTagName('p')->item(0);
                if ($p) {
                    $excerpt = $this->getTextContent($p);
                    $showExcerpt = true;
                }
                
                // Extract date
                $dateEl = $this->findByClass($child, 'date') ?: $this->findByClass($child, 'time');
                if ($dateEl) {
                    $date = $this->getTextContent($dateEl);
                    $showDate = true;
                }
                $timeEl = $child->getElementsByTagName('time')->item(0);
                if ($timeEl) {
                    $date = $timeEl->getAttribute('datetime') ?: $this->getTextContent($timeEl);
                    $showDate = true;
                }
                
                if ($title || $image) {
                    $posts[] = [
                        'title' => $title,
                        'image' => $image,
                        'excerpt' => $excerpt,
                        'link' => $link,
                        'date' => $date
                    ];
                }
            }
        }
        
        // Check for autoplay
        $autoplay = $el->hasAttribute('data-autoplay') ? $el->getAttribute('data-autoplay') !== 'false' : true;
        
        $extracted = [
            'content' => [
                'posts' => $posts,
                'posts_count' => count($posts),
                'category' => '',
                'autoplay' => $autoplay
            ],
            'design' => array_merge($this->styleExtractor->extractDesign($el, $gs), [
                'show_excerpt' => $showExcerpt,
                'show_date' => $showDate
            ]),
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        return ModuleDefaults::mergeWithDefaults('post_slider', $extracted);
    }
    
    private function mapFullwidth(\DOMElement $el, array $gs): array
    {
        // Detect inner module type
        $innerType = 'code'; // default
        
        // Check for specific content types
        if ($el->getElementsByTagName('img')->length > 0 && $el->getElementsByTagName('img')->length < 3) {
            $innerType = 'image';
        } elseif ($this->isMap($el) || $el->getElementsByTagName('iframe')->length > 0) {
            $src = '';
            $iframe = $el->getElementsByTagName('iframe')->item(0);
            if ($iframe) $src = strtolower($iframe->getAttribute('src'));
            if (strpos($src, 'google.com/maps') !== false || strpos($src, 'maps.google') !== false) {
                $innerType = 'map';
            }
        } elseif ($this->isMenu($el) || $el->getElementsByTagName('nav')->length > 0) {
            $innerType = 'menu';
        } elseif ($this->isSlider($el)) {
            $innerType = 'slider';
        } elseif ($this->isPortfolio($el)) {
            $innerType = 'portfolio';
        } elseif ($this->isPostSlider($el)) {
            $innerType = 'post_slider';
        } elseif ($el->getElementsByTagName('header')->length > 0) {
            $innerType = 'header';
        }
        
        $fullwidthType = 'fullwidth_' . $innerType;
        
        // Get inner content based on type - delegate to specific mappers where possible
        $innerData = [];
        switch ($innerType) {
            case 'image':
                $img = $el->getElementsByTagName('img')->item(0);
                $link = '';
                $lazyLoad = false;
                // Check if image is wrapped in link
                if ($img && $img->parentNode && strtolower($img->parentNode->tagName) === 'a') {
                    $link = $img->parentNode->getAttribute('href');
                }
                if ($img && $img->getAttribute('loading') === 'lazy') {
                    $lazyLoad = true;
                }
                $innerData = [
                    'content' => [
                        'src' => $img ? $img->getAttribute('src') : '',
                        'alt' => $img ? $img->getAttribute('alt') : '',
                        'link' => $link,
                        'lazy_load' => $lazyLoad
                    ]
                ];
                break;
                
            case 'map':
                $iframe = $el->getElementsByTagName('iframe')->item(0);
                $embedUrl = $iframe ? $iframe->getAttribute('src') : '';
                $lat = '';
                $lng = '';
                $zoom = 14;
                // Extract coordinates from embed URL
                if ($embedUrl && preg_match('/@(-?[\d.]+),(-?[\d.]+),(\d+)z/', $embedUrl, $m)) {
                    $lat = $m[1];
                    $lng = $m[2];
                    $zoom = (int)$m[3];
                }
                $innerData = [
                    'content' => [
                        'embed_url' => $embedUrl,
                        'address' => $el->getAttribute('data-address') ?: '',
                        'lat' => $lat,
                        'lng' => $lng,
                        'zoom' => $zoom,
                        'marker_title' => $el->getAttribute('data-marker') ?: ''
                    ]
                ];
                break;
                
            case 'menu':
                $nav = $el->getElementsByTagName('nav')->item(0) ?: $el;
                $items = [];
                $title = '';
                // Get title
                foreach (['h3', 'h4'] as $hTag) {
                    $h = $el->getElementsByTagName($hTag)->item(0);
                    if ($h) { $title = $this->getTextContent($h); break; }
                }
                foreach ($nav->getElementsByTagName('a') as $a) {
                    $text = trim($this->getTextContent($a));
                    if ($text) {
                        $items[] = [
                            'text' => $text,
                            'url' => $a->getAttribute('href'),
                            'target' => $a->getAttribute('target') ?: '_self',
                            'children' => []
                        ];
                    }
                }
                $innerData = [
                    'content' => [
                        'items' => $items,
                        'title' => $title
                    ]
                ];
                break;
                
            case 'slider':
                $slides = [];
                $slidePatterns = ['slide', 'swiper-slide', 'carousel-item'];
                foreach ($el->childNodes as $child) {
                    if ($child->nodeType !== XML_ELEMENT_NODE) continue;
                    $class = strtolower($child->getAttribute('class'));
                    $isSlide = false;
                    foreach ($slidePatterns as $pattern) {
                        if (strpos($class, $pattern) !== false) { $isSlide = true; break; }
                    }
                    if ($isSlide) {
                        $img = $child->getElementsByTagName('img')->item(0);
                        $text = '';
                        $buttonText = '';
                        $buttonUrl = '';
                        $p = $child->getElementsByTagName('p')->item(0);
                        if ($p) $text = $this->getTextContent($p);
                        $btn = $this->findButton($child);
                        if ($btn) {
                            $buttonText = $this->getTextContent($btn);
                            $buttonUrl = $btn->getAttribute('href') ?: '#';
                        }
                        $slides[] = [
                            'image' => $img ? $img->getAttribute('src') : '',
                            'title' => '',
                            'text' => $text,
                            'button_text' => $buttonText,
                            'button_url' => $buttonUrl
                        ];
                    }
                }
                $innerData = [
                    'content' => [
                        'slides' => $slides,
                        'autoplay' => $el->hasAttribute('data-autoplay') ? $el->getAttribute('data-autoplay') !== 'false' : true,
                        'loop' => $el->hasAttribute('data-loop') ? $el->getAttribute('data-loop') !== 'false' : true,
                        'speed' => (int)($el->getAttribute('data-speed') ?: 500)
                    ]
                ];
                break;
                
            case 'portfolio':
                $items = [];
                $categories = [];
                foreach ($el->childNodes as $child) {
                    if ($child->nodeType !== XML_ELEMENT_NODE) continue;
                    $img = $child->getElementsByTagName('img')->item(0);
                    $title = '';
                    foreach (['h3', 'h4'] as $hTag) {
                        $h = $child->getElementsByTagName($hTag)->item(0);
                        if ($h) { $title = $this->getTextContent($h); break; }
                    }
                    $category = $this->findTextByClass($child, ['category', 'tag']);
                    if ($category && !in_array($category, $categories)) {
                        $categories[] = $category;
                    }
                    if ($img || $title) {
                        $link = '';
                        $a = $child->getElementsByTagName('a')->item(0);
                        if ($a) $link = $a->getAttribute('href');
                        $items[] = [
                            'image' => $img ? $img->getAttribute('src') : '',
                            'title' => $title,
                            'category' => $category,
                            'link' => $link,
                            'description' => ''
                        ];
                    }
                }
                $columns = min(4, max(1, count($items)));
                $innerData = [
                    'content' => [
                        'items' => $items,
                        'filter' => count($categories) > 1,
                        'categories' => $categories
                    ],
                    'design' => [
                        'columns' => $columns,
                        'columns_tablet' => min(2, $columns),
                        'columns_mobile' => 1
                    ]
                ];
                break;
                
            case 'post_slider':
                $posts = [];
                $slidePatterns = ['slide', 'swiper-slide', 'carousel-item'];
                foreach ($el->childNodes as $child) {
                    if ($child->nodeType !== XML_ELEMENT_NODE) continue;
                    $class = strtolower($child->getAttribute('class'));
                    $isSlide = false;
                    foreach ($slidePatterns as $pattern) {
                        if (strpos($class, $pattern) !== false) { $isSlide = true; break; }
                    }
                    if ($isSlide) {
                        $title = '';
                        foreach (['h3', 'h4'] as $hTag) {
                            $h = $child->getElementsByTagName($hTag)->item(0);
                            if ($h) { $title = $this->getTextContent($h); break; }
                        }
                        $img = $child->getElementsByTagName('img')->item(0);
                        $excerpt = '';
                        $p = $child->getElementsByTagName('p')->item(0);
                        if ($p) $excerpt = $this->getTextContent($p);
                        if ($title || $img) {
                            $posts[] = [
                                'title' => $title,
                                'image' => $img ? $img->getAttribute('src') : '',
                                'excerpt' => $excerpt,
                                'link' => '',
                                'date' => ''
                            ];
                        }
                    }
                }
                $innerData = [
                    'content' => [
                        'posts' => $posts,
                        'posts_count' => count($posts),
                        'category' => '',
                        'autoplay' => true
                    ]
                ];
                break;
                
            case 'header':
                // fullwidth_header uses hero defaults
                $heading = '';
                $subheading = '';
                $text = '';
                $buttonText = '';
                $buttonUrl = '';
                $backgroundImage = '';
                
                foreach (['h1', 'h2'] as $hTag) {
                    $h = $el->getElementsByTagName($hTag)->item(0);
                    if ($h) { $heading = $this->getTextContent($h); break; }
                }
                $h3 = $el->getElementsByTagName('h3')->item(0);
                if ($h3) $subheading = $this->getTextContent($h3);
                
                $p = $el->getElementsByTagName('p')->item(0);
                if ($p) $text = $this->getTextContent($p);
                
                $btn = $this->findButton($el);
                if ($btn) {
                    $buttonText = $this->getTextContent($btn);
                    $buttonUrl = $btn->getAttribute('href') ?: '#';
                }
                
                // Background from style
                $style = $el->getAttribute('style');
                if (preg_match('/background(?:-image)?\s*:\s*url\([\'\"]?([^\'\"\)]+)[\'\"]?\)/', $style, $m)) {
                    $backgroundImage = $m[1];
                }
                
                $innerData = [
                    'content' => [
                        'heading' => $heading,
                        'subheading' => $subheading,
                        'text' => $text,
                        'button_text' => $buttonText,
                        'button_url' => $buttonUrl,
                        'secondary_button_text' => '',
                        'secondary_button_url' => '',
                        'background_image' => $backgroundImage,
                        'video_url' => ''
                    ]
                ];
                break;
                
            default: // code
                $innerData = [
                    'content' => [
                        'code' => $this->getHtmlContent($el),
                        'language' => 'html'
                    ]
                ];
        }
        
        // Merge with base design
        $design = $this->styleExtractor->extractDesign($el, $gs);
        if (isset($innerData['design'])) {
            $design = array_merge($design, $innerData['design']);
        }
        
        $extracted = [
            'content' => $innerData['content'] ?? [],
            'design' => $design,
            'advanced' => [
                'css_class' => $el->getAttribute('class'),
                'css_id' => $el->getAttribute('id')
            ]
        ];
        
        return ModuleDefaults::mergeWithDefaults($fullwidthType, $extracted);
    }
}