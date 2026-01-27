<?php
declare(strict_types=1);
/**
 * AI Designer - Image Fetcher
 * 
 * Fetches high-quality images from Pexels and Unsplash APIs.
 * Replaces PLACEHOLDER:description tags with real image URLs.
 *
 * @package AiDesigner
 * @version 4.0
 */

namespace Core\AiDesigner;

class ImageFetcher
{
    private string $pexelsApiKey;
    private string $unsplashAccessKey;
    private string $defaultSource;
    private string $cachePath;
    private array $cache = [];
    
    // Image size presets
    private array $sizes = [
        'hero' => ['width' => 1920, 'height' => 1080],
        'feature' => ['width' => 800, 'height' => 600],
        'card' => ['width' => 600, 'height' => 400],
        'thumbnail' => ['width' => 400, 'height' => 300],
        'avatar' => ['width' => 200, 'height' => 200],
        'gallery' => ['width' => 800, 'height' => 600],
        'blog' => ['width' => 1200, 'height' => 630]
    ];

    public function __construct(array $config = [])
    {
        $this->pexelsApiKey = $config['pexels_api_key'] ?? '';
        $this->unsplashAccessKey = $config['unsplash_access_key'] ?? '';
        $this->defaultSource = $config['default_source'] ?? 'pexels';
        $this->cachePath = $config['cache_path'] ?? sys_get_temp_dir() . '/ai-designer-images';
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Process HTML and replace all PLACEHOLDER:description with real images
     */
    public function processHtml(string $html, string $industry = 'business'): string
    {
        // Find all PLACEHOLDER:description patterns
        preg_match_all('/PLACEHOLDER:([^"\']+)/i', $html, $matches);
        
        if (empty($matches[0])) {
            return $html;
        }
        
        $replacements = [];
        
        foreach ($matches[1] as $index => $description) {
            $placeholder = $matches[0][$index];
            
            if (!isset($replacements[$placeholder])) {
                // Determine image size from context
                $size = $this->detectSizeFromDescription($description);
                
                // Fetch image
                $imageUrl = $this->fetchImage($description, $industry, $size);
                
                $replacements[$placeholder] = $imageUrl;
            }
        }
        
        // Replace all placeholders
        foreach ($replacements as $placeholder => $url) {
            $html = str_replace($placeholder, $url, $html);
        }
        
        return $html;
    }

    /**
     * Fetch single image from API
     */
    public function fetchImage(string $query, string $industry = '', string $size = 'feature'): string
    {
        // Build search query
        $searchQuery = $this->buildSearchQuery($query, $industry);
        
        // Check cache first
        $cacheKey = md5($searchQuery . $size);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // Try to get from file cache
        $cachedUrl = $this->getFromFileCache($cacheKey);
        if ($cachedUrl) {
            $this->cache[$cacheKey] = $cachedUrl;
            return $cachedUrl;
        }
        
        // Fetch from API
        $imageUrl = '';
        
        if ($this->defaultSource === 'pexels' && !empty($this->pexelsApiKey)) {
            $imageUrl = $this->fetchFromPexels($searchQuery, $size);
        }
        
        if (empty($imageUrl) && !empty($this->unsplashAccessKey)) {
            $imageUrl = $this->fetchFromUnsplash($searchQuery, $size);
        }
        
        // Fallback to placeholder service
        if (empty($imageUrl)) {
            $imageUrl = $this->getFallbackImage($searchQuery, $size);
        }
        
        // Cache the result
        $this->cache[$cacheKey] = $imageUrl;
        $this->saveToFileCache($cacheKey, $imageUrl);
        
        return $imageUrl;
    }

    /**
     * Build optimized search query
     */
    private function buildSearchQuery(string $description, string $industry): string
    {
        // Clean description
        $query = strtolower(trim($description));
        
        // Remove common words
        $removeWords = ['image', 'photo', 'picture', 'of', 'a', 'an', 'the', 'for', 'with', 'and', 'professional'];
        $words = explode(' ', $query);
        $words = array_filter($words, fn($w) => !in_array($w, $removeWords) && strlen($w) > 2);
        $query = implode(' ', $words);
        
        // Add industry context if helpful
        $industryKeywords = $this->getIndustryKeywords($industry);
        if (!empty($industryKeywords) && strpos($query, $industry) === false) {
            // Only add if query is generic
            if (in_array($query, ['team', 'office', 'work', 'service', 'business'])) {
                $query .= ' ' . $industryKeywords[0];
            }
        }
        
        return trim($query);
    }

    /**
     * Get industry-specific keywords for better image search
     */
    private function getIndustryKeywords(string $industry): array
    {
        $keywords = [
            'restaurant' => ['food', 'dining', 'restaurant', 'chef', 'cuisine'],
            'technology' => ['tech', 'computer', 'software', 'digital', 'coding'],
            'healthcare' => ['medical', 'health', 'doctor', 'hospital', 'care'],
            'fitness' => ['gym', 'workout', 'exercise', 'fitness', 'training'],
            'spa' => ['spa', 'wellness', 'relaxation', 'massage', 'beauty'],
            'hotel' => ['hotel', 'hospitality', 'room', 'luxury', 'accommodation'],
            'realestate' => ['property', 'house', 'home', 'real estate', 'interior'],
            'cafe' => ['coffee', 'cafe', 'barista', 'espresso', 'cozy'],
            'salon' => ['hair', 'salon', 'beauty', 'styling', 'hairdresser'],
            'photography' => ['camera', 'photography', 'studio', 'photographer', 'lens'],
            'wedding' => ['wedding', 'bride', 'celebration', 'ceremony', 'love'],
            'finance' => ['finance', 'money', 'banking', 'investment', 'business'],
            'education' => ['education', 'learning', 'school', 'student', 'classroom'],
            'construction' => ['construction', 'building', 'architecture', 'workers', 'site']
        ];
        
        return $keywords[$industry] ?? ['business', 'professional'];
    }

    /**
     * Detect appropriate size from description context
     */
    private function detectSizeFromDescription(string $description): string
    {
        $description = strtolower($description);
        
        if (strpos($description, 'hero') !== false || strpos($description, 'banner') !== false) {
            return 'hero';
        }
        if (strpos($description, 'headshot') !== false || strpos($description, 'avatar') !== false || strpos($description, 'person') !== false) {
            return 'avatar';
        }
        if (strpos($description, 'thumbnail') !== false) {
            return 'thumbnail';
        }
        if (strpos($description, 'blog') !== false || strpos($description, 'post') !== false) {
            return 'blog';
        }
        if (strpos($description, 'gallery') !== false || strpos($description, 'portfolio') !== false) {
            return 'gallery';
        }
        
        return 'feature';
    }

    /**
     * Fetch from Pexels API
     */
    private function fetchFromPexels(string $query, string $size): string
    {
        if (empty($this->pexelsApiKey)) {
            return '';
        }
        
        $url = 'https://api.pexels.com/v1/search?' . http_build_query([
            'query' => $query,
            'per_page' => 5,
            'orientation' => $size === 'avatar' ? 'square' : 'landscape'
        ]);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->pexelsApiKey
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("[ImageFetcher] Pexels API error: HTTP {$httpCode}");
            return '';
        }
        
        $data = json_decode($response, true);
        
        if (empty($data['photos'])) {
            return '';
        }
        
        // Get random photo from results
        $photo = $data['photos'][array_rand($data['photos'])];
        
        // Get appropriate size
        $dimensions = $this->sizes[$size] ?? $this->sizes['feature'];
        
        // Pexels provides src.original, src.large, src.medium, src.small, src.tiny
        // Use their resize API for exact dimensions
        $baseUrl = $photo['src']['original'];
        $imageUrl = $baseUrl . '?auto=compress&cs=tinysrgb&w=' . $dimensions['width'] . '&h=' . $dimensions['height'] . '&fit=crop';
        
        return $imageUrl;
    }

