<?php
/**
 * SEO Assistant Helper Class
 * Provides utility methods for SEO operations
 */
class SEOAssistant {
    public static function generateSlug($string) {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    public static function generateMetaDescription($content, $maxLength = 160) {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);
        return substr($content, 0, $maxLength);
    }

    public static function getHeaderStructure($html) {
        preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $html, $matches);
        $structure = [];
        foreach ($matches[1] as $i => $level) {
            $structure[] = [
                'level' => $level,
                'text' => trim(strip_tags($matches[2][$i]))
            ];
        }
        return $structure;
    }

    public static function checkBrokenLinks($html) {
        preg_match_all('/
<a[^>]+href=([\'"])(.*?)\1[^>]*>/i',
 $html, $matches);
        return $matches[2] ?? [];
    }
}
