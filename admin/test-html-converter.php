<?php
/**
 * Test HTML Converter Endpoint
 * Access: /admin/test-html-converter.php (requires login)
 */

session_start();

// Check login
if (empty($_SESSION['admin_user_id'])) {
    header('Location: /admin/login');
    exit;
}

define('CMS_ROOT', dirname(__DIR__));

// Load converter
require_once CMS_ROOT . '/core/theme-builder/html-converter/Converter.php';
require_once CMS_ROOT . '/core/theme-builder/html-converter/StyleExtractor.php';
require_once CMS_ROOT . '/core/theme-builder/html-converter/SectionDetector.php';
require_once CMS_ROOT . '/core/theme-builder/html-converter/LayoutAnalyzer.php';
require_once CMS_ROOT . '/core/theme-builder/html-converter/ElementMapper.php';

use Core\ThemeBuilder\HtmlConverter\Converter;

$result = null;
$error = null;
$inputHtml = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['html'])) {
    $inputHtml = $_POST['html'];
    
    try {
        $converter = new Converter();
        $result = $converter->convert($inputHtml);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Sample HTML for testing
$sampleHtml = <<<'HTML'
<style>
.hero { background: linear-gradient(135deg, #667eea, #764ba2); padding: 80px 20px; text-align: center; color: white; }
.hero h1 { font-size: 48px; margin-bottom: 20px; }
.hero p { font-size: 20px; opacity: 0.9; }
.btn { display: inline-block; background: #fff; color: #6366f1; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 20px; }
.features { padding: 60px 20px; background: #f8fafc; }
.features h2 { text-align: center; font-size: 36px; margin-bottom: 40px; }
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; max-width: 1200px; margin: 0 auto; }
.feature-card { background: white; padding: 30px; border-radius: 12px; text-align: center; }
.feature-card i { font-size: 40px; color: #6366f1; margin-bottom: 15px; }
.feature-card h3 { font-size: 22px; margin-bottom: 10px; }
</style>

<section class="hero">
    <h1>Build Amazing Websites</h1>
    <p>Create stunning landing pages in minutes</p>
    <a href="#features" class="btn">Get Started</a>
</section>

<section class="features">
    <h2>Why Choose Us</h2>
    <div class="features-grid">
        <div class="feature-card">
            <i class="fas fa-rocket"></i>
            <h3>Lightning Fast</h3>
            <p>Build websites in minutes</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-paint-brush"></i>
            <h3>Beautiful Design</h3>
            <p>Professional templates</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-code"></i>
            <h3>Clean Code</h3>
            <p>Export production-ready code</p>
        </div>
    </div>
</section>
HTML;
?>
<!DOCTYPE html>
<html>
<head>
    <title>HTML Converter Test</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #1a1a2e; color: #fff; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #6366f1; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .panel { background: #16213e; border-radius: 12px; padding: 20px; }
        .panel h2 { margin-top: 0; color: #a5b4fc; font-size: 18px; }
        textarea { width: 100%; height: 400px; background: #0f0f23; border: 1px solid #333; color: #e2e8f0; font-family: monospace; font-size: 13px; padding: 15px; border-radius: 8px; resize: vertical; }
        pre { background: #0f0f23; padding: 15px; border-radius: 8px; overflow: auto; max-height: 500px; font-size: 12px; }
        .btn { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; margin: 10px 0; }
        .btn:hover { opacity: 0.9; }
        .btn-secondary { background: #374151; }
        .error { background: #7f1d1d; color: #fca5a5; padding: 15px; border-radius: 8px; }
        .success { background: #14532d; color: #86efac; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .summary { background: #1e293b; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .summary span { display: inline-block; margin-right: 20px; }
        .tag { background: #6366f1; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 HTML to TB JSON Converter Test</h1>
        
        <form method="POST">
            <div class="grid">
                <div class="panel">
                    <h2>📝 Input HTML</h2>
                    <textarea name="html" placeholder="Paste HTML here..."><?= htmlspecialchars($inputHtml ?: $sampleHtml) ?></textarea>
                    <button type="submit" class="btn">🚀 Convert to TB JSON</button>
                    <button type="button" class="btn btn-secondary" onclick="document.querySelector('textarea').value = <?= htmlspecialchars(json_encode($sampleHtml)) ?>">Load Sample</button>
                </div>
                
                <div class="panel">
                    <h2>📦 Output TB JSON</h2>
                    <?php if ($error): ?>
                        <div class="error">❌ Error: <?= htmlspecialchars($error) ?></div>
                    <?php elseif ($result): ?>
                        <div class="success">✅ Conversion successful!</div>
                        <div class="summary">
                            <span><strong>Sections:</strong> <?= count($result['sections'] ?? []) ?></span>
                            <?php 
                            $totalRows = 0;
                            $totalModules = 0;
                            foreach ($result['sections'] ?? [] as $s) {
                                $totalRows += count($s['rows'] ?? []);
                                foreach ($s['rows'] ?? [] as $r) {
                                    foreach ($r['columns'] ?? [] as $c) {
                                        $totalModules += count($c['modules'] ?? []);
                                    }
                                }
                            }
                            ?>
                            <span><strong>Rows:</strong> <?= $totalRows ?></span>
                            <span><strong>Modules:</strong> <?= $totalModules ?></span>
                        </div>
                        
                        <?php foreach ($result['sections'] ?? [] as $idx => $section): ?>
                            <div style="margin-bottom: 10px;">
                                <span class="tag"><?= $idx + 1 ?></span>
                                <strong><?= htmlspecialchars($section['name'] ?? 'Section') ?></strong>
                                - <?= count($section['rows'] ?? []) ?> row(s)
                            </div>
                        <?php endforeach; ?>
                        
                        <pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    <?php else: ?>
                        <p style="color: #64748b;">Enter HTML and click Convert to see results.</p>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
