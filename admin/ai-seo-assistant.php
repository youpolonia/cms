<?php
/**
 * AI SEO Assistant - Modern Dark UI v3.0
 * Catppuccin Mocha Theme
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/database.php';

cms_session_start('admin');
csrf_boot('admin');


cms_require_admin_role();

// Load AI content module (OpenAI)
$aiConfigured = false;
if (file_exists(CMS_ROOT . '/core/ai_content.php')) {
    require_once CMS_ROOT . '/core/ai_content.php';
    $aiConfig = ai_config_load();
    $aiConfigured = !empty($aiConfig['api_key']);
}
if (file_exists(CMS_ROOT . '/core/ai_seo_assistant.php')) {
    require_once CMS_ROOT . '/core/ai_seo_assistant.php';
}

function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

$form = ['title'=>'','url'=>'','focus_keyword'=>'','secondary_keywords'=>'','content_type'=>'blog_post','language'=>'en','content_html'=>'','notes'=>''];
$competitorContent = '';
$report = null;
$generatedJson = '';
$generatorError = null;
$savedReportFilename = null;
$selectedPageId = 0;
$pageLoaded = false;
$loadedPageTitle = '';

$seoPages = [];
try {
    $pdo = \core\Database::connection();
    $stmt = $pdo->query("SELECT id, title, slug FROM pages ORDER BY updated_at DESC LIMIT 100");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) { $seoPages[] = $row; }
} catch (\Exception $e) {}

// Handle GET page_id - auto-fill form with page data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['page_id']) && (int)$_GET['page_id'] > 0) {
    $selectedPageId = (int)$_GET['page_id'];
    try {
        $stmt = $pdo->prepare("SELECT id, title, content, slug, meta_title, meta_description, focus_keyword FROM pages WHERE id = ? LIMIT 1");
        $stmt->execute([$selectedPageId]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($page) {
            $form['title'] = $page['meta_title'] ?: $page['title'];
            $form['url'] = '/' . ltrim($page['slug'], '/');
            $form['content_html'] = $page['content'] ?? '';
            $form['focus_keyword'] = $page['focus_keyword'] ?? '';
            $pageLoaded = true;
            $loadedPageTitle = $page['title'];
        }
    } catch (\Exception $e) {}
}

// Handle GET article_id - auto-fill form with article data
$selectedArticleId = 0;
$articleLoaded = false;
$loadedArticleTitle = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['article_id']) && (int)$_GET['article_id'] > 0) {
    $selectedArticleId = (int)$_GET['article_id'];
    try {
        $stmt = $pdo->prepare("SELECT id, title, content, slug, meta_title, meta_description, focus_keyword FROM articles WHERE id = ? LIMIT 1");
        $stmt->execute([$selectedArticleId]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($article) {
            $form['title'] = !empty($article['meta_title']) ? $article['meta_title'] : $article['title'];
            $form['url'] = '/article/' . ltrim($article['slug'], '/');
            $form['content_html'] = $article['content'] ?? '';
            $form['focus_keyword'] = $article['focus_keyword'] ?? '';
            $articleLoaded = true;
            $loadedArticleTitle = $article['title'];
        }
    } catch (\Exception $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_validate_or_403();
    foreach (array_keys($form) as $key) { $form[$key] = trim($_POST[$key] ?? ''); }
    $competitorContent = trim($_POST['competitor_content'] ?? '');
    $selectedPageId = (int)($_POST['page_id'] ?? 0);
    $selectedArticleId = (int)($_POST['article_id'] ?? 0);

    if ($_POST['action'] === 'analyze_seo') {
        // Load page content if page_id selected
        if ($selectedPageId > 0) {
            try {
                $stmt = $pdo->prepare("SELECT title, content, slug, focus_keyword FROM pages WHERE id = ? LIMIT 1");
                $stmt->execute([$selectedPageId]);
                $page = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($page) {
                    $form['content_html'] = $page['content'];
                    if (empty($form['title'])) $form['title'] = $page['title'];
                    if (empty($form['url'])) $form['url'] = '/' . ltrim($page['slug'], '/');
                    if (empty($form['focus_keyword']) && !empty($page['focus_keyword'])) {
                        $form['focus_keyword'] = $page['focus_keyword'];
                    }
                }
            } catch (\Exception $e) {}
        }
        // Load article content if article_id selected
        if ($selectedArticleId > 0) {
            try {
                $stmt = $pdo->prepare("SELECT title, content, slug, focus_keyword FROM articles WHERE id = ? LIMIT 1");
                $stmt->execute([$selectedArticleId]);
                $article = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($article) {
                    $form['content_html'] = $article['content'];
                    if (empty($form['title'])) $form['title'] = $article['title'];
                    if (empty($form['url'])) $form['url'] = '/article/' . ltrim($article['slug'], '/');
                    if (empty($form['focus_keyword']) && !empty($article['focus_keyword'])) {
                        $form['focus_keyword'] = $article['focus_keyword'];
                    }
                }
            } catch (\Exception $e) {}
        }
        if (function_exists('ai_seo_assistant_analyze')) {
            $result = ai_seo_assistant_analyze($form);
            if ($result['ok']) {
                $report = $result['report'];
                $generatedJson = $result['json'];
                if (function_exists('ai_seo_assistant_save_report')) {
                    $savedReportFilename = ai_seo_assistant_save_report($result, [
                        'page_id' => $selectedPageId,
                        'article_id' => $selectedArticleId,
                        'title' => $form['title'],
                        'keyword' => $form['focus_keyword']
                    ]);
                }
            } else { $generatorError = $result['error']; }
        } else { $generatorError = 'AI SEO module not available'; }
    }
}

$csrf = $_SESSION['csrf_token'] ?? '';
$score = $report ? (int)($report['health_score'] ?? 0) : 0;
$scoreClass = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger');
$wordCount = str_word_count(strip_tags($form['content_html']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI SEO Assistant - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --bg-primary: #11111b;
    --bg-secondary: #181825;
    --bg-tertiary: #1e1e2e;
    --bg-surface: #313244;
    --bg-overlay: #45475a;
    --text-primary: #cdd6f4;
    --text-secondary: #bac2de;
    --text-muted: #6c7086;
    --accent-color: #89b4fa;
    --accent-hover: #b4befe;
    --success: #a6e3a1;
    --warning: #f9e2af;
    --danger: #f38ba8;
    --purple: #cba6f7;
    --teal: #94e2d5;
    --pink: #f5c2e7;
    --border-color: #313244;
    --border-highlight: #45475a;
    --shadow: 0 4px 24px rgba(0,0,0,0.4);
    --radius: 16px;
    --radius-sm: 10px;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 14px;
    line-height: 1.6;
    min-height: 100vh;
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
    border-bottom: 1px solid var(--border-color);
    padding: 24px 32px;
    margin-bottom: 24px;
}
.page-header-inner {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}
.page-title-section {
    display: flex;
    align-items: center;
    gap: 16px;
}
.page-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--accent-color), var(--purple));
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    box-shadow: var(--shadow);
}
.page-title h1 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 4px;
    background: linear-gradient(135deg, var(--text-primary), var(--accent-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.page-title p {
    font-size: 14px;
    color: var(--text-muted);
}
.page-actions {
    display: flex;
    gap: 12px;
}

/* Stats Row */
.stats-row {
    max-width: 1400px;
    margin: 0 auto 24px;
    padding: 0 32px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}
