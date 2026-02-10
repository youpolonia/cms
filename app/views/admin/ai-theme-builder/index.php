<?php
/**
 * AI Theme Builder — Full-Screen UI
 * Catppuccin Mocha Dark Theme
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
    /* ═══════════════════════════════════════
       Catppuccin Mocha — AI Theme Builder
       ═══════════════════════════════════════ */
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
        height: 56px;
        background: var(--ctp-mantle);
        border-bottom: 1px solid var(--ctp-surface0);
        flex-shrink: 0;
    }
    .atb-topbar-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .atb-topbar-left a {
        color: var(--ctp-subtext0);
        text-decoration: none;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s;
    }
    .atb-topbar-left a:hover { color: var(--ctp-text); }
    .atb-topbar-title {
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .atb-topbar-title .badge {
        background: linear-gradient(135deg, var(--ctp-mauve), var(--ctp-blue));
        color: var(--ctp-crust);
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ── Main layout ── */
    .atb-main {
        display: flex;
        flex: 1;
        min-height: 0;
    }

    /* ── Left Panel (controls) ── */
    .atb-panel {
        width: 420px;
        min-width: 420px;
        background: var(--ctp-mantle);
        border-right: 1px solid var(--ctp-surface0);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }
    .atb-panel-content {
        padding: 24px;
        flex: 1;
    }

    /* ── Form elements ── */
    .atb-field {
        margin-bottom: 20px;
    }
    .atb-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--ctp-subtext1);
        margin-bottom: 6px;
    }
    .atb-textarea {
        width: 100%;
        min-height: 120px;
        padding: 12px 14px;
        background: var(--ctp-surface0);
        border: 1px solid var(--ctp-surface1);
        border-radius: 10px;
        color: var(--ctp-text);
        font-family: inherit;
        font-size: 14px;
        line-height: 1.5;
        resize: vertical;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .atb-textarea:focus {
        outline: none;
        border-color: var(--ctp-blue);
        box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.15);
    }
    .atb-textarea::placeholder { color: var(--ctp-overlay0); }

    .atb-select {
        width: 100%;
        padding: 10px 14px;
        background: var(--ctp-surface0);
        border: 1px solid var(--ctp-surface1);
        border-radius: 10px;
        color: var(--ctp-text);
        font-family: inherit;
        font-size: 14px;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%237f849c' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        transition: border-color 0.2s;
    }
    .atb-select:focus {
        outline: none;
        border-color: var(--ctp-blue);
    }
    .atb-select option {
        background: var(--ctp-surface0);
        color: var(--ctp-text);
    }

    .atb-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
    }
    .atb-row .atb-field { margin-bottom: 0; }

    /* ── Generate button ── */
    .atb-btn-generate {
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, var(--ctp-mauve), var(--ctp-blue));
        color: var(--ctp-crust);
        font-family: inherit;
        font-size: 15px;
        font-weight: 700;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: opacity 0.2s, transform 0.1s;
        margin-top: 8px;
    }
    .atb-btn-generate:hover { opacity: 0.9; }
    .atb-btn-generate:active { transform: scale(0.98); }
    .atb-btn-generate:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    .atb-btn-generate .spinner {
        display: none;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(0,0,0,0.2);
        border-top-color: var(--ctp-crust);
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    .atb-btn-generate.loading .spinner { display: block; }
    .atb-btn-generate.loading .btn-label { display: none; }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Progress Steps ── */
    .atb-progress {
        margin-top: 24px;
        display: none;
    }
    .atb-progress.visible { display: block; }

    .atb-step {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 8px;
        margin-bottom: 6px;
        font-size: 13px;
        color: var(--ctp-overlay0);
        transition: all 0.3s;
    }
    .atb-step.active {
        background: var(--ctp-surface0);
        color: var(--ctp-text);
    }
    .atb-step.done {
        color: var(--ctp-green);
    }
    .atb-step.error {
        color: var(--ctp-red);
    }
    .atb-step-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: var(--ctp-surface0);
        font-size: 13px;
        flex-shrink: 0;
    }
    .atb-step.active .atb-step-icon {
        background: var(--ctp-blue);
        color: var(--ctp-crust);
    }
    .atb-step.done .atb-step-icon {
        background: var(--ctp-green);
        color: var(--ctp-crust);
    }
    .atb-step.error .atb-step-icon {
        background: var(--ctp-red);
        color: var(--ctp-crust);
    }
    .atb-step-spinner {
        width: 14px;
        height: 14px;
        border: 2px solid rgba(0,0,0,0.2);
        border-top-color: var(--ctp-crust);
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
        display: none;
    }
    .atb-step.active .atb-step-spinner { display: block; }
    .atb-step.active .atb-step-num { display: none; }

    /* ── Error message ── */
    .atb-error {
        background: rgba(243, 139, 168, 0.1);
        border: 1px solid rgba(243, 139, 168, 0.3);
        border-radius: 10px;
        padding: 14px 16px;
        margin-top: 16px;
        font-size: 13px;
        color: var(--ctp-red);
        display: none;
    }
    .atb-error.visible { display: block; }

    /* ── Action buttons (after generation) ── */
    .atb-actions {
        margin-top: 20px;
        display: none;
        gap: 10px;
    }
    .atb-actions.visible {
        display: flex;
    }
    .atb-btn {
        flex: 1;
        padding: 11px 16px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: opacity 0.2s;
        text-decoration: none;
    }
    .atb-btn:hover { opacity: 0.85; }
    .atb-btn-apply {
        background: var(--ctp-green);
        color: var(--ctp-crust);
        border: none;
    }
    .atb-btn-studio {
        background: var(--ctp-surface0);
        color: var(--ctp-text);
        border: 1px solid var(--ctp-surface1);
    }

    /* ── Previously Generated ── */
    .atb-previous {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid var(--ctp-surface0);
    }
    .atb-previous h3 {
        font-size: 13px;
        font-weight: 600;
        color: var(--ctp-subtext0);
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .atb-theme-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: var(--ctp-surface0);
        border-radius: 8px;
        margin-bottom: 6px;
        font-size: 13px;
    }
    .atb-theme-item-name {
        font-weight: 500;
    }
    .atb-theme-item-actions {
        display: flex;
        gap: 8px;
    }
    .atb-theme-item-actions button,
    .atb-theme-item-actions a {
        background: none;
        border: none;
        color: var(--ctp-blue);
        cursor: pointer;
        font-size: 12px;
        font-family: inherit;
        text-decoration: none;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background 0.2s;
    }
    .atb-theme-item-actions button:hover,
    .atb-theme-item-actions a:hover {
        background: rgba(137, 180, 250, 0.1);
    }

    /* ── Right Panel (preview) ── */
    .atb-preview {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--ctp-crust);
        position: relative;
    }
    .atb-preview-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        background: var(--ctp-mantle);
        border-bottom: 1px solid var(--ctp-surface0);
        font-size: 12px;
        color: var(--ctp-overlay0);
    }
    .atb-preview-toolbar .url-bar {
        background: var(--ctp-surface0);
        border: 1px solid var(--ctp-surface1);
        border-radius: 6px;
        padding: 5px 14px;
        font-size: 12px;
        color: var(--ctp-subtext0);
        min-width: 300px;
        text-align: center;
    }
    .atb-preview-device {
        display: flex;
        gap: 6px;
    }
    .atb-preview-device button {
        background: none;
        border: 1px solid var(--ctp-surface1);
        color: var(--ctp-overlay0);
        padding: 4px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s;
    }
    .atb-preview-device button:hover,
    .atb-preview-device button.active {
        background: var(--ctp-surface0);
        color: var(--ctp-text);
        border-color: var(--ctp-blue);
    }

    .atb-preview-frame {
        flex: 1;
        display: flex;
        justify-content: center;
        overflow: hidden;
        padding: 0;
    }
    .atb-preview-frame iframe {
        width: 100%;
        height: 100%;
        border: none;
        background: #fff;
        transition: width 0.3s ease;
    }
    .atb-preview-frame.device-tablet iframe { width: 768px; }
    .atb-preview-frame.device-mobile iframe { width: 375px; }

    /* ── Empty preview state ── */
    .atb-preview-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--ctp-overlay0);
        gap: 16px;
    }
    .atb-preview-empty i {
        font-size: 48px;
        opacity: 0.3;
    }
    .atb-preview-empty p {
        font-size: 14px;
    }

    /* ── No AI warning ── */
    .atb-no-ai {
        background: rgba(249, 226, 175, 0.1);
        border: 1px solid rgba(249, 226, 175, 0.3);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 20px;
        font-size: 13px;
        color: var(--ctp-yellow);
    }
    .atb-no-ai a {
        color: var(--ctp-blue);
        text-decoration: underline;
    }

    /* ── Timings ── */
    .atb-timings {
        margin-top: 12px;
        font-size: 11px;
        color: var(--ctp-overlay0);
        display: none;
    }
    .atb-timings.visible { display: block; }
    .atb-timings span {
        display: inline-block;
        margin-right: 12px;
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .atb-main { flex-direction: column; }
        .atb-panel { width: 100%; min-width: 0; border-right: none; border-bottom: 1px solid var(--ctp-surface0); max-height: 50vh; }
        .atb-preview { min-height: 50vh; }
    }
    </style>
