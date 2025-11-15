<?php
/**
 * Homepage Content Loader
 * Supports multiple content types with dynamic loading
 */

declare(strict_types=1);

class HomepageContentLoader {
    private const CONTENT_TYPES = [
        'text' => 'loadTextContent',
        'featured_posts' => 'loadFeaturedPosts',
        'featured_gallery' => 'loadFeaturedGallery',
        // Add more content types here
    ];

    public static function load(array $config): string {
        $output = '';
        
        try {
            foreach ($config['content'] as $contentItem) {
                $type = $contentItem['type'] ?? 'text';
                
                if (isset(self::CONTENT_TYPES[$type])) {
                    $method = self::CONTENT_TYPES[$type];
                    $output .= self::$method($contentItem);
                } else {
                    $output .= self::loadTextContent($contentItem);
                }
            }
        } catch (Exception $e) {
            error_log("Homepage content loading failed: " . $e->getMessage());
            $output = self::loadFallbackContent();
        }

        return $output;
    }

    private static function loadTextContent(array $config): string {
        return sprintf(
            '
<div class="text-content">%s</div>',
            htmlspecialchars(
$config['text'] ?? '', ENT_QUOTES, 'UTF-8')
        );
    }

    private static function loadFeaturedPosts(array $config): string {
        $limit = min($config['limit'] ?? 3, 10);
        $posts = [
            ['id' => 1, 'title' => 'First Post', 'excerpt' => 'This is the first blog post'],
            ['id' => 2, 'title' => 'Second Post', 'excerpt' => 'Another interesting post'],
            ['id' => 3, 'title' => 'Third Post', 'excerpt' => 'More content for our blog']
        ];
        
        $html = '
<div class="featured-posts">';
        $html .= '
<h2>' . htmlspecialchars($config['title'] ?? 'Latest Updates') . '</h2>';
        $html .= '
<div class="post-list">';
        
        foreach ($posts as $post) {
            $html .= '
<article class="post blog-post">';
            $html .= '
<h3>' . htmlspecialchars($post['title']) . '</h3>';
            $html .= '
<p>' . htmlspecialchars(
$post['excerpt']) . '</p>';
            $html .= '
<a href="?action=blog&id=' . $post['id'] . '">Read More</a>';
            $html .= '
</article>';
        }
        
        $html .= '</div></div>';
        return $html;
    }

    private static function loadFeaturedGallery(array $config): string {
        $limit = min($config['limit'] ?? 3, 10);
        $galleries = [
            [
                'id' => '1',
                'title' => 'Nature Gallery',
                'description' => 'Beautiful nature scenes',
                'images' => [
                    ['src' => '/media/nature1.jpg', 'alt' => 'Mountain view'],
                    ['src' => '/media/nature2.jpg', 'alt' => 'Forest path']
                ],
                'date' => '2025-06-17'
            ],
            [
                'id' => '2',
                'title' => 'City Gallery',
                'description' => 'Urban landscapes',
                'images' => [
                    ['src' => '/media/city1.jpg', 'alt' => 'Skyline'],
                    ['src' => '/media/city2.jpg', 'alt' => 'Street view']
                ],
                'date' => '2025-06-16'
            ]
        ];
        
        $html = '
<div class="featured-gallery">';
        $html .= '
<h2>' . htmlspecialchars($config['title'] ?? 'Featured Galleries') . '</h2>';
        $html .= '
<div class="gallery-list">';
        
        foreach ($galleries as $gallery) {
            $html .= '
<div class="gallery-item">';
            $html .= '
<h3>' . htmlspecialchars($gallery['title']) . '</h3>';
            $html .= '
<div class="gallery-preview">';
            $html .= '<img src="' . htmlspecialchars($gallery['images'][0]['src']) . '" ';
            $html .= 'alt="' . htmlspecialchars($gallery['images'][0]['alt']) . '">';
            $html .= '
</div></div>';
        }
        
        $html .= '</div></div>';
        return $html;
    }

    private static function loadFallbackContent(): string {
        return '
<div class="error">Content unavailable</div>';
    }
}
