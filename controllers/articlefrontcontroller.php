<?php
/**
 * Article Frontend Controller
 * Displays articles on the public site
 */

require_once __DIR__ . '/../config.php';

class ArticleFrontController {
    
    private $pdo;
    
    public function __construct() {
        $this->pdo = \core\Database::connection();
    }
    
    /**
     * Show single article by slug
     */
    public function show(string $slug): void {
        $stmt = $this->pdo->prepare("
            SELECT a.*, c.name as category_name, c.slug as category_slug,
                   u.username as author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN admins u ON a.author_id = u.id
            WHERE a.slug = ? AND a.status = 'published'
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$article) {
            http_response_code(404);
            echo "Article not found";
            return;
        }
        
        // Load theme and render
        $this->renderWithTheme($article);
    }
    
    /**
     * List all published articles
     */
    public function index(): void {
        $stmt = $this->pdo->query("
            SELECT a.*, c.name as category_name, c.slug as category_slug,
                   u.username as author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN admins u ON a.author_id = u.id
            WHERE a.status = 'published'
            ORDER BY a.published_at DESC
            LIMIT 20
        ");
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->renderListingWithTheme($articles);
    }
    
    /**
     * Render article with current theme
     */
    private function renderWithTheme(array $article): void {
        // Get active theme from config_core/theme.php
        $theme = 'default';
        $themeConfigFile = CMS_ROOT . '/config_core/theme.php';
        if (file_exists($themeConfigFile)) {
            $themeConfig = require $themeConfigFile;
            if (is_array($themeConfig) && !empty($themeConfig['active_theme'])) {
                $theme = $themeConfig['active_theme'];
            }
        }
        
        $themePath = CMS_ROOT . '/themes/' . $theme;
        
        // Check if theme has article template
        $articleTemplate = $themePath . '/article.php';
        if (!file_exists($articleTemplate)) {
            $articleTemplate = $themePath . '/single.php';
        }
        if (!file_exists($articleTemplate)) {
            $articleTemplate = $themePath . '/blog/single.php';
        }
        if (!file_exists($articleTemplate)) {
            $articleTemplate = $themePath . '/blog/article.php';
        }
        
        if (file_exists($articleTemplate)) {
            // Theme handles full layout
            require_once $articleTemplate;
        } else {
            // Fallback - use theme layout with inline content
            $content = $this->renderArticleContent($article);
            $pageTitle = htmlspecialchars($article['meta_title'] ?: $article['title']);
            $pageDescription = htmlspecialchars($article['meta_description'] ?: $article['excerpt']);
            
            if (file_exists($themePath . '/layout.php')) {
                require_once $themePath . '/layout.php';
            } else {
                echo $content;
            }
        }
    }
    
    /**
     * Render article listing with theme
     */
    private function renderListingWithTheme(array $articles): void {
        // Get active theme from config_core/theme.php
        $theme = 'default';
        $themeConfigFile = CMS_ROOT . '/config_core/theme.php';
        if (file_exists($themeConfigFile)) {
            $themeConfig = require $themeConfigFile;
            if (is_array($themeConfig) && !empty($themeConfig['active_theme'])) {
                $theme = $themeConfig['active_theme'];
            }
        }
        
        $themePath = CMS_ROOT . '/themes/' . $theme;
        $blogTemplate = $themePath . '/blog.php';
        
        if (file_exists($blogTemplate)) {
            require_once $blogTemplate;
        } else {
            // Fallback
            $content = '<div class="articles-list">';
            foreach ($articles as $article) {
                $content .= '<article class="article-item">';
                $content .= '<h2><a href="/blog/' . htmlspecialchars($article['slug']) . '">' . htmlspecialchars($article['title']) . '</a></h2>';
                $content .= '<p>' . htmlspecialchars($article['excerpt']) . '</p>';
                $content .= '</article>';
            }
            $content .= '</div>';
            
            $pageTitle = 'Blog';
            $pageDescription = 'Latest articles';
            
            if (file_exists($themePath . '/layout.php')) {
                require_once $themePath . '/layout.php';
            } else {
                echo $content;
            }
        }
    }
    
    /**
     * Render article content HTML
     */
    private function renderArticleContent(array $article): string {
        $html = '<article class="article-single">';
        
        if (!empty($article['featured_image'])) {
            $alt = htmlspecialchars($article['featured_image_alt'] ?? $article['title']);
            $title = htmlspecialchars($article['featured_image_title'] ?? '');
            $html .= '<img src="' . htmlspecialchars($article['featured_image']) . '" alt="' . $alt . '" title="' . $title . '" class="featured-image">';
        }
        
        $html .= '<h1>' . htmlspecialchars($article['title']) . '</h1>';
        
        $html .= '<div class="article-meta">';
        if (!empty($article['author_name'])) {
            $html .= '<span class="author">By ' . htmlspecialchars($article['author_name']) . '</span>';
        }
        if (!empty($article['published_at'])) {
            $html .= '<span class="date">' . date('F j, Y', strtotime($article['published_at'])) . '</span>';
        }
        if (!empty($article['category_name'])) {
            $html .= '<span class="category">' . htmlspecialchars($article['category_name']) . '</span>';
        }
        $html .= '</div>';
        
        $html .= '<div class="article-content">' . $article['content'] . '</div>';
        
        $html .= '</article>';
        
        return $html;
    }
}