.stat-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
}
.stat-card:hover {
    border-color: var(--border-highlight);
    transform: translateY(-2px);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.stat-icon.blue { background: rgba(137, 180, 250, 0.15); }
.stat-icon.green { background: rgba(166, 227, 161, 0.15); }
.stat-icon.purple { background: rgba(203, 166, 247, 0.15); }
.stat-icon.yellow { background: rgba(249, 226, 175, 0.15); }
.stat-content .stat-value {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
}
.stat-content .stat-label {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Container */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 32px 32px;
}

/* Grid Layout */
.grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
}
@media (max-width: 1100px) { .grid { grid-template-columns: 1fr; } }

/* Cards */
.card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    margin-bottom: 24px;
    overflow: hidden;
    transition: border-color 0.2s ease;
}
.card:hover { border-color: var(--border-highlight); }
.card-head {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-tertiary);
}
.card-head.success { background: rgba(166, 227, 161, 0.08); border-left: 4px solid var(--success); }
.card-head.warning { background: rgba(249, 226, 175, 0.08); border-left: 4px solid var(--warning); }
.card-head.danger { background: rgba(243, 139, 168, 0.08); border-left: 4px solid var(--danger); }
.card-title {
    font-size: 15px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}
.card-body { padding: 24px; }

/* Alerts */
.alert {
    padding: 18px 24px;
    border-radius: var(--radius);
    margin-bottom: 24px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    animation: slideIn 0.3s ease;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.alert-icon {
    font-size: 20px;
    line-height: 1;
}
.alert-warn {
    background: linear-gradient(135deg, rgba(249, 226, 175, 0.1), rgba(249, 226, 175, 0.05));
    border: 1px solid rgba(249, 226, 175, 0.3);
    color: var(--warning);
}
.alert-err {
    background: linear-gradient(135deg, rgba(243, 139, 168, 0.1), rgba(243, 139, 168, 0.05));
    border: 1px solid rgba(243, 139, 168, 0.3);
    color: var(--danger);
}
.alert-ok {
    background: linear-gradient(135deg, rgba(166, 227, 161, 0.1), rgba(166, 227, 161, 0.05));
    border: 1px solid rgba(166, 227, 161, 0.3);
    color: var(--success);
}
.alert a { color: inherit; text-decoration: underline; }

/* Forms */
.form-group { margin-bottom: 24px; }
.form-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--text-secondary);
}
.form-label .req { color: var(--danger); margin-left: 4px; }
.form-hint {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 8px;
}
.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 14px 18px;
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-family: inherit;
    font-size: 14px;
    transition: all 0.2s ease;
}
.form-input:hover, .form-select:hover, .form-textarea:hover {
    border-color: var(--border-highlight);
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 4px rgba(137, 180, 250, 0.15);
}
.form-input::placeholder, .form-textarea::placeholder {
    color: var(--text-muted);
}
.form-textarea {
    min-height: 140px;
    resize: vertical;
    font-family: 'SF Mono', Monaco, monospace;
    font-size: 13px;
    line-height: 1.5;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

/* Select enhancement */
.form-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236c7086' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 44px;
}
.form-select option {
    background: var(--bg-secondary);
    color: var(--text-primary);
    padding: 12px;
}