</head>
<body>

<!-- Topbar -->
<div class="atb-topbar">
    <div class="atb-topbar-left">
        <a href="/admin"><i class="fas fa-arrow-left"></i> Admin</a>
        <div class="atb-topbar-title">
            🤖 AI Theme Builder
            <span class="badge">Beta</span>
        </div>
    </div>
    <div style="font-size:13px;color:var(--ctp-subtext0);">
        <span><?= esc($username) ?></span>
    </div>
</div>

<!-- Main Layout -->
<div class="atb-main">

    <!-- Left Panel: Controls -->
    <div class="atb-panel">
        <div class="atb-panel-content">

            <?php if (!$aiAvailable): ?>
            <div class="atb-no-ai">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>AI not configured.</strong> 
                <a href="/admin/ai-settings">Add your API key</a> to use the AI Theme Builder.
            </div>
            <?php endif; ?>

            <div class="atb-field">
                <label class="atb-label">Describe your website</label>
                <textarea class="atb-textarea" id="atbPrompt" placeholder="e.g. A modern law firm website with dark blue tones, professional look, featuring team members and practice areas..."><?= esc($_GET['prompt'] ?? '') ?></textarea>
            </div>

            <div class="atb-row">
                <div class="atb-field">
                    <label class="atb-label">Industry</label>
                    <select class="atb-select" id="atbIndustry">
                        <option value="restaurant">🍽️ Restaurant</option>
                        <option value="saas">💻 SaaS</option>
                        <option value="portfolio" selected>🎨 Portfolio</option>
                        <option value="blog">📝 Blog</option>
                        <option value="ecommerce">🛒 E-commerce</option>
                        <option value="agency">🏢 Agency</option>
                        <option value="law">⚖️ Law</option>
                        <option value="medical">🏥 Medical</option>
                        <option value="fitness">💪 Fitness</option>
                        <option value="education">🎓 Education</option>
                    </select>
                </div>
                <div class="atb-field">
                    <label class="atb-label">Style</label>
                    <select class="atb-select" id="atbStyle">
                        <option value="minimalist" selected>Minimalist</option>
                        <option value="bold">Bold</option>
                        <option value="elegant">Elegant</option>
                        <option value="playful">Playful</option>
                        <option value="corporate">Corporate</option>
                    </select>
                </div>
                <div class="atb-field">
                    <label class="atb-label">Mood</label>
                    <select class="atb-select" id="atbMood">
                        <option value="light" selected>☀️ Light</option>
                        <option value="dark">🌙 Dark</option>
                        <option value="colorful">🌈 Colorful</option>
                        <option value="monochrome">⚪ Mono</option>
                    </select>
                </div>
            </div>

            <button class="atb-btn-generate" id="atbGenerate" <?= !$aiAvailable ? 'disabled' : '' ?>>
                <span class="btn-label"><i class="fas fa-magic"></i> Generate Theme</span>
                <span class="spinner"></span>
            </button>

            <!-- Progress Steps -->
            <div class="atb-progress" id="atbProgress">
                <div class="atb-step" id="step1">
                    <div class="atb-step-icon">
                        <span class="atb-step-num">1</span>
                        <span class="atb-step-spinner"></span>
                    </div>
                    <span>Design Brief — colors, fonts, config</span>
                </div>
                <div class="atb-step" id="step2">
                    <div class="atb-step-icon">
                        <span class="atb-step-num">2</span>
                        <span class="atb-step-spinner"></span>
                    </div>
                    <span>HTML Structure — header, footer, sections</span>
                </div>
                <div class="atb-step" id="step3">
                    <div class="atb-step-icon">
                        <span class="atb-step-num">3</span>
                        <span class="atb-step-spinner"></span>
                    </div>
                    <span>CSS Styling — complete stylesheet</span>
                </div>
                <div class="atb-step" id="step4">
                    <div class="atb-step-icon">
                        <span class="atb-step-num">4</span>
                        <span class="atb-step-spinner"></span>
                    </div>
                    <span>Assembly — write theme files</span>
                </div>
            </div>

            <!-- Timings -->
            <div class="atb-timings" id="atbTimings"></div>

            <!-- Error -->
            <div class="atb-error" id="atbError"></div>

            <!-- Actions after generation -->
            <div class="atb-actions" id="atbActions">
                <button class="atb-btn atb-btn-apply" id="atbApply">
                    <i class="fas fa-check"></i> Apply Theme
                </button>
                <a class="atb-btn atb-btn-studio" id="atbStudio" href="#">
                    <i class="fas fa-paint-brush"></i> Open in Theme Studio
                </a>
            </div>

            <!-- Previously Generated Themes -->
            <?php if (!empty($generatedThemes)): ?>
            <div class="atb-previous">
                <h3>Previously Generated</h3>
                <?php foreach ($generatedThemes as $gt): ?>
                <div class="atb-theme-item">
                    <span class="atb-theme-item-name"><?= esc($gt['name']) ?></span>
                    <div class="atb-theme-item-actions">
                        <button onclick="previewTheme('<?= esc($gt['slug']) ?>')" title="Preview">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="applyTheme('<?= esc($gt['slug']) ?>')" title="Apply">
                            <i class="fas fa-check"></i>
                        </button>
                        <a href="/admin/theme-studio" title="Customize">
                            <i class="fas fa-paint-brush"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Right Panel: Preview -->
    <div class="atb-preview">
        <div class="atb-preview-toolbar" id="atbPreviewToolbar" style="display:none;">
            <div class="atb-preview-device">
                <button class="active" onclick="setDevice('desktop', this)"><i class="fas fa-desktop"></i></button>
                <button onclick="setDevice('tablet', this)"><i class="fas fa-tablet-alt"></i></button>
                <button onclick="setDevice('mobile', this)"><i class="fas fa-mobile-alt"></i></button>
            </div>
            <div class="url-bar" id="atbUrlBar">No preview</div>
            <div>
                <button class="atb-preview-device" onclick="refreshPreview()" style="border:1px solid var(--ctp-surface1);padding:4px 10px;border-radius:6px;background:none;color:var(--ctp-overlay0);cursor:pointer;font-size:12px;">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <div class="atb-preview-frame" id="atbPreviewFrame">
            <div class="atb-preview-empty" id="atbPreviewEmpty">
                <i class="fas fa-palette"></i>
                <p>Your theme preview will appear here</p>
                <p style="font-size:12px;opacity:0.6;">Fill in the form and click "Generate Theme"</p>
            </div>
        </div>
    </div>