    /**
     * Fetch from Unsplash API
     */
    private function fetchFromUnsplash(string $query, string $size): string
    {
        if (empty($this->unsplashAccessKey)) {
            return '';
        }
        
        $url = 'https://api.unsplash.com/search/photos?' . http_build_query([
            'query' => $query,
            'per_page' => 5,
            'orientation' => $size === 'avatar' ? 'squarish' : 'landscape'
        ]);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: Client-ID ' . $this->unsplashAccessKey
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("[ImageFetcher] Unsplash API error: HTTP {$httpCode}");
            return '';
        }
        
        $data = json_decode($response, true);
        
        if (empty($data['results'])) {
            return '';
        }
        
        // Get random photo from results
        $photo = $data['results'][array_rand($data['results'])];
        
        // Get appropriate size
        $dimensions = $this->sizes[$size] ?? $this->sizes['feature'];
        
        // Unsplash uses imgix for transformations
        $baseUrl = $photo['urls']['raw'];
        $imageUrl = $baseUrl . '&w=' . $dimensions['width'] . '&h=' . $dimensions['height'] . '&fit=crop&auto=format';
        
        return $imageUrl;
    }

    /**
     * Get fallback placeholder image
     */
    private function getFallbackImage(string $query, string $size): string
    {
        $dimensions = $this->sizes[$size] ?? $this->sizes['feature'];
        $width = $dimensions['width'];
        $height = $dimensions['height'];
        
        // Use placeholder.com as fallback
        $text = urlencode(substr($query, 0, 20));
        return "https://via.placeholder.com/{$width}x{$height}/e5e5e5/666666?text={$text}";
    }

    /**
     * Get from file cache
     */
    private function getFromFileCache(string $key): ?string
    {
        $cacheFile = $this->cachePath . '/' . $key . '.txt';
        
        if (file_exists($cacheFile)) {
            // Check if cache is still valid (24 hours)
            if (time() - filemtime($cacheFile) < 86400) {
                return file_get_contents($cacheFile);
            }
        }
        
        return null;
    }

    /**
     * Save to file cache
     */
    private function saveToFileCache(string $key, string $url): void
    {
        $cacheFile = $this->cachePath . '/' . $key . '.txt';
        file_put_contents($cacheFile, $url);
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
        
        $files = glob($this->cachePath . '/*.txt');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Download image and save locally
     */
    public function downloadImage(string $url, string $savePath): bool
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || empty($imageData)) {
            return false;
        }
        
        // Ensure directory exists
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return file_put_contents($savePath, $imageData) !== false;
    }

    /**
     * Fetch multiple images for a page
     */
    public function fetchImagesForPage(array $sections, string $industry): array
    {
        $images = [];
        
        foreach ($sections as $section) {
            $sectionType = $section['type'] ?? 'content';
            
            switch ($sectionType) {
                case 'hero':
                    $images['hero'] = $this->fetchImage('hero banner ' . $industry, $industry, 'hero');
                    break;
                    
                case 'about':
                case 'about-preview':
                    $images['about'] = $this->fetchImage('team working together', $industry, 'feature');
                    break;
                    
                case 'testimonials':
                    for ($i = 1; $i <= 3; $i++) {
                        $images["testimonial_{$i}"] = $this->fetchImage('professional headshot', '', 'avatar');
                    }
                    break;
                    
                case 'team':
                case 'team-preview':
                    for ($i = 1; $i <= 4; $i++) {
                        $images["team_{$i}"] = $this->fetchImage('business professional portrait', '', 'avatar');
                    }
                    break;
                    
                case 'gallery':
                case 'portfolio':
                    for ($i = 1; $i <= 6; $i++) {
                        $images["gallery_{$i}"] = $this->fetchImage($industry . ' work project', $industry, 'gallery');
                    }
                    break;
                    
                case 'blog':
                case 'posts':
                    for ($i = 1; $i <= 3; $i++) {
                        $images["blog_{$i}"] = $this->fetchImage($industry . ' article', $industry, 'blog');
                    }
                    break;
                    
                case 'services':
                    for ($i = 1; $i <= 3; $i++) {
                        $images["service_{$i}"] = $this->fetchImage($industry . ' service', $industry, 'card');
                    }
                    break;
            }
        }
        
        return $images;
    }

    /**
     * Check if APIs are configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->pexelsApiKey) || !empty($this->unsplashAccessKey);
    }

    /**
     * Get configuration status
     */
    public function getStatus(): array
    {
        return [
            'pexels_configured' => !empty($this->pexelsApiKey),
            'unsplash_configured' => !empty($this->unsplashAccessKey),
            'default_source' => $this->defaultSource,
            'cache_path' => $this->cachePath
        ];
    }
}
