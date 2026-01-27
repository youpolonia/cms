<?php
/**
 * AI Theme Builder Pro - Create New Theme
 * Phase 1.1: Extended Design System with Typography, Spacing, Shadows
 * 
 * NO CLI, pure PHP 8.1+, FTP-only, require_once only
 * DO NOT add closing ?> tag
 */
ob_start();

// Default design system values
$defaultDesignSystem = [
    'colors' => [
        'primary' => '#6366f1',
        'secondary' => '#8b5cf6',
        'accent' => '#ec4899',
        'background' => '#0a0a0f',
        'surface' => '#1a1a2e',
        'text' => '#ffffff',
        'textMuted' => '#9ca3af'
    ],
    'typography' => [
        'headingFont' => 'Inter',
        'bodyFont' => 'Inter',
        'baseFontSize' => 16,
        'h1Size' => 48,
        'h2Size' => 36,
        'h3Size' => 28,
        'h4Size' => 22,
        'lineHeight' => 1.6,
        'headingWeight' => 700,
        'bodyWeight' => 400
    ],
    'spacing' => [
        'containerWidth' => 1200,
        'sectionPadding' => 80,
        'cardPadding' => 24,
        'gap' => 24
    ],
    'borders' => [
        'radius' => 12,
        'radiusSmall' => 6,
        'radiusLarge' => 20,
        'width' => 1
    ],
    'shadows' => [
        'preset' => 'medium'
    ]
];

// Popular Google Fonts
$googleFonts = [
    'Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins', 
    'Raleway', 'Nunito', 'Playfair Display', 'Merriweather', 'Source Sans Pro',
    'Ubuntu', 'Oswald', 'Quicksand', 'Work Sans', 'DM Sans', 'Outfit',
    'Space Grotesk', 'Manrope', 'Plus Jakarta Sans', 'Sora', 'Lexend'
];
?>

<style>
/* ═══════════════════════════════════════════════════════════ */
/* AI THEME BUILDER PRO - STYLES                               */
/* ═══════════════════════════════════════════════════════════ */

.theme-wizard {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 24px;
    max-width: 1600px;
    margin: 0 auto;
    min-height: calc(100vh - 80px);
}

@media (max-width: 1200px) {
    .theme-wizard {
        grid-template-columns: 1fr;
    }
    .preview-panel {
        display: none;
    }
}

/* ─── Left Panel (Form) ─── */
.form-panel {
    padding: 0 24px 24px;
}

.wizard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}

.wizard-header h1 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.wizard-header p {
    color: var(--text-muted);
    margin: 4px 0 0 0;
    font-size: 14px;
}

/* ─── Wizard Steps ─── */
.wizard-step {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.wizard-step:hover {
    border-color: var(--border-light, #3d3d4d);
}

.wizard-step.collapsed .step-content {
    display: none;
}

.step-header {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    user-select: none;
}

.step-number {
    width: 32px;
    height: 32px;
    background: var(--accent);
    color: #000;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    flex-shrink: 0;
}

.step-title {
    font-size: 16px;
    font-weight: 600;
    flex: 1;
}

.step-toggle {
    font-size: 12px;
    color: var(--text-muted);
    transition: transform 0.3s;
}

.wizard-step.collapsed .step-toggle {
    transform: rotate(-90deg);
}

.step-content {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

/* ─── Form Elements ─── */
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.form-row-3 {
    grid-template-columns: repeat(3, 1fr);
}

.form-row-4 {
    grid-template-columns: repeat(4, 1fr);
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 6px;
    color: var(--text-secondary);
}

.form-group .hint {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 4px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 14px;
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 14px;
    transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

/* ─── Color Inputs ─── */
.color-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

@media (max-width: 900px) {
    .color-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.color-item {
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px;
    transition: all 0.2s;
}

.color-item:hover {
    border-color: var(--accent);
}

.color-item label {
    font-size: 11px;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 8px;
    display: block;
}

.color-input-wrapper {
    display: flex;
    gap: 8px;
    align-items: center;
}

.color-input-wrapper input[type="color"] {
    width: 40px;
    height: 40px;
    padding: 2px;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid var(--border);
}

.color-input-wrapper input[type="text"] {
    flex: 1;
    font-family: 'JetBrains Mono', monospace;
    text-transform: uppercase;
    font-size: 12px;
    padding: 8px;
}

/* ─── Type Cards ─── */
.type-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 10px;
}

.type-card {
    background: var(--bg-tertiary);
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 16px 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}

.type-card:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
}

.type-card:has(input:checked) {
    border-color: var(--accent);
    background: rgba(99, 102, 241, 0.15);
}

.type-card input {
    display: none;
}

.type-card .icon {
    font-size: 28px;
    margin-bottom: 6px;
}

.type-card .label {
    font-size: 11px;
    font-weight: 500;
}

/* ─── Style Options ─── */
.style-options {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.style-option {
    padding: 10px 16px;
    background: var(--bg-tertiary);
    border: 2px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
}

.style-option:hover {
    border-color: var(--accent);
}

.style-option:has(input:checked) {
    border-color: var(--accent);
    background: rgba(99, 102, 241, 0.15);
}

.style-option input {
    display: none;
}

/* ─── Range Slider ─── */
.range-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.range-group input[type="range"] {
    flex: 1;
    -webkit-appearance: none;
    height: 6px;
    background: var(--bg-tertiary);
    border-radius: 3px;
    cursor: pointer;
}

.range-group input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    background: var(--accent);
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.2s;
}

.range-group input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.2);
}

.range-value {
    min-width: 50px;
    padding: 6px 10px;
    background: var(--bg-tertiary);
    border-radius: 6px;
    text-align: center;
    font-size: 12px;
    font-family: 'JetBrains Mono', monospace;
}

/* ─── Shadow Presets ─── */
.shadow-presets {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
}

.shadow-preset {
    background: var(--bg-tertiary);
    border: 2px solid var(--border);
    border-radius: 10px;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}

.shadow-preset:hover {
    border-color: var(--accent);
}

.shadow-preset:has(input:checked) {
    border-color: var(--accent);
    background: rgba(99, 102, 241, 0.15);
}

.shadow-preset input {
    display: none;
}

.shadow-preview {
    width: 50px;
    height: 50px;
    background: var(--bg-secondary);
    border-radius: 8px;
    margin: 0 auto 8px;
}

.shadow-preset .label {
    font-size: 11px;
    font-weight: 500;
}

.shadow-none .shadow-preview { box-shadow: none; }
.shadow-sm .shadow-preview { box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
.shadow-md .shadow-preview { box-shadow: 0 4px 12px rgba(0,0,0,0.4); }
.shadow-lg .shadow-preview { box-shadow: 0 8px 25px rgba(0,0,0,0.5); }
.shadow-xl .shadow-preview { box-shadow: 0 15px 40px rgba(0,0,0,0.6); }

/* ─── Section Title ─── */
.section-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
}

/* ─── Generate Button ─── */
.generate-btn {
    width: 100%;
    padding: 18px 32px;
    font-size: 16px;
    font-weight: 600;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: none;
    border-radius: 12px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    transition: all 0.3s;
    margin-top: 8px;
}

.generate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
}

