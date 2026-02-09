<?php
/**
 * JTB AI Pexels Integration
 * Fetches high-quality stock images from Pexels API
 *
 * API key is stored in database: settings.pexels_api_key
 *
 * @package JessieThemeBuilder
 * @updated 2026-02-04 - Etap 2: Usunięto debug logi, ulepszono randomizację
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Pexels
{
    private static ?string $apiKey = null;
    private const API_URL = 'https://api.pexels.com/v1';

    // Cache for used photo IDs to avoid duplicates in same session
    private static array $usedPhotoIds = [];

    // ========================================
    // Configuration
    // ========================================

    /**
     * Get Pexels API key from database settings
     * @return string|null API key or null if not configured
     */
    private static function getApiKey(): ?string
    {
        if (self::$apiKey !== null) {
            return self::$apiKey ?: null;
        }

        // Check if Database class exists (CMS environment)
        if (!class_exists('\\core\\Database')) {
            self::$apiKey = '';
            return null;
        }

        // Priority 1: Check database (CMS settings)
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = 'pexels_api_key' LIMIT 1");
            $stmt->execute();
            $key = $stmt->fetchColumn();
            if (!empty($key)) {
                self::$apiKey = $key;
                return self::$apiKey;
            }
        } catch (\Exception $e) {
            // Continue to fallback
        }

        // Priority 2: Check central ai_settings.json (unified config)
        $settingsPath = CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true);
            if (!empty($settings['media']['pexels']['api_key'])) {
                self::$apiKey = $settings['media']['pexels']['api_key'];
                return self::$apiKey;
            }
        }

        self::$apiKey = '';
        return null;
    }

    /**
     * Check if Pexels is configured with valid API key
     * @return bool True if configured
     */
    public static function isConfigured(): bool
    {
        $key = self::getApiKey();
        return !empty($key) && strlen($key) > 20;
    }

    // ========================================
    // Search Methods
    // ========================================

    /**
     * Search for photos
     * @param string $query Search query
     * @param array $options Search options (per_page, page, orientation, size, color)
     * @return array Search results
     */
    public static function searchPhotos(string $query, array $options = []): array
    {
        $apiKey = self::getApiKey();
        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'Pexels API key not configured. Add pexels_api_key to settings table.'];
        }

        // Randomize page for variety (1-5)
        $randomPage = $options['page'] ?? random_int(1, 5);

        $params = [
            'query' => $query,
            'per_page' => $options['per_page'] ?? 15,
            'page' => $randomPage
        ];

        if (!empty($options['orientation'])) {
            $params['orientation'] = $options['orientation']; // landscape, portrait, square
        }

        if (!empty($options['size'])) {
            $params['size'] = $options['size']; // large, medium, small
        }

        if (!empty($options['color'])) {
            $params['color'] = $options['color'];
        }

        $url = self::API_URL . '/search?' . http_build_query($params);
        $result = self::makeRequest($url);

        if (!$result['ok']) {
            return $result;
        }

        $photos = self::formatPhotos($result['data']['photos'] ?? []);

        return [
            'ok' => true,
            'photos' => $photos,
            'total_results' => $result['data']['total_results'] ?? 0,
            'page' => $result['data']['page'] ?? 1,
            'per_page' => $result['data']['per_page'] ?? 15
        ];
    }

    /**
     * Get curated photos
     * @param array $options Options (per_page, page)
     * @return array Curated photos
     */
    public static function getCurated(array $options = []): array
    {
        $apiKey = self::getApiKey();
        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'Pexels API key not configured'];
        }

        $params = [
            'per_page' => $options['per_page'] ?? 15,
            'page' => $options['page'] ?? random_int(1, 10)
        ];

        $url = self::API_URL . '/curated?' . http_build_query($params);
        $result = self::makeRequest($url);

        if (!$result['ok']) {
            return $result;
        }

        return [
            'ok' => true,
            'photos' => self::formatPhotos($result['data']['photos'] ?? [])
        ];
    }

    /**
     * Get a specific photo by ID
     * @param int $photoId Photo ID
     * @return array Photo data
     */
    public static function getPhoto(int $photoId): array
    {
        $apiKey = self::getApiKey();
        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'Pexels API key not configured'];
        }

        $url = self::API_URL . '/photos/' . $photoId;
        $result = self::makeRequest($url);

        if (!$result['ok']) {
            return $result;
        }

        return [
            'ok' => true,
            'photo' => self::formatPhoto($result['data'])
        ];
    }

    // ========================================
    // Contextual Image Fetching
    // ========================================

    /**
     * Get hero image based on industry/context
     * @param array $context Context with industry, style, business_keywords
     * @return array|null Photo data or null
     */
    public static function getHeroImage(array $context): ?array
    {
        $industry = $context['industry'] ?? 'business';
        $businessKeywords = $context['business_keywords'] ?? '';

        $queries = self::getHeroQueries($industry);

        // Add business-specific query if provided
        if (!empty($businessKeywords)) {
            array_unshift($queries, $businessKeywords . ' professional');
            array_unshift($queries, $businessKeywords);
        }

        // Shuffle for randomness
        shuffle($queries);
        $query = $queries[0];

        $result = self::searchPhotos($query, [
            'orientation' => 'landscape',
            'size' => 'large',
            'per_page' => 20
        ]);

        if ($result['ok'] && !empty($result['photos'])) {
            $photo = self::pickUniquePhoto($result['photos']);
            if ($photo) {
                return [
                    'ok' => true,
                    'url' => $photo['src']['large2x'] ?? $photo['src']['large'],
                    'url_medium' => $photo['src']['medium'],
                    'url_small' => $photo['src']['small'],
                    'alt' => $photo['alt'] ?: $query,
                    'photographer' => $photo['photographer'],
                    'pexels_url' => $photo['url']
                ];
            }
        }

        return null;
    }

    /**
     * Get team/person photo
     * @param array $context Context with gender, role
     * @return array|null Photo data or null
     */
    public static function getPersonPhoto(array $context = []): ?array
    {
        $gender = $context['gender'] ?? '';

        // Large variety of queries for different people
        $queries = [
            'professional portrait business',
            'corporate headshot smiling',
            'business person portrait confident',
            'office worker portrait professional',
            'entrepreneur portrait modern',
            'executive portrait professional',
            'happy professional portrait',
            'young professional headshot',
            'senior professional portrait',
            'diverse professional portrait',
            'startup founder portrait',
            'consultant headshot professional',
            'manager portrait business confident'
        ];

        if ($gender === 'female') {
            $queries = [
                'professional woman portrait smiling',
                'business woman headshot confident',
                'female entrepreneur portrait',
                'woman executive portrait professional',
                'confident business woman portrait',
                'female professional headshot modern',
                'woman manager portrait',
                'professional woman office'
            ];
        } elseif ($gender === 'male') {
            $queries = [
                'professional man portrait smiling',
                'business man headshot confident',
                'male entrepreneur portrait',
                'man executive portrait professional',
                'confident business man portrait',
                'male professional headshot modern',
                'man manager portrait',
                'professional man office'
            ];
        }

        shuffle($queries);
        $query = $queries[0];

        $result = self::searchPhotos($query, [
            'orientation' => 'portrait',
            'size' => 'medium',
            'per_page' => 25
        ]);

        if ($result['ok'] && !empty($result['photos'])) {
            $photo = self::pickUniquePhoto($result['photos']);
            if ($photo) {
                return [
                    'ok' => true,
                    'url' => $photo['src']['medium'],
                    'url_large' => $photo['src']['large'],
                    'url_small' => $photo['src']['small'],
                    'alt' => $photo['alt'] ?: 'Team member',
                    'photographer' => $photo['photographer']
                ];
            }
        }

        return null;
    }

    /**
     * Get feature/service image
     * @param array $context Context with feature, industry
     * @return array|null Photo data or null
     */
    public static function getFeatureImage(array $context = []): ?array
    {
        $feature = $context['feature'] ?? 'business';
        $industry = $context['industry'] ?? 'general';

        $queries = self::getFeatureQueries($feature, $industry);
        shuffle($queries);
        $query = $queries[0];

        $result = self::searchPhotos($query, [
            'orientation' => 'landscape',
            'size' => 'medium',
            'per_page' => 20
        ]);

        if ($result['ok'] && !empty($result['photos'])) {
            $photo = self::pickUniquePhoto($result['photos']);
            if ($photo) {
                return [
                    'ok' => true,
                    'url' => $photo['src']['medium'],
                    'url_large' => $photo['src']['large'],
                    'alt' => $photo['alt'] ?: $feature
                ];
            }
        }

        return null;
    }

    /**
     * Get about section image
     * @param array $context Context data
     * @return array|null Photo data or null
     */
    public static function getAboutImage(array $context = []): ?array
    {
        $queries = [
            'team meeting office modern',
            'business collaboration teamwork',
            'startup team working together',
            'modern office workspace creative',
            'team brainstorming session',
            'creative agency team meeting',
            'professional team discussion',
            'office workers collaborating'
        ];

        shuffle($queries);
        $query = $queries[0];

        $result = self::searchPhotos($query, [
            'orientation' => 'landscape',
            'size' => 'large',
            'per_page' => 15
        ]);

        if ($result['ok'] && !empty($result['photos'])) {
            $photo = self::pickUniquePhoto($result['photos']);
            if ($photo) {
                return [
                    'ok' => true,
                    'url' => $photo['src']['large'],
                    'alt' => $photo['alt'] ?: 'About us'
                ];
            }
        }

        return null;
    }

    /**
     * Get gallery images
     * @param array $context Context data
     * @param int $count Number of images
     * @return array|null Photos data or null
     */
    public static function getGalleryImages(array $context = [], int $count = 6): ?array
    {
        $industry = $context['industry'] ?? 'business';
        $queries = self::getGalleryQueries($industry);
        shuffle($queries);
        $query = $queries[0];

        $result = self::searchPhotos($query, [
            'per_page' => $count + 10 // Get extra for variety
        ]);

        if ($result['ok'] && !empty($result['photos'])) {
            $photos = [];
            $available = $result['photos'];
            shuffle($available);

            foreach (array_slice($available, 0, $count) as $photo) {
                $photos[] = [
                    'url' => $photo['src']['large'],
                    'url_medium' => $photo['src']['medium'],
                    'alt' => $photo['alt'] ?: 'Gallery image'
                ];
                self::$usedPhotoIds[] = $photo['id'];
            }

            return [
                'ok' => true,
                'images' => $photos
            ];
        }

        return null;
    }

    /**
     * Get background image
     * @param array $context Context data
     * @return array|null Photo data or null
     */
    public static function getBackgroundImage(array $context = []): ?array
    {
        $type = $context['background_type'] ?? 'abstract';

        $queries = [
            'abstract' => ['abstract background minimal', 'geometric pattern subtle', 'minimal background design'],
            'nature' => ['nature landscape scenic', 'forest background peaceful', 'mountain landscape beautiful'],
            'city' => ['city skyline modern', 'urban architecture night', 'cityscape aerial'],
            'texture' => ['paper texture subtle', 'concrete texture minimal', 'marble texture elegant'],
            'gradient' => ['colorful gradient abstract', 'blue gradient background', 'purple gradient smooth']
        ];

        $queryList = $queries[$type] ?? $queries['abstract'];
        shuffle($queryList);
        $query = $queryList[0];

        $result = self::searchPhotos($query, [
            'orientation' => 'landscape',
            'size' => 'large',
            'per_page' => 15
        ]);

        if ($result['ok'] && !empty($result['photos'])) {
            $photo = self::pickUniquePhoto($result['photos']);
            if ($photo) {
                return [
                    'ok' => true,
                    'url' => $photo['src']['original'],
                    'url_large' => $photo['src']['large2x'],
                    'alt' => 'Background'
                ];
            }
        }

        return null;
    }

    // ========================================
    // Query Builders
    // ========================================

    /**
     * Get hero queries for industry
     */
    private static function getHeroQueries(string $industry): array
    {
        $queries = [
            'technology' => [
                'technology workspace modern', 'software developer coding', 'tech startup office',
                'digital innovation', 'modern tech office', 'computer programming team',
                'data center servers', 'tech team meeting professional'
            ],
            'healthcare' => [
                'modern medical facility', 'doctor patient consultation', 'healthcare professional',
                'medical technology', 'hospital care', 'medical clinic interior modern',
                'nurse helping patient', 'medical team professional'
            ],
            'ecommerce' => [
                'online shopping delivery', 'ecommerce packaging', 'product delivery happy',
                'retail modern store', 'shopping experience customer', 'warehouse logistics',
                'happy customer package delivery'
            ],
            'agency' => [
                'creative agency team', 'design studio workspace', 'marketing team meeting',
                'creative brainstorming session', 'advertising agency modern',
                'graphic designer working', 'modern creative office'
            ],
            'education' => [
                'modern classroom learning', 'students engaged learning', 'education technology',
                'university campus modern', 'online learning student', 'teacher students classroom',
                'library studying focused'
            ],
            'restaurant' => [
                'fine dining restaurant elegant', 'chef cooking kitchen professional',
                'gourmet food plating', 'restaurant interior modern', 'food preparation chef',
                'delicious meal plate presentation', 'cafe coffee shop cozy'
            ],
            'real_estate' => [
                'luxury home interior modern', 'modern architecture house', 'real estate property beautiful',
                'beautiful living room interior', 'dream home exterior', 'apartment building modern',
                'house garden backyard beautiful'
            ],
            'fitness' => [
                'fitness gym modern equipment', 'athlete training workout', 'workout exercise gym',
                'healthy lifestyle fitness', 'personal trainer gym', 'yoga meditation peaceful',
                'running jogging outdoor nature'
            ],
            'legal' => [
                'lawyer office professional', 'law firm modern interior', 'legal consultation meeting',
                'attorney meeting client', 'courtroom justice', 'legal professional confident'
            ],
            'construction' => [
                'construction workers building', 'construction site modern', 'building construction project',
                'contractor working professional', 'home construction project', 'house renovation work',
                'builder team working'
            ],
            'general' => [
                'modern business office professional', 'professional team meeting success',
                'corporate workspace modern', 'business success teamwork', 'professional environment office',
                'office workers happy productive', 'startup team collaboration',
                'business handshake deal success'
            ]
        ];

        return $queries[$industry] ?? $queries['general'];
    }

    /**
     * Get feature queries
     */
    private static function getFeatureQueries(string $feature, string $industry): array
    {
        // Industry-specific features
        $industryFeatures = [
            'technology' => [
                'coding programming computer', 'tech workspace modern', 'data analytics dashboard',
                'software development team', 'server room technology', 'digital innovation creative'
            ],
            'healthcare' => [
                'medical care professional', 'healthcare technology modern', 'patient care doctor',
                'medical equipment modern', 'health wellness lifestyle'
            ],
            'restaurant' => [
                'delicious food plate', 'restaurant kitchen chef', 'food presentation gourmet',
                'dining experience elegant', 'fresh ingredients cooking'
            ],
            'fitness' => [
                'gym workout exercise', 'fitness training personal', 'exercise equipment modern',
                'personal training gym', 'yoga class peaceful', 'healthy lifestyle active'
            ]
        ];

        if (isset($industryFeatures[$industry])) {
            return $industryFeatures[$industry];
        }

        // Feature keyword mapping
        $featureMap = [
            'support' => ['customer support service', 'help desk professional', 'customer service team'],
            'quality' => ['quality control excellence', 'premium quality product', 'excellence achievement'],
            'speed' => ['fast delivery service', 'quick service efficient', 'efficiency productivity'],
            'security' => ['data security protection', 'cyber security technology', 'protection shield'],
            'innovation' => ['innovation technology creative', 'creative ideas brainstorm', 'breakthrough innovation'],
            'team' => ['team collaboration meeting', 'teamwork success', 'professional team working'],
            'global' => ['global business world', 'worldwide international', 'international business'],
            'analytics' => ['data analytics charts', 'business intelligence', 'metrics dashboard']
        ];

        foreach ($featureMap as $key => $queries) {
            if (stripos($feature, $key) !== false) {
                return $queries;
            }
        }

        // Default fallback
        return [
            'business professional service', 'modern office work', 'professional service quality',
            'team collaboration success', 'customer satisfaction happy'
        ];
    }

    /**
     * Get gallery queries
     */
    private static function getGalleryQueries(string $industry): array
    {
        $queries = [
            'technology' => ['technology workspace computers modern', 'tech office modern'],
            'healthcare' => ['medical healthcare hospital modern', 'healthcare professional'],
            'ecommerce' => ['products shopping retail modern', 'ecommerce products'],
            'agency' => ['creative design studio work', 'creative agency'],
            'restaurant' => ['food restaurant dishes gourmet', 'restaurant food'],
            'real_estate' => ['interior design homes modern', 'home interior'],
            'fitness' => ['gym fitness workout equipment', 'fitness gym'],
            'general' => ['business professional office modern', 'professional work']
        ];

        return $queries[$industry] ?? $queries['general'];
    }

    // ========================================
    // API Communication
    // ========================================

    /**
     * Make HTTP request to Pexels API
     */
    private static function makeRequest(string $url): array
    {
        $apiKey = self::getApiKey();
        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'API key not configured'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['ok' => false, 'error' => 'Connection error: ' . $error];
        }

        if ($httpCode === 401) {
            return ['ok' => false, 'error' => 'Invalid Pexels API key'];
        }

        if ($httpCode !== 200) {
            return ['ok' => false, 'error' => 'Pexels API error: HTTP ' . $httpCode];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['ok' => false, 'error' => 'Invalid JSON response from Pexels'];
        }

        return ['ok' => true, 'data' => $data];
    }

    // ========================================
    // Data Formatting
    // ========================================

    /**
     * Format array of photos
     */
    private static function formatPhotos(array $photos): array
    {
        return array_map([self::class, 'formatPhoto'], $photos);
    }

    /**
     * Format single photo
     */
    private static function formatPhoto(array $photo): array
    {
        return [
            'id' => $photo['id'] ?? null,
            'width' => $photo['width'] ?? 0,
            'height' => $photo['height'] ?? 0,
            'url' => $photo['url'] ?? '',
            'photographer' => $photo['photographer'] ?? 'Unknown',
            'photographer_url' => $photo['photographer_url'] ?? '',
            'avg_color' => $photo['avg_color'] ?? '#CCCCCC',
            'alt' => $photo['alt'] ?? '',
            'src' => [
                'original' => $photo['src']['original'] ?? '',
                'large2x' => $photo['src']['large2x'] ?? '',
                'large' => $photo['src']['large'] ?? '',
                'medium' => $photo['src']['medium'] ?? '',
                'small' => $photo['src']['small'] ?? '',
                'portrait' => $photo['src']['portrait'] ?? '',
                'landscape' => $photo['src']['landscape'] ?? '',
                'tiny' => $photo['src']['tiny'] ?? ''
            ]
        ];
    }

    /**
     * Pick a unique photo that hasn't been used in this session
     */
    private static function pickUniquePhoto(array $photos): ?array
    {
        // Shuffle for randomness
        shuffle($photos);

        foreach ($photos as $photo) {
            $id = $photo['id'] ?? null;
            if ($id && !in_array($id, self::$usedPhotoIds)) {
                self::$usedPhotoIds[] = $id;
                return $photo;
            }
        }

        // If all photos used, return first one anyway
        if (!empty($photos)) {
            return $photos[0];
        }

        return null;
    }

    /**
     * Clear used photo cache (call between different websites)
     */
    public static function clearUsedPhotosCache(): void
    {
        self::$usedPhotoIds = [];
    }

    // ========================================
    // Download & Save
    // ========================================

    /**
     * Download image from Pexels and save locally
     * @param string $imageUrl Pexels image URL
     * @param array $metadata Image metadata
     * @return array Result with local URL
     */
    public static function downloadAndSave(string $imageUrl, array $metadata = []): array
    {
        try {
            // Download image
            $imageData = @file_get_contents($imageUrl);
            if ($imageData === false) {
                return ['ok' => false, 'error' => 'Failed to download image from Pexels'];
            }

            // Determine extension from mime type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
            $extension = 'jpg';
            if (strpos($mimeType, 'png') !== false) {
                $extension = 'png';
            } elseif (strpos($mimeType, 'webp') !== false) {
                $extension = 'webp';
            }

            // Generate unique filename
            $filename = 'pexels_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

            // Create directory
            $mediaPath = CMS_ROOT . '/media/pexels';
            if (!is_dir($mediaPath)) {
                mkdir($mediaPath, 0755, true);
            }

            $filePath = $mediaPath . '/' . $filename;
            $relativeUrl = '/media/pexels/' . $filename;

            // Save file
            if (file_put_contents($filePath, $imageData) === false) {
                return ['ok' => false, 'error' => 'Failed to save image to disk'];
            }

            // Get dimensions
            $imageInfo = @getimagesize($filePath);
            $width = $imageInfo[0] ?? 0;
            $height = $imageInfo[1] ?? 0;

            return [
                'ok' => true,
                'url' => $relativeUrl,
                'filename' => $filename,
                'width' => $width,
                'height' => $height,
                'mime_type' => $mimeType
            ];

        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