/* Page Selector Enhanced */
.page-selector-wrap {
    position: relative;
}
.page-selector-current {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all 0.2s ease;
}
.page-selector-current:hover {
    border-color: var(--accent-color);
}
.page-selector-current.selected {
    border-color: var(--success);
    background: rgba(166, 227, 161, 0.05);
}
.page-selector-icon {
    width: 36px;
    height: 36px;
    background: var(--bg-surface);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}
.page-selector-current.selected .page-selector-icon {
    background: rgba(166, 227, 161, 0.15);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 28px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    border: none;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}
.btn-primary {
    background: linear-gradient(135deg, var(--accent-color), var(--purple));
    color: var(--bg-primary);
    box-shadow: 0 4px 16px rgba(137, 180, 250, 0.25);
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(137, 180, 250, 0.35);
}
.btn-secondary {
    background: var(--bg-surface);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}
.btn-secondary:hover {
    background: var(--bg-overlay);
    border-color: var(--border-highlight);
}
.btn-sm {
    padding: 10px 18px;
    font-size: 13px;
}
.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

/* Score Display */
.score-box { text-align: center; padding: 24px; }
.score-circle {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    border: 5px solid var(--bg-surface);
    background: var(--bg-primary);
    position: relative;
}
.score-circle::before {
    content: '';
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    padding: 5px;
    background: conic-gradient(var(--bg-surface) 0deg, var(--bg-surface) 360deg);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
}
.score-circle.success { border-color: var(--success); }
.score-circle.success::before { background: conic-gradient(var(--success) calc(var(--score) * 3.6deg), var(--bg-surface) 0deg); }
.score-circle.warning { border-color: var(--warning); }
.score-circle.warning::before { background: conic-gradient(var(--warning) calc(var(--score) * 3.6deg), var(--bg-surface) 0deg); }
.score-circle.danger { border-color: var(--danger); }
.score-circle.danger::before { background: conic-gradient(var(--danger) calc(var(--score) * 3.6deg), var(--bg-surface) 0deg); }
.score-val {
    font-size: 42px;
    font-weight: 700;
    line-height: 1;
    position: relative;
    z-index: 1;
}
.score-val.success { color: var(--success); }
.score-val.warning { color: var(--warning); }
.score-val.danger { color: var(--danger); }
.score-lbl {
    font-size: 13px;
    color: var(--text-muted);
    position: relative;
    z-index: 1;
}