</div>

<script>
(function() {
    const CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
    let currentSlug = null;

    // Elements
    const btnGenerate = document.getElementById('atbGenerate');
    const txtPrompt = document.getElementById('atbPrompt');
    const selIndustry = document.getElementById('atbIndustry');
    const selStyle = document.getElementById('atbStyle');
    const selMood = document.getElementById('atbMood');
    const divProgress = document.getElementById('atbProgress');
    const divError = document.getElementById('atbError');
    const divActions = document.getElementById('atbActions');
    const divTimings = document.getElementById('atbTimings');
    const previewFrame = document.getElementById('atbPreviewFrame');
    const previewEmpty = document.getElementById('atbPreviewEmpty');
    const previewToolbar = document.getElementById('atbPreviewToolbar');
    const urlBar = document.getElementById('atbUrlBar');

    // ── Generate ──
    btnGenerate.addEventListener('click', async function() {
        const prompt = txtPrompt.value.trim();
        if (!prompt) {
            txtPrompt.focus();
            return;
        }

        // Reset UI
        btnGenerate.classList.add('loading');
        btnGenerate.disabled = true;
        divProgress.classList.add('visible');
        divError.classList.remove('visible');
        divActions.classList.remove('visible');
        divTimings.classList.remove('visible');
        currentSlug = null;

        // Reset steps
        for (let i = 1; i <= 4; i++) {
            const step = document.getElementById('step' + i);
            step.className = 'atb-step';
        }

        // Simulate step progress (we don't have real streaming)
        let stepTimer = simulateSteps();

        try {
            const res = await fetch('/api/ai-theme-builder/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    prompt: prompt,
                    industry: selIndustry.value,
                    style: selStyle.value,
                    mood: selMood.value,
                }),
            });

            clearInterval(stepTimer);
            const data = await res.json();

            if (data.ok) {
                // All steps done
                for (let i = 1; i <= 4; i++) {
                    document.getElementById('step' + i).className = 'atb-step done';
                }

                currentSlug = data.slug;
                divActions.classList.add('visible');
                
                // Update studio link
                document.getElementById('atbStudio').href = '/admin/theme-studio';

                // Show timings
                if (data.timings) {
                    let html = '';
                    if (data.timings.step1) html += '<span>Brief: ' + (data.timings.step1/1000).toFixed(1) + 's</span>';
                    if (data.timings.step2) html += '<span>HTML: ' + (data.timings.step2/1000).toFixed(1) + 's</span>';
                    if (data.timings.step3) html += '<span>CSS: ' + (data.timings.step3/1000).toFixed(1) + 's</span>';
                    if (data.timings.step4) html += '<span>Assembly: ' + (data.timings.step4/1000).toFixed(1) + 's</span>';
                    divTimings.innerHTML = html;
                    divTimings.classList.add('visible');
                }

                // Load preview
                showPreview(data.slug);
            } else {
                // Mark failed step
                const failedStep = data.step || 1;
                for (let i = 1; i <= 4; i++) {
                    const step = document.getElementById('step' + i);
                    if (i < failedStep) step.className = 'atb-step done';
                    else if (i === failedStep) step.className = 'atb-step error';
                }

                divError.textContent = data.error || 'Generation failed';
                divError.classList.add('visible');
            }
        } catch (err) {
            clearInterval(stepTimer);
            divError.textContent = 'Request failed: ' + err.message;
            divError.classList.add('visible');
        }

        btnGenerate.classList.remove('loading');
        btnGenerate.disabled = false;
    });

    // ── Simulate step progress (visual feedback during long request) ──
    function simulateSteps() {
        let step = 1;
        document.getElementById('step1').className = 'atb-step active';

        return setInterval(function() {
            if (step < 4) {
                document.getElementById('step' + step).className = 'atb-step done';
                step++;
                document.getElementById('step' + step).className = 'atb-step active';
            }
        }, 8000); // Move to next step every 8 seconds
    }

    // ── Show preview ──
    function showPreview(slug) {
        previewEmpty.style.display = 'none';
        previewToolbar.style.display = 'flex';

        // Remove existing iframe
        const existing = previewFrame.querySelector('iframe');
        if (existing) existing.remove();

        const iframe = document.createElement('iframe');
        iframe.src = '/admin/ai-theme-builder/preview?theme=' + encodeURIComponent(slug);
        previewFrame.appendChild(iframe);
        urlBar.textContent = 'Preview: ' + slug;
    }

    // ── Apply theme ──
    document.getElementById('atbApply').addEventListener('click', async function() {
        if (!currentSlug) return;

        if (!confirm('Activate theme "' + currentSlug + '"? This will change your live site.')) return;

        try {
            const res = await fetch('/api/ai-theme-builder/apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN,
                },
                body: JSON.stringify({ slug: currentSlug }),
            });

            const data = await res.json();
            if (data.ok) {
                alert('Theme "' + currentSlug + '" is now active!');
                // Update studio link to work with new theme
                document.getElementById('atbStudio').href = '/admin/theme-studio';
            } else {
                alert('Failed: ' + (data.error || 'Unknown error'));
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    });

    // ── Device switcher ──
    window.setDevice = function(device, btn) {
        const frame = document.getElementById('atbPreviewFrame');
        frame.className = 'atb-preview-frame';
        if (device !== 'desktop') {
            frame.classList.add('device-' + device);
        }
        // Update active button
        document.querySelectorAll('.atb-preview-device button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    };

    // ── Refresh preview ──
    window.refreshPreview = function() {
        const iframe = previewFrame.querySelector('iframe');
        if (iframe) iframe.src = iframe.src;
    };

    // ── Preview existing theme ──
    window.previewTheme = function(slug) {
        currentSlug = slug;
        showPreview(slug);
        divActions.classList.add('visible');
        document.getElementById('atbStudio').href = '/admin/theme-studio';
    };

    // ── Apply existing theme ──
    window.applyTheme = async function(slug) {
        if (!confirm('Activate theme "' + slug + '"?')) return;

        try {
            const res = await fetch('/api/ai-theme-builder/apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN,
                },
                body: JSON.stringify({ slug: slug }),
            });

            const data = await res.json();
            if (data.ok) {
                alert('Theme "' + slug + '" is now active!');
            } else {
                alert('Failed: ' + (data.error || 'Unknown error'));
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    };

})();
</script>
</body>
</html>
