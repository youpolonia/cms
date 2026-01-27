<?php
/**
 * AI SEO Research - Content Research & Keyword Extraction
 * Similar to NeuronWriter / Surfer SEO
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_seo_research.php';
require_once CMS_ROOT . '/core/ai_content.php';
require_once CMS_ROOT . '/core/ai_models.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$msg = '';
$msgType = '';
$research = null;
$activeView = $_GET['view'] ?? 'list';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'new_research':
            $keyword = trim($_POST['keyword'] ?? '');
            if ($keyword) {
                set_time_limit(120);
                $research = seo_research_run($keyword, 15);
                
                if ($research['status'] === 'completed') {
                    $msg = 'Research completed! Analyzed ' . $research['pages_analyzed'] . ' pages.';
                    $msgType = 'success';
                    $activeView = 'results';
                } else {
                    $msg = 'Research failed: ' . implode(', ', $research['errors'] ?? ['Unknown error']);
                    $msgType = 'danger';
                }
            }
            break;
            
        case 'delete_research':
            $keyword = trim($_POST['keyword'] ?? '');
            if ($keyword && seo_research_delete($keyword)) {
                $msg = 'Research deleted.';
                $msgType = 'success';
            }
            break;
            
        case 'generate_headings':
            header('Content-Type: application/json');
            $keyword = trim($_POST['keyword'] ?? '');
            $topKeywords = $_POST['keywords'] ?? '';
            
            if (!$keyword) {
                echo json_encode(['ok' => false, 'error' => 'Keyword required']);
                exit;
            }
            
            $aiConfig = ai_config_load();
            if (empty($aiConfig['api_key'])) {
                echo json_encode(['ok' => false, 'error' => 'AI not configured. Go to AI Settings.']);
                exit;
            }
            
            $prompt = "Write 7 H2 headings for an article about: \"{$keyword}\"

RULES:
- One heading per line, plain text only
- 5-10 words each, specific to the topic
- NO numbering, bullets, or markdown
- NO generic headings like 'Introduction' or 'Part 1'

REQUIRED ORDER:
1-2: What/How (explain the topic)
3-5: Benefits, tips, best practices, common mistakes
6: FAQ (optional)
7: Conclusion

EXAMPLE for 'email marketing':
What Is Email Marketing and How It Works
Key Benefits of Email Campaigns for Business
How to Build and Grow Your Email List
Best Practices for Higher Open Rates
Common Email Marketing Mistakes to Avoid
Frequently Asked Questions About Email Marketing
Conclusion and Next Steps

Now write 7 headings for \"{$keyword}\":";
            
            // Get provider and model from POST
            $provider = $_POST['ai_provider'] ?? 'openai';
            $selectedModel = $_POST['ai_model'] ?? 'gpt-5.2';

            // Validate provider and model
            if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
                $provider = 'openai';
            }
            if (!function_exists('ai_is_valid_provider_model') || !ai_is_valid_provider_model($provider, $selectedModel)) {
                $selectedModel = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-5.2';
            }

            // Use universal generate for multi-provider support
            $systemPrompt = 'You are an SEO expert who writes clear, engaging H2 headings for articles. Output only the headings, one per line.';
            $result = ai_universal_generate($provider, $selectedModel, $systemPrompt, $prompt, [
                'max_tokens' => 400,
                'temperature' => 0.6
            ]);
            
            if ($result['ok'] ?? false) {
                $text = trim($result['text'] ?? '');
                
                // Remove common AI artifacts
                $text = preg_replace('/<think>.*?<\/think>/si', '', $text);
                $text = preg_replace('/<[^>]+>/', '', $text); // Remove any HTML/XML tags
                $text = preg_replace('/```[^`]*```/', '', $text); // Remove code blocks
                
                $lines = array_filter(array_map('trim', explode("\n", $text)));
                
                $headings = [];
                $seen = []; // Track duplicates
                
                foreach ($lines as $line) {
                    // Remove bullets, numbers, quotes, dashes at start
                    $clean = preg_replace('/^[\d\.\-\*\#\•\"\'\`\:]+\s*/', '', $line);
                    // Remove "Part X:", "Step X:", "Section X:" prefixes
                    $clean = preg_replace('/^(part|step|section|chapter)\s*\d*[\:\.\-]?\s*/i', '', $clean);
                    // Remove quotes around entire heading
                    $clean = trim($clean, '"\' ');
                    // Remove trailing explanations like "– uses tool"
                    $clean = preg_replace('/\s*[\–\-\—]\s*.+$/', '', $clean);
                    // Remove anything in parentheses at end
                    $clean = preg_replace('/\s*\([^)]+\)\s*$/', '', $clean);
                    $clean = trim($clean);
                    
                    // Skip invalid headings
                    if (strlen($clean) < 15 || strlen($clean) > 70) continue;
                    
                    // Skip headings that look like article titles (too many words, contains "Guide", "Complete", etc.)
                    $wordCount = str_word_count($clean);
                    if ($wordCount > 12) continue;
                    if ($wordCount < 3) continue;
                    if (preg_match('/\b(complete|ultimate|definitive|comprehensive)\s+(guide|tutorial|handbook)/i', $clean)) continue;
                    
                    // Skip junk patterns
                    if (preg_match('/^(contact|about|company|price|url|http|www\.|\.com|\.ai|\.io)/i', $clean)) continue;
                    if (preg_match('/\.(com|ai|io|org|net|co)\b/i', $clean)) continue;
                    if (preg_match('/^(example|note|tip|warning|disclaimer):/i', $clean)) continue;
                    // Skip generic single-word or two-word headings
                    if (preg_match('/^(introduction|overview|background|context|summary|resources|references)$/i', $clean)) continue;
                    // Skip "Why This Matters" type generic headings
                    if (preg_match('/^(why\s+(this|it)\s+(matters|is\s+important)|the\s+bottom\s+line)$/i', $clean)) continue;
                    
                    // Check for duplicates (case-insensitive)
                    $key = strtolower($clean);
                    if (isset($seen[$key])) continue;
                    $seen[$key] = true;
                    
                    $headings[] = $clean;
                }
                
                // POST-PROCESSING: Enforce correct order (FAQ second-to-last, Conclusion last)
                $faqIndex = null;
                $conclusionIndex = null;
                
                foreach ($headings as $i => $h) {
                    $lower = strtolower($h);
                    if (preg_match('/\b(faq|frequently\s+asked)/i', $lower) && $faqIndex === null) {
                        $faqIndex = $i;
                    }
                    if (preg_match('/\b(conclusion|summary|final\s+thoughts|wrapping\s+up)\b/i', $lower) && $conclusionIndex === null) {
                        $conclusionIndex = $i;
                    }
                }
                
                // Extract FAQ and Conclusion if found
                $faqHeading = $faqIndex !== null ? $headings[$faqIndex] : null;
                $conclusionHeading = $conclusionIndex !== null ? $headings[$conclusionIndex] : null;
                
                // Remove them from their current positions
                $mainHeadings = [];
                foreach ($headings as $i => $h) {
                    if ($i !== $faqIndex && $i !== $conclusionIndex) {
                        $mainHeadings[] = $h;
                    }
                }
                
                // Rebuild in correct order: main sections, then FAQ, then Conclusion
                $headings = $mainHeadings;
                if ($faqHeading) $headings[] = $faqHeading;
                if ($conclusionHeading) $headings[] = $conclusionHeading;
                
                if (count($headings) < 3) {
                    echo json_encode(['ok' => false, 'error' => 'AI generated too few valid headings. Try again.']);
                } else {
                    echo json_encode(['ok' => true, 'headings' => array_slice($headings, 0, 8)]);
                }
            } else {
                echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'AI generation failed']);
            }
            exit;
    }
}

