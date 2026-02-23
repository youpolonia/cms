<?php
/**
 * End-to-End Test for AI Theme Builder Pipeline
 * Tests: Brief → HTML+Header+Footer Patterns → CSS → Assembly → SubPages
 * 
 * Usage: php test-e2e-atb.php [provider] [model]
 * Example: php test-e2e-atb.php anthropic claude-sonnet-4-20250514
 */

// Bootstrap CMS
define('CMS_ROOT', __DIR__);
require_once CMS_ROOT . '/core/bootstrap.php';
require_once CMS_ROOT . '/core/ai-theme-builder.php';

// Parse CLI args
$provider = $argv[1] ?? 'anthropic';
$model = $argv[2] ?? 'claude-sonnet-4-20250514';

echo "╔═══════════════════════════════════════════════════════╗\n";
echo "║  AI Theme Builder — End-to-End Pipeline Test         ║\n";
echo "║  Provider: {$provider}                               \n";
echo "║  Model: {$model}                                     \n";
echo "╚═══════════════════════════════════════════════════════╝\n\n";

$startTime = microtime(true);

// Create builder
$builder = new AiThemeBuilder([
    'provider' => $provider,
    'model' => $model,
    'language' => 'English',
    'creativity' => 'medium',
]);

// Progress callback
$builder->setProgressCallback(function(string $event, array $data) {
    $step = $data['step'] ?? '';
    $status = $data['status'] ?? '';
    $label = $data['label'] ?? '';
    $timing = isset($data['timing']) ? " ({$data['timing']}ms)" : '';
    
    if ($event === 'step') {
        $icon = match($status) {
            'running' => '⏳',
            'done' => '✅',
            'error' => '❌',
            default => '•'
        };
        echo "  {$icon} Step {$step}: {$label}{$timing}\n";
        if ($status === 'done' && isset($data['name'])) {
            echo "     Theme name: {$data['name']}\n";
        }
        if ($status === 'done' && isset($data['sections'])) {
            echo "     Sections parsed: {$data['sections']}\n";
        }
        if ($status === 'done' && isset($data['coverage'])) {
            echo "     CSS selector coverage: {$data['coverage']}%\n";
        }
    } elseif ($event === 'header_pattern') {
        echo "  🏗️ Header pattern: {$data['pattern']} (prefix: {$data['prefix']})\n";
    } elseif ($event === 'footer_pattern') {
        echo "  🦶 Footer pattern: {$data['pattern']} (prefix: {$data['prefix']})\n";
    }
});

// ════════════════════════════════════════════
// TEST 1: Full generation pipeline
// ════════════════════════════════════════════
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 1: Full Theme Generation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$params = [
    'prompt' => 'Modern Japanese fusion restaurant in London with omakase dining experience',
    'industry' => 'restaurant',
    'style' => 'elegant',
    'mood' => 'dark',
    'tone' => 'luxurious',
    'selectedPages' => ['home', 'about', 'services', 'contact', 'gallery', 'blog'],
];

echo "  Prompt: {$params['prompt']}\n";
echo "  Industry: {$params['industry']} | Style: {$params['style']} | Mood: {$params['mood']}\n\n";

$result = $builder->generate($params);

if ($result['ok']) {
    $slug = $result['slug'];
    echo "\n✅ Theme generated successfully!\n";
    echo "   Slug: {$slug}\n";
    echo "   Name: {$result['theme_name']}\n";
    echo "   Model: {$result['model_used']}\n";
    echo "   Sections: {$result['summary']['sections']}\n";
    echo "   Fonts: {$result['summary']['fonts']}\n";
    echo "   Colors: {$result['summary']['colors']}\n";
    
    // Timing summary
    echo "\n   Timing:\n";
    foreach ($result['timings'] as $step => $ms) {
        echo "     {$step}: {$ms}ms (" . round($ms/1000, 1) . "s)\n";
    }
} else {
    echo "\n❌ Generation FAILED: {$result['error']}\n";
    if (isset($result['step'])) echo "   Failed at step: {$result['step']}\n";
    if (isset($result['error_info'])) echo "   Error info: " . json_encode($result['error_info']) . "\n";
    exit(1);
}

// ════════════════════════════════════════════
// TEST 2: Verify theme files
// ════════════════════════════════════════════
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 2: Theme File Verification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$themeDir = CMS_ROOT . '/themes/' . $slug;
$requiredFiles = [
    'theme.json',
    'layout.php',
    'assets/css/style.css',
    'assets/js/main.js',
    'templates/home.php',
    'templates/page.php',
    'templates/article.php',
    'templates/articles.php',
    'templates/gallery.php',
    'templates/404.php',
];

