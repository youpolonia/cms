<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;

class DocsController
{
    /**
     * GET /admin/docs — Built-in documentation / user guide
     */
    public function index(Request $request): void
    {
        $section = $_GET['section'] ?? 'getting-started';

        $docs = self::loadDocs();

        // Find current doc
        $currentDoc = null;
        $currentCategory = null;
        foreach ($docs as $cat) {
            foreach ($cat['items'] as $item) {
                if ($item['slug'] === $section) {
                    $currentDoc = $item;
                    $currentCategory = $cat;
                    break 2;
                }
            }
        }

        if (!$currentDoc) {
            $currentDoc = $docs[0]['items'][0];
            $currentCategory = $docs[0];
            $section = $currentDoc['slug'];
        }

        $data = [
            'title' => 'Documentation',
            'docs' => $docs,
            'currentDoc' => $currentDoc,
            'currentCategory' => $currentCategory,
            'section' => $section,
            'csrfToken' => csrf_token(),
        ];

        extract($data);
        require \CMS_APP . '/views/admin/docs/index.php';
        exit;
    }

    /**
     * GET /api/docs/search — Search documentation
     */
    public function search(Request $request): void
    {
        $query = strtolower(trim($_GET['q'] ?? ''));
        if (strlen($query) < 2) {
            Response::json(['results' => []]);
            return;
        }

        $results = [];
        $docs = self::loadDocs();

        foreach ($docs as $cat) {
            foreach ($cat['items'] as $item) {
                $score = 0;
                // Search in title
                if (str_contains(strtolower($item['title']), $query)) $score += 10;
                // Search in content
                $contentLower = strtolower($item['content']);
                if (str_contains($contentLower, $query)) {
                    $score += substr_count($contentLower, $query);
                }
                // Search in keywords
                foreach ($item['keywords'] ?? [] as $kw) {
                    if (str_contains(strtolower($kw), $query)) $score += 5;
                }

                if ($score > 0) {
                    // Extract snippet around first match
                    $pos = stripos($item['content'], $query);
                    $snippet = '';
                    if ($pos !== false) {
                        $start = max(0, $pos - 60);
                        $snippet = ($start > 0 ? '...' : '') . substr($item['content'], $start, 150) . '...';
                    }

                    $results[] = [
                        'slug' => $item['slug'],
                        'title' => $item['title'],
                        'category' => $cat['label'],
                        'snippet' => strip_tags($snippet),
                        'score' => $score,
                    ];
                }
            }
        }

        usort($results, fn($a, $b) => $b['score'] - $a['score']);
        Response::json(['results' => array_slice($results, 0, 10)]);
    }