/* Progress Items */
.progress-item { margin-bottom: 20px; }
.progress-head {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 500;
}
.progress-bar {
    height: 10px;
    background: var(--bg-surface);
    border-radius: 5px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    border-radius: 5px;
    transition: width 0.5s ease;
}
.progress-fill.success { background: linear-gradient(90deg, var(--success), var(--teal)); }
.progress-fill.warning { background: linear-gradient(90deg, var(--warning), #fab387); }
.progress-fill.danger { background: linear-gradient(90deg, var(--danger), var(--pink)); }

/* Tags */
.tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: var(--bg-surface);
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    margin: 3px;
}
.tag.success { background: rgba(166, 227, 161, 0.15); color: var(--success); }
.tag.warning { background: rgba(249, 226, 175, 0.15); color: var(--warning); }
.tag.danger { background: rgba(243, 139, 168, 0.15); color: var(--danger); }
.tag.purple { background: rgba(203, 166, 247, 0.15); color: var(--purple); }

/* Tabs */
.tabs {
    display: flex;
    gap: 6px;
    background: var(--bg-primary);
    padding: 6px;
    border-radius: var(--radius);
    margin-bottom: 24px;
    border: 1px solid var(--border-color);
}
.tab {
    flex: 1;
    padding: 12px 16px;
    background: transparent;
    border: none;
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.tab:hover { color: var(--text-primary); background: var(--bg-surface); }
.tab.active {
    background: linear-gradient(135deg, var(--accent-color), var(--purple));
    color: var(--bg-primary);
}
.tab-content { display: none; }
.tab-content.active { display: block; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

/* Data Tables */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.data-table th, .data-table td {
    padding: 14px 18px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}
.data-table th {
    font-weight: 600;
    color: var(--text-muted);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: var(--bg-tertiary);
}
.data-table tr:hover td { background: rgba(137, 180, 250, 0.03); }
.data-table tr:last-child td { border-bottom: none; }

/* Task Items */
.task-item {
    display: flex;
    gap: 14px;
    padding: 16px;
    background: var(--bg-primary);
    border-radius: var(--radius-sm);
    margin-bottom: 12px;
    border-left: 4px solid var(--border-color);
    transition: all 0.2s ease;
}
.task-item:hover { transform: translateX(4px); }
.task-item.high { border-left-color: var(--danger); }
.task-item.medium { border-left-color: var(--warning); }
.task-item.low { border-left-color: var(--text-muted); }
.task-priority {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 6px;
    white-space: nowrap;
}
.task-priority.high { background: rgba(243, 139, 168, 0.15); color: var(--danger); }
.task-priority.medium { background: rgba(249, 226, 175, 0.15); color: var(--warning); }
.task-priority.low { background: var(--bg-surface); color: var(--text-muted); }

/* Empty State */
.empty {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-muted);
}
.empty-icon {
    font-size: 56px;
    margin-bottom: 16px;
    opacity: 0.4;
}

/* Output Box */
.output-box {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 18px;
    font-family: 'SF Mono', Monaco, monospace;
    font-size: 12px;
    max-height: 450px;
    overflow-y: auto;
    white-space: pre-wrap;
    line-height: 1.6;
}

/* Sidebar */
.sidebar { display: flex; flex-direction: column; gap: 24px; }

/* Stat Grid */
.stat-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.stat-box {
    background: var(--bg-primary);
    border-radius: var(--radius-sm);
    padding: 18px;
    text-align: center;
    border: 1px solid var(--border-color);
}
.stat-box .stat-val {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 4px;
}
.stat-box .stat-lbl {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
}

/* Keyword Pills */
.kw-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.kw-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 13px;
    transition: all 0.2s ease;
}
.kw-pill:hover { border-color: var(--border-highlight); }
.kw-pill.used { border-color: var(--success); background: rgba(166, 227, 161, 0.05); }
.kw-pill.missing { border-color: var(--warning); background: rgba(249, 226, 175, 0.05); }
.kw-pill .cnt {
    background: var(--bg-surface);
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
}

/* Word Counter */
.word-counter {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: var(--bg-surface);
    border-radius: 6px;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
}
.word-counter.active { color: var(--accent-color); }

/* How It Works */
.how-item {
    display: flex;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid var(--border-color);
}
.how-item:last-child { border-bottom: none; }
.how-num {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, var(--accent-color), var(--purple));
    color: var(--bg-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}
.how-text strong {
    display: block;
    margin-bottom: 2px;
}
.how-text span {
    font-size: 12px;
    color: var(--text-muted);
}

/* Analysis Tags */
.analysis-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.analysis-tag {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 12px;
    transition: all 0.2s ease;
}
.analysis-tag:hover {
    border-color: var(--accent-color);
    transform: translateY(-2px);
}

/* Scrollbar */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: var(--bg-primary); }
::-webkit-scrollbar-thumb { background: var(--bg-surface); border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: var(--bg-overlay); }
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🎯',
    'title' => 'AI SEO Assistant',
    'description' => 'Analyze and optimize your content for search engines',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--accent-color), var(--purple)',
    'actions' => [
        ['type' => 'link', 'url' => '/admin/ai-seo-reports', 'text' => '📊 View Reports', 'class' => 'secondary'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<?php if ($pageLoaded && !$report): ?>
<!-- Stats Row - Show when page is loaded -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon blue">📄</div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($wordCount) ?></div>
            <div class="stat-label">Words</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">🎯</div>
        <div class="stat-content">
            <div class="stat-value"><?= !empty($form['focus_keyword']) ? esc(substr($form['focus_keyword'], 0, 15)) . (strlen($form['focus_keyword']) > 15 ? '…' : '') : 'Not Set' ?></div>
            <div class="stat-label">Focus Keyword</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">📑</div>
        <div class="stat-content">
            <div class="stat-value"><?= preg_match_all('/<h[1-6][^>]*>/i', $form['content_html']) ?></div>
            <div class="stat-label">Headings</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow">🔗</div>
        <div class="stat-content">
            <div class="stat-value"><?= preg_match_all('/<a[^>]+href/i', $form['content_html']) ?></div>
            <div class="stat-label">Links</div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container">

<?php if (!$aiConfigured): ?>
<div class="alert alert-warn">
    <span class="alert-icon">⚠️</span>
    <div>
        <strong>AI Provider Not Configured</strong><br>
        Please configure Hugging Face API in <a href="/admin/hf-settings.php">AI Settings</a> to enable SEO analysis.
    </div>
</div>
<?php endif; ?>

<?php if ($generatorError): ?>
<div class="alert alert-err">
    <span class="alert-icon">❌</span>
    <div><strong>Analysis Error:</strong> <?= esc($generatorError) ?></div>
</div>
<?php endif; ?>

<?php if ($pageLoaded && !$report): ?>
<div class="alert alert-ok">
    <span class="alert-icon">✅</span>
    <div>
        <strong>Page Loaded: <?= esc($loadedPageTitle) ?></strong><br>
        <?= !empty($form['focus_keyword']) ? 'Focus keyword is set. Click <strong>Analyze SEO</strong> to run the analysis.' : 'Enter a focus keyword and click <strong>Analyze SEO</strong> to begin.' ?>
    </div>
</div>
<?php endif; ?>

<?php if ($articleLoaded && !$report): ?>
<div class="alert alert-ok">
    <span class="alert-icon">📝</span>
    <div>
        <strong>Article Loaded: <?= esc($loadedArticleTitle) ?></strong><br>
        <?= !empty($form['focus_keyword']) ? 'Focus keyword is set. Click <strong>Analyze SEO</strong> to run the analysis.' : 'Enter a focus keyword and click <strong>Analyze SEO</strong> to begin.' ?>
    </div>
</div>
<?php endif; ?>

<?php if ($report): ?>
<!-- ============ RESULTS VIEW ============ -->
<div class="grid">
<div class="main">

<!-- Score Card -->
<div class="card">
<div class="card-head <?= $scoreClass ?>">
    <span class="card-title"><span>✅</span> Analysis Complete</span>
    <?php if ($savedReportFilename): ?><span class="tag success">💾 Saved</span><?php endif; ?>
</div>
<div class="card-body" style="display:grid;grid-template-columns:180px 1fr;gap:28px;align-items:center;">
    <div class="score-box">
        <div class="score-circle <?= $scoreClass ?>" style="--score: <?= $score ?>">
            <span class="score-val <?= $scoreClass ?>"><?= $score ?></span>
            <span class="score-lbl">/ 100</span>
        </div>
        <span class="tag <?= $scoreClass ?>" style="margin-top:12px;">
            <?= $score >= 80 ? '🏆 Excellent' : ($score >= 60 ? '👍 Good' : '⚠️ Needs Work') ?>
        </span>
    </div>
    <div>
        <h3 style="font-size:20px;margin-bottom:12px"><?= esc($form['title'] ?: 'Untitled Content') ?></h3>
        <p style="color:var(--text-secondary);margin-bottom:18px;line-height:1.7"><?= esc($report['summary'] ?? 'SEO analysis complete. Review the detailed breakdown below.') ?></p>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <span class="tag purple">🎯 <?= esc($form['focus_keyword']) ?></span>
            <span class="tag">🌐 <?= strtoupper($form['language']) ?></span>
            <span class="tag">📄 <?= ucfirst(str_replace('_', ' ', $form['content_type'])) ?></span>
            <?php $wc = str_word_count(strip_tags($form['content_html'])); ?>
            <span class="tag">📝 <?= number_format($wc) ?> words</span>
        </div>
    </div>
</div>
</div>

<!-- Score Breakdown -->
<?php $breakdown = $report['content_score_breakdown'] ?? []; if (!empty($breakdown)): ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>📊</span> Score Breakdown</span></div>
<div class="card-body">
<?php
$factors = [
    'word_count' => ['📝', 'Word Count'],
    'headings' => ['📑', 'Headings Structure'],
    'keywords' => ['🎯', 'Keyword Usage'],
    'structure' => ['🏗️', 'Content Structure'],
    'media' => ['🖼️', 'Media & Images'],
    'links' => ['🔗', 'Internal/External Links']
];
foreach ($breakdown as $k => $d): if (!is_array($d)) continue;
$s = (int)($d['score'] ?? 0);
$c = $s >= 70 ? 'success' : ($s >= 50 ? 'warning' : 'danger');
$f = $factors[$k] ?? ['📌', ucfirst(str_replace('_', ' ', $k))];
?>
<div class="progress-item">
    <div class="progress-head">
        <span><?= $f[0] ?> <?= $f[1] ?></span>
        <span style="color: var(--<?= $c ?>)"><?= $s ?>/100</span>
    </div>
    <div class="progress-bar"><div class="progress-fill <?= $c ?>" style="width:<?= $s ?>%"></div></div>
    <?php if (!empty($d['note'])): ?><p class="form-hint"><?= esc($d['note']) ?></p><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="tabs">
    <button class="tab active" data-tab="keywords">🎯 Keywords</button>
    <button class="tab" data-tab="tasks">✅ Tasks</button>
    <button class="tab" data-tab="serp">🏆 SERP</button>
    <button class="tab" data-tab="nlp">🧠 NLP</button>
    <button class="tab" data-tab="json">📋 JSON</button>
</div>

<!-- Keywords Tab -->
<div class="tab-content active" id="tab-keywords">
<?php $kws = $report['keyword_difficulty'] ?? []; $contentLower = mb_strtolower($form['content_html']); ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>🎯</span> Keyword Analysis</span></div>
<div class="card-body">
<?php if (!empty($kws)): ?>
<div class="kw-grid" style="margin-bottom:24px">
<?php foreach ($kws as $kw):
    $kwt = $kw['keyword'] ?? '';
    $cnt = mb_substr_count($contentLower, mb_strtolower($kwt));
    $used = $cnt > 0;
?>
<div class="kw-pill <?= $used ? 'used' : 'missing' ?>">
    <span><?= esc($kwt) ?></span>
    <span class="cnt"><?= $cnt ?>×</span>
</div>
<?php endforeach; ?>
</div>
<table class="data-table">
<thead>
    <tr><th>Keyword</th><th>Difficulty</th><th>Level</th><th>Count</th><th>Status</th></tr>
</thead>
<tbody>
<?php foreach ($kws as $kw):
    $kwt = $kw['keyword'] ?? '';
    $cnt = mb_substr_count($contentLower, mb_strtolower($kwt));
    $used = $cnt > 0;
    $lvl = strtolower($kw['level'] ?? 'medium');
?>
<tr>
    <td><strong><?= esc($kwt) ?></strong></td>
    <td><?= (int)($kw['difficulty'] ?? 0) ?></td>
    <td><span class="tag <?= $lvl === 'easy' ? 'success' : ($lvl === 'hard' ? 'danger' : 'warning') ?>"><?= ucfirst($lvl) ?></span></td>
    <td><?= $cnt ?></td>
    <td><?= $used ? '<span class="tag success">✓ Used</span>' : '<span class="tag warning">Missing</span>' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<div class="empty"><div class="empty-icon">🎯</div><p>No keyword data available</p></div>
<?php endif; ?>
</div>
</div>
</div>

<!-- Tasks Tab -->
<div class="tab-content" id="tab-tasks">
<?php $tasks = $report['actionable_tasks'] ?? []; ?>
<div class="card">
<div class="card-head warning">
    <span class="card-title"><span>✅</span> Actionable Tasks</span>
    <span class="tag"><?= count($tasks) ?> items</span>
</div>
<div class="card-body">
<?php if (!empty($tasks)): ?>
<?php foreach ($tasks as $t): $p = $t['priority'] ?? 'medium'; ?>
<div class="task-item <?= $p ?>">
    <span class="task-priority <?= $p ?>"><?= ucfirst($p) ?></span>
    <div style="flex:1">
        <div style="font-weight:600;margin-bottom:4px"><?= esc($t['task'] ?? '') ?></div>
        <div style="font-size:13px;color:var(--text-muted)"><?= esc($t['impact'] ?? '') ?></div>
    </div>
    <span class="tag"><?= ucfirst($t['category'] ?? 'content') ?></span>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="empty"><div class="empty-icon">✅</div><p>No tasks - your content is well optimized!</p></div>
<?php endif; ?>
</div>
</div>

<?php $qw = $report['quick_wins'] ?? []; if (!empty($qw)): ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>⚡</span> Quick Wins</span></div>
<div class="card-body">
    <ul style="margin:0;padding-left:24px;display:flex;flex-direction:column;gap:10px">
    <?php foreach ($qw as $w): ?>
        <li style="color:var(--text-secondary)"><?= esc($w) ?></li>
    <?php endforeach; ?>
    </ul>
</div>
</div>
<?php endif; ?>
</div>

<!-- SERP Tab -->
<div class="tab-content" id="tab-serp">
<?php $serp = $report['serp_profile'] ?? []; $comps = $serp['competitors'] ?? []; ?>
<div class="card">
<div class="card-head" style="background:rgba(203,166,247,0.08);border-left:4px solid var(--purple)">
    <span class="card-title"><span>🏆</span> SERP Analysis</span>
</div>
<div class="card-body">
<?php if (!empty($comps)): ?>
<div class="stat-grid" style="margin-bottom:24px">
    <div class="stat-box">
        <div class="stat-val" style="color:var(--accent-color)"><?= number_format($serp['median_word_count'] ?? 0) ?></div>
        <div class="stat-lbl">Median Words</div>
    </div>
    <div class="stat-box">
        <div class="stat-val" style="color:var(--warning)"><?= $serp['median_headings'] ?? 0 ?></div>
        <div class="stat-lbl">Median Headings</div>
    </div>
    <div class="stat-box">
        <div class="stat-val" style="color:var(--success)"><?= number_format($wc) ?></div>
        <div class="stat-lbl">Your Words</div>
    </div>
    <div class="stat-box">
        <div class="stat-val" style="color:var(--purple)"><?= esc($serp['your_position_estimate'] ?? '—') ?></div>
        <div class="stat-lbl">Est. Position</div>
    </div>
</div>
<table class="data-table">
<thead>
    <tr><th>#</th><th>Title</th><th>Words</th><th>Headings</th></tr>
</thead>
<tbody>
<?php foreach ($comps as $c): ?>
<tr>
    <td><?= (int)($c['position'] ?? 0) ?></td>
    <td><?= esc($c['title'] ?? '') ?></td>
    <td><?= number_format($c['word_count'] ?? 0) ?></td>
    <td><?= $c['headings_count'] ?? 0 ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<div class="empty"><div class="empty-icon">🏆</div><p>No SERP data available for this analysis</p></div>
<?php endif; ?>
</div>
</div>
</div>

<!-- NLP Tab -->
<div class="tab-content" id="tab-nlp">
<?php $nlp = $report['semantic_terms'] ?? []; ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>🧠</span> NLP Semantic Terms</span></div>
<div class="card-body">
<?php if (!empty($nlp)):
$good = $low = $high = 0;
foreach ($nlp as $t) {
    $st = $t['status'] ?? 'good';
    if ($st === 'good') $good++;
    elseif ($st === 'too_low') $low++;
    else $high++;
}
$total = count($nlp);
$cov = $total > 0 ? round(($good / $total) * 100) : 0;
?>
<div class="stat-grid" style="margin-bottom:24px">
    <div class="stat-box">
        <div class="stat-val" style="color:<?= $cov >= 70 ? 'var(--success)' : ($cov >= 40 ? 'var(--warning)' : 'var(--danger)') ?>"><?= $cov ?>%</div>
        <div class="stat-lbl">Coverage</div>
    </div>
    <div class="stat-box">
        <div class="stat-val" style="color:var(--success)"><?= $good ?></div>
        <div class="stat-lbl">Good</div>
    </div>
    <div class="stat-box">
        <div class="stat-val" style="color:var(--warning)"><?= $low ?></div>
        <div class="stat-lbl">Too Low</div>
    </div>
    <div class="stat-box">
        <div class="stat-val" style="color:var(--danger)"><?= $high ?></div>
        <div class="stat-lbl">Too High</div>
    </div>
</div>
<table class="data-table">
<thead>
    <tr><th>Term</th><th>Recommended</th><th>Your Count</th><th>Status</th></tr>
</thead>
<tbody>
<?php foreach ($nlp as $t): $st = $t['status'] ?? 'good'; ?>
<tr>
    <td><?= esc($t['term'] ?? '') ?></td>
    <td><?= ($t['recommended_min'] ?? 1) ?>–<?= ($t['recommended_max'] ?? 5) ?></td>
    <td><?= ($t['your_count'] ?? 0) ?></td>
    <td><span class="tag <?= $st === 'good' ? 'success' : ($st === 'too_low' ? 'warning' : 'danger') ?>"><?= ucfirst(str_replace('_', ' ', $st)) ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<div class="empty"><div class="empty-icon">🧠</div><p>No NLP semantic data available</p></div>
<?php endif; ?>
</div>
</div>
</div>

<!-- JSON Tab -->
<div class="tab-content" id="tab-json">
<div class="card">
<div class="card-head">
    <span class="card-title"><span>📋</span> Raw JSON Output</span>
    <button class="btn btn-secondary btn-sm" id="copyJsonBtn">📋 Copy JSON</button>
</div>
<div class="card-body">
    <div class="output-box" id="json-out"><?= esc($generatedJson) ?></div>
</div>
</div>
</div>

</div>

<!-- Sidebar -->
<div class="sidebar">
<div class="card">
<div class="card-head"><span class="card-title"><span>🔄</span> Actions</span></div>
<div class="card-body">
    <a href="/admin/ai-seo-assistant.php" class="btn btn-primary" style="width:100%;margin-bottom:14px">✏️ New Analysis</a>
    <a href="/admin/ai-seo-reports.php" class="btn btn-secondary" style="width:100%">📊 All Reports</a>
</div>
</div>

<?php $recWc = $report['recommended_word_count'] ?? null; if ($recWc): ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>💡</span> Recommendation</span></div>
<div class="card-body">
    <div class="stat-box" style="border:none;background:transparent;padding:0">
        <div class="stat-val" style="color:var(--accent-color)"><?= number_format($recWc) ?></div>
        <div class="stat-lbl">Target Word Count</div>
    </div>
    <?php if ($wc < $recWc): ?>
    <p class="form-hint" style="margin-top:12px;text-align:center">Add ~<?= number_format($recWc - $wc) ?> more words to match top competitors</p>
    <?php endif; ?>
</div>
</div>
<?php endif; ?>

<?php $flags = $report['technical_flags'] ?? []; if (!empty($flags)): ?>
<div class="card">
<div class="card-head warning"><span class="card-title"><span>⚠️</span> Technical Flags</span></div>
<div class="card-body">
    <ul style="margin:0;padding-left:20px;color:var(--warning);display:flex;flex-direction:column;gap:8px">
    <?php foreach ($flags as $f): ?>
        <li><?= esc($f) ?></li>
    <?php endforeach; ?>
    </ul>
</div>
</div>
<?php endif; ?>
</div>
</div>

<?php else: ?>
<!-- ============ INPUT FORM ============ -->
<div class="grid">
<div class="main">
<div class="card">
<div class="card-head"><span class="card-title"><span>📝</span> Content Details</span></div>
<div class="card-body">
<form method="POST" id="seoForm">
<input type="hidden" name="csrf_token" value="<?= esc($csrf) ?>">
<input type="hidden" name="action" value="analyze_seo">
<input type="hidden" name="article_id" value="<?= (int)$selectedArticleId ?>">

<?php if (!empty($seoPages)): ?>
<div class="form-group">
    <label class="form-label">
        <span>Select CMS Page</span>
        <span class="word-counter" id="pageStatus"><?= $selectedPageId > 0 ? '✓ Page Selected' : 'No page selected' ?></span>
    </label>
    <select name="page_id" class="form-select" id="pageSelect">
        <option value="">— Enter content manually —</option>
        <?php foreach ($seoPages as $pg): ?>
        <option value="<?= (int)$pg['id'] ?>" <?= $selectedPageId === (int)$pg['id'] ? 'selected' : '' ?>>
            [<?= $pg['id'] ?>] <?= esc($pg['title']) ?> (<?= esc($pg['slug']) ?>)
        </option>
        <?php endforeach; ?>
    </select>
    <p class="form-hint">Select a page to auto-load its content, or enter manually below</p>
</div>
<?php endif; ?>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">Page Title</label>
        <input type="text" name="title" class="form-input" value="<?= esc($form['title']) ?>" placeholder="Enter your page title">
    </div>
    <div class="form-group">
        <label class="form-label">URL / Slug</label>
        <input type="text" name="url" class="form-input" value="<?= esc($form['url']) ?>" placeholder="/blog/my-awesome-post">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">Focus Keyword <span class="req">*</span></label>
        <input type="text" name="focus_keyword" class="form-input" value="<?= esc($form['focus_keyword']) ?>" placeholder="e.g., SEO optimization tips" required>
    </div>
    <div class="form-group">
        <label class="form-label">Secondary Keywords</label>
        <input type="text" name="secondary_keywords" class="form-input" value="<?= esc($form['secondary_keywords']) ?>" placeholder="on-page SEO, search ranking, meta tags">
        <p class="form-hint">Comma-separated list of related keywords</p>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">Content Type</label>
        <select name="content_type" class="form-select">
            <option value="blog_post" <?= $form['content_type'] === 'blog_post' ? 'selected' : '' ?>>📝 Blog Post</option>
            <option value="landing_page" <?= $form['content_type'] === 'landing_page' ? 'selected' : '' ?>>🎯 Landing Page</option>
            <option value="product" <?= $form['content_type'] === 'product' ? 'selected' : '' ?>>🛒 Product Page</option>
            <option value="other" <?= $form['content_type'] === 'other' ? 'selected' : '' ?>>📄 Other</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Language</label>
        <select name="language" class="form-select">
            <option value="en" <?= $form['language'] === 'en' ? 'selected' : '' ?>>🇺🇸 English</option>
            <option value="pl" <?= $form['language'] === 'pl' ? 'selected' : '' ?>>🇵🇱 Polski</option>
            <option value="de" <?= $form['language'] === 'de' ? 'selected' : '' ?>>🇩🇪 Deutsch</option>
            <option value="fr" <?= $form['language'] === 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
            <option value="es" <?= $form['language'] === 'es' ? 'selected' : '' ?>>🇪🇸 Español</option>
        </select>
    </div>
</div>

<div class="form-group">
    <label class="form-label">
        <span>Content <span class="req">*</span></span>
        <span class="word-counter" id="wordCounter">0 words</span>
    </label>
    <textarea name="content_html" class="form-textarea" id="contentArea" rows="14" required placeholder="Paste your HTML content here..."><?= esc($form['content_html']) ?></textarea>
</div>

<div class="form-group">
    <label class="form-label">Competitor Content <span style="font-weight:400;color:var(--text-muted)">(Optional)</span></label>
    <textarea name="competitor_content" class="form-textarea" rows="6" placeholder="Paste a top-ranking competitor's article for comparison..."><?= esc($competitorContent) ?></textarea>
    <p class="form-hint">Adding competitor content helps with SERP comparison analysis</p>
</div>

<div class="form-group">
    <label class="form-label">Additional Notes <span style="font-weight:400;color:var(--text-muted)">(Optional)</span></label>
    <textarea name="notes" class="form-textarea" rows="3" placeholder="Any specific SEO goals or context..."><?= esc($form['notes']) ?></textarea>
</div>

<div style="display:flex;gap:14px;align-items:center">
    <button type="submit" class="btn btn-primary" <?= !$aiConfigured ? 'disabled' : '' ?>>
        🔍 Analyze SEO
    </button>
    <?php if (!$aiConfigured): ?>
    <span style="font-size:13px;color:var(--text-muted)">Configure AI provider to enable analysis</span>
    <?php endif; ?>
</div>
</form>
</div>
</div>
</div>

<div class="sidebar">
<div class="card">
<div class="card-head"><span class="card-title"><span>💡</span> How It Works</span></div>
<div class="card-body">
    <div class="how-item">
        <span class="how-num">1</span>
        <div class="how-text">
            <strong>Input Content</strong>
            <span>Select a page or paste your content with focus keyword</span>
        </div>
    </div>
    <div class="how-item">
        <span class="how-num">2</span>
        <div class="how-text">
            <strong>AI Analysis</strong>
            <span>Our AI analyzes SEO factors and compares with competitors</span>
        </div>
    </div>
    <div class="how-item">
        <span class="how-num">3</span>
        <div class="how-text">
            <strong>Get Insights</strong>
            <span>Receive detailed score breakdown and actionable tasks</span>
        </div>
    </div>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📊</span> What We Analyze</span></div>
<div class="card-body">
    <div class="analysis-tags">
        <span class="analysis-tag">🎯 Keywords</span>
        <span class="analysis-tag">📑 Headings</span>
        <span class="analysis-tag">📝 Word Count</span>
        <span class="analysis-tag">🔗 Links</span>
        <span class="analysis-tag">📖 Readability</span>
        <span class="analysis-tag">🖼️ Images</span>
        <span class="analysis-tag">🏷️ Meta Tags</span>
        <span class="analysis-tag">🏆 SERP Position</span>
        <span class="analysis-tag">🧠 NLP Terms</span>
    </div>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📈</span> Tips for Better Scores</span></div>
<div class="card-body">
    <ul style="margin:0;padding-left:20px;display:flex;flex-direction:column;gap:10px;color:var(--text-secondary);font-size:13px">
        <li>Use focus keyword in title, URL, and first 100 words</li>
        <li>Add 2-3 H2 headings with related keywords</li>
        <li>Include internal and external links</li>
        <li>Aim for 1,500+ words for blog posts</li>
        <li>Add images with descriptive alt text</li>
    </ul>
</div>
</div>
</div>
</div>
<?php endif; ?>
</div>

<script>
(function() {
    // Tab switching
    document.querySelectorAll('.tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var tabId = this.getAttribute('data-tab');
            document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            var content = document.getElementById('tab-' + tabId);
            if (content) content.classList.add('active');
        });
    });

    // Copy JSON button
    var copyBtn = document.getElementById('copyJsonBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var jsonText = document.getElementById('json-out').textContent;
            navigator.clipboard.writeText(jsonText).then(function() {
                copyBtn.innerHTML = '✅ Copied!';
                setTimeout(function() { copyBtn.innerHTML = '📋 Copy JSON'; }, 2000);
            });
        });
    }

    // Word counter
    var contentArea = document.getElementById('contentArea');
    var wordCounter = document.getElementById('wordCounter');
    if (contentArea && wordCounter) {
        function updateWordCount() {
            var text = contentArea.value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            var words = text ? text.split(' ').length : 0;
            wordCounter.textContent = words.toLocaleString() + ' words';
            wordCounter.classList.toggle('active', words > 0);
        }
        contentArea.addEventListener('input', updateWordCount);
        updateWordCount();
    }

    // Page selector redirect
    var pageSelect = document.getElementById('pageSelect');
    if (pageSelect) {
        pageSelect.addEventListener('change', function() {
            var pageId = this.value;
            if (pageId && pageId !== '') {
                window.location.href = '/admin/ai-seo-assistant.php?page_id=' + pageId;
            } else {
                window.location.href = '/admin/ai-seo-assistant.php';
            }
        });
    }
})();
</script>
</body>
</html>