$allOk = true;
foreach ($requiredFiles as $file) {
    $path = $themeDir . '/' . $file;
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    $icon = $exists ? '✅' : '❌';
    echo "  {$icon} {$file}" . ($exists ? " ({$size} bytes)" : " MISSING") . "\n";
    if (!$exists) $allOk = false;
}

// Check sections
$sections = glob($themeDir . '/sections/*.php');
echo "  📂 Sections: " . count($sections) . " files\n";
foreach ($sections as $sf) {
    $sName = basename($sf);
    $sSize = filesize($sf);
    echo "     • {$sName} ({$sSize} bytes)\n";
}

// Verify layout.php has header from pattern (not AI)
$layoutContent = file_get_contents($themeDir . '/layout.php');
$hasPatternHeader = strpos($layoutContent, 'header-container') !== false || strpos($layoutContent, 'header-logo') !== false;
echo "  " . ($hasPatternHeader ? '✅' : '❌') . " Layout uses Header Pattern\n";

// Verify footer from pattern
$hasPatternFooter = strpos($layoutContent, '-footer') !== false && strpos($layoutContent, 'render_menu') !== false;
echo "  " . ($hasPatternFooter ? '✅' : '❌') . " Layout uses Footer Pattern\n";

// Verify render_menu uses <?= (not bare)
$bareRenderMenu = preg_match('/(?<!\?=\s)(?<!echo\s)render_menu\(/', $layoutContent);
echo "  " . (!$bareRenderMenu ? '✅' : '❌') . " render_menu always uses <?= or echo\n";

// Check CSS quality
$css = file_get_contents($themeDir . '/assets/css/style.css');
echo "\n  📊 CSS Analysis:\n";
echo "     Total size: " . strlen($css) . " bytes (" . round(strlen($css)/1024, 1) . " KB)\n";
echo "     Lines: " . substr_count($css, "\n") . "\n";

// Count CSS rules
preg_match_all('/\{[^}]+\}/', $css, $ruleMatches);
echo "     Rules: " . count($ruleMatches[0]) . "\n";

// Check for required selectors
$requiredSelectors = ['.container', '.hero', '.hero-overlay', '.btn-primary', '.btn-outline', 
    '.section-header', '.section-title', '.page-hero', '.prose', '.article-card', '@media'];
$missingCss = [];
foreach ($requiredSelectors as $sel) {
    if (stripos($css, $sel) === false) $missingCss[] = $sel;
}
echo "     Selector coverage: " . count($requiredSelectors) - count($missingCss) . "/" . count($requiredSelectors) . "\n";
if (!empty($missingCss)) {
    echo "     ⚠️ Missing: " . implode(', ', $missingCss) . "\n";
}

// Check for :root variables
preg_match('/:root\s*\{([^}]+)\}/', $css, $rootMatch);
if ($rootMatch) {
    preg_match_all('/--([a-z-]+)\s*:/', $rootMatch[1], $varMatches);
    echo "     CSS Variables: " . count($varMatches[1]) . " defined\n";
} else {
    echo "     ⚠️ No :root CSS variables found\n";
}

// Check for responsive breakpoints
$mediaCount = substr_count($css, '@media');
echo "     Media queries: {$mediaCount}\n";

// PHP syntax check for all theme files
echo "\n  🔍 PHP Syntax Check:\n";
$phpFiles = array_merge(
    [$themeDir . '/layout.php'],
    glob($themeDir . '/sections/*.php'),
    glob($themeDir . '/templates/*.php')
);
$syntaxErrors = 0;
foreach ($phpFiles as $phpFile) {
    $output = [];
    $returnCode = 0;
    exec('php -l ' . escapeshellarg($phpFile) . ' 2>&1', $output, $returnCode);
    if ($returnCode !== 0) {
        echo "     ❌ " . basename($phpFile) . ": " . implode(' ', $output) . "\n";
        $syntaxErrors++;
    }
}
echo "     " . ($syntaxErrors === 0 ? '✅' : '❌') . " {$syntaxErrors} syntax errors in " . count($phpFiles) . " files\n";

// ════════════════════════════════════════════
// TEST 3: Database content verification
// ════════════════════════════════════════════
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 3: Database Content Verification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$pdo = \core\Database::connection();

// Check pages
$stmt = $pdo->prepare("SELECT slug, title, template, LENGTH(content) as content_len FROM pages WHERE theme_slug = ?");
$stmt->execute([$slug]);
$pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
echo "  📄 Pages ({$slug}): " . count($pages) . "\n";
foreach ($pages as $p) {
    echo "     • {$p['slug']} — \"{$p['title']}\" (template: {$p['template']}, content: {$p['content_len']} bytes)\n";
}

