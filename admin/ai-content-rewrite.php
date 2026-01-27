<?php
/**
 * AI Content Rewrite Suite
 * Paraphrasing, summarizing, expanding, tone-shifting UI
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/..'));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_content_rewrite.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';
require_once CMS_ROOT . '/core/database.php';

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Forbidden');
}

cms_require_admin_role();

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$message = '';
$messageType = 'info';
$result = null;
$comparison = null;

// Flash message pattern - odczytaj z sesji i wyczyść
if (isset($_SESSION['rewrite_flash'])) {
    $flash = $_SESSION['rewrite_flash'];
    $message = $flash['message'] ?? '';
    $messageType = $flash['type'] ?? 'info';
    $result = $flash['result'] ?? null;
    $comparison = $flash['comparison'] ?? null;
    unset($_SESSION['rewrite_flash']);
}

$modes = ai_rewrite_get_modes();
$tones = ai_rewrite_get_tones();
$presets = ai_rewrite_get_presets();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'rewrite':
            $content = $_POST['content'] ?? '';
            $mode = $_POST['mode'] ?? 'paraphrase';
            $tone = $_POST['tone'] ?? 'neutral';
            $keyword = trim($_POST['keyword'] ?? '');
            $targetLength = (int)($_POST['target_length'] ?? 0);
            $provider = $_POST['ai_provider'] ?? 'openai';
            $selectedModel = $_POST['ai_model'] ?? 'gpt-5.2';

            // Validate provider and model selection
            if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
                $provider = 'openai';
            }
            if (!function_exists('ai_is_valid_provider_model') || !ai_is_valid_provider_model($provider, $selectedModel)) {
                $selectedModel = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-5.2';
            }

            if (empty(trim($content))) {
                $message = 'Please enter content to rewrite.';
                $messageType = 'warning';
            } else {
                $options = ['tone' => $tone, 'keyword' => $keyword, 'provider' => $provider, 'model' => $selectedModel];
                if ($targetLength > 0) $options['target_length'] = $targetLength;

                $result = ai_rewrite_content($content, $mode, $options);
                if ($result['ok']) {
                    $comparison = ai_rewrite_compare($content, $result['rewritten']);
                    $message = 'Content rewritten successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Rewrite failed: ' . ($result['error'] ?? 'Unknown error');
                    $messageType = 'danger';
                }
            }
            break;

        case 'preset':
            $content = $_POST['content'] ?? '';
            $preset = $_POST['preset'] ?? '';

            if (empty(trim($content))) {
                $message = 'Please enter content.';
                $messageType = 'warning';
            } elseif (empty($preset)) {
                $message = 'Please select a preset.';
                $messageType = 'warning';
            } else {
                $result = ai_rewrite_preset($content, $preset);
                if ($result['ok']) {
                    $comparison = ai_rewrite_compare($content, $result['rewritten']);
                    $message = 'Preset applied!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed: ' . ($result['error'] ?? 'Unknown error');
                    $messageType = 'danger';
                }
            }
            break;

        case 'save_as_article':
            $content = $_POST['content'] ?? '';
            if (!empty($content)) {
                $pdo = \core\Database::connection();
                $slug = 'ai-rewrite-' . date('Y-m-d-His');
                $stmt = $pdo->prepare("INSERT INTO articles (title, slug, content, status, created_at, updated_at) VALUES (?, ?, ?, 'draft', NOW(), NOW())");
                $stmt->execute(['AI Rewrite - ' . date('M j, Y H:i'), $slug, $content]);
                $newId = $pdo->lastInsertId();
                $message = 'Saved as draft article! <a href="/admin/articles/' . $newId . '/edit">Edit Article →</a>';
                $messageType = 'success';
            }
            break;

        case 'save_as_page':
            $content = $_POST['content'] ?? '';
            if (!empty($content)) {
                $pdo = \core\Database::connection();
                $slug = 'ai-rewrite-' . date('Y-m-d-His');
                $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, status, created_at, updated_at) VALUES (?, ?, ?, 'draft', NOW(), NOW())");
                $stmt->execute(['AI Rewrite - ' . date('M j, Y H:i'), $slug, $content]);
                $newId = $pdo->lastInsertId();
                $message = 'Saved as draft page! <a href="/admin/pages/' . $newId . '/edit">Edit Page →</a>';
                $messageType = 'success';
            }
            break;

        case 'update_existing':
            $content = $_POST['content'] ?? '';
            $targetType = $_POST['target_type'] ?? '';
            $targetId = (int)($_POST['target_id'] ?? 0);
            if (!empty($content) && $targetId > 0) {
                $pdo = \core\Database::connection();
                $table = $targetType === 'article' ? 'articles' : 'pages';
                $stmt = $pdo->prepare("UPDATE {$table} SET content = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$content, $targetId]);
                $message = ucfirst($targetType) . ' updated successfully!';
                $messageType = 'success';
            }
            break;
    }
    
    // PRG pattern - zapisz do sesji i przekieruj
    $_SESSION['rewrite_flash'] = [
        'message' => $message,
        'type' => $messageType,
        'result' => $result,
        'comparison' => $comparison
    ];
    header('Location: /admin/ai-content-rewrite.php');
    exit;
}

// Get data for dropdowns
$pages = [];
$articles = [];
try {
    $pdo = \core\Database::connection();
    $stmt = $pdo->query("SELECT id, title FROM pages ORDER BY title LIMIT 100");
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->query("SELECT id, title FROM articles ORDER BY title LIMIT 100");
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$title = 'AI Content Rewrite';
ob_start();

?>
<style>
:root {
    --bg-primary: #13111C;
    --bg-secondary: #1a1625;
    --bg-tertiary: #241f31;
    --border: rgba(255,255,255,0.08);
    --text-primary: #f4f4f5;
    --text-secondary: #a1a1aa;
    --text-muted: #71717a;
    --accent: #8b5cf6;
    --accent-hover: #7c3aed;
    --success: #22c55e;
    --warning: #eab308;
    --danger: #ef4444;
}
.rewrite-container { display: grid; grid-template-columns: 1fr 350px; gap: 24px; margin-top: 24px; }
@media (max-width: 1200px) { .rewrite-container { grid-template-columns: 1fr; } }
.card { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
.card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
.card-header h3 { margin: 0; font-size: 15px; font-weight: 600; }
.card-body { padding: 20px; }
.tabs { display: flex; gap: 4px; background: var(--bg-tertiary); padding: 4px; border-radius: 10px; margin-bottom: 20px; }
.tab-btn { flex: 1; padding: 10px 14px; border: none; background: transparent; color: var(--text-secondary); font-size: 13px; font-weight: 500; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
.tab-btn:hover { color: var(--text-primary); background: rgba(255,255,255,0.05); }
.tab-btn.active { background: var(--accent); color: white; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 6px; }
textarea, input[type="text"], input[type="number"], select { width: 100%; padding: 10px 14px; background: var(--bg-tertiary); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); font-size: 13px; }
textarea:focus, input:focus, select:focus { outline: none; border-color: var(--accent); }
textarea { min-height: 150px; resize: vertical; font-family: inherit; line-height: 1.6; }
.form-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
@media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
.mode-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
.mode-card { padding: 14px; background: var(--bg-tertiary); border: 2px solid var(--border); border-radius: 10px; cursor: pointer; transition: all 0.2s; }
.mode-card:hover { border-color: var(--accent); }
.mode-card.selected { border-color: var(--accent); background: rgba(139, 92, 246, 0.1); }
.mode-card .icon { font-size: 20px; margin-bottom: 6px; }
.mode-card .name { font-weight: 600; font-size: 13px; margin-bottom: 2px; }
.mode-card .desc { font-size: 11px; color: var(--text-muted); }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { background: var(--accent-hover); }
.btn-lg { padding: 14px 28px; font-size: 14px; }
.result-card { margin-top: 20px; border: 2px solid var(--success); }
.result-header { background: rgba(34, 197, 94, 0.1); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; }
.result-header h3 { color: var(--success); margin: 0; font-size: 15px; }
.result-content { padding: 20px; background: var(--bg-tertiary); border-radius: 10px; margin: 20px; margin-top: 0; line-height: 1.7; white-space: pre-wrap; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; padding: 0 20px 20px; }
.stat-box { text-align: center; padding: 12px; background: var(--bg-tertiary); border-radius: 8px; }
.stat-box .value { font-size: 20px; font-weight: 700; }
.stat-box .label { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.stat-box.positive .value { color: var(--success); }
.stat-box.negative .value { color: var(--danger); }
.save-options { padding: 20px; border-top: 1px solid var(--border); }
.save-options h4 { margin: 0 0 12px 0; font-size: 14px; }
.save-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
.btn-save { padding: 10px 16px; background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border); border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-save:hover { background: var(--accent); border-color: var(--accent); }
.sidebar-card { margin-bottom: 16px; }
.mode-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--border); }
.mode-item:last-child { border-bottom: none; }
.mode-item .icon { font-size: 18px; }
.mode-item .info .name { font-weight: 600; font-size: 13px; }
.mode-item .info .desc { font-size: 11px; color: var(--text-muted); }
.alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; }
.alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); color: var(--success); }
.alert-warning { background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3); color: var(--warning); }
.alert-danger { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--danger); }
.alert a { color: inherit; text-decoration: underline; }
.page-header { margin-bottom: 8px; }
.page-header h1 { font-size: 26px; margin: 0 0 6px 0; }
.page-header p { color: var(--text-secondary); margin: 0; font-size: 14px; }
.preset-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.preset-card { padding: 14px; background: var(--bg-tertiary); border: 2px solid var(--border); border-radius: 10px; cursor: pointer; }
.preset-card:hover { border-color: var(--accent); }
.preset-card.selected { border-color: var(--success); background: rgba(34, 197, 94, 0.1); }
.preset-card .name { font-weight: 600; font-size: 13px; margin-bottom: 2px; }
.preset-card .desc { font-size: 11px; color: var(--text-muted); }
.modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-box { background: var(--bg-secondary); border-radius: 16px; padding: 24px; width: 100%; max-width: 400px; }
.modal-box h3 { margin: 0 0 20px 0; font-size: 18px; }
.btn-row { display: flex; gap: 10px; margin-top: 20px; }
.btn-cancel { padding: 10px 20px; background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border); border-radius: 8px; cursor: pointer; }
</style>


<div class="page-header">
    <h1>✍️ AI Content Rewrite</h1>
    <p>Transform your content with AI - paraphrase, summarize, expand, or change tone</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
<?php endif; ?>

<div class="rewrite-container">
    <div class="main-panel">
        <div class="card">
            <div class="card-body">
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('custom')">🎯 Custom Rewrite</button>
                    <button class="tab-btn" onclick="switchTab('presets')">⚡ Quick Presets</button>
                </div>

                <div id="tab-custom" class="tab-content active">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="rewrite">
                        <input type="hidden" name="mode" id="selected-mode" value="<?= esc($_POST['mode'] ?? 'paraphrase') ?>">

                        <div class="form-group">
                            <label>Original Content</label>
                            <textarea name="content" placeholder="Paste your content here..." required><?= esc($_POST['content'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Select Mode</label>
                            <div class="mode-grid">
                                <?php foreach ($modes as $key => $mode): ?>
                                <div class="mode-card <?= ($_POST['mode'] ?? 'paraphrase') === $key ? 'selected' : '' ?>" onclick="selectMode('<?= $key ?>')">
                                    <div class="icon"><?= $mode['icon'] ?></div>
                                    <div class="name"><?= esc($mode['label']) ?></div>
                                    <div class="desc"><?= esc($mode['description']) ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>AI Provider & Model</label>
                            <?= ai_render_dual_selector('ai_provider', 'ai_model', 'openai', 'gpt-5.2') ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Tone</label>
                                <select name="tone">
                                    <?php foreach ($tones as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= ($_POST['tone'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Focus Keyword</label>
                                <input type="text" name="keyword" value="<?= esc($_POST['keyword'] ?? '') ?>" placeholder="Optional">
                            </div>
                            <div class="form-group">
                                <label>Target Length</label>
                                <input type="number" name="target_length" value="<?= esc($_POST['target_length'] ?? '') ?>" placeholder="Words" min="10" max="2000">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">🚀 Rewrite Content</button>
                    </form>
                </div>

                <div id="tab-presets" class="tab-content">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="preset">
                        <input type="hidden" name="preset" id="selected-preset" value="">

                        <div class="form-group">
                            <label>Content to Transform</label>
                            <textarea name="content" placeholder="Paste your content here..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Select Preset</label>
                            <div class="preset-grid">
                                <?php foreach ($presets as $key => $desc): ?>
                                <div class="preset-card" onclick="selectPreset('<?= $key ?>')">
                                    <div class="name"><?= ucwords(str_replace('_', ' ', $key)) ?></div>
                                    <div class="desc"><?= esc($desc) ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">⚡ Apply Preset</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($result && $result['ok']): ?>
        <div class="card result-card">
            <div class="result-header">
                <h3>✅ Rewritten Content</h3>
                <button class="btn-save" onclick="copyResult()">📋 Copy</button>
            </div>
            <div class="result-content" id="result-text"><?= esc($result['rewritten']) ?></div>
            
            <?php if ($comparison): ?>
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="value"><?= $comparison['original']['words'] ?></div>
                    <div class="label">Original</div>
                </div>
                <div class="stat-box">
                    <div class="value"><?= $comparison['rewritten']['words'] ?></div>
                    <div class="label">New</div>
                </div>
                <div class="stat-box <?= $comparison['changes']['word_diff'] >= 0 ? 'positive' : 'negative' ?>">
                    <div class="value"><?= ($comparison['changes']['word_diff'] >= 0 ? '+' : '') . $comparison['changes']['word_percent'] ?>%</div>
                    <div class="label">Change</div>
                </div>
                <div class="stat-box">
                    <div class="value"><?= $comparison['changes']['similarity_percent'] ?>%</div>
                    <div class="label">Similarity</div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="save-options">
                <h4>💾 Save As</h4>
                <div class="save-buttons">
                    <form method="post" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="save_as_article">
                        <input type="hidden" name="content" value="<?= esc($result['rewritten']) ?>">
                        <button type="submit" class="btn-save">📝 New Article</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="save_as_page">
                        <input type="hidden" name="content" value="<?= esc($result['rewritten']) ?>">
                        <button type="submit" class="btn-save">📄 New Page</button>
                    </form>
                    <button class="btn-save" onclick="showUpdateModal()">🔄 Update Existing</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="sidebar">
        <div class="card sidebar-card">
            <div class="card-header"><span>📚</span><h3>Rewrite Modes</h3></div>
            <div class="card-body">
                <?php foreach ($modes as $mode): ?>
                <div class="mode-item">
                    <div class="icon"><?= $mode['icon'] ?></div>
                    <div class="info">
                        <div class="name"><?= esc($mode['label']) ?></div>
                        <div class="desc"><?= esc($mode['description']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card sidebar-card">
            <div class="card-header"><span>💡</span><h3>Pro Tips</h3></div>
            <div class="card-body">
                <div class="mode-item">
                    <div class="icon">🎯</div>
                    <div class="info"><div class="name">Keywords</div><div class="desc">Add focus keyword for SEO</div></div>
                </div>
                <div class="mode-item">
                    <div class="icon">📏</div>
                    <div class="info"><div class="name">Length</div><div class="desc">Set word count for summarize</div></div>
                </div>
                <div class="mode-item">
                    <div class="icon">🔄</div>
                    <div class="info"><div class="name">Iterate</div><div class="desc">Run multiple times for variety</div></div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="update-modal" class="modal-overlay" onclick="if(event.target===this)hideUpdateModal()">
    <div class="modal-box">
        <h3>🔄 Update Existing Content</h3>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_existing">
            <input type="hidden" name="content" value="<?= isset($result['rewritten']) ? esc($result['rewritten']) : '' ?>">
            
            <div class="form-group">
                <label>Content Type</label>
                <select name="target_type" id="target-type" onchange="loadTargets()">
                    <option value="">-- Select --</option>
                    <option value="article">Article</option>
                    <option value="page">Page</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Select Item</label>
                <select name="target_id" id="target-id">
                    <option value="">-- Select type first --</option>
                </select>
            </div>
            
            <div class="btn-row">
                <button type="button" class="btn-cancel" onclick="hideUpdateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Content</button>
            </div>
        </form>
    </div>
</div>

<script>
const articles = <?= json_encode($articles) ?>;
const pagesData = <?= json_encode($pages) ?>;

function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

function selectMode(mode) {
    document.getElementById('selected-mode').value = mode;
    document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

function selectPreset(preset) {
    document.getElementById('selected-preset').value = preset;
    document.querySelectorAll('.preset-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

function copyResult() {
    const text = document.getElementById('result-text').innerText;
    navigator.clipboard.writeText(text).then(() => {
        event.target.innerHTML = '✅ Copied!';
        setTimeout(() => event.target.innerHTML = '📋 Copy', 2000);
    });
}

function showUpdateModal() {
    document.getElementById('update-modal').classList.add('active');
}

function hideUpdateModal() {
    document.getElementById('update-modal').classList.remove('active');
}

function loadTargets() {
    const type = document.getElementById('target-type').value;
    const select = document.getElementById('target-id');
    select.innerHTML = '<option value="">-- Select --</option>';
    const items = type === 'article' ? articles : pagesData;
    items.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.title;
        select.appendChild(opt);
    });
}
</script>

<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/topbar.php';