.generate-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ─── Right Panel (Live Preview) ─── */
.preview-panel {
    background: var(--bg-secondary);
    border-left: 1px solid var(--border);
    padding: 24px;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}

.preview-header h3 {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.preview-device-toggle {
    display: flex;
    background: var(--bg-tertiary);
    border-radius: 6px;
    padding: 2px;
}

.preview-device-btn {
    padding: 6px 10px;
    background: transparent;
    border: none;
    border-radius: 4px;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.preview-device-btn:hover {
    color: var(--text-secondary);
}

.preview-device-btn.active {
    background: var(--accent);
    color: white;
}

.preview-frame {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    transition: all 0.3s;
}

.preview-frame.desktop { width: 100%; }
.preview-frame.tablet { width: 280px; margin: 0 auto; }
.preview-frame.mobile { width: 200px; margin: 0 auto; }

.preview-content {
    padding: 0;
    min-height: 500px;
}

/* Preview Elements */
.preview-header-element {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.preview-logo {
    font-weight: 700;
    font-size: 18px;
}

.preview-nav {
    display: flex;
    gap: 16px;
    font-size: 13px;
}

.preview-hero {
    padding: 60px 20px;
    text-align: center;
}

.preview-hero h1 {
    margin: 0 0 12px 0;
}

.preview-hero p {
    margin: 0 0 20px 0;
}

.preview-btn {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
}

.preview-features {
    padding: 40px 20px;
}

.preview-features h2 {
    text-align: center;
    margin: 0 0 24px 0;
}

.preview-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.preview-card {
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.preview-card-icon {
    font-size: 24px;
    margin-bottom: 8px;
}

.preview-card h3 {
    font-size: 14px;
    margin: 0 0 6px 0;
}

.preview-card p {
    font-size: 12px;
    margin: 0;
    opacity: 0.8;
}

.preview-footer {
    padding: 20px;
    text-align: center;
    font-size: 12px;
}

/* ─── Typography Preview ─── */
.typography-preview {
    background: var(--bg-tertiary);
    border-radius: 10px;
    padding: 16px;
    margin-top: 16px;
}

.typo-sample {
    margin-bottom: 8px;
}

.typo-sample:last-child {
    margin-bottom: 0;
}

.typo-label {
    font-size: 10px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* ─── Result Card ─── */
.result-card {
    background: var(--bg-secondary);
    border: 2px solid var(--accent);
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    display: none;
}

.result-card.show {
    display: block;
}

.result-card .success-icon {
    font-size: 64px;
    margin-bottom: 16px;
}

.result-card h3 {
    font-size: 24px;
    margin: 0 0 8px 0;
}

.result-card p {
    color: var(--text-muted);
    margin: 0 0 24px 0;
}

.result-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

/* ─── AI Badge ─── */
.ai-badge {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
</style>

<div class="theme-wizard">
    <!-- Left Panel - Form -->
    <div class="form-panel">
        <div class="wizard-header">
            <div>
                <a href="/admin/themes" style="color: var(--text-muted); text-decoration: none; font-size: 13px; display: inline-block; margin-bottom: 8px;">
                    ← Back to Themes
                </a>
                <h1>✨ AI Theme Builder Pro</h1>
                <p>Design your complete theme with AI-powered generation</p>
            </div>
            <span class="ai-badge">🤖 AI Powered</span>
        </div>
        
        <form id="theme-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <!-- Step 1: Basic Info -->
            <div class="wizard-step" data-step="1">
                <div class="step-header" onclick="toggleStep(this)">
                    <span class="step-number">1</span>
                    <span class="step-title">Basic Information</span>
                    <span class="step-toggle">▼</span>
                </div>
                <div class="step-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="theme-name">Theme Name *</label>
                            <input type="text" id="theme-name" name="name" placeholder="my-awesome-theme" required pattern="[a-z0-9\-]+" title="Lowercase letters, numbers and hyphens only">
                            <div class="hint">Lowercase, no spaces (e.g., corporate-dark)</div>
                        </div>
                        <div class="form-group">
                            <label for="theme-title">Display Title</label>
                            <input type="text" id="theme-title" name="title" placeholder="My Awesome Theme">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Describe Your Website *</label>
                        <textarea id="description" name="description" placeholder="E.g., A professional law firm website with a modern, trustworthy look. Should convey expertise and reliability. Target audience is corporate clients seeking legal services." required></textarea>
                        <div class="hint">Be specific! Include industry, target audience, and desired mood.</div>
                    </div>
                </div>
            </div>
            
            <!-- Step 2: Website Type -->
            <div class="wizard-step" data-step="2">
                <div class="step-header" onclick="toggleStep(this)">
                    <span class="step-number">2</span>
                    <span class="step-title">Website Type</span>
                    <span class="step-toggle">▼</span>
                </div>
                <div class="step-content">
                    <div class="type-cards">
                        <label class="type-card">
                            <input type="radio" name="type" value="business" checked>
                            <div class="icon">🏢</div>
                            <div class="label">Business</div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="blog">
                            <div class="icon">📝</div>
                            <div class="label">Blog</div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="portfolio">
                            <div class="icon">🎨</div>
                            <div class="label">Portfolio</div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="restaurant">
                            <div class="icon">🍽️</div>
                            <div class="label">Restaurant</div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="ecommerce">
                            <div class="icon">🛒</div>
                            <div class="label">E-commerce</div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="landing">
                            <div class="icon">🚀</div>
                            <div class="label">Landing</div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="agency">
                            <div class="icon">💼</div>
                            <div class="label">Agency</div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="medical">
                            <div class="icon">🏥</div>
                            <div class="label">Medical</div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="saas">
                            <div class="icon">☁️</div>
                            <div class="label">SaaS</div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="nonprofit">
                            <div class="icon">💚</div>
                            <div class="label">Non-Profit</div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Step 3: Visual Style -->
            <div class="wizard-step" data-step="3">
                <div class="step-header" onclick="toggleStep(this)">
                    <span class="step-number">3</span>
                    <span class="step-title">Visual Style</span>
                    <span class="step-toggle">▼</span>
                </div>
                <div class="step-content">
                    <div class="form-group">
                        <label>Design Style</label>
                        <div class="style-options">
                            <label class="style-option">
                                <input type="radio" name="style" value="modern" checked>
                                ✨ Modern & Clean
                            </label>
                            <label class="style-option">
                                <input type="radio" name="style" value="minimalist">
                                ⬜ Minimalist
                            </label>
                            <label class="style-option">
                                <input type="radio" name="style" value="bold">
                                🔥 Bold & Dynamic
                            </label>
                            <label class="style-option">
                                <input type="radio" name="style" value="elegant">
                                👑 Elegant
                            </label>
                            <label class="style-option">
                                <input type="radio" name="style" value="playful">
                                🎨 Playful
                            </label>
                            <label class="style-option">
                                <input type="radio" name="style" value="corporate">
                                🏛️ Corporate
                            </label>
                            <label class="style-option">
                                <input type="radio" name="style" value="futuristic">
                                🚀 Futuristic
                            </label>
                            <label class="style-option">
                                <input type="radio" name="style" value="vintage">
                                📜 Vintage
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label>Color Scheme</label>
                        <div class="style-options">
                            <label class="style-option">
                                <input type="radio" name="color_scheme" value="dark" checked>
                                🌙 Dark Mode
                            </label>
                            <label class="style-option">
                                <input type="radio" name="color_scheme" value="light">
                                ☀️ Light Mode
                            </label>
                            <label class="style-option">
                                <input type="radio" name="color_scheme" value="auto">
                                🔄 Both (Auto)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 4: Color Palette -->
            <div class="wizard-step" data-step="4">
                <div class="step-header" onclick="toggleStep(this)">
                    <span class="step-number">4</span>
                    <span class="step-title">Color Palette</span>
                    <span class="step-toggle">▼</span>
                </div>
                <div class="step-content">
                    <div class="color-grid">
                        <div class="color-item">
                            <label>Primary</label>
                            <div class="color-input-wrapper">
                                <input type="color" name="color_primary" id="color_primary" value="#6366f1" onchange="syncColorInput(this)">
                                <input type="text" id="color_primary_text" value="#6366f1" onchange="syncColorPicker(this, 'color_primary')">
                            </div>
                        </div>
                        <div class="color-item">
                            <label>Secondary</label>
                            <div class="color-input-wrapper">
                                <input type="color" name="color_secondary" id="color_secondary" value="#8b5cf6" onchange="syncColorInput(this)">
                                <input type="text" id="color_secondary_text" value="#8b5cf6" onchange="syncColorPicker(this, 'color_secondary')">
                            </div>
                        </div>
                        <div class="color-item">
                            <label>Accent</label>
                            <div class="color-input-wrapper">
                                <input type="color" name="color_accent" id="color_accent" value="#ec4899" onchange="syncColorInput(this)">
                                <input type="text" id="color_accent_text" value="#ec4899" onchange="syncColorPicker(this, 'color_accent')">
                            </div>
                        </div>
                        <div class="color-item">
                            <label>Background</label>
                            <div class="color-input-wrapper">
                                <input type="color" name="color_background" id="color_background" value="#0a0a0f" onchange="syncColorInput(this)">
                                <input type="text" id="color_background_text" value="#0a0a0f" onchange="syncColorPicker(this, 'color_background')">
                            </div>
                        </div>
                        <div class="color-item">
                            <label>Surface</label>
                            <div class="color-input-wrapper">
                                <input type="color" name="color_surface" id="color_surface" value="#1a1a2e" onchange="syncColorInput(this)">
                                <input type="text" id="color_surface_text" value="#1a1a2e" onchange="syncColorPicker(this, 'color_surface')">
                            </div>
                        </div>
                        <div class="color-item">
                            <label>Text</label>
                            <div class="color-input-wrapper">
                                <input type="color" name="color_text" id="color_text" value="#ffffff" onchange="syncColorInput(this)">
                                <input type="text" id="color_text_text" value="#ffffff" onchange="syncColorPicker(this, 'color_text')">
                            </div>
                        </div>
                        <div class="color-item">
                            <label>Text Muted</label>
                            <div class="color-input-wrapper">
                                <input type="color" name="color_text_muted" id="color_text_muted" value="#9ca3af" onchange="syncColorInput(this)">
                                <input type="text" id="color_text_muted_text" value="#9ca3af" onchange="syncColorPicker(this, 'color_text_muted')">
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 16px; display: flex; gap: 10px;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="generateAIPalette()">
                            🎨 AI Generate Palette
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="randomizePalette()">
                            🎲 Randomize
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Step 5: Typography -->
            <div class="wizard-step" data-step="5">
                <div class="step-header" onclick="toggleStep(this)">
                    <span class="step-number">5</span>
                    <span class="step-title">Typography</span>
                    <span class="step-toggle">▼</span>
                </div>
                <div class="step-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="heading_font">Heading Font</label>
                            <select name="heading_font" id="heading_font" onchange="updatePreview()">
                                <?php foreach ($googleFonts as $font): ?>
                                <option value="<?= $font ?>" <?= $font === 'Inter' ? 'selected' : '' ?>><?= $font ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="body_font">Body Font</label>
                            <select name="body_font" id="body_font" onchange="updatePreview()">
                                <?php foreach ($googleFonts as $font): ?>
                                <option value="<?= $font ?>" <?= $font === 'Inter' ? 'selected' : '' ?>><?= $font ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="section-title" style="margin-top: 20px;">Font Sizes</div>
                    <div class="form-row form-row-4">
                        <div class="form-group">
                            <label>H1 Size</label>
                            <div class="range-group">
                                <input type="range" name="h1_size" id="h1_size" min="32" max="72" value="48" oninput="updateRangeValue(this)">
                                <span class="range-value" id="h1_size_val">48px</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>H2 Size</label>
                            <div class="range-group">
                                <input type="range" name="h2_size" id="h2_size" min="24" max="54" value="36" oninput="updateRangeValue(this)">
                                <span class="range-value" id="h2_size_val">36px</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>H3 Size</label>
                            <div class="range-group">
                                <input type="range" name="h3_size" id="h3_size" min="18" max="36" value="28" oninput="updateRangeValue(this)">
                                <span class="range-value" id="h3_size_val">28px</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Base Size</label>
                            <div class="range-group">
                                <input type="range" name="base_size" id="base_size" min="14" max="20" value="16" oninput="updateRangeValue(this)">
                                <span class="range-value" id="base_size_val">16px</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Line Height</label>
                            <div class="range-group">
                                <input type="range" name="line_height" id="line_height" min="1.2" max="2" step="0.1" value="1.6" oninput="updateRangeValue(this)">
                                <span class="range-value" id="line_height_val">1.6</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Heading Weight</label>
                            <select name="heading_weight" id="heading_weight" onchange="updatePreview()">
                                <option value="400">Regular (400)</option>
                                <option value="500">Medium (500)</option>
                                <option value="600">Semibold (600)</option>
                                <option value="700" selected>Bold (700)</option>
                                <option value="800">Extra Bold (800)</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Typography Preview -->
                    <div class="typography-preview" id="typography-preview">
                        <div class="typo-sample">
                            <span class="typo-label">H1</span>
                            <div id="typo-h1" style="font-size: 48px; font-weight: 700;">Heading One</div>
                        </div>
                        <div class="typo-sample">
                            <span class="typo-label">H2</span>
                            <div id="typo-h2" style="font-size: 36px; font-weight: 700;">Heading Two</div>
                        </div>
                        <div class="typo-sample">
                            <span class="typo-label">Body</span>
                            <div id="typo-body" style="font-size: 16px; line-height: 1.6;">The quick brown fox jumps over the lazy dog. This is sample body text.</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 6: Spacing & Layout -->
            <div class="wizard-step" data-step="6">
                <div class="step-header" onclick="toggleStep(this)">
                    <span class="step-number">6</span>
                    <span class="step-title">Spacing & Layout</span>
                    <span class="step-toggle">▼</span>
                </div>
                <div class="step-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Container Width</label>
                            <div class="range-group">
                                <input type="range" name="container_width" id="container_width" min="960" max="1400" step="20" value="1200" oninput="updateRangeValue(this)">
                                <span class="range-value" id="container_width_val">1200px</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Section Padding</label>
                            <div class="range-group">
                                <input type="range" name="section_padding" id="section_padding" min="40" max="120" step="10" value="80" oninput="updateRangeValue(this)">
                                <span class="range-value" id="section_padding_val">80px</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section-title" style="margin-top: 20px;">Border Radius</div>
                    <div class="form-row form-row-3">
                        <div class="form-group">
                            <label>Small (buttons, inputs)</label>
                            <div class="range-group">
                                <input type="range" name="radius_small" id="radius_small" min="0" max="16" value="6" oninput="updateRangeValue(this)">
                                <span class="range-value" id="radius_small_val">6px</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Medium (cards)</label>
                            <div class="range-group">
                                <input type="range" name="radius_medium" id="radius_medium" min="0" max="24" value="12" oninput="updateRangeValue(this)">
                                <span class="range-value" id="radius_medium_val">12px</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Large (sections)</label>
                            <div class="range-group">
                                <input type="range" name="radius_large" id="radius_large" min="0" max="32" value="20" oninput="updateRangeValue(this)">
                                <span class="range-value" id="radius_large_val">20px</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 7: Shadows -->
            <div class="wizard-step" data-step="7">
                <div class="step-header" onclick="toggleStep(this)">
                    <span class="step-number">7</span>
                    <span class="step-title">Shadow Style</span>
                    <span class="step-toggle">▼</span>
                </div>
                <div class="step-content">
                    <div class="shadow-presets">
                        <label class="shadow-preset shadow-none">
                            <input type="radio" name="shadow_preset" value="none">
                            <div class="shadow-preview"></div>
                            <div class="label">None</div>
                        </label>
                        <label class="shadow-preset shadow-sm">
                            <input type="radio" name="shadow_preset" value="small">
                            <div class="shadow-preview"></div>
                            <div class="label">Small</div>
                        </label>
                        <label class="shadow-preset shadow-md">
                            <input type="radio" name="shadow_preset" value="medium" checked>
                            <div class="shadow-preview"></div>
                            <div class="label">Medium</div>
                        </label>
                        <label class="shadow-preset shadow-lg">
                            <input type="radio" name="shadow_preset" value="large">
                            <div class="shadow-preview"></div>
                            <div class="label">Large</div>
                        </label>
                        <label class="shadow-preset shadow-xl">
                            <input type="radio" name="shadow_preset" value="xl">
                            <div class="shadow-preview"></div>
                            <div class="label">Extra Large</div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Generate Button -->
            <button type="submit" class="generate-btn" id="generate-btn">
                <span class="btn-text">✨ Generate Theme with AI</span>
                <span class="spinner" style="display: none;"></span>
            </button>
        </form>
        
        <!-- Result -->
        <div class="result-card" id="result-card">
            <div class="success-icon">🎉</div>
            <h3>Theme Generated Successfully!</h3>
            <p id="result-message">Your new theme is ready to use.</p>
            <div class="result-actions">
                <a href="#" class="btn btn-secondary" id="preview-link">👁️ Preview Theme</a>
                <a href="/admin/themes" class="btn btn-primary">View All Themes</a>
            </div>
        </div>
    </div>
    
    <!-- Right Panel - Live Preview -->
    <div class="preview-panel">
        <div class="preview-header">
            <h3>👁️ Live Preview</h3>
            <div class="preview-device-toggle">
                <button type="button" class="preview-device-btn active" data-device="desktop">🖥️</button>
                <button type="button" class="preview-device-btn" data-device="tablet">📱</button>
                <button type="button" class="preview-device-btn" data-device="mobile">📲</button>
            </div>
        </div>
        
        <div class="preview-frame desktop" id="preview-frame">
            <div class="preview-content" id="preview-content">
                <!-- Dynamic preview will be rendered here -->
            </div>
        </div>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════════════
// AI THEME BUILDER PRO - JAVASCRIPT
// ═══════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    console.log('AI Theme Builder Pro initialized');
    initializePreview();
    initializeDeviceToggle();
    updatePreview();
});

// ─── Step Toggle ───
function toggleStep(header) {
    const step = header.closest('.wizard-step');
    step.classList.toggle('collapsed');
}

// ─── Color Sync ───
function syncColorInput(colorPicker) {
    const textInput = document.getElementById(colorPicker.id + '_text');
    if (textInput) {
        textInput.value = colorPicker.value;
    }
    updatePreview();
}

function syncColorPicker(textInput, pickerId) {
    const colorPicker = document.getElementById(pickerId);
    if (colorPicker && /^#[0-9A-Fa-f]{6}$/.test(textInput.value)) {
        colorPicker.value = textInput.value;
    }
    updatePreview();
}

// ─── Range Value Update ───
function updateRangeValue(input) {
    const valueSpan = document.getElementById(input.id + '_val');
    if (valueSpan) {
        let suffix = 'px';
        if (input.id === 'line_height') suffix = '';
        valueSpan.textContent = input.value + suffix;
    }
    updatePreview();
    updateTypographyPreview();
}

// ─── Typography Preview ───
function updateTypographyPreview() {
    const headingFont = document.getElementById('heading_font').value;
    const bodyFont = document.getElementById('body_font').value;
    const h1Size = document.getElementById('h1_size').value;
    const h2Size = document.getElementById('h2_size').value;
    const baseSize = document.getElementById('base_size').value;
    const lineHeight = document.getElementById('line_height').value;
    const headingWeight = document.getElementById('heading_weight').value;
    
    // Load Google Fonts dynamically
    loadGoogleFont(headingFont);
    loadGoogleFont(bodyFont);
    
    const typoH1 = document.getElementById('typo-h1');
    const typoH2 = document.getElementById('typo-h2');
    const typoBody = document.getElementById('typo-body');
    
    if (typoH1) {
        typoH1.style.fontFamily = `'${headingFont}', sans-serif`;
        typoH1.style.fontSize = h1Size + 'px';
        typoH1.style.fontWeight = headingWeight;
    }
    if (typoH2) {
        typoH2.style.fontFamily = `'${headingFont}', sans-serif`;
        typoH2.style.fontSize = h2Size + 'px';
        typoH2.style.fontWeight = headingWeight;
    }
    if (typoBody) {
        typoBody.style.fontFamily = `'${bodyFont}', sans-serif`;
        typoBody.style.fontSize = baseSize + 'px';
        typoBody.style.lineHeight = lineHeight;
    }
}

// ─── Load Google Font ───
const loadedFonts = new Set();
function loadGoogleFont(fontName) {
    if (loadedFonts.has(fontName)) return;
    loadedFonts.add(fontName);
    
    const link = document.createElement('link');
    link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(fontName)}:wght@400;500;600;700;800&display=swap`;
    link.rel = 'stylesheet';
    document.head.appendChild(link);
}

// ─── Device Toggle ───
function initializeDeviceToggle() {
    document.querySelectorAll('.preview-device-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.preview-device-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const device = this.dataset.device;
            const frame = document.getElementById('preview-frame');
            frame.className = 'preview-frame ' + device;
        });
    });
}

// ─── Initialize Preview ───
function initializePreview() {
    // Watch for changes on all inputs
    document.querySelectorAll('#theme-form input, #theme-form select, #theme-form textarea').forEach(input => {
        input.addEventListener('change', updatePreview);
        input.addEventListener('input', updatePreview);
    });
}

// ─── Update Live Preview ───
function updatePreview() {
    const preview = document.getElementById('preview-content');
    if (!preview) return;
    
    // Get current values
    const colors = {
        primary: document.getElementById('color_primary')?.value || '#6366f1',
        secondary: document.getElementById('color_secondary')?.value || '#8b5cf6',
        accent: document.getElementById('color_accent')?.value || '#ec4899',
        background: document.getElementById('color_background')?.value || '#0a0a0f',
        surface: document.getElementById('color_surface')?.value || '#1a1a2e',
        text: document.getElementById('color_text')?.value || '#ffffff',
        textMuted: document.getElementById('color_text_muted')?.value || '#9ca3af'
    };
    
    const typography = {
        headingFont: document.getElementById('heading_font')?.value || 'Inter',
        bodyFont: document.getElementById('body_font')?.value || 'Inter',
        h1Size: document.getElementById('h1_size')?.value || 48,
        h2Size: document.getElementById('h2_size')?.value || 36,
        baseSize: document.getElementById('base_size')?.value || 16,
        lineHeight: document.getElementById('line_height')?.value || 1.6,
        headingWeight: document.getElementById('heading_weight')?.value || 700
    };
    
    const spacing = {
        radiusSmall: document.getElementById('radius_small')?.value || 6,
        radiusMedium: document.getElementById('radius_medium')?.value || 12
    };
    
    // Load fonts
    loadGoogleFont(typography.headingFont);
    loadGoogleFont(typography.bodyFont);
    
    // Get shadow
    let shadow = '0 4px 12px rgba(0,0,0,0.4)';
    const shadowPreset = document.querySelector('input[name="shadow_preset"]:checked')?.value;
    switch(shadowPreset) {
        case 'none': shadow = 'none'; break;
        case 'small': shadow = '0 1px 3px rgba(0,0,0,0.3)'; break;
        case 'medium': shadow = '0 4px 12px rgba(0,0,0,0.4)'; break;
        case 'large': shadow = '0 8px 25px rgba(0,0,0,0.5)'; break;
        case 'xl': shadow = '0 15px 40px rgba(0,0,0,0.6)'; break;
    }
    
    // Render preview
    preview.innerHTML = `
        <div style="background: ${colors.background}; color: ${colors.text}; font-family: '${typography.bodyFont}', sans-serif; font-size: ${typography.baseSize}px; line-height: ${typography.lineHeight}; min-height: 500px;">
            <!-- Header -->
            <div class="preview-header-element" style="background: ${colors.surface}; border-bottom: 1px solid ${colors.surface};">
                <div class="preview-logo" style="font-family: '${typography.headingFont}', sans-serif; color: ${colors.primary};">Brand</div>
                <div class="preview-nav" style="color: ${colors.textMuted};">
                    <span>Home</span>
                    <span>About</span>
                    <span>Contact</span>
                </div>
            </div>
            
            <!-- Hero -->
            <div class="preview-hero" style="background: linear-gradient(135deg, ${colors.background}, ${colors.surface});">
                <h1 style="font-family: '${typography.headingFont}', sans-serif; font-size: ${Math.round(typography.h1Size * 0.6)}px; font-weight: ${typography.headingWeight}; color: ${colors.text};">Welcome to Our Site</h1>
                <p style="color: ${colors.textMuted}; font-size: ${typography.baseSize}px;">Discover amazing things with us</p>
                <a class="preview-btn" style="background: ${colors.primary}; color: white; border-radius: ${spacing.radiusSmall}px; box-shadow: ${shadow};">Get Started</a>
            </div>
            
            <!-- Features -->
            <div class="preview-features" style="background: ${colors.surface};">
                <h2 style="font-family: '${typography.headingFont}', sans-serif; font-size: ${Math.round(typography.h2Size * 0.6)}px; font-weight: ${typography.headingWeight}; color: ${colors.text};">Our Features</h2>
                <div class="preview-cards">
                    <div class="preview-card" style="background: ${colors.background}; border-radius: ${spacing.radiusMedium}px; box-shadow: ${shadow};">
                        <div class="preview-card-icon">✨</div>
                        <h3 style="color: ${colors.text}; font-family: '${typography.headingFont}', sans-serif;">Feature One</h3>
                        <p style="color: ${colors.textMuted};">Description text</p>
                    </div>
                    <div class="preview-card" style="background: ${colors.background}; border-radius: ${spacing.radiusMedium}px; box-shadow: ${shadow};">
                        <div class="preview-card-icon">🚀</div>
                        <h3 style="color: ${colors.text}; font-family: '${typography.headingFont}', sans-serif;">Feature Two</h3>
                        <p style="color: ${colors.textMuted};">Description text</p>
                    </div>
                    <div class="preview-card" style="background: ${colors.background}; border-radius: ${spacing.radiusMedium}px; box-shadow: ${shadow};">
                        <div class="preview-card-icon">💡</div>
                        <h3 style="color: ${colors.text}; font-family: '${typography.headingFont}', sans-serif;">Feature Three</h3>
                        <p style="color: ${colors.textMuted};">Description text</p>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="preview-footer" style="background: ${colors.background}; color: ${colors.textMuted}; border-top: 1px solid ${colors.surface};">
                © 2025 Your Company. All rights reserved.
            </div>
        </div>
    `;
}

// ─── Generate AI Palette ───
async function generateAIPalette() {
    const description = document.getElementById('description').value;
    const style = document.querySelector('input[name="style"]:checked')?.value || 'modern';
    const colorScheme = document.querySelector('input[name="color_scheme"]:checked')?.value || 'dark';
    
    if (!description) {
        alert('Please describe your website first to generate an AI palette.');
        return;
    }
    
    // For now, use smart color generation based on style
    const palettes = {
        modern: { primary: '#6366f1', secondary: '#8b5cf6', accent: '#ec4899' },
        minimalist: { primary: '#374151', secondary: '#6b7280', accent: '#10b981' },
        bold: { primary: '#ef4444', secondary: '#f97316', accent: '#eab308' },
        elegant: { primary: '#7c3aed', secondary: '#a78bfa', accent: '#c4b5fd' },
        playful: { primary: '#f472b6', secondary: '#a855f7', accent: '#38bdf8' },
        corporate: { primary: '#1e40af', secondary: '#3b82f6', accent: '#06b6d4' },
        futuristic: { primary: '#00f5d4', secondary: '#8338ec', accent: '#ff006e' },
        vintage: { primary: '#92400e', secondary: '#b45309', accent: '#a16207' }
    };
    
    const palette = palettes[style] || palettes.modern;
    
    // Set colors
    document.getElementById('color_primary').value = palette.primary;
    document.getElementById('color_primary_text').value = palette.primary;
    document.getElementById('color_secondary').value = palette.secondary;
    document.getElementById('color_secondary_text').value = palette.secondary;
    document.getElementById('color_accent').value = palette.accent;
    document.getElementById('color_accent_text').value = palette.accent;
    
    // Set background based on color scheme
    if (colorScheme === 'dark') {
        document.getElementById('color_background').value = '#0a0a0f';
        document.getElementById('color_background_text').value = '#0a0a0f';
        document.getElementById('color_surface').value = '#1a1a2e';
        document.getElementById('color_surface_text').value = '#1a1a2e';
        document.getElementById('color_text').value = '#ffffff';
        document.getElementById('color_text_text').value = '#ffffff';
        document.getElementById('color_text_muted').value = '#9ca3af';
        document.getElementById('color_text_muted_text').value = '#9ca3af';
    } else {
        document.getElementById('color_background').value = '#ffffff';
        document.getElementById('color_background_text').value = '#ffffff';
        document.getElementById('color_surface').value = '#f8fafc';
        document.getElementById('color_surface_text').value = '#f8fafc';
        document.getElementById('color_text').value = '#1e293b';
        document.getElementById('color_text_text').value = '#1e293b';
        document.getElementById('color_text_muted').value = '#64748b';
        document.getElementById('color_text_muted_text').value = '#64748b';
    }
    
    updatePreview();
}

// ─── Randomize Palette ───
function randomizePalette() {
    const hue = Math.floor(Math.random() * 360);
    
    function hslToHex(h, s, l) {
        l /= 100;
        const a = s * Math.min(l, 1 - l) / 100;
        const f = n => {
            const k = (n + h / 30) % 12;
            const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
            return Math.round(255 * color).toString(16).padStart(2, '0');
        };
        return `#${f(0)}${f(8)}${f(4)}`;
    }
    
    const primary = hslToHex(hue, 70, 55);
    const secondary = hslToHex((hue + 30) % 360, 65, 60);
    const accent = hslToHex((hue + 180) % 360, 75, 55);
    
    document.getElementById('color_primary').value = primary;
    document.getElementById('color_primary_text').value = primary;
    document.getElementById('color_secondary').value = secondary;
    document.getElementById('color_secondary_text').value = secondary;
    document.getElementById('color_accent').value = accent;
    document.getElementById('color_accent_text').value = accent;
    
    updatePreview();
}

// ─── Form Submission ───
document.getElementById('theme-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const btn = document.getElementById('generate-btn');
    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner');
    
    btn.disabled = true;
    btnText.textContent = 'Generating theme... This may take a minute';
    spinner.style.display = 'block';
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('/admin/ai-theme-builder/generate', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        let data;
        
        try {
            data = JSON.parse(text);
        } catch (parseErr) {
            console.error('JSON parse error:', parseErr);
            console.error('Response:', text);
            alert('Invalid response from server. Check console for details.');
            btn.disabled = false;
            btnText.textContent = '✨ Generate Theme with AI';
            spinner.style.display = 'none';
            return;
        }
        
        if (data.success) {
            document.getElementById('theme-form').style.display = 'none';
            document.getElementById('result-card').classList.add('show');
            document.getElementById('result-message').textContent = 'Theme "' + data.theme_name + '" has been created!';
            document.getElementById('preview-link').href = data.preview_url;
        } else {
            alert('Error: ' + (data.error || 'Failed to generate theme'));
            btn.disabled = false;
            btnText.textContent = '✨ Generate Theme with AI';
            spinner.style.display = 'none';
        }
    } catch (err) {
        console.error('Fetch error:', err);
        alert('Network error: ' + err.message);
        btn.disabled = false;
        btnText.textContent = '✨ Generate Theme with AI';
        spinner.style.display = 'none';
    }
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
