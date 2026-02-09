<?php
/**
 * AI Copywriter - Modern Dark UI
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../core/ai_copywriter.php';
require_once __DIR__ . '/../core/ai_models.php';
require_once __DIR__ . '/../core/ai_content.php';

$userId = $_SESSION['admin_id'] ?? null;
$tenantId = $_SESSION['tenant_id'] ?? null;
$copywriter = new \Core\AICopywriter($userId, $tenantId);
$copyTypes = $copywriter->getCopyTypes();
$tones = $copywriter->getTones();
$audiences = $copywriter->getAudiences();

// AJAX handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    csrf_validate_or_403();
    $action = $_POST['ajax_action'] ?? '';
    $response = ['success' => false, 'error' => 'Invalid action'];
    
    // Set provider and model if provided
    $provider = $_POST['provider'] ?? 'openai';
    $model = $_POST['model'] ?? null;

    // Validate provider
    if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
        $provider = 'openai';
    }

    $copywriter->setProvider($provider);
    if ($model) {
        // Validate model for provider
        if (function_exists('ai_is_valid_provider_model') && !ai_is_valid_provider_model($provider, $model)) {
            $model = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-5.2';
        }
        $copywriter->setModel($model);
    }
    
    switch ($action) {
        case 'generate':
            $response = $copywriter->generate([
                'copy_type' => $_POST['copy_type'] ?? 'custom',
                'topic' => trim($_POST['topic'] ?? ''),
                'context' => trim($_POST['context'] ?? ''),
                'tone' => $_POST['tone'] ?? 'professional',
                'audience' => $_POST['audience'] ?? 'general',
                'keywords' => array_filter(array_map('trim', explode(',', $_POST['keywords'] ?? ''))),
                'max_length' => (int)($_POST['max_length'] ?? 500),
                'variants' => (int)($_POST['variants'] ?? 1),
                'language' => $_POST['language'] ?? 'en',
                'brand_voice' => trim($_POST['brand_voice'] ?? ''),
                'include_emoji' => !empty($_POST['include_emoji'])
            ]);
            break;
        case 'improve':
            $response = $copywriter->improve(trim($_POST['original_copy'] ?? ''), [
                'improvement_type' => $_POST['improvement_type'] ?? 'general',
                'tone' => $_POST['tone'] ?? null
            ]);
            break;
        case 'analyze':
            $response = $copywriter->analyze(trim($_POST['copy'] ?? ''));
            break;
        case 'ab_variants':
            $response = $copywriter->generateABVariants(trim($_POST['original_copy'] ?? ''), (int)($_POST['variant_count'] ?? 3), ['focus_area' => $_POST['focus_area'] ?? 'general']);
            break;
        case 'get_history':
            $response = $copywriter->getHistory((int)($_POST['limit'] ?? 20), (int)($_POST['offset'] ?? 0));
            break;
        case 'get_favorites':
            $response = $copywriter->getFavorites(50);
            break;
        case 'save_favorite':
            $response = $copywriter->saveFavorite(trim($_POST['copy'] ?? ''), $_POST['copy_type'] ?? 'custom', trim($_POST['name'] ?? ''));
            break;
        case 'delete_favorite':
            $response = $copywriter->deleteFavorite((int)($_POST['favorite_id'] ?? 0));
            break;
    }
    echo json_encode($response);
    exit;
}

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Copywriter - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.tabs{display:flex;gap:4px;background:var(--bg2);padding:6px;border-radius:12px;border:1px solid var(--border);margin-bottom:20px;flex-wrap:wrap}
.tab{padding:10px 18px;font-size:13px;font-weight:500;color:var(--text2);background:transparent;border:none;border-radius:8px;cursor:pointer;transition:.15s}
.tab:hover{color:var(--text);background:rgba(137,180,250,.1)}
.tab.active{color:#000;background:var(--accent)}
.panel{display:none}
.panel.active{display:block}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px;transition:.15s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent)}
.form-group textarea{resize:vertical;min-height:100px}
.form-group small{display:block;margin-top:4px;font-size:11px;color:var(--muted)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-check{display:flex;align-items:center;gap:8px;font-size:13px}
.form-check input{width:16px;height:16px;accent-color:var(--accent)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-success{background:var(--success);color:#000}
.btn-sm{padding:6px 12px;font-size:12px}
.output-box{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px;min-height:200px;white-space:pre-wrap;font-size:13px}
.loading{text-align:center;padding:40px;color:var(--muted)}
.loading::after{content:'';display:inline-block;width:20px;height:20px;border:2px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin 1s linear infinite;margin-left:10px}
@keyframes spin{to{transform:rotate(360deg)}}
.alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.variant{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:10px}
.variant-head{display:flex;justify-content:space-between;margin-bottom:8px;font-size:12px;color:var(--muted)}
.score-bar{height:6px;background:var(--bg3);border-radius:3px;overflow:hidden;margin-top:8px}
.score-fill{height:100%;border-radius:3px}
.history-item{padding:12px;background:var(--bg);border-radius:8px;margin-bottom:8px;cursor:pointer;transition:.15s}
.history-item:hover{border-color:var(--accent)}
.history-item .type{font-size:10px;color:var(--accent);text-transform:uppercase}
.history-item .preview{font-size:12px;color:var(--text2);margin-top:4px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '✍️',
    'title' => 'AI Copywriter',
    'description' => 'Generate professional copy',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--accent-color), var(--purple)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="tabs">
<button class="tab active" data-tab="generate">✨ Generate</button>
<button class="tab" data-tab="improve">🔧 Improve</button>
<button class="tab" data-tab="analyze">📊 Analyze</button>
<button class="tab" data-tab="ab">🔀 A/B Variants</button>
<button class="tab" data-tab="history">📜 History</button>
</div>

<!-- Generate Tab -->
<div class="panel active" id="panel-generate">
<div class="grid">
<div class="card">
<div class="card-head"><span class="card-title"><span>📝</span> Copy Settings</span></div>
<div class="card-body">
<form id="generateForm">
<div class="form-row">
<div class="form-group">
<label>Copy Type</label>
<select name="copy_type">
<?php foreach ($copyTypes as $k => $v): ?><option value="<?= $k ?>"><?= esc(is_array($v) ? ($v["label"] ?? $k) : $v) ?></option><?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label>Tone</label>
<select name="tone">
<?php foreach ($tones as $k => $v): ?><option value="<?= $k ?>" <?= $k === 'professional' ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach; ?>
</select>
</div>
</div>
<div class="form-group">
<label>Topic *</label>
<input type="text" name="topic" placeholder="What is this copy about?" required>
</div>
<div class="form-group">
<label>Context / Details</label>
<textarea name="context" rows="3" placeholder="Additional context, requirements..."></textarea>
</div>
<div class="form-row">
<div class="form-group">
<label>Audience</label>
<select name="audience">
<?php foreach ($audiences as $k => $v): ?><option value="<?= $k ?>"><?= esc($v) ?></option><?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label>Max Length</label>
<input type="number" name="max_length" value="500" min="50" max="5000">
</div>
</div>
<div class="form-group">
<label>Keywords (comma separated)</label>
<input type="text" name="keywords" placeholder="keyword1, keyword2">
</div>
<div class="form-row">
<div class="form-group">
<label>Variants</label>
<select name="variants"><option value="1">1</option><option value="2">2</option><option value="3" selected>3</option></select>
</div>
<div class="form-group">
<label>Language</label>
<select name="language"><option value="en">English</option><option value="pl">Polski</option><option value="de">Deutsch</option><option value="es">Español</option></select>
</div>
<div class="form-group">
<label>AI Provider & Model</label>
<?= ai_render_dual_selector('provider', 'model', 'openai', 'gpt-5.2') ?>
</div>
</div>
<label class="form-check"><input type="checkbox" name="include_emoji"> Include emojis</label>
<div style="margin-top:16px"><button type="submit" class="btn btn-primary">✨ Generate Copy</button></div>
</form>
</div>
</div>
<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Generated Copy</span></div>
<div class="card-body">
<div id="generateOutput" class="output-box" style="color:var(--muted);text-align:center">Generated copy will appear here</div>
</div>
</div>
</div>
</div>

<!-- Improve Tab -->
<div class="panel" id="panel-improve">
<div class="grid">
<div class="card">
<div class="card-head"><span class="card-title"><span>📝</span> Original Copy</span></div>
<div class="card-body">
<form id="improveForm">
<div class="form-group">
<label>Paste your copy</label>
<textarea name="original_copy" rows="8" placeholder="Paste the copy you want to improve..." required></textarea>
</div>
<div class="form-row">
<div class="form-group">
<label>Improvement Type</label>
<select name="improvement_type">
<option value="general">General</option>
<option value="clarity">Clarity</option>
<option value="engagement">Engagement</option>
<option value="seo">SEO</option>
<option value="conversion">Conversion</option>
</select>
</div>
<div class="form-group">
<label>Target Tone</label>
<select name="tone">
<option value="">Keep original</option>
<?php foreach ($tones as $k => $v): ?><option value="<?= $k ?>"><?= esc($v) ?></option><?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label>AI Provider & Model</label>
<?= ai_render_dual_selector('provider', 'model', 'openai', 'gpt-5.2') ?>
</div>
</div>
<button type="submit" class="btn btn-primary">🔧 Improve Copy</button>
</form>
</div>
</div>
<div class="card">
<div class="card-head"><span class="card-title"><span>✅</span> Improved Copy</span></div>
<div class="card-body">
<div id="improveOutput" class="output-box" style="color:var(--muted);text-align:center">Improved copy will appear here</div>
</div>
</div>
</div>
</div>

<!-- Analyze Tab -->
<div class="panel" id="panel-analyze">
<div class="grid">
<div class="card">
<div class="card-head"><span class="card-title"><span>📝</span> Copy to Analyze</span></div>
<div class="card-body">
<form id="analyzeForm">
<div class="form-group">
<label>Paste your copy</label>
<textarea name="copy" rows="10" placeholder="Paste the copy you want to analyze..." required></textarea>
</div>
<button type="submit" class="btn btn-primary">📊 Analyze</button>
</form>
</div>
</div>
<div class="card">
<div class="card-head"><span class="card-title"><span>📊</span> Analysis Results</span></div>
<div class="card-body">
<div id="analyzeOutput" style="color:var(--muted);text-align:center">Analysis will appear here</div>
</div>
</div>
</div>
</div>

<!-- A/B Tab -->
<div class="panel" id="panel-ab">
<div class="grid">
<div class="card">
<div class="card-head"><span class="card-title"><span>📝</span> Original Copy</span></div>
<div class="card-body">
<form id="abForm">
<div class="form-group">
<label>Paste your copy</label>
<textarea name="original_copy" rows="6" placeholder="Paste the copy to create variants of..." required></textarea>
</div>
<div class="form-row">
<div class="form-group">
<label>Variants</label>
<select name="variant_count"><option value="2">2</option><option value="3" selected>3</option><option value="4">4</option><option value="5">5</option></select>
</div>
<div class="form-group">
<label>Focus Area</label>
<select name="focus_area">
<option value="general">General</option>
<option value="headline">Headlines</option>
<option value="cta">Call to Action</option>
<option value="emotion">Emotional Appeal</option>
</select>
</div>
<div class="form-group">
<label>AI Provider & Model</label>
<?= ai_render_dual_selector('provider', 'model', 'openai', 'gpt-5.2') ?>
</div>
</div>
<button type="submit" class="btn btn-primary">🔀 Generate Variants</button>
</form>
</div>
</div>
<div class="card">
<div class="card-head"><span class="card-title"><span>🔀</span> A/B Variants</span></div>
<div class="card-body">
<div id="abOutput" style="color:var(--muted);text-align:center">Variants will appear here</div>
</div>
</div>
</div>
</div>

<!-- History Tab -->
<div class="panel" id="panel-history">
<div class="card">
<div class="card-head"><span class="card-title"><span>📜</span> Generation History</span><button class="btn btn-sm btn-secondary" onclick="loadHistory()">🔄 Refresh</button></div>
<div class="card-body">
<div id="historyOutput" style="color:var(--muted);text-align:center">Loading...</div>
</div>
</div>
</div>
</div>

<script>
const csrf = '<?= $csrfToken ?>';

document.querySelectorAll('.tab').forEach(tab => {
    tab.onclick = () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
        if (tab.dataset.tab === 'history') loadHistory();
    };
});

async function post(action, data) {
    const fd = new FormData();
    fd.append('ajax_action', action);
    fd.append('csrf_token', csrf);
    for (const [k, v] of Object.entries(data)) fd.append(k, v);
    const r = await fetch('', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    return r.json();
}

document.getElementById('generateForm').onsubmit = async e => {
    e.preventDefault();
    const out = document.getElementById('generateOutput');
    out.innerHTML = '<div class="loading">Generating...</div>';
    const fd = new FormData(e.target);
    const data = Object.fromEntries(fd);
    data.include_emoji = fd.get('include_emoji') ? '1' : '';
    const r = await post('generate', data);
    if (r.success && (r.copies || r.variants)) {
        out.innerHTML = ((r.copies || r.variants)).map((v, i) => `<div class="variant"><div class="variant-head"><span>Variant ${i+1}</span><button class="btn btn-sm btn-secondary" onclick="navigator.clipboard.writeText(this.parentElement.nextElementSibling.textContent)">📋 Copy</button></div><div>${v.content || v}</div></div>`).join('');
    } else {
        out.innerHTML = `<div class="alert alert-danger">${r.error || 'Failed'}</div>`;
    }
};

document.getElementById('improveForm').onsubmit = async e => {
    e.preventDefault();
    const out = document.getElementById('improveOutput');
    out.innerHTML = '<div class="loading">Improving...</div>';
    const r = await post('improve', Object.fromEntries(new FormData(e.target)));
    out.innerHTML = r.success ? `<div class="variant"><div>${r.improved_copy || r.copy || JSON.stringify(r)}</div></div>` : `<div class="alert alert-danger">${r.error}</div>`;
};

document.getElementById('analyzeForm').onsubmit = async e => {
    e.preventDefault();
    const out = document.getElementById('analyzeOutput');
    out.innerHTML = '<div class="loading">Analyzing...</div>';
    const r = await post('analyze', Object.fromEntries(new FormData(e.target)));
    if (r.success && r.analysis) {
        const a = r.analysis;
        out.innerHTML = `
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px">
                <div style="background:var(--bg);padding:12px;border-radius:8px;text-align:center"><div style="font-size:24px;font-weight:700;color:var(--accent)">${a.word_count||0}</div><div style="font-size:11px;color:var(--muted)">Words</div></div>
                <div style="background:var(--bg);padding:12px;border-radius:8px;text-align:center"><div style="font-size:24px;font-weight:700;color:var(--success)">${a.readability_score||0}</div><div style="font-size:11px;color:var(--muted)">Readability</div></div>
                <div style="background:var(--bg);padding:12px;border-radius:8px;text-align:center"><div style="font-size:24px;font-weight:700;color:var(--purple)">${a.sentiment||'Neutral'}</div><div style="font-size:11px;color:var(--muted)">Sentiment</div></div>
            </div>
            ${a.suggestions ? `<div style="margin-top:12px"><strong>Suggestions:</strong><ul style="margin:8px 0 0 20px;color:var(--text2)">${a.suggestions.map(s=>`<li>${s}</li>`).join('')}</ul></div>` : ''}
        `;
    } else {
        out.innerHTML = `<div class="alert alert-danger">${r.error || 'Failed'}</div>`;
    }
};

document.getElementById('abForm').onsubmit = async e => {
    e.preventDefault();
    const out = document.getElementById('abOutput');
    out.innerHTML = '<div class="loading">Generating variants...</div>';
    const r = await post('ab_variants', Object.fromEntries(new FormData(e.target)));
    if (r.success && (r.copies || r.variants)) {
        out.innerHTML = ((r.copies || r.variants)).map((v, i) => `<div class="variant"><div class="variant-head"><span>Variant ${String.fromCharCode(65+i)}</span><button class="btn btn-sm btn-secondary" onclick="navigator.clipboard.writeText(this.parentElement.nextElementSibling.textContent)">📋</button></div><div>${v.content || v}</div></div>`).join('');
    } else {
        out.innerHTML = `<div class="alert alert-danger">${r.error}</div>`;
    }
};

async function loadHistory() {
    const out = document.getElementById('historyOutput');
    out.innerHTML = '<div class="loading">Loading...</div>';
    const r = await post('get_history', { limit: 20 });
    if (r.success && r.history?.length) {
        out.innerHTML = r.history.map(h => `<div class="history-item"><div class="type">${h.copy_type||'custom'}</div><div class="preview">${(h.content||'').substring(0,100)}...</div><small style="color:var(--muted)">${h.created_at||''}</small></div>`).join('');
    } else {
        out.innerHTML = '<div style="color:var(--muted);text-align:center">No history yet</div>';
    }
}
</script>
</body>
</html>