    /**
     * Load all documentation content
     */
    private static function loadDocs(): array
    {
        return [
            // ─── Getting Started ───
            [
                'label' => 'Getting Started',
                'icon' => '🚀',
                'items' => [
                    [
                        'slug' => 'getting-started',
                        'title' => 'Welcome to Jessie AI-CMS',
                        'keywords' => ['welcome', 'intro', 'start', 'begin', 'first'],
                        'content' => <<<'HTML'
<h2>Welcome to Jessie AI-CMS 👋</h2>
<p>Jessie is a modern, AI-powered Content Management System. It's designed to help you build beautiful websites with minimal effort — whether you're a developer, designer, or complete beginner.</p>

<h3>What makes Jessie special?</h3>
<div class="doc-features">
    <div class="doc-feature">
        <div class="doc-feature-icon">🤖</div>
        <div>
            <strong>AI-Powered</strong>
            <p>5 AI providers (OpenAI, Anthropic, Google, DeepSeek, HuggingFace). Generate content, images, entire websites, and SEO optimization — all built in.</p>
        </div>
    </div>
    <div class="doc-feature">
        <div class="doc-feature-icon">🎨</div>
        <div>
            <strong>Beautiful Themes</strong>
            <p>Professional themes with Theme Studio for real-time customization. Or use AI Theme Builder to generate unique designs from a single prompt.</p>
        </div>
    </div>
    <div class="doc-feature">
        <div class="doc-feature-icon">📊</div>
        <div>
            <strong>Built-in SEO Suite</strong>
            <p>15 SEO tools — keyword tracking, competitor analysis, content scoring, broken link checking, schema markup, and more. No plugins needed.</p>
        </div>
    </div>
    <div class="doc-feature">
        <div class="doc-feature-icon">⚡</div>
        <div>
            <strong>Self-Hosted & Lightweight</strong>
            <p>Pure PHP — no frameworks, no Composer, no npm. Deploy via FTP to any $3/month hosting. You own your data.</p>
        </div>
    </div>
</div>

<h3>Quick Start</h3>
<ol>
    <li><strong>Run Setup Wizard</strong> — If you haven't already, go to <a href="/admin/setup-wizard">Setup Wizard</a> to configure your site name, AI providers, and generate your first page.</li>
    <li><strong>Choose a Theme</strong> — Visit <a href="/admin/themes">Themes</a> to pick a starter theme, or use <a href="/admin/ai-theme-builder">AI Theme Builder</a> to generate one.</li>
    <li><strong>Customize with Theme Studio</strong> — Open <a href="/admin/theme-studio">Theme Studio</a> to adjust colors, fonts, layout, and content with live preview.</li>
    <li><strong>Create Content</strong> — Add <a href="/admin/pages">Pages</a> and <a href="/admin/articles">Articles</a>. Use AI tools to help write content.</li>
    <li><strong>Optimize SEO</strong> — Use the <a href="/admin/ai-seo-assistant">SEO Assistant</a> to analyze and improve your site's search visibility.</li>
</ol>
HTML,
                    ],
                    [
                        'slug' => 'dashboard',
                        'title' => 'Dashboard Overview',
                        'keywords' => ['dashboard', 'home', 'overview', 'stats'],
                        'content' => <<<'HTML'
<h2>Dashboard</h2>
<p>The Dashboard is your CMS home screen. It shows key metrics and quick access to common tasks.</p>

<h3>What you'll see</h3>
<ul>
    <li><strong>Stats cards</strong> — Total pages, articles, media files, and comments at a glance</li>
    <li><strong>Recent activity</strong> — Latest content changes and updates</li>
    <li><strong>Quick actions</strong> — Shortcuts to create new pages, articles, or run AI tools</li>
</ul>

<h3>Tips</h3>
<ul>
    <li>The dashboard auto-redirects to Setup Wizard on first login</li>
    <li>Use the sidebar menu to navigate to any section</li>
    <li>The admin toolbar appears on your live site when logged in — click "Edit" to jump to any page's editor</li>
</ul>
HTML,
                    ],
                ],
            ],

            // ─── Content ───
            [
                'label' => 'Content',
                'icon' => '📄',
                'items' => [
                    [
                        'slug' => 'pages',
                        'title' => 'Managing Pages',
                        'keywords' => ['pages', 'create page', 'edit page', 'static page', 'content'],
                        'content' => <<<'HTML'
<h2>Pages</h2>
<p>Pages are the main building blocks of your website — Home, About, Services, Contact, etc.</p>

<h3>Creating a Page</h3>
<ol>
    <li>Go to <strong>Content → Pages</strong></li>
    <li>Click <strong>"+ New Page"</strong></li>
    <li>Enter a title — the URL slug is generated automatically</li>
    <li>Write your content in the editor</li>
    <li>Set status to <strong>Published</strong> when ready</li>
    <li>Click <strong>Save</strong></li>
</ol>

<h3>Page Options</h3>
<ul>
    <li><strong>Title</strong> — Displayed as the page heading and in browser tab</li>
    <li><strong>Slug</strong> — URL path (e.g., <code>/about-us</code>). Auto-generated from title, editable.</li>
    <li><strong>Content</strong> — Rich text editor for your page body</li>
    <li><strong>Featured Image</strong> — Hero/banner image for the page</li>
    <li><strong>SEO</strong> — Meta title, description, and keywords (or use AI SEO tools)</li>
    <li><strong>Status</strong> — Draft (hidden) or Published (live)</li>
</ul>

<h3>Using JTB Page Builder</h3>
<p>For advanced layouts, enable the <strong>Jessie Theme Builder (JTB)</strong> on any page. This gives you a drag-and-drop editor with 79 modules — headers, galleries, testimonials, pricing tables, and more.</p>
HTML,
                    ],
                    [
                        'slug' => 'articles',
                        'title' => 'Managing Articles',
                        'keywords' => ['articles', 'blog', 'posts', 'create article', 'blog post'],
                        'content' => <<<'HTML'
<h2>Articles</h2>
<p>Articles are your blog/news posts. They have dates, categories, excerpts, and appear in chronological feeds.</p>

<h3>Creating an Article</h3>
<ol>
    <li>Go to <strong>Content → Articles</strong></li>
    <li>Click <strong>"+ New Article"</strong></li>
    <li>Enter title, content, and select a category</li>
    <li>Add a featured image and excerpt</li>
    <li>Set status to <strong>Published</strong></li>
</ol>

<h3>Articles vs Pages</h3>
<table class="doc-table">
    <tr><th>Feature</th><th>Pages</th><th>Articles</th></tr>
    <tr><td>URL format</td><td><code>/page-slug</code></td><td><code>/article/slug</code></td></tr>
    <tr><td>Date-based</td><td>No</td><td>Yes (published_at)</td></tr>
    <tr><td>Categories</td><td>No</td><td>Yes</td></tr>
    <tr><td>Excerpt</td><td>No</td><td>Yes</td></tr>
    <tr><td>Appears in feeds</td><td>No</td><td>Yes (/articles listing)</td></tr>
</table>

<h3>Tip: AI Content</h3>
<p>Use <a href="/admin/ai-content-creator">AI Content Creator</a> to generate article drafts from a topic prompt. You can also use <a href="/admin/ai-copywriter">AI Copywriter</a> to improve existing text.</p>
HTML,
                    ],
                    [
                        'slug' => 'media',
                        'title' => 'Media Library',
                        'keywords' => ['media', 'images', 'upload', 'files', 'gallery', 'photos'],
                        'content' => <<<'HTML'
<h2>Media Library</h2>
<p>The Media Library stores all your uploaded images and files. You can upload, organize, and insert them into pages and articles.</p>

<h3>Uploading Files</h3>
<ul>
    <li>Go to <strong>Content → Media</strong></li>
    <li>Click <strong>"Upload"</strong> or drag & drop files onto the page</li>
    <li>Supported formats: JPG, PNG, GIF, WebP, SVG, PDF</li>
</ul>

<h3>Using Media in Content</h3>
<p>When editing a page or article, click the image icon in the editor to open the Media Gallery. You can:</p>
<ul>
    <li><strong>Upload</strong> — Upload a new file</li>
    <li><strong>Library</strong> — Choose from existing uploads</li>
    <li><strong>Pexels</strong> — Search free stock photos (requires Pexels API key)</li>
    <li><strong>AI Generate</strong> — Generate images with AI (requires AI provider)</li>
</ul>
HTML,
                    ],
                    [
                        'slug' => 'menus',
                        'title' => 'Navigation Menus',
                        'keywords' => ['menus', 'navigation', 'header', 'footer', 'menu items', 'links'],
                        'content' => <<<'HTML'
<h2>Navigation Menus</h2>
<p>Menus control the navigation links on your website — header navigation, footer links, etc.</p>

<h3>Managing Menus</h3>
<ol>
    <li>Go to <strong>Content → Navigation</strong></li>
    <li>Select or create a menu (e.g., "Header Menu", "Footer Menu")</li>
    <li>Add items — pages, custom links, or categories</li>
    <li>Drag to reorder items</li>
    <li>Assign the menu to a location (header, footer)</li>
</ol>

<h3>Menu Locations</h3>
<p>Themes define menu locations. Most themes have:</p>
<ul>
    <li><strong>header</strong> — Main navigation bar</li>
    <li><strong>footer</strong> — Footer link columns</li>
</ul>
<p>If no menu is assigned to a location, the theme falls back to showing published pages automatically.</p>
HTML,
                    ],
                ],
            ],

            // ─── Appearance ───
            [
                'label' => 'Appearance',
                'icon' => '🎨',
                'items' => [
                    [
                        'slug' => 'themes',
                        'title' => 'Themes',
                        'keywords' => ['themes', 'templates', 'design', 'switch theme', 'activate'],
                        'content' => <<<'HTML'
<h2>Themes</h2>
<p>Themes control the visual design of your website — layout, colors, typography, and style.</p>

<h3>Switching Themes</h3>
<ol>
    <li>Go to <strong>Appearance → Themes</strong></li>
    <li>Browse available themes</li>
    <li>Click <strong>"Activate"</strong> on the theme you want</li>
    <li>Optionally click <strong>"📦 Demo Content"</strong> to install sample pages and articles for that theme</li>
</ol>

<h3>Included Themes</h3>
<ul>
    <li><strong>Starter Restaurant</strong> — Warm, elegant, perfect for restaurants and cafés</li>
    <li><strong>Starter SaaS</strong> — Modern indigo gradients for software products</li>
    <li><strong>Starter Blog</strong> — Clean, readable for writers and bloggers</li>
    <li><strong>Starter Business</strong> — Professional blue for consulting and corporate</li>
    <li><strong>Starter Portfolio</strong> — Minimal and creative for designers and photographers</li>
</ul>

<h3>AI Theme Builder</h3>
<p>Want something unique? Use <a href="/admin/ai-theme-builder">AI Theme Builder</a> to generate a completely custom theme from a text description. Choose industry, style, mood, and AI does the rest.</p>
HTML,
                    ],
                    [
                        'slug' => 'theme-studio',
                        'title' => 'Theme Studio',
                        'keywords' => ['theme studio', 'customize', 'colors', 'fonts', 'logo', 'live preview', 'css'],
                        'content' => <<<'HTML'
<h2>Theme Studio</h2>
<p>Theme Studio is a full-screen visual customizer. See changes in real-time as you tweak every aspect of your theme.</p>

<h3>What you can customize</h3>
<ul>
    <li><strong>Brand</strong> — Logo, site name, colors (primary, secondary, accent, background, text), favicon, social image</li>
    <li><strong>Header & Footer</strong> — Layout, CTA button text, sticky behavior, colors</li>
    <li><strong>Typography</strong> — Google Fonts (60+ fonts), sizes, line height, heading styles</li>
    <li><strong>Buttons</strong> — Border radius, padding, weight, uppercase, shadow</li>
    <li><strong>Layout</strong> — Container width, section spacing, border radius</li>
    <li><strong>Effects</strong> — Shadow intensity, hover scale, animation speed</li>
    <li><strong>Custom CSS</strong> — Add your own CSS overrides</li>
</ul>

<h3>Advanced Features</h3>
<ul>
    <li><strong>Color Presets</strong> — 24 industry color palettes (one click)</li>
    <li><strong>Font Pairing</strong> — AI-suggested font combinations</li>
    <li><strong>Color from Image</strong> — Extract a color palette from any photo</li>
    <li><strong>Gradient Builder</strong> — Visual gradient editor for backgrounds</li>
    <li><strong>Box Shadow Editor</strong> — Visual shadow editor with preview</li>
    <li><strong>Spacing Editor</strong> — Visual margin/padding editor (like Chrome DevTools)</li>
    <li><strong>Export/Import</strong> — Save and share theme settings as JSON</li>
</ul>

<h3>Sections Tab</h3>
<p>The Sections tab lets you manage your homepage layout:</p>
<ul>
    <li>Drag to reorder homepage sections</li>
    <li>Toggle sections on/off</li>
    <li>Edit content inline (headings, descriptions, images, CTAs)</li>
</ul>
HTML,
                    ],
                    [
                        'slug' => 'ai-theme-builder',
                        'title' => 'AI Theme Builder',
                        'keywords' => ['ai theme', 'generate theme', 'custom theme', 'theme builder'],
                        'content' => <<<'HTML'
<h2>AI Theme Builder</h2>
<p>Generate a completely unique website theme from a text description. AI creates the design system, HTML structure, and CSS styling — ready to use in seconds.</p>

<h3>How to use</h3>
<ol>
    <li>Go to <strong>Appearance → AI Theme Builder</strong></li>
    <li><strong>Describe your website</strong> — Be specific! Include industry, personality, features you want.</li>
    <li><strong>Pick Industry</strong> — Choose from 45+ industries (Restaurant, SaaS, Law, Medical, etc.)</li>
    <li><strong>Pick Style</strong> — Minimalist, Bold, Elegant, Brutalist, Art Deco, Glassmorphism, etc.</li>
    <li><strong>Pick Mood</strong> — Light, Dark, Warm, Cool, Neon, Pastel, Luxury, etc.</li>
    <li><strong>Choose AI Model</strong> — Higher-tier models produce better results (⭐ Best quality)</li>
    <li>Click <strong>Generate Theme</strong> and wait ~30-60 seconds</li>
</ol>

<h3>After Generation</h3>
<ul>
    <li><strong>Preview</strong> — See the theme live in the right panel</li>
    <li><strong>Apply Theme</strong> — Activate it on your live site</li>
    <li><strong>Theme Studio</strong> — Fine-tune colors, fonts, content</li>
</ul>

<h3>Tips for best results</h3>
<ul>
    <li>Use <strong>Claude Opus 4.5+</strong> or <strong>GPT-5</strong> for best quality</li>
    <li>Be descriptive: "Modern Japanese restaurant in Brooklyn with zen minimalism" → much better than "restaurant website"</li>
    <li>Generated themes are fully editable in Theme Studio</li>
    <li>Each generation creates unique designs — run it multiple times for variety</li>
</ul>
HTML,
                    ],
                ],
            ],

            // ─── SEO ───
            [
                'label' => 'SEO',
                'icon' => '🎯',
                'items' => [
                    [
                        'slug' => 'seo-overview',
                        'title' => 'SEO Overview',
                        'keywords' => ['seo', 'search engine', 'optimization', 'google', 'ranking'],
                        'content' => <<<'HTML'
<h2>SEO Suite</h2>
<p>Jessie CMS includes a comprehensive SEO toolkit — 15 tools to help your site rank higher in search engines.</p>

<h3>Key Tools</h3>
<ul>
    <li><strong><a href="/admin/ai-seo-assistant">SEO Assistant</a></strong> — AI analyzes your pages and gives actionable recommendations</li>
    <li><strong><a href="/admin/ai-seo-content">SEO Content</a></strong> — Optimize meta titles, descriptions, and keywords for all pages and articles</li>
    <li><strong><a href="/admin/ai-seo-bulk">Bulk SEO Editor</a></strong> — Edit meta data for multiple pages at once</li>
    <li><strong><a href="/admin/ai-seo-keywords">Keywords</a></strong> — Track keyword rankings and discover new opportunities</li>
    <li><strong><a href="/admin/ai-seo-competitors">Competitors</a></strong> — Analyze competitor websites</li>
    <li><strong><a href="/admin/ai-seo-images">Image Alt Text</a></strong> — AI generates alt text for all your images</li>
    <li><strong><a href="/admin/ai-seo-links">Broken Links</a></strong> — Find and fix broken links on your site</li>
    <li><strong><a href="/admin/ai-seo-timeline">Score Timeline</a></strong> — Track your SEO score over time</li>
</ul>

<h3>Getting Started with SEO</h3>
<ol>
    <li>Run the <strong>SEO Assistant</strong> on your homepage — it'll analyze and score your content</li>
    <li>Follow the recommendations to improve your score</li>
    <li>Use <strong>Bulk SEO Editor</strong> to fill in missing meta descriptions</li>
    <li>Generate <strong>Image Alt Text</strong> for accessibility and SEO</li>
    <li>Check for <strong>Broken Links</strong> monthly</li>
</ol>
HTML,
                    ],
                ],
            ],

            // ─── AI Tools ───
            [
                'label' => 'AI Tools',
                'icon' => '🤖',
                'items' => [
                    [
                        'slug' => 'ai-setup',
                        'title' => 'Setting Up AI',
                        'keywords' => ['ai setup', 'api key', 'openai', 'anthropic', 'deepseek', 'provider', 'configuration'],
                        'content' => <<<'HTML'
<h2>Setting Up AI</h2>
<p>Jessie CMS uses a <strong>Bring Your Own Keys (BYOK)</strong> model. You connect your own AI provider API keys — you pay the providers directly, no markup.</p>

<h3>Supported Providers</h3>
<ul>
    <li><strong>OpenAI</strong> — GPT-5, GPT-4.1, O3 (best for creative writing)</li>
    <li><strong>Anthropic</strong> — Claude Opus, Sonnet (best for code and themes)</li>
    <li><strong>Google</strong> — Gemini 2.0 (good value, large context)</li>
    <li><strong>DeepSeek</strong> — V3, R1 (budget-friendly)</li>
    <li><strong>HuggingFace</strong> — Open models (free tier available)</li>
</ul>

<h3>How to configure</h3>
<ol>
    <li>Go to <strong>System → Settings</strong> or the <strong>Setup Wizard</strong></li>
    <li>Find the <strong>AI Configuration</strong> section</li>
    <li>Enter your API key for at least one provider</li>
    <li>Choose a default provider and model</li>
    <li>Save — AI features are now enabled across the CMS</li>
</ol>

<h3>Which provider to choose?</h3>
<table class="doc-table">
    <tr><th>Use Case</th><th>Best Provider</th><th>Why</th></tr>
    <tr><td>Theme Generation</td><td>Anthropic Claude Opus</td><td>Best HTML/CSS quality</td></tr>
    <tr><td>Content Writing</td><td>OpenAI GPT-5 or Claude</td><td>Natural, creative text</td></tr>
    <tr><td>Quick Tasks</td><td>Google Gemini Flash</td><td>Fastest, cheapest</td></tr>
    <tr><td>Budget</td><td>DeepSeek V3</td><td>Very cheap, decent quality</td></tr>
</table>
HTML,
                    ],
                    [
                        'slug' => 'ai-tools',
                        'title' => 'AI Content Tools',
                        'keywords' => ['ai content', 'copywriter', 'translate', 'images', 'forms', 'landing page'],
                        'content' => <<<'HTML'
<h2>AI Content Tools</h2>
<p>Jessie CMS includes 12+ AI-powered tools for content creation:</p>

<h3>Writing</h3>
<ul>
    <li><strong><a href="/admin/ai-content-creator">Content Creator</a></strong> — Generate full articles from a topic prompt</li>
    <li><strong><a href="/admin/ai-copywriter">Copywriter</a></strong> — Rewrite, improve, or expand existing text</li>
    <li><strong><a href="/admin/ai-content-rewrite">Content Rewriter</a></strong> — Transform content style (formal, casual, technical)</li>
    <li><strong><a href="/admin/ai-translate">Translator</a></strong> — Translate content to any language</li>
</ul>

<h3>Visual</h3>
<ul>
    <li><strong><a href="/admin/ai-images">AI Images</a></strong> — Generate images from text descriptions (DALL-E)</li>
    <li><strong><a href="/admin/ai-alt-generator">Alt Text Generator</a></strong> — AI describes images for accessibility</li>
</ul>

<h3>Specialized</h3>
<ul>
    <li><strong><a href="/admin/ai-forms">AI Forms</a></strong> — Generate contact forms, surveys, sign-up forms from descriptions</li>
    <li><strong><a href="/admin/ai-landing">Landing Pages</a></strong> — Generate marketing landing pages</li>
    <li><strong><a href="/admin/ai-email-campaign">Email Campaigns</a></strong> — Generate email sequences and newsletters</li>
    <li><strong><a href="/admin/ai-student-materials">Student Materials</a></strong> — Generate educational content, quizzes, study guides</li>
</ul>
HTML,
                    ],
                ],
            ],

            // ─── System ───
            [
                'label' => 'System',
                'icon' => '⚙️',
                'items' => [
                    [
                        'slug' => 'settings',
                        'title' => 'Settings',
                        'keywords' => ['settings', 'configuration', 'site name', 'email', 'general'],
                        'content' => <<<'HTML'
<h2>Settings</h2>
<p>Configure your CMS from <strong>System → Settings</strong>.</p>

<h3>General</h3>
<ul>
    <li><strong>Site Name</strong> — Your website name (appears in header, title tags)</li>
    <li><strong>Site Description</strong> — Used in meta tags and SEO</li>
    <li><strong>Admin Email</strong> — For notifications</li>
</ul>

<h3>AI Configuration</h3>
<ul>
    <li>API keys for each provider</li>
    <li>Default provider and model</li>
    <li>Rate limits and usage tracking</li>
</ul>

<h3>SEO Settings</h3>
<ul>
    <li>Default meta tags</li>
    <li>Robots.txt configuration</li>
    <li>Sitemap settings</li>
</ul>
HTML,
                    ],
                    [
                        'slug' => 'users',
                        'title' => 'User Management',
                        'keywords' => ['users', 'accounts', 'password', 'admin', 'roles'],
                        'content' => <<<'HTML'
<h2>User Management</h2>
<p>Manage admin users from <strong>System → Users</strong>.</p>

<h3>Adding Users</h3>
<ol>
    <li>Go to <strong>System → Users</strong></li>
    <li>Click <strong>"+ New User"</strong></li>
    <li>Enter username, email, and password</li>
    <li>Assign a role (admin, editor)</li>
</ol>

<h3>Roles</h3>
<ul>
    <li><strong>Admin</strong> — Full access to all features and settings</li>
    <li><strong>Editor</strong> — Can create and edit content, but not change settings or manage users</li>
</ul>

<h3>Security Tips</h3>
<ul>
    <li>Use strong passwords (12+ characters)</li>
    <li>Change default admin password after setup</li>
    <li>Review the <a href="/admin/security-dashboard">Security Dashboard</a> periodically</li>
</ul>
HTML,
                    ],
                    [
                        'slug' => 'backup',
                        'title' => 'Backup & Restore',
                        'keywords' => ['backup', 'restore', 'export', 'database', 'files'],
                        'content' => <<<'HTML'
<h2>Backup & Restore</h2>
<p>Protect your content with regular backups.</p>

<h3>Creating Backups</h3>
<ol>
    <li>Go to <strong>System → Backup</strong></li>
    <li>Click <strong>"Create Backup"</strong></li>
    <li>The system exports your database and uploaded files</li>
    <li>Download the backup file and store it safely</li>
</ol>

<h3>Best Practices</h3>
<ul>
    <li>Create backups before major changes (theme switches, updates)</li>
    <li>Store backups off-server (cloud storage, local drive)</li>
    <li>Test restore periodically to ensure backups work</li>
</ul>
HTML,
                    ],
                ],
            ],

            // ─── Page Builder ───
            [
                'label' => 'Page Builder',
                'icon' => '🏗️',
                'items' => [
                    [
                        'slug' => 'jtb',
                        'title' => 'Jessie Theme Builder (JTB)',
                        'keywords' => ['jtb', 'page builder', 'drag drop', 'modules', 'visual editor'],
                        'content' => <<<'HTML'
<h2>Jessie Theme Builder (JTB)</h2>
<p>JTB is a visual drag-and-drop page builder with 79 modules. Use it to create rich, structured page layouts without writing code.</p>

<h3>Opening JTB</h3>
<ul>
    <li>Go to <strong>Appearance → Page Builder</strong></li>
    <li>Or enable JTB on any page in the page editor</li>
</ul>

<h3>Module Categories (79 total)</h3>
<ul>
    <li><strong>Structure</strong> — Sections, columns, containers, dividers, spacers</li>
    <li><strong>Content</strong> — Headings, text, buttons, lists, quotes, icons, code</li>
    <li><strong>Interactive</strong> — Tabs, accordions, toggles, counters, progress bars</li>
    <li><strong>Media</strong> — Images, galleries, videos, sliders, carousels</li>
    <li><strong>Forms</strong> — Contact forms, input fields, checkboxes, selects</li>
    <li><strong>Blog</strong> — Post grids, post lists, categories, recent posts</li>
    <li><strong>Full-width</strong> — Hero banners, CTAs, testimonials, pricing tables</li>
    <li><strong>Theme</strong> — Headers, footers, navigation, sidebars</li>
</ul>

<h3>Working with JTB</h3>
<ol>
    <li>Add modules from the sidebar panel</li>
    <li>Drag to reorder or nest modules</li>
    <li>Click any module to edit its settings</li>
    <li>Use the device preview (desktop/tablet/mobile)</li>
    <li>Save your layout — it's stored in the database</li>
</ol>

<h3>AI Website Builder</h3>
<p>The <a href="/admin/website-builder">Website Builder</a> uses AI multi-agent pipeline to generate entire page layouts with content, styling, and images. Perfect for generating complete websites quickly.</p>
HTML,
                    ],
                ],
            ],

            // ─── FAQ ───
            [
                'label' => 'FAQ',
                'icon' => '❓',
                'items' => [
                    [
                        'slug' => 'faq-general',
                        'title' => 'General FAQ',
                        'keywords' => ['faq', 'questions', 'help', 'common', 'frequently asked'],
                        'content' => <<<'HTML'
<h2>Frequently Asked Questions</h2>

<div class="doc-faq">
    <details class="doc-faq-item">
        <summary>How do I change my site name?</summary>
        <p>Go to <strong>System → Settings</strong> and update the <strong>Site Name</strong> field. The change takes effect immediately across your entire site.</p>
    </details>

    <details class="doc-faq-item">
        <summary>How do I add a logo?</summary>
        <p>Open <strong>Theme Studio</strong> → <strong>Brand</strong> section. Click the logo area to upload your image. Recommended size: 200×60px (PNG or SVG).</p>
    </details>

    <details class="doc-faq-item">
        <summary>Can I use Jessie CMS without AI?</summary>
        <p>Yes! AI features are optional. You can create pages, articles, and manage your entire site without configuring any AI provider. AI just makes things faster.</p>
    </details>

    <details class="doc-faq-item">
        <summary>How do I switch themes?</summary>
        <p>Go to <strong>Appearance → Themes</strong> and click <strong>Activate</strong> on the theme you want. Your content is preserved — only the visual design changes.</p>
    </details>

    <details class="doc-faq-item">
        <summary>Is my content safe when switching themes?</summary>
        <p>Yes. Pages, articles, media, and menus are independent of themes. Switching themes only changes the visual presentation. Theme Studio customizations are saved per-theme.</p>
    </details>

    <details class="doc-faq-item">
        <summary>How do I set up email sending?</summary>
        <p>Go to <strong>System → Email Settings</strong>. Enter your SMTP server details (host, port, username, password). Use port 587 with TLS for most providers (Gmail, Mailgun, etc.). Click <strong>Test</strong> to verify.</p>
    </details>

    <details class="doc-faq-item">
        <summary>How do I make my site SEO-friendly?</summary>
        <p>Start with the <strong>SEO Assistant</strong> — it analyzes your pages and gives specific recommendations. Key basics: fill in meta titles/descriptions, use headings properly, add alt text to images, and create quality content regularly.</p>
    </details>

    <details class="doc-faq-item">
        <summary>How do I create a backup?</summary>
        <p>Go to <strong>System → Backup</strong> and click <strong>Create Backup</strong>. Download the file and store it safely. Do this before making major changes.</p>
    </details>

    <details class="doc-faq-item">
        <summary>Can I have multiple admins?</summary>
        <p>Yes. Go to <strong>System → Users</strong> to create additional admin or editor accounts. Editors can manage content but not system settings.</p>
    </details>

    <details class="doc-faq-item">
        <summary>How do I add Google Analytics?</summary>
        <p>Go to <strong>System → Settings</strong> and paste your Google Analytics tracking code (GA4 measurement ID). The tracking script is automatically added to all public pages.</p>
    </details>

    <details class="doc-faq-item">
        <summary>What hosting do I need?</summary>
        <p>Any PHP 8.2+ hosting with MySQL/MariaDB. No special requirements — no Composer, no Node.js, no CLI access needed. Even $3/month shared hosting works. Upload via FTP and you're done.</p>
    </details>

    <details class="doc-faq-item">
        <summary>How do I set up URL redirects?</summary>
        <p>Go to <strong>System → URL Redirects</strong>. Add the old URL as Source and new URL as Target. Choose 301 (permanent) for SEO-friendly redirects or 302 (temporary).</p>
    </details>

    <details class="doc-faq-item">
        <summary>How do I add a contact form?</summary>
        <p>Use <strong>AI Forms</strong> to generate a form from a description, or use JTB Page Builder's form modules to build one visually. Forms can send submissions via email or store them in the database.</p>
    </details>

    <details class="doc-faq-item">
        <summary>Can I use my own domain?</summary>
        <p>Yes. Point your domain's DNS to your hosting server. Update your hosting control panel to associate the domain with your CMS installation directory. No configuration needed in the CMS itself.</p>
    </details>

    <details class="doc-faq-item">
        <summary>How do I update Jessie CMS?</summary>
        <p>Download the latest version and upload the new files via FTP (overwrite existing). Then go to <strong>System → Migrations</strong> and run any pending database migrations. Always backup first!</p>
    </details>
</div>
HTML,
                    ],
                    [
                        'slug' => 'faq-ai',
                        'title' => 'AI FAQ',
                        'keywords' => ['ai faq', 'api key', 'cost', 'tokens', 'ai not working'],
                        'content' => <<<'HTML'
<h2>AI — Frequently Asked Questions</h2>

<div class="doc-faq">
    <details class="doc-faq-item">
        <summary>How much does AI cost?</summary>
        <p>You pay AI providers directly using your own API keys. Typical costs:</p>
        <ul>
            <li><strong>Content generation</strong> (1 article): $0.01–$0.05</li>
            <li><strong>Theme generation</strong> (full theme): $0.10–$0.50</li>
            <li><strong>SEO analysis</strong> (per page): $0.01–$0.03</li>
            <li><strong>Image generation</strong> (1 image): $0.02–$0.04</li>
        </ul>
        <p>DeepSeek is the cheapest (~10× cheaper than OpenAI). Claude Opus is the most expensive but produces the best quality.</p>
    </details>

    <details class="doc-faq-item">
        <summary>AI features aren't working — what do I check?</summary>
        <p>Common issues:</p>
        <ol>
            <li><strong>No API key</strong> — Go to Settings and add at least one provider key</li>
            <li><strong>Invalid key</strong> — Check for typos, expired keys, or exceeded quotas</li>
            <li><strong>Wrong model</strong> — Some models require specific API tiers (e.g., GPT-5 needs paid OpenAI)</li>
            <li><strong>Timeout</strong> — Large requests (themes, long articles) may timeout on slow hosting. Try a faster model.</li>
        </ol>
    </details>

    <details class="doc-faq-item">
        <summary>Which AI model should I use for theme generation?</summary>
        <p><strong>Claude Opus 4.5</strong> or <strong>GPT-5</strong> produce the best themes. They have large output limits (16K+ tokens) needed for quality CSS. Avoid DeepSeek for themes — its 8K output limit produces incomplete CSS.</p>
    </details>

    <details class="doc-faq-item">
        <summary>Can I use multiple AI providers?</summary>
        <p>Yes! Configure all providers you want in Settings. Different tools can use different providers. For example: Claude for themes, GPT for content, DeepSeek for quick tasks.</p>
    </details>

    <details class="doc-faq-item">
        <summary>Is my content sent to AI providers?</summary>
        <p>When you use AI features, relevant content is sent to the provider's API. Major providers (OpenAI, Anthropic, Google) don't use API data for training. Check each provider's data policy for details.</p>
    </details>

    <details class="doc-faq-item">
        <summary>Can I use AI without internet?</summary>
        <p>No. AI features require internet access to reach provider APIs. All other CMS features (pages, articles, themes, SEO tools) work fully offline.</p>
    </details>
</div>
HTML,
                    ],
                    [
                        'slug' => 'faq-troubleshooting',
                        'title' => 'Troubleshooting',
                        'keywords' => ['troubleshoot', 'error', 'bug', 'fix', 'problem', '500', 'white screen', 'broken'],
                        'content' => <<<'HTML'
<h2>Troubleshooting</h2>

<div class="doc-faq">
    <details class="doc-faq-item">
        <summary>I see a white/blank page</summary>
        <p>This usually means a PHP error. Check:</p>
        <ol>
            <li>PHP error log (usually in hosting control panel → Error Logs)</li>
            <li>Add <code>ini_set('display_errors', 1);</code> temporarily to <code>index.php</code></li>
            <li>Check PHP version — Jessie requires PHP 8.2+</li>
            <li>Check file permissions — PHP files should be readable by the web server</li>
        </ol>
    </details>

    <details class="doc-faq-item">
        <summary>I get a 500 Internal Server Error</summary>
        <p>Common causes:</p>
        <ul>
            <li><strong>.htaccess issue</strong> — Check if mod_rewrite is enabled on your Apache server</li>
            <li><strong>PHP memory</strong> — Increase PHP memory_limit in php.ini (128M recommended)</li>
            <li><strong>Database connection</strong> — Verify credentials in <code>config.php</code></li>
            <li><strong>File permissions</strong> — <code>uploads/</code> directory needs write permission (755 or 775)</li>
        </ul>
    </details>

    <details class="doc-faq-item">
        <summary>Images aren't uploading</summary>
        <p>Check:</p>
        <ul>
            <li><code>uploads/</code> directory exists and is writable (chmod 755)</li>
            <li>PHP <code>upload_max_filesize</code> is large enough (default 2M may be too small)</li>
            <li>PHP <code>post_max_size</code> is larger than <code>upload_max_filesize</code></li>
        </ul>
    </details>

    <details class="doc-faq-item">
        <summary>Theme changes aren't showing</summary>
        <p>Try:</p>
        <ul>
            <li><strong>Hard refresh</strong> — Ctrl+Shift+R (or Cmd+Shift+R on Mac)</li>
            <li><strong>Clear browser cache</strong></li>
            <li>Check if you're editing the correct theme (active theme shown at top of Theme Studio)</li>
            <li>Check if a CDN or caching plugin is serving old files</li>
        </ul>
    </details>

    <details class="doc-faq-item">
        <summary>I forgot my admin password</summary>
        <p>Access your database (via phpMyAdmin or CLI) and run:</p>
        <pre><code>UPDATE users SET password = '$2y$10$...' WHERE username = 'admin';</code></pre>
        <p>Generate a new bcrypt hash at <a href="https://bcrypt-generator.com/" target="_blank">bcrypt-generator.com</a> and use it in the query above.</p>
    </details>

    <details class="doc-faq-item">
        <summary>My site is slow</summary>
        <p>Performance tips:</p>
        <ul>
            <li>Optimize images before uploading (use WebP format)</li>
            <li>Enable browser caching in .htaccess</li>
            <li>Use a CDN for static assets</li>
            <li>Reduce the number of homepage sections</li>
            <li>Check hosting — shared hosting can be slow under load</li>
        </ul>
    </details>
</div>
HTML,
                    ],
                ],
            ],

        ];
    }
}
