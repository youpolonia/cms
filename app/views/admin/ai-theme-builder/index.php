<?php
/**
 * AI Theme Builder — Full-Screen UI
 * Catppuccin Mocha Dark Theme — v2
 */
$username = \Core\Session::getAdminUsername() ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Theme Builder — Jessie AI-CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <style>
    :root {
        --ctp-base: #1e1e2e;
        --ctp-mantle: #181825;
        --ctp-crust: #11111b;
        --ctp-surface0: #313244;
        --ctp-surface1: #45475a;
        --ctp-surface2: #585b70;
        --ctp-overlay0: #6c7086;
        --ctp-overlay1: #7f849c;
        --ctp-text: #cdd6f4;
        --ctp-subtext0: #a6adc8;
        --ctp-subtext1: #bac2de;
        --ctp-blue: #89b4fa;
        --ctp-green: #a6e3a1;
        --ctp-mauve: #cba6f7;
        --ctp-red: #f38ba8;
        --ctp-peach: #fab387;
        --ctp-yellow: #f9e2af;
        --ctp-teal: #94e2d5;
        --ctp-lavender: #b4befe;
        --ctp-sky: #89dceb;
        --ctp-flamingo: #f2cdcd;
        --ctp-pink: #f5c2e7;
        --ctp-rosewater: #f5e0dc;
    }

    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: 'Inter', -apple-system, sans-serif;
        background: var(--ctp-base);
        color: var(--ctp-text);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* ── Topbar ── */
    .atb-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        height: 52px;
        background: var(--ctp-mantle);
        border-bottom: 1px solid var(--ctp-surface0);
        flex-shrink: 0;
    }
    .atb-topbar-left { display: flex; align-items: center; gap: 16px; }
    .atb-topbar-left a {
        color: var(--ctp-subtext0); text-decoration: none; font-size: 13px;
        display: flex; align-items: center; gap: 6px; transition: color .2s;
    }
    .atb-topbar-left a:hover { color: var(--ctp-text); }
    .atb-topbar-title {
        font-size: 15px; font-weight: 600;
        display: flex; align-items: center; gap: 8px;
    }
    .atb-topbar-title .badge {
        background: linear-gradient(135deg, var(--ctp-mauve), var(--ctp-blue));
        color: var(--ctp-crust); font-size: 9px; font-weight: 700;
        padding: 2px 7px; border-radius: 5px; text-transform: uppercase; letter-spacing: .05em;
    }

    /* ── Main layout ── */
    .atb-main { display: flex; flex: 1; min-height: 0; }

    /* ── Left Panel ── */
    .atb-panel {
        width: 460px; min-width: 460px;
        background: var(--ctp-mantle);
        border-right: 1px solid var(--ctp-surface0);
        display: flex; flex-direction: column;
        overflow-y: auto;
    }
    .atb-panel::-webkit-scrollbar { width: 6px; }
    .atb-panel::-webkit-scrollbar-track { background: transparent; }
    .atb-panel::-webkit-scrollbar-thumb { background: var(--ctp-surface1); border-radius: 3px; }
    .atb-panel-content { padding: 20px 24px; flex: 1; }

    /* ── Section headers ── */
    .atb-section { margin-bottom: 20px; }
    .atb-section-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 10px; cursor: default;
    }
    .atb-section-title {
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        letter-spacing: .08em; color: var(--ctp-overlay0);
    }
    .atb-section-hint {
        font-size: 10px; color: var(--ctp-overlay0); font-style: italic;
    }

    /* ── Form elements ── */
    .atb-field { margin-bottom: 16px; }
    .atb-label {
        display: block; font-size: 12px; font-weight: 500;
        color: var(--ctp-subtext1); margin-bottom: 6px;
    }
    .atb-textarea {
        width: 100%; min-height: 90px; padding: 12px 14px;
        background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1);
        border-radius: 10px; color: var(--ctp-text); font-family: inherit;
        font-size: 14px; line-height: 1.5; resize: vertical;
        transition: border-color .2s, box-shadow .2s;
    }
    .atb-textarea:focus {
        outline: none; border-color: var(--ctp-blue);
        box-shadow: 0 0 0 3px rgba(137,180,250,.15);
    }
    .atb-textarea::placeholder { color: var(--ctp-overlay0); }

    .atb-select {
        width: 100%; padding: 9px 14px;
        background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1);
        border-radius: 8px; color: var(--ctp-text); font-family: inherit; font-size: 13px;
        cursor: pointer; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%237f849c' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px;
    }
    .atb-select:focus { outline: none; border-color: var(--ctp-blue); }
    .atb-select option { background: var(--ctp-surface0); color: var(--ctp-text); }
    .atb-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .atb-row-2 .atb-field { margin-bottom: 0; }

    /* ── Chip Grid ── */
    .atb-chips {
        display: flex; flex-wrap: wrap; gap: 6px;
    }
    .atb-chip {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 500;
        background: var(--ctp-surface0); border: 1px solid transparent;
        color: var(--ctp-subtext0); cursor: pointer;
        transition: all .15s; user-select: none; white-space: nowrap;
    }
    .atb-chip:hover {
        background: var(--ctp-surface1); color: var(--ctp-text);
    }
    .atb-chip.selected {
        background: rgba(137,180,250,.12); border-color: var(--ctp-blue);
        color: var(--ctp-blue); font-weight: 600;
    }
    .atb-chip .chip-icon { font-size: 13px; }

    /* ── Mood chips with color dots ── */
    .atb-chip .mood-dot {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
        border: 1px solid rgba(255,255,255,.1);
    }

    /* ── Style chips with mini preview ── */
    .atb-chip .style-bar {
        width: 16px; height: 10px; border-radius: 2px; flex-shrink: 0;
    }

    /* ── Category label in chips ── */
    .atb-chip-group-label {
        width: 100%; font-size: 10px; font-weight: 600;
        color: var(--ctp-surface2); text-transform: uppercase;
        letter-spacing: .06em; margin-top: 4px; margin-bottom: 2px;
        padding-left: 2px;
    }
    .atb-chip-group-label:first-child { margin-top: 0; }

    /* ── Generate button ── */
    .atb-btn-generate {
        width: 100%; padding: 13px 24px; margin-top: 4px;
        background: linear-gradient(135deg, var(--ctp-mauve), var(--ctp-blue));
        color: var(--ctp-crust); font-family: inherit; font-size: 14px; font-weight: 700;
        border: none; border-radius: 12px; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        transition: opacity .2s, transform .1s;
    }
    .atb-btn-generate:hover { opacity: .9; }
    .atb-btn-generate:active { transform: scale(.98); }
    .atb-btn-generate:disabled { opacity: .5; cursor: not-allowed; }
    .atb-btn-generate .spinner {
        display: none; width: 18px; height: 18px;
        border: 2px solid rgba(0,0,0,.2); border-top-color: var(--ctp-crust);
        border-radius: 50%; animation: spin .6s linear infinite;
    }
    .atb-btn-generate.loading .spinner { display: block; }
    .atb-btn-generate.loading .btn-label { display: none; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Progress ── */
    .atb-progress { margin-top: 20px; display: none; }
    .atb-progress.visible { display: block; }
    .atb-step {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 12px; border-radius: 8px; margin-bottom: 4px;
        font-size: 12px; color: var(--ctp-overlay0); transition: all .3s;
    }
    .atb-step.active { background: var(--ctp-surface0); color: var(--ctp-text); }
    .atb-step.done { color: var(--ctp-green); }
    .atb-step.error { color: var(--ctp-red); }
    .atb-step-icon {
        width: 26px; height: 26px; display: flex; align-items: center; justify-content: center;
        border-radius: 7px; background: var(--ctp-surface0); font-size: 12px; flex-shrink: 0;
    }
    .atb-step.active .atb-step-icon { background: var(--ctp-blue); color: var(--ctp-crust); }
    .atb-step.done .atb-step-icon { background: var(--ctp-green); color: var(--ctp-crust); }
    .atb-step.error .atb-step-icon { background: var(--ctp-red); color: var(--ctp-crust); }
    .atb-step-spinner {
        width: 13px; height: 13px;
        border: 2px solid rgba(0,0,0,.2); border-top-color: var(--ctp-crust);
        border-radius: 50%; animation: spin .6s linear infinite; display: none;
    }
    .atb-step.active .atb-step-spinner { display: block; }
    .atb-step.active .atb-step-num { display: none; }

    /* ── Error ── */
    .atb-error {
        background: rgba(243,139,168,.1); border: 1px solid rgba(243,139,168,.3);
        border-radius: 10px; padding: 12px 14px; margin-top: 14px;
        font-size: 12px; color: var(--ctp-red); display: none;
    }
    .atb-error.visible { display: block; }

    /* ── Timings ── */
    .atb-timings { margin-top: 10px; font-size: 11px; color: var(--ctp-overlay0); display: none; }
    .atb-timings.visible { display: block; }
    .atb-timings span { display: inline-block; margin-right: 10px; }

    /* ── Actions ── */
    .atb-actions { margin-top: 16px; display: none; gap: 8px; }
    .atb-actions.visible { display: flex; }
    .atb-btn {
        flex: 1; padding: 10px 14px; font-family: inherit; font-size: 12px; font-weight: 600;
        border-radius: 10px; cursor: pointer; display: flex; align-items: center;
        justify-content: center; gap: 7px; transition: opacity .2s; text-decoration: none;
    }
    .atb-btn:hover { opacity: .85; }
    .atb-btn-apply { background: var(--ctp-green); color: var(--ctp-crust); border: none; }
    .atb-btn-studio { background: var(--ctp-surface0); color: var(--ctp-text); border: 1px solid var(--ctp-surface1); }
    .atb-btn-delete { background: transparent; color: var(--ctp-red); border: 1px solid rgba(243,139,168,.3); flex: 0; padding: 10px 14px; }

    /* ── Previously Generated ── */
    .atb-previous { margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--ctp-surface0); }
    .atb-previous h3 {
        font-size: 11px; font-weight: 600; color: var(--ctp-overlay0);
        margin-bottom: 10px; text-transform: uppercase; letter-spacing: .05em;
    }
    .atb-theme-item {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        padding: 8px 12px; background: var(--ctp-surface0); border-radius: 8px;
        margin-bottom: 5px; font-size: 12px;
    }
    .atb-theme-item-name { font-weight: 500; flex: 1; }
    .atb-theme-item-actions { display: flex; gap: 4px; }
    .atb-theme-item-actions button, .atb-theme-item-actions a {
        background: none; border: none; color: var(--ctp-blue); cursor: pointer;
        font-size: 11px; font-family: inherit; text-decoration: none; padding: 4px 7px;
        border-radius: 4px; transition: background .2s;
    }
    .atb-theme-item-actions button:hover, .atb-theme-item-actions a:hover {
        background: rgba(137,180,250,.1);
    }

    /* ── Divider ── */
    .atb-divider { height: 1px; background: var(--ctp-surface0); margin: 16px 0; }

    /* ── No AI warning ── */
    .atb-no-ai {
        background: rgba(249,226,175,.1); border: 1px solid rgba(249,226,175,.3);
        border-radius: 10px; padding: 14px; margin-bottom: 16px; font-size: 12px; color: var(--ctp-yellow);
    }
    .atb-no-ai a { color: var(--ctp-blue); text-decoration: underline; }

    /* ── Right Panel (preview) ── */
    .atb-preview {
        flex: 1; display: flex; flex-direction: column;
        background: var(--ctp-crust); position: relative;
    }
    .atb-preview-toolbar {
        display: flex; align-items: center; justify-content: space-between;
        padding: 8px 16px; background: var(--ctp-mantle);
        border-bottom: 1px solid var(--ctp-surface0); font-size: 12px; color: var(--ctp-overlay0);
    }
    .atb-preview-toolbar .url-bar {
        background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1);
        border-radius: 6px; padding: 4px 14px; font-size: 11px; color: var(--ctp-subtext0);
        min-width: 260px; text-align: center;
    }
    .atb-preview-device { display: flex; gap: 4px; }
    .atb-preview-device button {
        background: none; border: 1px solid var(--ctp-surface1); color: var(--ctp-overlay0);
        padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 12px; transition: all .2s;
    }
    .atb-preview-device button:hover, .atb-preview-device button.active {
        background: var(--ctp-surface0); color: var(--ctp-text); border-color: var(--ctp-blue);
    }
    .atb-preview-frame { flex: 1; display: flex; justify-content: center; overflow: hidden; }
    .atb-preview-frame iframe {
        width: 100%; height: 100%; border: none; background: #fff; transition: width .3s ease;
    }
    .atb-preview-frame.device-tablet iframe { width: 768px; }
    .atb-preview-frame.device-mobile iframe { width: 375px; }
    .atb-preview-empty {
        flex: 1; display: flex; flex-direction: column; align-items: center;
        justify-content: center; color: var(--ctp-overlay0); gap: 12px;
    }
    .atb-preview-empty i { font-size: 48px; opacity: .25; }
    .atb-preview-empty p { font-size: 13px; }

    /* ── Responsive ── */
    @media (max-width: 960px) {
        .atb-main { flex-direction: column; }
        .atb-panel { width: 100%; min-width: 0; border-right: none; border-bottom: 1px solid var(--ctp-surface0); max-height: 55vh; }
        .atb-preview { min-height: 45vh; }
    }
    
    /* Inline Help Tooltips */
    .tip{display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;font-size:10px;font-weight:700;line-height:1;color:#6c7086;background:#313244;border:1px solid #45475a;border-radius:50%;cursor:help;position:relative;vertical-align:middle;margin-left:4px;transition:color .15s,border-color .15s,background .15s;user-select:none;flex-shrink:0}
    .tip:hover{color:#6366f1;border-color:#6366f1;background:rgba(99,102,241,.1)}
    .tip::before{content:"?"}
    .tip-text{visibility:hidden;opacity:0;position:absolute;z-index:9999;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);width:max-content;max-width:280px;padding:8px 12px;font-size:12px;font-weight:400;line-height:1.5;color:#e2e8f0;background:#1e293b;border:1px solid #334155;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.3);pointer-events:none;transition:opacity .15s,visibility .15s;white-space:normal;text-align:left}
    .tip-text::after{content:"";position:absolute;top:100%;left:50%;transform:translateX(-50%);border:5px solid transparent;border-top-color:#334155}
    .tip:hover .tip-text{visibility:visible;opacity:1}

    /* Edit Panel */
    .atb-edit-panel .atb-btn { transition: all .2s; }
    .atb-edit-panel .atb-btn:hover { background: var(--ctp-surface1) !important; }
    .atb-edit-panel .atb-btn:disabled { opacity: .5; cursor: wait; }
    .atb-color-input { -webkit-appearance: none; border: none; padding: 2px; }
    .atb-color-input::-webkit-color-swatch-wrapper { padding: 0; }
    .atb-color-input::-webkit-color-swatch { border: none; border-radius: 6px; }
    .atb-edit-status { font-size: 11px; color: var(--ctp-green); margin-top: 8px; text-align: center; display: none; }
    .atb-edit-status.visible { display: block; }
    .atb-edit-status.error { color: var(--ctp-red); }

</style>
</head>
<body>

<div class="atb-topbar">
    <div class="atb-topbar-left">
        <a href="/admin"><i class="fas fa-arrow-left"></i> Admin</a>
        <div class="atb-topbar-title">
            <img src="/assets/images/jessie-logo.svg" alt="Jessie" width="28" height="28" style="vertical-align:middle;margin-right:8px"> AI Theme Builder <span class="badge">Beta</span>
        </div>
    </div>
    <div style="font-size:12px;color:var(--ctp-subtext0);"><?= esc($username) ?></div>
</div>

<div class="atb-main">

<!-- ═══ Left Panel ═══ -->
<div class="atb-panel">
<div class="atb-panel-content">

<?php if (!$aiAvailable): ?>
<div class="atb-no-ai">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>AI not configured.</strong>
    <a href="/admin/ai-settings">Add your API key</a> to use the AI Theme Builder.
</div>
<?php endif; ?>

<!-- Prompt -->
<div class="atb-field">
    <label class="atb-label">Describe your website</label>
    <textarea class="atb-textarea" id="atbPrompt" placeholder="e.g. A high-end Japanese restaurant in Brooklyn with minimalist zen aesthetics, dark tones, and focus on omakase dining experience..."><?= esc($_GET['prompt'] ?? '') ?></textarea>
</div>

<!-- Industry -->
<div class="atb-section">
    <div class="atb-section-head">
        <span class="atb-section-title">Industry <span class="tip"><span class="tip-text">Choose the business type — AI tailors layout, imagery and tone accordingly.</span></span></span>
        <span class="atb-section-hint">pick one</span>
    </div>
    <div class="atb-chips" id="atbIndustry" data-name="industry">
        <div class="atb-chip-group-label">Food & Hospitality</div>
        <div class="atb-chip" data-value="restaurant"><span class="chip-icon">🍽️</span> Restaurant</div>
        <div class="atb-chip" data-value="cafe"><span class="chip-icon">☕</span> Café / Bakery</div>
        <div class="atb-chip" data-value="bar"><span class="chip-icon">🍸</span> Bar / Nightclub</div>
        <div class="atb-chip" data-value="hotel"><span class="chip-icon">🏨</span> Hotel / B&B</div>
        <div class="atb-chip" data-value="catering"><span class="chip-icon">🍱</span> Catering</div>

        <div class="atb-chip-group-label">Tech & Digital</div>
        <div class="atb-chip" data-value="saas"><span class="chip-icon">💻</span> SaaS</div>
        <div class="atb-chip" data-value="startup"><span class="chip-icon">🚀</span> Tech Startup</div>
        <div class="atb-chip" data-value="ai"><span class="chip-icon">🤖</span> AI / ML</div>
        <div class="atb-chip" data-value="app"><span class="chip-icon">📱</span> Mobile App</div>
        <div class="atb-chip" data-value="crypto"><span class="chip-icon">🔗</span> Crypto / Web3</div>

        <div class="atb-chip-group-label">Creative</div>
        <div class="atb-chip selected" data-value="portfolio"><span class="chip-icon">🎨</span> Portfolio</div>
        <div class="atb-chip" data-value="photography"><span class="chip-icon">📸</span> Photography</div>
        <div class="atb-chip" data-value="agency"><span class="chip-icon">🏢</span> Creative Agency</div>
        <div class="atb-chip" data-value="music"><span class="chip-icon">🎵</span> Music / Band</div>
        <div class="atb-chip" data-value="film"><span class="chip-icon">🎬</span> Film / Video</div>

        <div class="atb-chip-group-label">Content</div>
        <div class="atb-chip" data-value="blog"><span class="chip-icon">📝</span> Blog</div>
        <div class="atb-chip" data-value="magazine"><span class="chip-icon">📰</span> Magazine</div>
        <div class="atb-chip" data-value="podcast"><span class="chip-icon">🎙️</span> Podcast</div>
        <div class="atb-chip" data-value="news"><span class="chip-icon">📡</span> News Portal</div>

        <div class="atb-chip-group-label">Commerce</div>
        <div class="atb-chip" data-value="ecommerce"><span class="chip-icon">🛒</span> E-commerce</div>
        <div class="atb-chip" data-value="fashion"><span class="chip-icon">👗</span> Fashion</div>
        <div class="atb-chip" data-value="jewelry"><span class="chip-icon">💎</span> Jewelry / Luxury</div>
        <div class="atb-chip" data-value="realestate"><span class="chip-icon">🏠</span> Real Estate</div>

        <div class="atb-chip-group-label">Professional Services</div>
        <div class="atb-chip" data-value="law"><span class="chip-icon">⚖️</span> Law Firm</div>
        <div class="atb-chip" data-value="finance"><span class="chip-icon">💰</span> Finance</div>
        <div class="atb-chip" data-value="consulting"><span class="chip-icon">📊</span> Consulting</div>
        <div class="atb-chip" data-value="accounting"><span class="chip-icon">🧮</span> Accounting</div>
        <div class="atb-chip" data-value="insurance"><span class="chip-icon">🛡️</span> Insurance</div>

        <div class="atb-chip-group-label">Health & Wellness</div>
        <div class="atb-chip" data-value="medical"><span class="chip-icon">🏥</span> Medical / Clinic</div>
        <div class="atb-chip" data-value="dental"><span class="chip-icon">🦷</span> Dental</div>
        <div class="atb-chip" data-value="fitness"><span class="chip-icon">💪</span> Fitness / Gym</div>
        <div class="atb-chip" data-value="yoga"><span class="chip-icon">🧘</span> Yoga / Wellness</div>
        <div class="atb-chip" data-value="spa"><span class="chip-icon">💆</span> Spa / Beauty</div>
        <div class="atb-chip" data-value="veterinary"><span class="chip-icon">🐾</span> Veterinary</div>

        <div class="atb-chip-group-label">Education</div>
        <div class="atb-chip" data-value="education"><span class="chip-icon">🎓</span> School</div>
        <div class="atb-chip" data-value="course"><span class="chip-icon">📚</span> Online Course</div>
        <div class="atb-chip" data-value="coaching"><span class="chip-icon">🎯</span> Coaching</div>

        <div class="atb-chip-group-label">Other</div>
        <div class="atb-chip" data-value="nonprofit"><span class="chip-icon">💚</span> Non-Profit</div>
        <div class="atb-chip" data-value="church"><span class="chip-icon">⛪</span> Church</div>
        <div class="atb-chip" data-value="events"><span class="chip-icon">🎉</span> Events</div>
        <div class="atb-chip" data-value="travel"><span class="chip-icon">✈️</span> Travel / Tourism</div>
        <div class="atb-chip" data-value="architecture"><span class="chip-icon">🏛️</span> Architecture</div>
        <div class="atb-chip" data-value="construction"><span class="chip-icon">🔨</span> Construction</div>
        <div class="atb-chip" data-value="automotive"><span class="chip-icon">🚗</span> Automotive</div>
        <div class="atb-chip" data-value="gaming"><span class="chip-icon">🎮</span> Gaming</div>
        <div class="atb-chip" data-value="sports"><span class="chip-icon">⚽</span> Sports</div>
        <div class="atb-chip" data-value="wedding"><span class="chip-icon">💒</span> Wedding</div>
    </div>
</div>

<!-- Style -->
<div class="atb-section">
    <div class="atb-section-head">
        <span class="atb-section-title">Design Style <span class="tip"><span class="tip-text">The visual approach for the theme. Each style produces a distinctly different look.</span></span></span>
    </div>
    <div class="atb-chips" id="atbStyle" data-name="style">
        <div class="atb-chip selected" data-value="minimalist"><span class="style-bar" style="background:linear-gradient(90deg,#f8f8f8,#e0e0e0)"></span> Minimalist</div>
        <div class="atb-chip" data-value="bold"><span class="style-bar" style="background:linear-gradient(90deg,#ff3366,#ff6b35)"></span> Bold</div>
        <div class="atb-chip" data-value="elegant"><span class="style-bar" style="background:linear-gradient(90deg,#d4a574,#8b6914)"></span> Elegant</div>
        <div class="atb-chip" data-value="playful"><span class="style-bar" style="background:linear-gradient(90deg,#ff6ec7,#7c4dff)"></span> Playful</div>
        <div class="atb-chip" data-value="corporate"><span class="style-bar" style="background:linear-gradient(90deg,#1a365d,#2563eb)"></span> Corporate</div>
        <div class="atb-chip" data-value="brutalist"><span class="style-bar" style="background:linear-gradient(90deg,#000,#333)"></span> Brutalist</div>
        <div class="atb-chip" data-value="retro"><span class="style-bar" style="background:linear-gradient(90deg,#f4a261,#e76f51)"></span> Retro / Vintage</div>
        <div class="atb-chip" data-value="futuristic"><span class="style-bar" style="background:linear-gradient(90deg,#00f0ff,#8b5cf6)"></span> Futuristic</div>
        <div class="atb-chip" data-value="organic"><span class="style-bar" style="background:linear-gradient(90deg,#6b8e23,#a0c45a)"></span> Organic / Natural</div>
        <div class="atb-chip" data-value="artdeco"><span class="style-bar" style="background:linear-gradient(90deg,#c9a227,#1a1a2e)"></span> Art Deco</div>
        <div class="atb-chip" data-value="glassmorphism"><span class="style-bar" style="background:linear-gradient(90deg,rgba(255,255,255,.3),rgba(255,255,255,.1))"></span> Glassmorphism</div>
        <div class="atb-chip" data-value="neubrutalism"><span class="style-bar" style="background:linear-gradient(90deg,#f9e2af,#fab387);border:2px solid #000;border-radius:0"></span> Neubrutalism</div>
        <div class="atb-chip" data-value="editorial"><span class="style-bar" style="background:linear-gradient(90deg,#faf5ef,#2d2d2d)"></span> Editorial</div>
        <div class="atb-chip" data-value="geometric"><span class="style-bar" style="background:linear-gradient(135deg,#6366f1 25%,#ec4899 50%,#f59e0b 75%)"></span> Geometric</div>
    </div>
</div>

<!-- Mood -->
<div class="atb-section">
    <div class="atb-section-head">
        <span class="atb-section-title">Color Mood <span class="tip"><span class="tip-text">Sets the overall color temperature and emotional feel of the design.</span></span></span>
    </div>
    <div class="atb-chips" id="atbMood" data-name="mood">
        <div class="atb-chip selected" data-value="light"><span class="mood-dot" style="background:#f8f9fa"></span> Light</div>
        <div class="atb-chip" data-value="dark"><span class="mood-dot" style="background:#1a1a2e"></span> Dark</div>
        <div class="atb-chip" data-value="colorful"><span class="mood-dot" style="background:linear-gradient(135deg,#ff6ec7,#7c4dff,#00b4d8)"></span> Colorful</div>
        <div class="atb-chip" data-value="monochrome"><span class="mood-dot" style="background:linear-gradient(135deg,#333,#999)"></span> Monochrome</div>
        <div class="atb-chip" data-value="warm"><span class="mood-dot" style="background:linear-gradient(135deg,#ff6b35,#ffa62b)"></span> Warm</div>
        <div class="atb-chip" data-value="cool"><span class="mood-dot" style="background:linear-gradient(135deg,#0077b6,#90e0ef)"></span> Cool</div>
        <div class="atb-chip" data-value="pastel"><span class="mood-dot" style="background:linear-gradient(135deg,#ffd6e0,#c1e3ff)"></span> Pastel</div>
        <div class="atb-chip" data-value="neon"><span class="mood-dot" style="background:linear-gradient(135deg,#39ff14,#ff073a);box-shadow:0 0 4px #39ff14"></span> Neon</div>
        <div class="atb-chip" data-value="earth"><span class="mood-dot" style="background:linear-gradient(135deg,#8b6914,#6b8e23)"></span> Earth Tones</div>
        <div class="atb-chip" data-value="luxury"><span class="mood-dot" style="background:linear-gradient(135deg,#1a1a2e,#c9a227)"></span> Luxury</div>
    </div>
</div>

<div class="atb-divider"></div>

<!-- Model + Language -->
<div class="atb-row-2">
    <?php if (!empty($aiModels)): ?>
    <div class="atb-field">
        <label class="atb-label">AI Model <span class="tip"><span class="tip-text">Better models produce higher quality themes but cost more. Premium recommended.</span></span></label>
        <select class="atb-select" id="atbModel">
            <?php foreach ($aiModels as $m): ?>
            <option value="<?= esc($m['provider']) ?>:<?= esc($m['model']) ?>"<?= $m['isDefault'] ? ' selected' : '' ?> data-tier="<?= esc($m['tier']) ?>">
                <?= esc($m['name']) ?><?= $m['tierLabel'] ? ' — ' . $m['tierLabel'] : '' ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="atb-field">
        <label class="atb-label">Content Language <span class="tip"><span class="tip-text">Language for all generated text: headings, paragraphs, buttons, menus.</span></span></label>
        <select class="atb-select" id="atbLanguage">
            <option value="English" selected>🇬🇧 English</option>
            <option value="Polish">🇵🇱 Polski</option>
            <option value="German">🇩🇪 Deutsch</option>
            <option value="French">🇫🇷 Français</option>
            <option value="Spanish">🇪🇸 Español</option>
            <option value="Italian">🇮🇹 Italiano</option>
            <option value="Portuguese">🇵🇹 Português</option>
            <option value="Dutch">🇳🇱 Nederlands</option>
            <option value="Swedish">🇸🇪 Svenska</option>
            <option value="Norwegian">🇳🇴 Norsk</option>
            <option value="Danish">🇩🇰 Dansk</option>
            <option value="Finnish">🇫🇮 Suomi</option>
            <option value="Czech">🇨🇿 Čeština</option>
            <option value="Romanian">🇷🇴 Română</option>
            <option value="Hungarian">🇭🇺 Magyar</option>
            <option value="Turkish">🇹🇷 Türkçe</option>
            <option value="Arabic">🇸🇦 العربية</option>
            <option value="Japanese">🇯🇵 日本語</option>
            <option value="Chinese">🇨🇳 中文</option>
            <option value="Korean">🇰🇷 한국어</option>
            <option value="Hindi">🇮🇳 हिन्दी</option>
            <option value="Ukrainian">🇺🇦 Українська</option>
            <option value="Russian">🇷🇺 Русский</option>
        </select>
    </div>
</div>

<button class="atb-btn-generate" id="atbGenerate" <?= !$aiAvailable ? 'disabled' : '' ?>>
    <span class="btn-label"><i class="fas fa-magic"></i> Generate Theme</span>
    <span class="spinner"></span>
</button>

<!-- Progress -->
<div class="atb-progress" id="atbProgress">
    <div class="atb-step" id="step1"><div class="atb-step-icon"><span class="atb-step-num">1</span><span class="atb-step-spinner"></span></div><span>Design Brief — colors, fonts, config</span></div>
    <div class="atb-step" id="step2"><div class="atb-step-icon"><span class="atb-step-num">2</span><span class="atb-step-spinner"></span></div><span>HTML Structure — header, footer, sections</span></div>
    <div class="atb-step" id="step3"><div class="atb-step-icon"><span class="atb-step-num">3</span><span class="atb-step-spinner"></span></div><span>CSS Styling — complete stylesheet</span></div>
    <div class="atb-step" id="step4"><div class="atb-step-icon"><span class="atb-step-num">4</span><span class="atb-step-spinner"></span></div><span>Assembly — write theme files</span></div>
</div>
<div class="atb-timings" id="atbTimings"></div>
<div class="atb-error" id="atbError"></div>

<!-- Actions -->
<div class="atb-actions" id="atbActions">
    <button class="atb-btn atb-btn-apply" id="atbApply"><i class="fas fa-check"></i> Apply Theme</button>
    <a class="atb-btn atb-btn-studio" id="atbStudio" href="#"><i class="fas fa-paint-brush"></i> Theme Studio</a>
    <button class="atb-btn atb-btn-delete" id="atbDelete" title="Delete"><i class="fas fa-trash"></i></button>
</div>

<!-- Post-Generation Edit Panel -->
<div class="atb-edit-panel" id="atbEditPanel" style="display:none;">
    <div class="atb-divider"></div>
    <div class="atb-section">
        <div class="atb-section-head">
            <span class="atb-section-title">✏️ Refine Theme</span>
        </div>

        <!-- Quick color edits -->
        <div class="atb-row-2" style="margin-bottom:12px">
            <div class="atb-field">
                <label class="atb-label">Primary Color</label>
                <input type="color" class="atb-color-input" id="atbEditPrimary" value="#6366f1" style="width:100%;height:34px;border:1px solid var(--ctp-surface1);border-radius:8px;background:var(--ctp-surface0);cursor:pointer;">
            </div>
            <div class="atb-field">
                <label class="atb-label">Secondary Color</label>
                <input type="color" class="atb-color-input" id="atbEditSecondary" value="#ec4899" style="width:100%;height:34px;border:1px solid var(--ctp-surface1);border-radius:8px;background:var(--ctp-surface0);cursor:pointer;">
            </div>
        </div>
        <div class="atb-row-2" style="margin-bottom:12px">
            <div class="atb-field">
                <label class="atb-label">Background</label>
                <input type="color" class="atb-color-input" id="atbEditBg" value="#ffffff" style="width:100%;height:34px;border:1px solid var(--ctp-surface1);border-radius:8px;background:var(--ctp-surface0);cursor:pointer;">
            </div>
            <div class="atb-field">
                <label class="atb-label">Text Color</label>
                <input type="color" class="atb-color-input" id="atbEditText" value="#1a1a2e" style="width:100%;height:34px;border:1px solid var(--ctp-surface1);border-radius:8px;background:var(--ctp-surface0);cursor:pointer;">
            </div>
        </div>

        <button class="atb-btn" id="atbApplyColors" style="width:100%;background:var(--ctp-surface0);color:var(--ctp-text);border:1px solid var(--ctp-surface1);margin-bottom:10px;">
            <i class="fas fa-palette"></i> Apply Color Changes
        </button>

        <!-- CSS regeneration -->
        <div class="atb-field">
            <label class="atb-label">CSS Instructions (optional)</label>
            <textarea class="atb-textarea" id="atbCssInstructions" rows="2" style="min-height:50px" placeholder="e.g. Make the hero section taller, add more animations, use glassmorphism cards..."></textarea>
        </div>
        <button class="atb-btn" id="atbRegenCss" style="width:100%;background:var(--ctp-surface0);color:var(--ctp-text);border:1px solid var(--ctp-surface1);margin-bottom:10px;">
            <i class="fas fa-sync-alt"></i> Regenerate CSS
        </button>

        <div class="atb-divider"></div>

        <!-- AI Chat Refinement -->
        <label class="atb-label" style="margin-bottom:6px">🤖 AI Refinement</label>
        <div style="display:flex;gap:6px;margin-bottom:10px;">
            <input type="text" id="atbChatInput" class="atb-textarea" style="min-height:36px;height:36px;padding:8px 12px;resize:none;flex:1" placeholder="e.g. Make hero taller, add gradient to cards...">
            <button class="atb-btn" id="atbChatSend" style="background:var(--ctp-blue);color:var(--ctp-crust);border:none;flex-shrink:0;padding:8px 14px;">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        <div id="atbChatLog" style="max-height:120px;overflow-y:auto;font-size:11px;color:var(--ctp-subtext0);margin-bottom:10px;"></div>

        <div class="atb-divider"></div>
        <label class="atb-label" style="margin-bottom:8px">⚡ Quick Actions</label>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <button class="atb-btn" id="atbVariantDark" style="flex:1;background:var(--ctp-surface0);color:var(--ctp-text);border:1px solid var(--ctp-surface1);font-size:11px;padding:8px;" title="Generate dark mode variant">
                🌙 Dark Variant
            </button>
            <button class="atb-btn" id="atbVariantLight" style="flex:1;background:var(--ctp-surface0);color:var(--ctp-text);border:1px solid var(--ctp-surface1);font-size:11px;padding:8px;" title="Generate light mode variant">
                ☀️ Light Variant
            </button>
            <button class="atb-btn" id="atbExportZip" style="flex:1;background:var(--ctp-surface0);color:var(--ctp-text);border:1px solid var(--ctp-surface1);font-size:11px;padding:8px;" title="Download theme as .zip">
                📦 Export .zip
            </button>
        </div>
    </div>
</div>

<!-- Previously Generated -->
<?php if (!empty($generatedThemes)): ?>
<div class="atb-previous">
    <h3>Previously Generated</h3>
    <?php foreach ($generatedThemes as $gt): ?>
    <div class="atb-theme-item" id="theme-<?= esc($gt['slug']) ?>">
        <?php if (file_exists(CMS_ROOT . '/themes/' . $gt['slug'] . '/thumbnail.svg')): ?>
        <img src="/themes/<?= esc($gt['slug']) ?>/thumbnail.svg" alt="<?= esc($gt['name']) ?> preview" style="width:48px;height:36px;border-radius:4px;object-fit:cover;flex-shrink:0;border:1px solid var(--ctp-surface1)">
        <?php endif; ?>
        <span class="atb-theme-item-name"><?= esc($gt['name']) ?></span>
        <div class="atb-theme-item-actions">
            <button onclick="previewTheme('<?= esc($gt['slug']) ?>')" title="Preview"><i class="fas fa-eye"></i></button>
            <button onclick="applyTheme('<?= esc($gt['slug']) ?>')" title="Apply"><i class="fas fa-check"></i></button>
            <button onclick="deleteTheme('<?= esc($gt['slug']) ?>')" title="Delete"><i class="fas fa-trash" style="color:var(--ctp-red)"></i></button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</div>
</div>

<!-- ═══ Right Panel: Preview ═══ -->
<div class="atb-preview">
    <div class="atb-preview-toolbar" id="atbPreviewToolbar" style="display:none;">
        <div class="atb-preview-device">
            <button class="active" onclick="setDevice('desktop',this)"><i class="fas fa-desktop"></i></button>
            <button onclick="setDevice('tablet',this)"><i class="fas fa-tablet-alt"></i></button>
            <button onclick="setDevice('mobile',this)"><i class="fas fa-mobile-alt"></i></button>
        </div>
        <div class="url-bar" id="atbUrlBar">No preview</div>
        <button onclick="refreshPreview()" style="background:none;border:1px solid var(--ctp-surface1);padding:4px 10px;border-radius:6px;color:var(--ctp-overlay0);cursor:pointer;font-size:12px;"><i class="fas fa-sync-alt"></i></button>
    </div>
    <div class="atb-preview-frame" id="atbPreviewFrame">
        <div class="atb-preview-empty" id="atbPreviewEmpty">
            <i class="fas fa-palette"></i>
            <p>Your theme preview will appear here</p>
            <p style="font-size:11px;opacity:.5;">Describe your website, pick options, and click Generate</p>
        </div>
    </div>
</div>

</div>

<script>
(function() {
    const CSRF = <?= json_encode($csrfToken) ?>;
    let currentSlug = null;

    const $ = id => document.getElementById(id);
    const btnGen = $('atbGenerate'), txtPrompt = $('atbPrompt');
    const selModel = $('atbModel'), selLang = $('atbLanguage');
    const divProg = $('atbProgress'), divErr = $('atbError');
    const divAct = $('atbActions'), divTim = $('atbTimings');
    const prevFrame = $('atbPreviewFrame'), prevEmpty = $('atbPreviewEmpty'), prevBar = $('atbPreviewToolbar');
    const urlBar = $('atbUrlBar');

    /* ── Chip selection ── */
    document.querySelectorAll('.atb-chips').forEach(group => {
        group.addEventListener('click', e => {
            const chip = e.target.closest('.atb-chip');
            if (!chip) return;
            group.querySelectorAll('.atb-chip').forEach(c => c.classList.remove('selected'));
            chip.classList.add('selected');
        });
    });

    function getChipValue(containerId) {
        const sel = document.getElementById(containerId)?.querySelector('.atb-chip.selected');
        return sel ? sel.dataset.value : '';
    }

    /* ── Generate ── */
    btnGen.addEventListener('click', async () => {
        const prompt = txtPrompt.value.trim();
        if (!prompt) { txtPrompt.focus(); return; }

        btnGen.classList.add('loading'); btnGen.disabled = true;
        divProg.classList.add('visible');
        divErr.classList.remove('visible');
        divAct.classList.remove('visible');
        divTim.classList.remove('visible');
        $('atbEditPanel') && ($('atbEditPanel').style.display = 'none');
        currentSlug = null;

        for (let i = 1; i <= 4; i++) $('step'+i).className = 'atb-step';

        const modelVal = selModel ? selModel.value : '';
        const payload = {
            prompt,
            industry: getChipValue('atbIndustry'),
            style: getChipValue('atbStyle'),
            mood: getChipValue('atbMood'),
            provider: modelVal.split(':')[0] || '',
            model: modelVal.split(':')[1] || '',
            language: selLang ? selLang.value : 'English',
        };

        // Use SSE streaming for real-time progress
        try {
            const res = await fetch('/api/ai-theme-builder/generate-stream', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify(payload),
            });

            const reader = res.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';
            let finalData = null;

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                buffer += decoder.decode(value, { stream: true });

                // Parse SSE events from buffer
                const lines = buffer.split('\n');
                buffer = lines.pop() || ''; // Keep incomplete line

                let eventType = '';
                for (const line of lines) {
                    if (line.startsWith('event: ')) {
                        eventType = line.slice(7).trim();
                    } else if (line.startsWith('data: ')) {
                        try {
                            const data = JSON.parse(line.slice(6));
                            if (eventType === 'step') {
                                const s = data.step;
                                if (data.status === 'running') {
                                    $('step'+s).className = 'atb-step active';
                                    // Mark previous as done
                                    for (let i = 1; i < s; i++) $('step'+i).className = 'atb-step done';
                                } else if (data.status === 'done') {
                                    $('step'+s).className = 'atb-step done';
                                }
                            } else if (eventType === 'complete') {
                                finalData = data;
                            } else if (eventType === 'error') {
                                divErr.textContent = data.error || 'Generation failed';
                                divErr.classList.add('visible');
                            }
                        } catch (e) { /* skip malformed */ }
                        eventType = '';
                    }
                }
            }

            if (finalData && finalData.ok) {
                for (let i = 1; i <= 4; i++) $('step'+i).className = 'atb-step done';
                currentSlug = finalData.slug;
                divAct.classList.add('visible');
                $('atbStudio').href = '/admin/theme-studio?theme=' + encodeURIComponent(finalData.slug);

                if (finalData.timings) {
                    let h = '';
                    if (finalData.timings.step1) h += '<span>Brief: '+(finalData.timings.step1/1000).toFixed(1)+'s</span>';
                    if (finalData.timings.step2) h += '<span>HTML: '+(finalData.timings.step2/1000).toFixed(1)+'s</span>';
                    if (finalData.timings.step3) h += '<span>CSS: '+(finalData.timings.step3/1000).toFixed(1)+'s</span>';
                    if (finalData.timings.step4) h += '<span>Assembly: '+(finalData.timings.step4/1000).toFixed(1)+'s</span>';
                    if (finalData.model_used) h += '<span>Model: '+finalData.model_used+'</span>';
                    const cov = finalData.steps?.css?.selector_coverage;
                    if (cov !== undefined) {
                        const covIcon = cov >= 90 ? '🟢' : cov >= 70 ? '🟡' : '🔴';
                        h += '<span>' + covIcon + ' CSS: ' + cov + '%</span>';
                    }
                    if (finalData.summary) {
                        h += '<span>Sections: ' + (finalData.summary.sections||'?') + '</span>';
                    }
                    divTim.innerHTML = h; divTim.classList.add('visible');
                }

                showPreview(finalData.slug);
                showEditPanel(finalData.steps?.brief?.data || null);
            } else if (finalData && !finalData.ok) {
                const f = finalData.step || 1;
                for (let i = 1; i <= 4; i++) {
                    $('step'+i).className = i < f ? 'atb-step done' : i === f ? 'atb-step error' : 'atb-step';
                }
                divErr.textContent = finalData.error || 'Generation failed';
                divErr.classList.add('visible');
            }
        } catch (err) {
            divErr.textContent = 'Request failed: ' + err.message;
            divErr.classList.add('visible');
        }

        btnGen.classList.remove('loading'); btnGen.disabled = false;
    });

    function showPreview(slug) {
        prevEmpty.style.display = 'none';
        prevBar.style.display = 'flex';
        const old = prevFrame.querySelector('iframe'); if (old) old.remove();
        const f = document.createElement('iframe');
        f.src = '/admin/ai-theme-builder/preview?theme=' + encodeURIComponent(slug);
        prevFrame.appendChild(f);
        urlBar.textContent = slug;
    }

    /* ── Apply ── */
    $('atbApply').addEventListener('click', () => applyTheme(currentSlug));

    /* ── Delete current ── */
    $('atbDelete').addEventListener('click', () => { if (currentSlug) deleteTheme(currentSlug); });

    window.applyTheme = async function(slug) {
        if (!slug || !confirm('Activate "' + slug + '"? This will change your live site.')) return;
        try {
            const r = await fetch('/api/ai-theme-builder/apply', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify({ slug }),
            });
            const d = await r.json();
            if (d.ok) alert('Theme "' + slug + '" activated!');
            else alert('Failed: ' + (d.error || 'Unknown'));
        } catch(e) { alert('Error: ' + e.message); }
    };

    window.deleteTheme = async function(slug) {
        if (!slug || !confirm('Delete "' + slug + '"? This cannot be undone.')) return;
        try {
            const r = await fetch('/api/ai-theme-builder/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify({ slug }),
            });
            const d = await r.json();
            if (d.ok) {
                const el = document.getElementById('theme-' + slug);
                if (el) el.remove();
                if (currentSlug === slug) {
                    currentSlug = null;
                    divAct.classList.remove('visible');
                    prevEmpty.style.display = ''; prevBar.style.display = 'none';
                    const iframe = prevFrame.querySelector('iframe'); if (iframe) iframe.remove();
                }
            } else { alert('Failed: ' + (d.error || 'Unknown')); }
        } catch(e) { alert('Error: ' + e.message); }
    };

    window.previewTheme = function(slug) {
        currentSlug = slug; showPreview(slug);
        divAct.classList.add('visible');
        $('atbStudio').href = '/admin/theme-studio?theme=' + encodeURIComponent(slug);
        showEditPanel(null);
    };

    window.setDevice = function(dev, btn) {
        prevFrame.className = 'atb-preview-frame';
        if (dev !== 'desktop') prevFrame.classList.add('device-' + dev);
        document.querySelectorAll('.atb-preview-device button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    };

    window.refreshPreview = function() {
        const f = prevFrame.querySelector('iframe'); if (f) f.src = f.src;
    };

    /* ── Post-Generation Edit Panel ── */
    function showEditPanel(themeData) {
        const panel = $('atbEditPanel');
        if (!panel) return;
        panel.style.display = 'block';
        // If we have theme.json colors, populate inputs
        if (themeData && themeData.colors) {
            const c = themeData.colors;
            if (c.primary) $('atbEditPrimary').value = c.primary;
            if (c.secondary) $('atbEditSecondary').value = c.secondary;
            if (c.background) $('atbEditBg').value = c.background;
            if (c.text) $('atbEditText').value = c.text;
        }
    }

    function editStatus(msg, isError) {
        let el = document.querySelector('.atb-edit-status');
        if (!el) {
            el = document.createElement('div');
            el.className = 'atb-edit-status';
            $('atbEditPanel').appendChild(el);
        }
        el.textContent = msg;
        el.className = 'atb-edit-status visible' + (isError ? ' error' : '');
        if (!isError) setTimeout(() => el.classList.remove('visible'), 3000);
    }

    // Apply color changes
    $('atbApplyColors')?.addEventListener('click', async () => {
        if (!currentSlug) return;
        const btn = $('atbApplyColors');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        try {
            const modelVal = selModel ? selModel.value : '';
            const res = await fetch('/api/ai-theme-builder/update-brief', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify({
                    slug: currentSlug,
                    changes: {
                        colors: {
                            primary: $('atbEditPrimary').value,
                            secondary: $('atbEditSecondary').value,
                            background: $('atbEditBg').value,
                            text: $('atbEditText').value,
                        }
                    },
                    provider: modelVal.split(':')[0] || '',
                    model: modelVal.split(':')[1] || '',
                }),
            });
            const data = await res.json();
            if (data.ok) {
                editStatus('✅ Colors updated & CSS regenerated (' + (data.css_lines||'?') + ' lines)');
                refreshPreview();
            } else {
                editStatus('❌ ' + (data.error || 'Failed'), true);
            }
        } catch (e) {
            editStatus('❌ ' + e.message, true);
        }
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-palette"></i> Apply Color Changes';
    });

    // AI Chat Refinement
    async function sendRefinement() {
        const input = $('atbChatInput');
        const msg = input?.value.trim();
        if (!msg || !currentSlug) return;

        const log = $('atbChatLog');
        log.innerHTML += '<div style="margin-bottom:4px;color:var(--ctp-text)">💬 ' + msg.replace(/</g,'&lt;') + '</div>';
        input.value = '';
        log.innerHTML += '<div style="margin-bottom:4px;color:var(--ctp-overlay0)"><i class="fas fa-spinner fa-spin"></i> Thinking...</div>';
        log.scrollTop = log.scrollHeight;

        try {
            const modelVal = selModel ? selModel.value : '';
            const res = await fetch('/api/ai-theme-builder/refine', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify({
                    slug: currentSlug, instruction: msg,
                    provider: modelVal.split(':')[0] || '', model: modelVal.split(':')[1] || '',
                }),
            });
            const data = await res.json();
            // Remove spinner
            log.lastChild?.remove();
            if (data.ok) {
                log.innerHTML += '<div style="margin-bottom:4px;color:var(--ctp-green)">✅ ' + (data.summary || 'Done') + '</div>';
                refreshPreview();
            } else {
                log.innerHTML += '<div style="margin-bottom:4px;color:var(--ctp-red)">❌ ' + (data.error || 'Failed') + '</div>';
            }
        } catch (e) {
            log.lastChild?.remove();
            log.innerHTML += '<div style="margin-bottom:4px;color:var(--ctp-red)">❌ ' + e.message + '</div>';
        }
        log.scrollTop = log.scrollHeight;
    }
    $('atbChatSend')?.addEventListener('click', sendRefinement);
    $('atbChatInput')?.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendRefinement(); } });

    // Dark/Light variant
    async function createVariant(mood) {
        if (!currentSlug) return;
        const btn = mood === 'dark' ? $('atbVariantDark') : $('atbVariantLight');
        btn.disabled = true;
        const darkColors = { background: '#0f1115', surface: '#1a1d25', text: '#e2e8f0', text_muted: '#94a3b8', border: '#2d3748' };
        const lightColors = { background: '#fafbfc', surface: '#ffffff', text: '#1a202c', text_muted: '#64748b', border: '#e2e8f0' };
        try {
            const modelVal = selModel ? selModel.value : '';
            const res = await fetch('/api/ai-theme-builder/update-brief', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify({
                    slug: currentSlug,
                    changes: { colors: mood === 'dark' ? darkColors : lightColors },
                    provider: modelVal.split(':')[0] || '', model: modelVal.split(':')[1] || '',
                }),
            });
            const data = await res.json();
            if (data.ok) { editStatus('✅ ' + mood + ' variant applied'); refreshPreview(); }
            else editStatus('❌ ' + (data.error || 'Failed'), true);
        } catch (e) { editStatus('❌ ' + e.message, true); }
        btn.disabled = false;
    }
    $('atbVariantDark')?.addEventListener('click', () => createVariant('dark'));
    $('atbVariantLight')?.addEventListener('click', () => createVariant('light'));

    // Export .zip
    $('atbExportZip')?.addEventListener('click', () => {
        if (!currentSlug) return;
        window.open('/api/ai-theme-builder/export?theme=' + encodeURIComponent(currentSlug), '_blank');
    });

    // Regenerate CSS
    $('atbRegenCss')?.addEventListener('click', async () => {
        if (!currentSlug) return;
        const btn = $('atbRegenCss');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerating...';
        try {
            const modelVal = selModel ? selModel.value : '';
            const res = await fetch('/api/ai-theme-builder/regenerate-css', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify({
                    slug: currentSlug,
                    instructions: $('atbCssInstructions')?.value || '',
                    provider: modelVal.split(':')[0] || '',
                    model: modelVal.split(':')[1] || '',
                }),
            });
            const data = await res.json();
            if (data.ok) {
                editStatus('✅ CSS regenerated — ' + (data.css_lines||'?') + ' lines, ' + (data.coverage||'?') + '% coverage');
                refreshPreview();
            } else {
                editStatus('❌ ' + (data.error || 'Failed'), true);
            }
        } catch (e) {
            editStatus('❌ ' + e.message, true);
        }
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-sync-alt"></i> Regenerate CSS';
    });
})();
</script>
</body>
</html>