// Load specific research if requested
if (isset($_GET['keyword']) && !$research) {
    $research = seo_research_load($_GET['keyword']);
    if ($research) {
        $activeView = 'results';
    }
}

$researchList = seo_research_list();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI SEO Research - CMS</title>
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1600px;margin:0 auto;padding:24px}
.grid-sidebar{display:grid;grid-template-columns:320px 1fr;gap:24px}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
@media(max-width:1200px){.grid-sidebar{grid-template-columns:1fr}.grid-4{grid-template-columns:repeat(2,1fr)}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:16px;text-align:center}
.stat-card.accent{border-top:3px solid var(--accent)}
.stat-card.success{border-top:3px solid var(--success)}
.stat-card.warning{border-top:3px solid var(--warning)}
.stat-card.purple{border-top:3px solid var(--purple)}
.stat-value{font-size:28px;font-weight:700;margin-bottom:4px}
.stat-label{font-size:11px;color:var(--muted);text-transform:uppercase}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:12px}
.alert-success{background:rgba(166,227,161,.1);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-danger{background:rgba(243,139,168,.1);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-control{width:100%;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px}
.form-control:focus{outline:none;border-color:var(--accent)}
.form-control-lg{padding:16px 20px;font-size:16px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none}
.btn:hover{transform:translateY(-1px)}
.btn-primary{background:var(--accent);color:#000}
.btn-success{background:var(--success);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-lg{padding:14px 28px;font-size:15px}
.btn-sm{padding:6px 12px;font-size:12px}
.btn-block{width:100%;justify-content:center}
.keyword-grid{display:flex;flex-wrap:wrap;gap:8px}
.keyword-tag{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:var(--bg3);border-radius:20px;font-size:12px}
.keyword-tag.must{background:rgba(166,227,161,.2);color:var(--success);border:1px solid rgba(166,227,161,.3)}
.keyword-tag.should{background:rgba(137,180,250,.2);color:var(--accent);border:1px solid rgba(137,180,250,.3)}
.keyword-tag.nice{background:var(--bg3);color:var(--text2)}
.keyword-count{font-size:10px;background:rgba(0,0,0,.2);padding:2px 6px;border-radius:10px}
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:12px;text-align:left;border-bottom:1px solid var(--border)}
.table th{background:var(--bg3);font-size:11px;font-weight:600;text-transform:uppercase;color:var(--text2)}
.table tr:hover{background:var(--bg3)}
.progress{height:6px;background:var(--bg3);border-radius:3px;overflow:hidden}
.progress-bar{height:100%;border-radius:3px}
.progress-bar.green{background:var(--success)}
.progress-bar.blue{background:var(--accent)}
.research-item{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:10px;cursor:pointer;transition:.15s}
.research-item:hover{border-color:var(--accent);background:var(--bg3)}
.research-item.active{border-color:var(--accent);background:linear-gradient(135deg,rgba(137,180,250,.1),rgba(203,166,247,.1))}
.research-keyword{font-weight:600;font-size:15px;margin-bottom:4px}
.research-meta{display:flex;gap:16px;font-size:12px;color:var(--muted)}
.tabs{display:flex;gap:4px;margin-bottom:20px;flex-wrap:wrap}
.tab{padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:500;font-size:13px;background:var(--bg3);color:var(--text2);transition:.15s}
.tab:hover{background:var(--bg4)}
.tab.active{background:var(--accent);color:#000}
.brief-section{background:var(--bg);border-radius:12px;padding:20px;margin-bottom:16px}
.brief-title{font-size:14px;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.brief-value{font-size:24px;font-weight:700;color:var(--accent)}
.copy-btn{background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:6px 12px;border-radius:6px;cursor:pointer;font-size:12px}
.copy-btn:hover{background:var(--bg4)}
.empty-state{text-align:center;padding:60px 20px}
.empty-icon{font-size:64px;margin-bottom:16px}
.empty-title{font-size:20px;font-weight:600;margin-bottom:8px}
.empty-desc{color:var(--muted);margin-bottom:20px}
.heading-item{display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--bg);border-radius:8px;margin-bottom:8px}
.heading-level{background:var(--accent);color:#000;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
.heading-text{flex:1}
.heading-count{font-size:12px;color:var(--muted)}
.spinner{width:40px;height:40px;border:3px solid var(--bg3);border-top-color:var(--accent);border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 16px}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🔬',
    'title' => 'AI SEO Research',
    'description' => 'Analyze top-ranking pages and extract winning keywords',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--accent-color), var(--purple)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?>"><span><?= $msgType === 'success' ? '✅' : '❌' ?></span><span><?= esc($msg) ?></span></div>
<?php endif; ?>

<div class="grid-sidebar">
<div>
    <div class="card">
        <div class="card-head"><span class="card-title"><span>🔬</span> New Research</span></div>
        <div class="card-body">
            <form method="POST" id="researchForm">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="new_research">
                <div class="form-group">
                    <label>Target Keyword</label>
                    <input type="text" name="keyword" class="form-control form-control-lg" placeholder="e.g. best coffee machines 2024" required>
                </div>
                <div class="form-group">
                    <label>AI Provider & Model</label>
                    <?= ai_render_dual_selector('ai_provider', 'ai_model', 'openai', 'gpt-5.2') ?>
                </div>
                <p style="font-size:12px;color:var(--muted);margin-bottom:16px">🔍 We'll search top 15 results, analyze each page, and extract keywords & phrases.</p>
                <button type="submit" class="btn btn-primary btn-lg btn-block" id="startBtn">🚀 Start Research</button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-head"><span class="card-title"><span>📚</span> Previous Research</span></div>
        <div class="card-body" style="max-height:400px;overflow-y:auto">
            <?php if (empty($researchList)): ?>
                <p style="text-align:center;color:var(--muted);padding:20px">No research yet</p>
            <?php else: ?>
                <?php foreach ($researchList as $r): ?>
                <div class="research-item <?= ($research && $research['keyword'] === $r['keyword']) ? 'active' : '' ?>" onclick="location.href='?keyword=<?= urlencode($r['keyword']) ?>'">
                    <div class="research-keyword"><?= esc($r['keyword']) ?></div>
                    <div class="research-meta"><span>📄 <?= $r['pages_analyzed'] ?> pages</span><span>🔑 <?= $r['keywords_found'] ?> keywords</span></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<div>
<?php if ($research && $research['status'] === 'completed'): ?>
<div class="grid-4" style="margin-bottom:20px">
    <div class="stat-card accent"><div class="stat-value"><?= $research['pages_analyzed'] ?? 0 ?></div><div class="stat-label">Pages Analyzed</div></div>
    <div class="stat-card success"><div class="stat-value"><?= count($research['keywords'] ?? []) ?></div><div class="stat-label">Keywords Found</div></div>
    <div class="stat-card warning"><div class="stat-value"><?= count($research['phrases_2gram'] ?? []) + count($research['phrases_3gram'] ?? []) ?></div><div class="stat-label">Phrases Extracted</div></div>
    <div class="stat-card purple"><div class="stat-value"><?= $research['word_count_analysis']['recommended'] ?? 0 ?></div><div class="stat-label">Recommended Words</div></div>
</div>

<div class="tabs">
    <div class="tab active" onclick="showTab('brief')">📋 Content Brief</div>
    <div class="tab" onclick="showTab('keywords')">🔑 Keywords</div>
    <div class="tab" onclick="showTab('phrases')">💬 Phrases</div>
    <div class="tab" onclick="showTab('headings')">📑 Headings</div>
    <div class="tab" onclick="showTab('serp')">🔍 SERP Results</div>
</div>

<div id="tab-brief" class="tab-content">
<div class="card">
    <div class="card-head">
        <span class="card-title"><span>📋</span> Content Brief for "<?= esc($research['keyword']) ?>"</span>
        <button class="copy-btn" onclick="copyBrief()">📋 Copy Brief</button>
    </div>
    <div class="card-body">
        <?php $brief = $research['brief'] ?? []; ?>
        
        <div class="brief-section">
            <div class="brief-title">📝 Target Word Count</div>
            <div style="display:flex;gap:30px">
                <div><div style="font-size:12px;color:var(--muted)">Minimum</div><div class="brief-value"><?= $brief['word_count']['minimum'] ?? 0 ?></div></div>
                <div><div style="font-size:12px;color:var(--muted)">Recommended</div><div class="brief-value" style="color:var(--success)"><?= $brief['word_count']['recommended'] ?? 0 ?></div></div>
                <div><div style="font-size:12px;color:var(--muted)">Optimal (Top 3)</div><div class="brief-value" style="color:var(--purple)"><?= $brief['word_count']['optimal'] ?? 0 ?></div></div>
            </div>
        </div>
        
        <div class="brief-section">
            <div class="brief-title">✅ Must Use Keywords <span style="font-weight:400;color:var(--muted)">(include each at least once)</span></div>
            <div class="keyword-grid">
                <?php foreach ($brief['keywords']['must_use'] ?? [] as $kw): ?><span class="keyword-tag must"><?= esc($kw) ?></span><?php endforeach; ?>
            </div>
        </div>
        
        <div class="brief-section">
            <div class="brief-title">💡 Should Use Keywords</div>
            <div class="keyword-grid">
                <?php foreach ($brief['keywords']['should_use'] ?? [] as $kw): ?><span class="keyword-tag should"><?= esc($kw) ?></span><?php endforeach; ?>
            </div>
        </div>
        
        <div class="brief-section">
            <div class="brief-title">💬 Use These Phrases</div>
            <div class="keyword-grid">
                <?php foreach ($brief['phrases'] ?? [] as $phrase): ?><span class="keyword-tag nice"><?= esc($phrase) ?></span><?php endforeach; ?>
            </div>
        </div>
        
        <div class="brief-section">
            <div class="brief-title" style="display:flex;justify-content:space-between;align-items:center">
                <span>📑 Recommended Headings (H2)</span>
                <button type="button" class="btn btn-sm" style="background:var(--accent);color:#000;padding:4px 12px;font-size:12px" onclick="generateAIHeadings()">🤖 Generate with AI</button>
            </div>
            <div id="headingsContainer">
            <?php foreach ($brief['recommended_headings'] ?? [] as $h): ?>
                <div class="heading-item"><span class="heading-level">H2</span><span class="heading-text"><?= esc($h) ?></span></div>
            <?php endforeach; ?>
            <?php if (empty($brief['recommended_headings'])): ?>
                <div style="color:var(--muted);font-size:13px;padding:10px">No headings found from competitors. Click "Generate with AI" to create SEO-optimized headings.</div>
            <?php endif; ?>
            </div>
        </div>
        
        <div class="brief-section">
            <div class="brief-title">🏗️ Content Structure</div>
            <div style="display:flex;gap:40px">
                <div><div style="font-size:12px;color:var(--muted)">H2 Sections</div><div style="font-size:24px;font-weight:700"><?= $brief['structure']['h2_count'] ?? 5 ?></div></div>
                <div><div style="font-size:12px;color:var(--muted)">Paragraphs</div><div style="font-size:24px;font-weight:700"><?= $brief['structure']['paragraphs'] ?? 10 ?></div></div>
                <div><div style="font-size:12px;color:var(--muted)">Images</div><div style="font-size:24px;font-weight:700"><?= $brief['structure']['images'] ?? 3 ?></div></div>
            </div>
        </div>
        
        <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
            <a href="#" id="createContentBtn" class="btn btn-success btn-lg">✨ Generate Article with AI</a>
            <button class="btn btn-secondary btn-lg" onclick="copyPrompt()" style="margin-left:10px">📋 Copy AI Prompt</button>
        </div>
    </div>
</div>
</div>

<div id="tab-keywords" class="tab-content" style="display:none">
<div class="card">
    <div class="card-head"><span class="card-title"><span>🔑</span> Extracted Keywords (<?= count($research['keywords'] ?? []) ?>)</span></div>
    <div class="card-body">
        <table class="table">
            <thead><tr><th>Keyword</th><th>Frequency</th><th>Coverage</th><th>Score</th><th>Priority</th></tr></thead>
            <tbody>
                <?php $i = 0; foreach ($research['keywords'] ?? [] as $kw): $priority = $i < 10 ? 'must' : ($i < 30 ? 'should' : 'nice'); $i++; ?>
                <tr>
                    <td><strong><?= esc($kw['word']) ?></strong></td>
                    <td><?= $kw['frequency'] ?></td>
                    <td><div style="display:flex;align-items:center;gap:8px"><div class="progress" style="width:80px"><div class="progress-bar <?= $kw['coverage'] >= 50 ? 'green' : 'blue' ?>" style="width:<?= min($kw['coverage'], 100) ?>%"></div></div><span><?= $kw['coverage'] ?>%</span></div></td>
                    <td><?= $kw['score'] ?></td>
                    <td><span class="keyword-tag <?= $priority ?>"><?= ucfirst($priority) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<div id="tab-phrases" class="tab-content" style="display:none">
<div class="card">
    <div class="card-head"><span class="card-title"><span>💬</span> 2-Word Phrases</span></div>
    <div class="card-body">
        <div class="keyword-grid">
            <?php foreach ($research['phrases_2gram'] ?? [] as $p): ?><span class="keyword-tag should"><?= esc($p['phrase']) ?><span class="keyword-count"><?= $p['frequency'] ?></span></span><?php endforeach; ?>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-head"><span class="card-title"><span>💬</span> 3-Word Phrases</span></div>
    <div class="card-body">
        <div class="keyword-grid">
            <?php foreach ($research['phrases_3gram'] ?? [] as $p): ?><span class="keyword-tag nice"><?= esc($p['phrase']) ?><span class="keyword-count"><?= $p['frequency'] ?></span></span><?php endforeach; ?>
        </div>
    </div>
</div>
</div>

<div id="tab-headings" class="tab-content" style="display:none">
<div class="card">
    <div class="card-head"><span class="card-title"><span>📑</span> Common Headings Used by Competitors</span></div>
    <div class="card-body">
        <?php foreach ($research['headings'] ?? [] as $h): ?>
            <div class="heading-item"><span class="heading-level">H<?= $h['level'] ?></span><span class="heading-text"><?= esc($h['text']) ?></span><span class="heading-count"><?= $h['count'] ?> competitors</span></div>
        <?php endforeach; ?>
    </div>
</div>
</div>

<div id="tab-serp" class="tab-content" style="display:none">
<div class="card">
    <div class="card-head"><span class="card-title"><span>🔍</span> Analyzed Pages</span></div>
    <div class="card-body">
        <table class="table">
            <thead><tr><th>#</th><th>Title</th><th>Words</th><th>Headings</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($research['analyzed_pages'] ?? [] as $page): ?>
                <tr>
                    <td><strong>#<?= $page['position'] ?></strong></td>
                    <td><div style="max-width:400px"><div style="font-weight:500"><?= esc($page['title'] ?: 'Untitled') ?></div><div style="font-size:11px;color:var(--accent);word-break:break-all"><?= esc($page['url']) ?></div></div></td>
                    <td><?= number_format($page['word_count']) ?></td>
                    <td><?= $page['headings_count'] ?></td>
                    <td><a href="<?= esc($page['url']) ?>" target="_blank" class="btn btn-sm btn-secondary">🔗 Visit</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-icon">🔬</div>
            <div class="empty-title">Start Your Research</div>
            <div class="empty-desc">Enter a keyword to analyze top-ranking pages and extract all keywords & phrases you need.</div>
            <div class="alert alert-info" style="max-width:500px;margin:20px auto;text-align:left">
                <span>💡</span>
                <div><strong>How it works:</strong><br>1. We search for your keyword<br>2. Analyze top 15 results<br>3. Extract keywords & phrases<br>4. Generate content brief</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</div>
</div>
</div>

<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tabId).style.display = 'block';
    event.target.classList.add('active');
}

// Global brief object - updated by AI tools
let brief = <?= json_encode($research['brief'] ?? []) ?>;

function copyBrief() {
    let text = `CONTENT BRIEF: ${brief.keyword}\n\nTARGET: ${brief.word_count?.recommended || 0} words\n\nMUST USE:\n${(brief.keywords?.must_use || []).join(', ')}\n\nSHOULD USE:\n${(brief.keywords?.should_use || []).join(', ')}\n\nPHRASES:\n${(brief.phrases || []).join(', ')}\n\nHEADINGS:\n${(brief.recommended_headings || []).map(h => '- ' + h).join('\n')}`;
    navigator.clipboard.writeText(text).then(() => alert('Brief copied!'));
}
function copyPrompt() {
    const prompt = <?= json_encode(isset($research['brief']) ? seo_research_generate_prompt($research['brief']) : '') ?>;
    navigator.clipboard.writeText(prompt).then(() => alert('AI Prompt copied!'));
}

// Create Content button - uses current brief data
document.getElementById('createContentBtn')?.addEventListener('click', function(e) {
    e.preventDefault();
    
    const allKeywords = [
        ...(brief.keywords?.must_use || []),
        ...(brief.keywords?.should_use || [])
    ].join(', ');
    
    const headings = (brief.recommended_headings || []).join('|');
    const phrases = (brief.phrases || []).join(', ');
    
    const params = new URLSearchParams({
        keyword: brief.keyword || '',
        keywords: allKeywords,
        phrases: phrases,
        headings: headings,
        wordcount: brief.word_count?.recommended || 0
    });
    
    window.location.href = '/admin/ai-content-creator?' + params.toString();
});

document.getElementById('researchForm')?.addEventListener('submit', function() {
    document.getElementById('startBtn').innerHTML = '<span class="spinner" style="width:16px;height:16px;margin-right:8px;display:inline-block"></span> Analyzing...';
    document.getElementById('startBtn').disabled = true;
});

async function generateAIHeadings() {
    const keyword = brief.keyword || '';
    const topKeywords = (brief.keywords?.must_use || []).slice(0, 5).join(', ');
    
    if (!keyword) {
        alert('No keyword found');
        return;
    }
    
    const container = document.getElementById('headingsContainer');
    container.innerHTML = '<div style="padding:20px;text-align:center"><span class="spinner"></span> Generating AI headings...</div>';
    
    const fd = new FormData();
    fd.append('action', 'generate_headings');
    fd.append('csrf_token', '<?= esc(csrf_token()) ?>');
    fd.append('keyword', keyword);
    fd.append('keywords', topKeywords);
    
    try {
        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.ok && data.headings?.length) {
            container.innerHTML = data.headings.map(h => 
                `<div class="heading-item"><span class="heading-level">H2</span><span class="heading-text">${escHtml(h)}</span></div>`
            ).join('');
            
            // Update brief for Copy functions
            brief.recommended_headings = data.headings;
        } else {
            container.innerHTML = '<div style="color:var(--danger);padding:10px">' + (data.error || 'Failed to generate headings') + '</div>';
        }
    } catch (err) {
        container.innerHTML = '<div style="color:var(--danger);padding:10px">Error: ' + err.message + '</div>';
    }
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
</script>
</body>
</html>
