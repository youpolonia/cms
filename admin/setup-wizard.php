<?php
/**
 * Setup Wizard — First-run onboarding for Jessie CMS
 * Guides new users through site setup + AI configuration + starter website generation
 * Catppuccin Dark UI
 */
if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) die('Cannot determine CMS_ROOT');
    define('CMS_ROOT', $cmsRoot);
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once CMS_ROOT . '/core/database.php';

function esc_w($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$pdo = \core\Database::connection();

// ── Check if wizard already completed ──
$wizardDone = false;
try {
    $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'setup_wizard_completed'");
    $stmt->execute();
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    $wizardDone = ($row && $row['value'] === '1');
} catch (\Throwable $e) {}

// ── Load current settings ──
function getSetting(PDO $pdo, string $key, string $default = ''): string {
    try {
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? ($row['value'] ?? $default) : $default;
    } catch (\Throwable $e) { return $default; }
}

function saveSetting(PDO $pdo, string $key, string $value, string $group = 'general'): void {
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = ?");
        $stmt->execute([$value, $key]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`, group_name, updated_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$key, $value, $group]);
    }
}

$siteName = getSetting($pdo, 'site_name', '');
$siteTagline = getSetting($pdo, 'site_tagline', '');

// ── Load AI config ──
$aiConfigFile = CMS_ROOT . '/config/ai_settings.json';
$aiConfig = file_exists($aiConfigFile) ? (json_decode(file_get_contents($aiConfigFile), true) ?? []) : [];
$providers = $aiConfig['providers'] ?? [];

// Count configured providers
$configuredProviders = 0;
foreach ($providers as $name => $p) {
    if (!empty($p['enabled']) && !empty($p['api_key']) && !str_starts_with($p['api_key'] ?? '', 'YOUR_')) {
        $configuredProviders++;
    }
}

// ── Handle POST actions ──
$message = '';
$messageType = '';
$currentStep = (int)($_GET['step'] ?? ($_POST['current_step'] ?? 1));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_site_info') {
        $siteName = trim($_POST['site_name'] ?? '');
        $siteTagline = trim($_POST['site_tagline'] ?? '');
        $siteIndustry = trim($_POST['site_industry'] ?? '');
        $siteLanguage = trim($_POST['site_language'] ?? 'en');
        
        if ($siteName) {
            saveSetting($pdo, 'site_name', $siteName);
            saveSetting($pdo, 'site_tagline', $siteTagline);
            saveSetting($pdo, 'site_industry', $siteIndustry);
            saveSetting($pdo, 'site_language', $siteLanguage);
            $currentStep = 2;
        } else {
            $message = 'Site name is required.';
            $messageType = 'error';
        }
    }
    
    elseif ($action === 'save_ai_config') {
        $provider = trim($_POST['ai_provider'] ?? '');
        $apiKey = trim($_POST['ai_api_key'] ?? '');
        
        if ($provider && $apiKey) {
            // Load current config
            $config = file_exists($aiConfigFile) ? (json_decode(file_get_contents($aiConfigFile), true) ?? []) : [];
            if (!isset($config['providers'])) $config['providers'] = [];
            
            // Update provider
            if (!isset($config['providers'][$provider])) {
                $config['providers'][$provider] = [];
            }
            $config['providers'][$provider]['enabled'] = true;
            $config['providers'][$provider]['api_key'] = $apiKey;
            $config['default_provider'] = $provider;
            
            // Set default model
            $defaultModels = [
                'openai' => 'gpt-4o',
                'anthropic' => 'claude-sonnet-4-20250514',
                'google' => 'gemini-2.0-flash',
                'deepseek' => 'deepseek-v3',
            ];
            if (isset($defaultModels[$provider]) && empty($config['providers'][$provider]['default_model'])) {
                $config['providers'][$provider]['default_model'] = $defaultModels[$provider];
            }
            
            // Ensure defaults exist
            if (!isset($config['generation_defaults'])) {
                $config['generation_defaults'] = ['temperature' => 0.7, 'max_tokens' => 8000, 'top_p' => 1];
            }
            if (!isset($config['rate_limits'])) {
                $config['rate_limits'] = ['requests_per_minute' => 60, 'tokens_per_day' => 1000000, 'cost_limit_daily_usd' => 50];
            }
            
            file_put_contents($aiConfigFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $configuredProviders = 1;
            $currentStep = 3;
        } else {
            $currentStep = 2;
            if (!$provider) {
                // Skip AI setup
                $currentStep = 3;
            } else {
                $message = 'Please enter your API key.';
                $messageType = 'error';
            }
        }
    }
    
    elseif ($action === 'skip_ai') {
        $currentStep = 3;
    }
    
    elseif ($action === 'generate_website') {
        $siteIndustry = getSetting($pdo, 'site_industry', 'general');
        $siteName = getSetting($pdo, 'site_name', 'My Website');
        $siteTagline = getSetting($pdo, 'site_tagline', '');
        $styleChoice = trim($_POST['style_choice'] ?? 'modern');
        $colorScheme = trim($_POST['color_scheme'] ?? 'blue');
        $pageCount = (int)($_POST['page_count'] ?? 4);
        
        saveSetting($pdo, 'wizard_style', $styleChoice);
        saveSetting($pdo, 'wizard_color', $colorScheme);
        
        // Store wizard data in session for generation
        $_SESSION['wizard_generate'] = [
            'site_name' => $siteName,
            'tagline' => $siteTagline,
            'industry' => $siteIndustry,
            'style' => $styleChoice,
            'color' => $colorScheme,
            'pages' => $pageCount,
        ];
        $currentStep = 4;
    }
    
    elseif ($action === 'complete_wizard') {
        saveSetting($pdo, 'setup_wizard_completed', '1');
        header('Location: /admin/dashboard');
        exit;
    }
    
    elseif ($action === 'skip_wizard') {
        saveSetting($pdo, 'setup_wizard_completed', '1');
        header('Location: /admin/dashboard');
        exit;
    }
}

// Reload settings after save
$siteName = getSetting($pdo, 'site_name', '');
$siteTagline = getSetting($pdo, 'site_tagline', '');
$siteIndustry = getSetting($pdo, 'site_industry', '');
$siteLanguage = getSetting($pdo, 'site_language', 'en');

$industries = [
    'restaurant' => '🍕 Restaurant / Food',
    'ecommerce' => '🛒 Online Store',
    'portfolio' => '🎨 Portfolio / Creative',
    'business' => '💼 Business / Corporate',
    'agency' => '🏢 Agency / Consulting',
    'blog' => '📝 Blog / Magazine',
    'fitness' => '💪 Fitness / Health',
    'education' => '📚 Education / Courses',
    'realestate' => '🏠 Real Estate',
    'tech' => '💻 Technology / SaaS',
    'medical' => '🏥 Medical / Healthcare',
    'legal' => '⚖️ Legal / Law Firm',
    'nonprofit' => '❤️ Non-Profit / Charity',
    'photography' => '📷 Photography',
    'construction' => '🔨 Construction / Trades',
    'beauty' => '💅 Beauty / Salon',
    'auto' => '🚗 Automotive',
    'travel' => '✈️ Travel / Tourism',
    'music' => '🎵 Music / Entertainment',
    'other' => '🌐 Other',
];

$styles = [
    'modern' => ['label' => 'Modern & Clean', 'icon' => '✨', 'desc' => 'Minimalist design, lots of whitespace, sharp typography'],
    'bold' => ['label' => 'Bold & Dynamic', 'icon' => '🔥', 'desc' => 'Strong colors, large headings, eye-catching layouts'],
    'elegant' => ['label' => 'Elegant & Refined', 'icon' => '💎', 'desc' => 'Sophisticated, serif fonts, subtle animations'],
    'playful' => ['label' => 'Playful & Creative', 'icon' => '🎨', 'desc' => 'Vibrant colors, rounded shapes, fun personality'],
    'corporate' => ['label' => 'Professional & Corporate', 'icon' => '🏢', 'desc' => 'Trustworthy, structured layouts, business-focused'],
    'dark' => ['label' => 'Dark & Premium', 'icon' => '🌙', 'desc' => 'Dark backgrounds, neon accents, luxury feel'],
];

$colors = [
    'blue' => ['label' => 'Ocean Blue', 'primary' => '#3b82f6', 'secondary' => '#1e40af'],
    'green' => ['label' => 'Forest Green', 'primary' => '#22c55e', 'secondary' => '#15803d'],
    'purple' => ['label' => 'Royal Purple', 'primary' => '#a855f7', 'secondary' => '#7c3aed'],
    'red' => ['label' => 'Ruby Red', 'primary' => '#ef4444', 'secondary' => '#dc2626'],
    'orange' => ['label' => 'Sunset Orange', 'primary' => '#f97316', 'secondary' => '#ea580c'],
    'teal' => ['label' => 'Teal', 'primary' => '#14b8a6', 'secondary' => '#0d9488'],
    'pink' => ['label' => 'Rose Pink', 'primary' => '#ec4899', 'secondary' => '#db2777'],
    'slate' => ['label' => 'Slate Gray', 'primary' => '#64748b', 'secondary' => '#475569'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup Wizard - Jessie CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f0f1a;--bg2:#1a1a2e;--bg3:#25253e;--bg4:#35355a;--text:#e2e8f0;--text2:#a0aec0;--muted:#6b7280;--accent:#818cf8;--accent2:#6366f1;--accent-glow:rgba(129,140,248,.15);--success:#34d399;--warning:#fbbf24;--danger:#f87171;--border:rgba(255,255,255,.08);--card-bg:rgba(26,26,46,.8);--glass:rgba(255,255,255,.03)}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden}
body::before{content:'';position:fixed;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle at 30% 20%, rgba(99,102,241,.08) 0%, transparent 50%), radial-gradient(circle at 70% 80%, rgba(129,140,248,.05) 0%, transparent 50%);z-index:0;pointer-events:none}

.wizard-container{max-width:720px;margin:0 auto;padding:40px 24px;position:relative;z-index:1}

.wizard-logo{text-align:center;margin-bottom:32px}
.wizard-logo h1{font-size:28px;font-weight:800;background:linear-gradient(135deg,#818cf8,#a78bfa,#c084fc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:4px}
.wizard-logo p{color:var(--muted);font-size:13px}

/* Stepper */
.stepper{display:flex;justify-content:center;gap:8px;margin-bottom:40px}
.step-dot{width:40px;height:5px;border-radius:3px;background:var(--bg3);transition:.3s}
.step-dot.active{background:var(--accent);box-shadow:0 0 12px rgba(129,140,248,.4)}
.step-dot.done{background:var(--success)}

/* Card */
.wizard-card{background:var(--card-bg);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:20px;padding:40px;animation:fadeUp .4s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}

.wizard-card h2{font-size:22px;font-weight:700;margin-bottom:6px}
.wizard-card .subtitle{color:var(--text2);font-size:14px;margin-bottom:28px;line-height:1.5}

/* Form */
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text2);margin-bottom:8px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:12px;color:var(--text);font-size:14px;font-family:'Inter',sans-serif;transition:.2s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow)}
.form-group input::placeholder{color:var(--muted)}
.form-group select{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center}
.form-group select option{background:var(--bg2);color:var(--text)}
.form-hint{font-size:11px;color:var(--muted);margin-top:6px}

/* Grid selectors */
.option-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:20px}
.option-grid.three{grid-template-columns:repeat(3,1fr)}
@media(max-width:600px){.option-grid,.option-grid.three{grid-template-columns:1fr}}
.option-card{background:var(--bg);border:2px solid var(--border);border-radius:14px;padding:16px;cursor:pointer;transition:.2s;text-align:center;position:relative}
.option-card:hover{border-color:rgba(129,140,248,.3);background:var(--glass)}
.option-card.selected{border-color:var(--accent);background:var(--accent-glow);box-shadow:0 0 20px rgba(129,140,248,.1)}
.option-card .icon{font-size:28px;margin-bottom:8px;display:block}
.option-card .name{font-size:13px;font-weight:600;margin-bottom:2px}
.option-card .desc{font-size:11px;color:var(--muted);line-height:1.4}
.option-card input[type="radio"]{position:absolute;opacity:0;width:0;height:0}

/* Color swatches */
.color-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
@media(max-width:500px){.color-grid{grid-template-columns:repeat(2,1fr)}}
.color-swatch{border:2px solid var(--border);border-radius:12px;padding:12px;cursor:pointer;transition:.2s;text-align:center}
.color-swatch:hover{border-color:rgba(129,140,248,.3)}
.color-swatch.selected{border-color:var(--accent);box-shadow:0 0 20px rgba(129,140,248,.15)}
.color-swatch .dot{width:32px;height:32px;border-radius:50%;margin:0 auto 8px;box-shadow:0 2px 8px rgba(0,0,0,.3)}
.color-swatch .name{font-size:11px;font-weight:500}
.color-swatch input[type="radio"]{position:absolute;opacity:0;width:0;height:0}

/* Buttons */
.btn-row{display:flex;justify-content:space-between;align-items:center;margin-top:32px;gap:12px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;font-size:14px;font-weight:600;border:none;border-radius:12px;cursor:pointer;transition:.2s;font-family:'Inter',sans-serif;text-decoration:none}
.btn-primary{background:linear-gradient(135deg,var(--accent2),var(--accent));color:#fff;box-shadow:0 4px 15px rgba(99,102,241,.3)}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(99,102,241,.4)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{background:var(--bg4)}
.btn-ghost{background:transparent;color:var(--muted);padding:12px 16px}
.btn-ghost:hover{color:var(--text)}
.btn-success{background:linear-gradient(135deg,#059669,#34d399);color:#fff;box-shadow:0 4px 15px rgba(52,211,153,.3)}

/* Alert */
.alert{padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:13px;display:flex;align-items:center;gap:10px}
.alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:var(--danger)}
.alert-success{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);color:var(--success)}

/* Provider cards */
.provider-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:20px}
.provider-card{background:var(--bg);border:2px solid var(--border);border-radius:14px;padding:14px;cursor:pointer;transition:.2s;display:flex;align-items:center;gap:12px}
.provider-card:hover{border-color:rgba(129,140,248,.3)}
.provider-card.selected{border-color:var(--accent);background:var(--accent-glow)}
.provider-card .p-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;flex-shrink:0}
.provider-card .p-info .p-name{font-size:13px;font-weight:600}
.provider-card .p-info .p-model{font-size:11px;color:var(--muted)}
.provider-card input[type="radio"]{position:absolute;opacity:0;width:0;height:0}

/* Generate step */
.gen-status{text-align:center;padding:40px 20px}
.gen-spinner{width:64px;height:64px;border:4px solid var(--bg3);border-top:4px solid var(--accent);border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 24px}
@keyframes spin{to{transform:rotate(360deg)}}
.gen-status h3{font-size:18px;font-weight:600;margin-bottom:8px}
.gen-status p{color:var(--text2);font-size:13px}
.gen-log{background:var(--bg);border-radius:10px;padding:14px;margin-top:20px;font-family:monospace;font-size:12px;max-height:200px;overflow-y:auto;color:var(--text2);text-align:left}
.gen-log .log-line{padding:2px 0;border-bottom:1px solid var(--border)}
.gen-log .log-success{color:var(--success)}
.gen-log .log-error{color:var(--danger)}

/* Done step */
.done-icon{font-size:64px;text-align:center;margin-bottom:16px}
.done-features{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin:24px 0}
.done-feature{background:var(--bg);border-radius:10px;padding:12px 14px;display:flex;align-items:center;gap:10px;font-size:13px}
.done-feature .f-icon{font-size:20px}

/* Skip link */
.skip-bar{text-align:center;margin-top:24px}
.skip-bar a{color:var(--muted);font-size:12px;text-decoration:none}
.skip-bar a:hover{color:var(--text)}

/* Page count slider */
.page-count-row{display:flex;align-items:center;gap:16px;margin-bottom:20px}
.page-count-row input[type="range"]{flex:1;accent-color:var(--accent);height:6px}
.page-count-val{font-size:20px;font-weight:700;color:var(--accent);min-width:30px;text-align:center}
</style>
</head>
<body>

<div class="wizard-container">

<div class="wizard-logo">
    <h1>🐕 Jessie CMS</h1>
    <p>Let's set up your website in a few minutes</p>
</div>

<!-- Stepper -->
<div class="stepper">
    <?php for ($i = 1; $i <= 5; $i++): ?>
    <div class="step-dot <?= $i < $currentStep ? 'done' : ($i === $currentStep ? 'active' : '') ?>"></div>
    <?php endfor; ?>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>"><?= $messageType === 'error' ? '⚠️' : '✅' ?> <?= esc_w($message) ?></div>
<?php endif; ?>

<?php if ($currentStep === 1): ?>
<!-- ═══════════════ STEP 1: Site Info ═══════════════ -->
<div class="wizard-card">
    <h2>👋 Welcome! Tell us about your site</h2>
    <p class="subtitle">We'll use this to personalize your experience and generate your starter website.</p>
    
    <form method="POST">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="save_site_info">
        <input type="hidden" name="current_step" value="1">
        
        <div class="form-group">
            <label>Site Name *</label>
            <input type="text" name="site_name" value="<?= esc_w($siteName) ?>" placeholder="e.g. Tony's Pizza, Acme Corp, My Blog" required autofocus>
        </div>
        
        <div class="form-group">
            <label>Tagline</label>
            <input type="text" name="site_tagline" value="<?= esc_w($siteTagline) ?>" placeholder="e.g. The best pizza in town">
            <div class="form-hint">A short description — shows in search results and headers</div>
        </div>
        
        <div class="form-group">
            <label>Industry / Category</label>
            <select name="site_industry">
                <?php foreach ($industries as $val => $label): ?>
                <option value="<?= $val ?>" <?= $siteIndustry === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Language</label>
            <select name="site_language">
                <option value="en" <?= $siteLanguage === 'en' ? 'selected' : '' ?>>🇬🇧 English</option>
                <option value="pl" <?= $siteLanguage === 'pl' ? 'selected' : '' ?>>🇵🇱 Polski</option>
                <option value="de" <?= $siteLanguage === 'de' ? 'selected' : '' ?>>🇩🇪 Deutsch</option>
                <option value="fr" <?= $siteLanguage === 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
                <option value="es" <?= $siteLanguage === 'es' ? 'selected' : '' ?>>🇪🇸 Español</option>
                <option value="it" <?= $siteLanguage === 'it' ? 'selected' : '' ?>>🇮🇹 Italiano</option>
                <option value="pt" <?= $siteLanguage === 'pt' ? 'selected' : '' ?>>🇧🇷 Português</option>
                <option value="nl" <?= $siteLanguage === 'nl' ? 'selected' : '' ?>>🇳🇱 Nederlands</option>
                <option value="cs" <?= $siteLanguage === 'cs' ? 'selected' : '' ?>>🇨🇿 Čeština</option>
            </select>
        </div>
        
        <div class="btn-row">
            <span></span>
            <button type="submit" class="btn btn-primary">Continue →</button>
        </div>
    </form>
</div>

<?php elseif ($currentStep === 2): ?>
<!-- ═══════════════ STEP 2: AI Setup ═══════════════ -->
<div class="wizard-card">
    <h2>🤖 Connect an AI Provider</h2>
    <p class="subtitle">Jessie CMS uses AI to generate websites, write content, optimize SEO, and more. You bring your own API key — no markup, you pay providers directly.</p>
    
    <form method="POST" id="aiForm">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="save_ai_config">
        <input type="hidden" name="current_step" value="2">
        
        <div class="provider-grid">
            <label class="provider-card" onclick="selectProvider('openai')">
                <input type="radio" name="ai_provider" value="openai" id="p_openai">
                <div class="p-icon" style="background:#10a37f;color:#fff">O</div>
                <div class="p-info"><div class="p-name">OpenAI</div><div class="p-model">GPT-4o, GPT-5</div></div>
            </label>
            <label class="provider-card" onclick="selectProvider('anthropic')">
                <input type="radio" name="ai_provider" value="anthropic" id="p_anthropic">
                <div class="p-icon" style="background:#d4a574;color:#fff">A</div>
                <div class="p-info"><div class="p-name">Anthropic</div><div class="p-model">Claude Sonnet, Opus</div></div>
            </label>
            <label class="provider-card" onclick="selectProvider('google')">
                <input type="radio" name="ai_provider" value="google" id="p_google">
                <div class="p-icon" style="background:#4285f4;color:#fff">G</div>
                <div class="p-info"><div class="p-name">Google</div><div class="p-model">Gemini 2.0 Flash</div></div>
            </label>
            <label class="provider-card" onclick="selectProvider('deepseek')">
                <input type="radio" name="ai_provider" value="deepseek" id="p_deepseek">
                <div class="p-icon" style="background:#5b6ee1;color:#fff">D</div>
                <div class="p-info"><div class="p-name">DeepSeek</div><div class="p-model">V3 — Budget-friendly</div></div>
            </label>
        </div>
        
        <div class="form-group" id="apiKeyGroup" style="display:none">
            <label>API Key</label>
            <input type="password" name="ai_api_key" id="aiApiKey" placeholder="Paste your API key here">
            <div class="form-hint" id="apiKeyHint">Get your key from the provider's dashboard</div>
        </div>
        
        <div class="btn-row">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('skipForm').submit()">Skip for now →</button>
            <button type="submit" class="btn btn-primary" id="saveAiBtn" style="display:none">Save & Continue →</button>
        </div>
    </form>
    
    <form method="POST" id="skipForm" style="display:none">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="skip_ai">
    </form>
</div>

<script>
function selectProvider(name) {
    document.querySelectorAll('.provider-card').forEach(c => c.classList.remove('selected'));
    const card = document.getElementById('p_' + name).closest('.provider-card');
    card.classList.add('selected');
    document.getElementById('p_' + name).checked = true;
    document.getElementById('apiKeyGroup').style.display = 'block';
    document.getElementById('saveAiBtn').style.display = 'inline-flex';
    
    const hints = {
        openai: 'Get your key at <a href="https://platform.openai.com/api-keys" target="_blank" style="color:var(--accent)">platform.openai.com/api-keys</a>',
        anthropic: 'Get your key at <a href="https://console.anthropic.com/settings/keys" target="_blank" style="color:var(--accent)">console.anthropic.com</a>',
        google: 'Get your key at <a href="https://aistudio.google.com/apikey" target="_blank" style="color:var(--accent)">aistudio.google.com/apikey</a>',
        deepseek: 'Get your key at <a href="https://platform.deepseek.com/api_keys" target="_blank" style="color:var(--accent)">platform.deepseek.com</a> — cheapest option!'
    };
    document.getElementById('apiKeyHint').innerHTML = hints[name] || '';
    document.getElementById('aiApiKey').focus();
}
</script>

<?php elseif ($currentStep === 3): ?>
<!-- ═══════════════ STEP 3: Style ═══════════════ -->
<div class="wizard-card">
    <h2>🎨 Choose your style</h2>
    <p class="subtitle">Pick a design style and color scheme. AI will use these to generate your website.</p>
    
    <form method="POST">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="generate_website">
        <input type="hidden" name="current_step" value="3">
        
        <label style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text2);display:block;margin-bottom:10px">Design Style</label>
        <div class="option-grid three">
            <?php foreach ($styles as $key => $s): ?>
            <label class="option-card" onclick="selectStyle(this, '<?= $key ?>')">
                <input type="radio" name="style_choice" value="<?= $key ?>" <?= $key === 'modern' ? 'checked' : '' ?>>
                <span class="icon"><?= $s['icon'] ?></span>
                <div class="name"><?= $s['label'] ?></div>
                <div class="desc"><?= $s['desc'] ?></div>
            </label>
            <?php endforeach; ?>
        </div>
        
        <label style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text2);display:block;margin-bottom:10px">Color Scheme</label>
        <div class="color-grid">
            <?php foreach ($colors as $key => $c): ?>
            <label class="color-swatch <?= $key === 'blue' ? 'selected' : '' ?>" onclick="selectColor(this, '<?= $key ?>')">
                <input type="radio" name="color_scheme" value="<?= $key ?>" <?= $key === 'blue' ? 'checked' : '' ?>>
                <div class="dot" style="background:linear-gradient(135deg, <?= $c['primary'] ?>, <?= $c['secondary'] ?>)"></div>
                <div class="name"><?= $c['label'] ?></div>
            </label>
            <?php endforeach; ?>
        </div>
        
        <label style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text2);display:block;margin-bottom:10px">Number of pages</label>
        <div class="page-count-row">
            <input type="range" name="page_count" min="1" max="8" value="4" oninput="document.getElementById('pcVal').textContent=this.value">
            <span class="page-count-val" id="pcVal">4</span>
        </div>
        <div class="form-hint" style="margin-top:-12px;margin-bottom:20px">Typically: Home, About, Services, Contact</div>
        
        <div class="btn-row">
            <a href="?step=2" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-primary"><?= $configuredProviders > 0 ? '✨ Generate My Website' : 'Continue →' ?></button>
        </div>
    </form>
</div>

<script>
function selectStyle(el, val) {
    document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}
function selectColor(el, val) {
    document.querySelectorAll('.color-swatch').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}
document.querySelector('.option-card input:checked')?.closest('.option-card')?.classList.add('selected');
</script>

<?php elseif ($currentStep === 4): ?>
<!-- ═══════════════ STEP 4: Generating ═══════════════ -->
<?php
$wizardData = $_SESSION['wizard_generate'] ?? [];
$hasAI = $configuredProviders > 0;
?>
<div class="wizard-card">
    <?php if ($hasAI && !empty($wizardData)): ?>
    <div class="gen-status" id="genStatus">
        <div class="gen-spinner" id="genSpinner"></div>
        <h3>✨ Generating your website...</h3>
        <p>AI is building <strong><?= esc_w($wizardData['site_name'] ?? 'your website') ?></strong> with <?= (int)($wizardData['pages'] ?? 4) ?> pages</p>
        <div class="gen-log" id="genLog">
            <div class="log-line">🚀 Starting website generation...</div>
        </div>
    </div>
    
    <div id="genDone" style="display:none">
        <div class="done-icon">🎉</div>
        <h2 style="text-align:center">Your website is ready!</h2>
        <p class="subtitle" style="text-align:center">We created <?= (int)($wizardData['pages'] ?? 4) ?> pages for <strong><?= esc_w($wizardData['site_name'] ?? '') ?></strong></p>
        
        <div class="btn-row" style="justify-content:center">
            <form method="POST" style="display:inline">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="complete_wizard">
                <button type="submit" class="btn btn-success">🚀 Go to Dashboard</button>
            </form>
            <a href="/admin/pages" class="btn btn-secondary">📄 View Pages</a>
        </div>
    </div>
    
    <div id="genError" style="display:none">
        <div class="done-icon">😅</div>
        <h2 style="text-align:center">Generation had an issue</h2>
        <p class="subtitle" style="text-align:center" id="genErrorMsg">But don't worry — you can always use the AI Website Builder later!</p>
        
        <div class="btn-row" style="justify-content:center">
            <form method="POST" style="display:inline">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="complete_wizard">
                <button type="submit" class="btn btn-primary">Continue to Dashboard →</button>
            </form>
        </div>
    </div>
    
    <script>
    (async function() {
        const log = document.getElementById('genLog');
        const addLog = (msg, cls='') => {
            const d = document.createElement('div');
            d.className = 'log-line' + (cls ? ' log-' + cls : '');
            d.textContent = msg;
            log.appendChild(d);
            log.scrollTop = log.scrollHeight;
        };
        
        try {
            // Step 1: Start multi-agent build
            addLog('📡 Connecting to AI multi-agent pipeline...');
            
            const startResp = await fetch('/api/jtb/ai/multi-agent', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'start',
                    prompt: <?= json_encode(
                        'Create a ' . ($wizardData['style'] ?? 'modern') . ' ' . ($wizardData['industry'] ?? 'business') . 
                        ' website called "' . ($wizardData['site_name'] ?? 'My Website') . '"' .
                        ($wizardData['tagline'] ? ' — ' . $wizardData['tagline'] : '') .
                        '. Color scheme: ' . ($wizardData['color'] ?? 'blue') .
                        '. Generate ' . ($wizardData['pages'] ?? 4) . ' pages.' .
                        ($wizardData['site_language'] ?? 'en') !== 'en' ? ' Content in ' . ($wizardData['site_language'] ?? 'English') . '.' : ''
                    ) ?>,
                    style: <?= json_encode($wizardData['style'] ?? 'modern') ?>,
                    industry: <?= json_encode($wizardData['industry'] ?? 'business') ?>,
                    color_scheme: <?= json_encode($wizardData['color'] ?? 'blue') ?>,
                    pages: <?= (int)($wizardData['pages'] ?? 4) ?>
                })
            });
            
            const startData = await startResp.json();
            
            if (!startData.success) {
                throw new Error(startData.error || 'Failed to start generation');
            }
            
            const sessionId = startData.data?.session_id || startData.session_id;
            addLog('✅ Session created: ' + sessionId, 'success');
            
            // Step 2: Run build steps
            const steps = startData.data?.steps || ['mockup', 'architect', 'content', 'stylist', 'seo', 'images', 'assemble'];
            const stepLabels = {
                mockup: '🎨 Creating visual mockup...',
                architect: '🏗️ Designing page structure...',
                content: '📝 Writing content...',
                stylist: '💅 Applying styles...',
                seo: '🔍 Optimizing for search engines...',
                images: '🖼️ Adding images...',
                assemble: '🧩 Assembling final website...'
            };
            
            for (const step of steps) {
                addLog(stepLabels[step] || '⚙️ Running ' + step + '...');
                
                const stepResp = await fetch('/api/jtb/ai/multi-agent', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'run_step',
                        session_id: sessionId,
                        step: step
                    })
                });
                
                const stepData = await stepResp.json();
                
                if (stepData.success) {
                    addLog('✅ ' + step + ' complete', 'success');
                } else {
                    addLog('⚠️ ' + step + ': ' + (stepData.error || 'partial'), 'error');
                }
            }
            
            // Step 3: Save to CMS
            addLog('💾 Saving website to CMS...');
            
            const saveResp = await fetch('/api/jtb/ai/save-website', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    session_id: sessionId,
                    clear_existing: true,
                    mapping: {}
                })
            });
            
            const saveData = await saveResp.json();
            
            if (saveData.success) {
                addLog('🎉 Website saved! ' + (saveData.data?.saved_count || '') + ' templates created', 'success');
            } else {
                addLog('⚠️ Save: ' + (saveData.error || 'partial save'), 'error');
            }
            
            // Show done
            document.getElementById('genStatus').style.display = 'none';
            document.getElementById('genDone').style.display = 'block';
            
        } catch (err) {
            addLog('❌ Error: ' + err.message, 'error');
            document.getElementById('genSpinner').style.display = 'none';
            document.getElementById('genErrorMsg').textContent = err.message + ' — You can use the AI Website Builder later from the admin panel.';
            
            setTimeout(() => {
                document.getElementById('genStatus').style.display = 'none';
                document.getElementById('genError').style.display = 'block';
            }, 2000);
        }
    })();
    </script>
    
    <?php else: ?>
    <!-- No AI configured — skip to done -->
    <div class="done-icon">🏗️</div>
    <h2 style="text-align:center">Almost there!</h2>
    <p class="subtitle" style="text-align:center">Since you haven't configured an AI provider yet, we'll set up a basic website structure for you. You can always use the <strong>AI Website Builder</strong> later to generate a full website!</p>
    
    <div class="done-features">
        <div class="done-feature"><span class="f-icon">📄</span> <span>Blank Home page created</span></div>
        <div class="done-feature"><span class="f-icon">🎨</span> <span>JTB Theme Builder ready</span></div>
        <div class="done-feature"><span class="f-icon">🤖</span> <span>AI tools available</span></div>
        <div class="done-feature"><span class="f-icon">📊</span> <span>SEO tools included</span></div>
    </div>
    
    <div class="btn-row" style="justify-content:center">
        <form method="POST">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="complete_wizard">
            <button type="submit" class="btn btn-primary">🚀 Go to Dashboard →</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php elseif ($currentStep === 5): ?>
<!-- ═══════════════ STEP 5: Complete ═══════════════ -->
<div class="wizard-card">
    <div class="done-icon">🎉</div>
    <h2 style="text-align:center">You're all set!</h2>
    <p class="subtitle" style="text-align:center">Your Jessie CMS is configured and ready. Here's what you can do next:</p>
    
    <div class="done-features">
        <div class="done-feature"><span class="f-icon">📄</span> <span>Edit your pages</span></div>
        <div class="done-feature"><span class="f-icon">🎨</span> <span>Customize design with JTB</span></div>
        <div class="done-feature"><span class="f-icon">✨</span> <span>Generate content with AI</span></div>
        <div class="done-feature"><span class="f-icon">📊</span> <span>Optimize SEO</span></div>
        <div class="done-feature"><span class="f-icon">📝</span> <span>Write blog articles</span></div>
        <div class="done-feature"><span class="f-icon">⚙️</span> <span>Configure settings</span></div>
    </div>
    
    <div class="btn-row" style="justify-content:center">
        <form method="POST">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="complete_wizard">
            <button type="submit" class="btn btn-success" style="font-size:16px;padding:14px 32px">🚀 Launch Dashboard</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Skip wizard -->
<?php if ($currentStep < 4): ?>
<div class="skip-bar">
    <form method="POST" style="display:inline">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="skip_wizard">
        <button type="submit" class="btn btn-ghost" style="font-size:12px">Skip setup — I'll configure later</button>
    </form>
</div>
<?php endif; ?>

</div>
</body>
</html>
