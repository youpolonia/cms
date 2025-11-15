<?php
/**
 * Template Processor with SEO helpers
 */
class TemplateProcessor {
    private $seoService;

    public function __construct() {
        $this->seoService = new SeoService();
    }

    /**
     * Render SEO meta tags for template
     */
    public function seo_meta_tags(array $contentData): string {
        $tags = $this->seoService->generateMetaTags($contentData);
        $output = '';
        
        foreach ($tags as $name => $content) {
            if (strpos($name, 'og:') === 0) {
                $output .= sprintf('
<meta property="%s" content="%s">', 
                    htmlspecialchars(
$name),
                    htmlspecialchars($content)
                );
            } else {
                $output .= sprintf('
<meta name="%s" content="%s">', 
                    htmlspecialchars(
$name),
                    htmlspecialchars($content)
                );
            }
            $output .= "\n";
        }
        
        return $output;
    }

    /**
     * Render structured data (JSON-LD)
     */
    public function structured_data(array $contentData): string {
        if (empty($contentData)) {
            return '';
        }
        
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $contentData['title'] ?? '',
            'description' => $contentData['description'] ?? '',
            'datePublished' => $contentData['published_date'] ?? '',
            'author' => [
                '@type' => 'Person',
                'name' => $contentData['author'] ?? ''
            ]
        ];
        
        return '
<script type="application/ld+json">' . 
            json_encode(
$data, JSON_UNESCAPED_SLASHES) . 
            '</script>';
    }
}