// Check menus
$stmt = $pdo->prepare("SELECT m.name, m.location, COUNT(mi.id) as items FROM menus m LEFT JOIN menu_items mi ON mi.menu_id = m.id WHERE m.theme_slug = ? GROUP BY m.id");
$stmt->execute([$slug]);
$menus = $stmt->fetchAll(\PDO::FETCH_ASSOC);
echo "  🧭 Menus: " . count($menus) . "\n";
foreach ($menus as $m) {
    echo "     • {$m['name']} (location: {$m['location']}, items: {$m['items']})\n";
}

// Check articles  
$stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE theme_slug = ?");
$stmt->execute([$slug]);
$articleCount = $stmt->fetchColumn();
echo "  📰 Articles: {$articleCount}\n";

// Check galleries
$stmt = $pdo->prepare("SELECT COUNT(*) FROM galleries WHERE theme = ?");
$stmt->execute([$slug]);
$galleryCount = $stmt->fetchColumn();
echo "  🖼️ Galleries: {$galleryCount}\n";

// Check theme customizations
$stmt = $pdo->prepare("SELECT section, field_key FROM theme_customizations WHERE theme_slug = ?");
$stmt->execute([$slug]);
$customizations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
echo "  🎨 Customizations: " . count($customizations) . "\n";

// ════════════════════════════════════════════
// TEST 4: Sub-page generation
// ════════════════════════════════════════════
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 4: Sub-Page Generation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$brief = $result['steps']['brief']['data'] ?? null;
if (!$brief) {
    echo "  ⚠️ Cannot test sub-pages — brief not available\n";
} else {
    $subPages = ['about', 'services', 'contact'];
    foreach ($subPages as $pageType) {
        echo "\n  Generating: {$pageType}...\n";
        $t = microtime(true);
        
        $pageResult = $builder->generateSubPage([
            'prompt' => $params['prompt'],
            'industry' => $params['industry'],
            'style' => $params['style'],
            'mood' => $params['mood'],
            'language' => 'English',
            'brief' => $brief,
            'slug' => $slug,
            'page_type' => $pageType,
            'business_info' => [],
        ]);
        
        $pageTiming = round((microtime(true) - $t) * 1000);
        
        if ($pageResult['ok']) {
            echo "  ✅ {$pageType}: {$pageResult['content_length']} bytes ({$pageTiming}ms)\n";
            echo "     Sections: {$pageResult['section_count']}, ";
            echo "Style: " . ($pageResult['has_style_block'] ? 'yes' : 'NO') . ", ";
            echo "Container: " . ($pageResult['has_container'] ? 'yes' : 'NO') . ", ";
            echo "Animations: " . ($pageResult['has_animations'] ? 'yes' : 'NO') . "\n";
            echo "     DB: " . ($pageResult['was_insert'] ? 'INSERT' : 'UPDATE') . "\n";
            
            // Quick content quality check
            $stmt = $pdo->prepare("SELECT content FROM pages WHERE slug = ? AND theme_slug = ?");
            $stmt->execute([$slug . '-' . $pageType, $slug]);
            $content = $stmt->fetchColumn();
            if ($content) {
                $hasForm = $pageType === 'contact' && stripos($content, '<form') !== false;
                $hasCards = stripos($content, 'card') !== false;
                $hasHero = stripos($content, 'hero') !== false;
                $hasCta = stripos($content, 'cta') !== false;
                echo "     Content: hero=" . ($hasHero ? '✓' : '✗');
                echo " cards=" . ($hasCards ? '✓' : '✗');
                echo " cta=" . ($hasCta ? '✓' : '✗');
                if ($pageType === 'contact') echo " form=" . ($hasForm ? '✓' : '✗');
                echo "\n";
            }
        } else {
            echo "  ❌ {$pageType}: {$pageResult['error']}\n";
        }
    }
}

// ════════════════════════════════════════════
// SUMMARY
// ════════════════════════════════════════════
$totalTime = round(microtime(true) - $startTime, 1);

echo "\n╔═══════════════════════════════════════════════════════╗\n";
echo "║  E2E Test Complete                                   ║\n";
echo "║  Theme: {$slug}                                      \n";
echo "║  Total time: {$totalTime}s                           \n";
echo "║  URL: http://localhost/?theme={$slug}                \n";
echo "╚═══════════════════════════════════════════════════════╝\n";
