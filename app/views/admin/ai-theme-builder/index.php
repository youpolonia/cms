<?php
/**
 * AI Theme Builder 4.0 - 4-Step Wizard UI
 * Creates TB 3.0 Layouts: Header, Pages, Footer
 *
 * NO CLI, pure PHP 8.1+, FTP-only, require_once only
 * DO NOT add closing ?> tag
 */
ob_start();

// Get AI providers from config
$aiProviders = [];
$configPath = CMS_ROOT . '/config/ai.php';
if (file_exists($configPath)) {
    $aiConfig = require $configPath;
    $aiProviders = $aiConfig['providers'] ?? [];
}

// Get available models from ai_settings.json
$availableModels = [];
$aiSettingsPath = CMS_ROOT . '/config/ai_settings.json';
if (file_exists($aiSettingsPath)) {
    $aiSettings = json_decode(file_get_contents($aiSettingsPath), true);
    // Add OpenAI models
    if (!empty($aiSettings['providers']['openai']['models'])) {
        foreach ($aiSettings['providers']['openai']['models'] as $id => $model) {
            $name = is_array($model) ? ($model['name'] ?? $id) : $model;
            if (!empty($model['recommended'])) $name .= ' ⭐';
            if (!empty($model['reasoning'])) $name .= ' 🧠';
            $availableModels[$id] = $name;
        }
    }
    // Add Anthropic models
    if (!empty($aiSettings['providers']['anthropic']['models'])) {
        foreach ($aiSettings['providers']['anthropic']['models'] as $id => $model) {
            $name = is_array($model) ? ($model['name'] ?? $id) : $model;
            if (!empty($model['recommended'])) $name .= ' ⭐';
            if (!empty($model['extended_thinking'])) $name .= ' 🧠';
            $availableModels[$id] = $name;
        }
    }
    // Add Gemini models
    if (!empty($aiSettings['providers']['google']['models'])) {
        foreach ($aiSettings['providers']['google']['models'] as $id => $model) {
            $name = is_array($model) ? ($model['name'] ?? $id) : $model;
            if (!empty($model['free_tier'])) $name .= ' 🆓';
            $availableModels[$id] = $name;
        }
    }
}
// Fallback if no models loaded
if (empty($availableModels)) {
    $availableModels = [
        'gpt-4.1-mini' => 'GPT-4.1 Mini',
        'claude-opus-4-5-20251101' => 'Claude Opus 4.5',
    ];
}

// Website types / Industries (mapped to ai-design-prompts.php)
$websiteTypes = [
    // Core
    'business' => ['icon' => '🏢', 'label' => 'Business'],
    'restaurant' => ['icon' => '🍽️', 'label' => 'Restaurant'],
    'technology' => ['icon' => '💻', 'label' => 'Technology'],
    'healthcare' => ['icon' => '🏥', 'label' => 'Healthcare'],
    'ecommerce' => ['icon' => '🛒', 'label' => 'E-commerce'],
    'professional_services' => ['icon' => '💼', 'label' => 'Professional Services'],
    // Beauty & Wellness
    'barber' => ['icon' => '💈', 'label' => 'Barbershop'],
    'salon' => ['icon' => '💇', 'label' => 'Hair Salon'],
    'spa' => ['icon' => '🧖', 'label' => 'Spa & Wellness'],
    'fitness' => ['icon' => '💪', 'label' => 'Fitness / Gym'],
    'yoga' => ['icon' => '🧘', 'label' => 'Yoga Studio'],
    // Food & Hospitality  
    'cafe' => ['icon' => '☕', 'label' => 'Cafe / Coffee'],
    'bar' => ['icon' => '🍸', 'label' => 'Bar / Cocktails'],
    'hotel' => ['icon' => '🏨', 'label' => 'Hotel'],
    'catering' => ['icon' => '🍴', 'label' => 'Catering'],
    'foodtruck' => ['icon' => '🚚', 'label' => 'Food Truck'],
    // Creative Services
    'photography' => ['icon' => '📷', 'label' => 'Photography'],
    'wedding' => ['icon' => '💒', 'label' => 'Wedding Planner'],
    'music' => ['icon' => '🎵', 'label' => 'Music / Band'],
    'tattoo' => ['icon' => '🎨', 'label' => 'Tattoo Studio'],
    'art' => ['icon' => '🖼️', 'label' => 'Art / Gallery'],
    // Professional
    'realestate' => ['icon' => '🏠', 'label' => 'Real Estate'],
    'finance' => ['icon' => '💰', 'label' => 'Finance'],
    'education' => ['icon' => '🎓', 'label' => 'Education'],
    'nonprofit' => ['icon' => '💚', 'label' => 'Non-Profit'],
    'automotive' => ['icon' => '🚗', 'label' => 'Automotive'],
    // Other
    'construction' => ['icon' => '🏗️', 'label' => 'Construction'],
    'blog' => ['icon' => '📝', 'label' => 'Blog'],
    'portfolio' => ['icon' => '🎯', 'label' => 'Portfolio'],
    'landing' => ['icon' => '🚀', 'label' => 'Landing Page']
];

// Design styles (mapped to ai-design-prompts.php 10 styles)
$designStyles = [
    'modern' => '✨ Modern & Clean',
    'corporate' => '🏛️ Corporate',
    'creative' => '🎨 Creative & Bold',
    'minimal' => '⬜ Minimal',
    'elegant' => '👑 Elegant',
    'vintage' => '📜 Vintage & Classic',
    'luxury' => '💎 Luxury',
    'bold' => '🔥 Bold & Dynamic',
    'organic' => '🌿 Organic & Natural',
    'industrial' => '🏭 Industrial'
];

// Image sources
$imageSources = [
    'pexels' => 'Pexels (Free)',
    'unsplash' => 'Unsplash (Free)',
    'media_library' => 'Media Library'
];

// Page templates
$pageTemplates = [
    'homepage' => ['icon' => '🏠', 'label' => 'Homepage', 'default' => true],
    'about' => ['icon' => '👋', 'label' => 'About Us', 'default' => true],
    'services' => ['icon' => '⚡', 'label' => 'Services', 'default' => true],
    'contact' => ['icon' => '📧', 'label' => 'Contact', 'default' => true],
    'blog' => ['icon' => '📝', 'label' => 'Blog'],
    'portfolio' => ['icon' => '🖼️', 'label' => 'Portfolio'],
    'pricing' => ['icon' => '💰', 'label' => 'Pricing'],
    'team' => ['icon' => '👥', 'label' => 'Team'],
    'faq' => ['icon' => '❓', 'label' => 'FAQ'],
    'testimonials' => ['icon' => '⭐', 'label' => 'Testimonials']
];
?>

<style>
/* ═══════════════════════════════════════════════════════════════════════════ */
/* AI THEME BUILDER 4.0 - CATPPUCCIN MOCHA THEME                               */
/* ═══════════════════════════════════════════════════════════════════════════ */

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
    --ctp-lavender: #b4befe;
    --ctp-sapphire: #74c7ec;
    --ctp-sky: #89dceb;
    --ctp-teal: #94e2d5;
    --ctp-green: #a6e3a1;
    --ctp-yellow: #f9e2af;
    --ctp-peach: #fab387;
    --ctp-maroon: #eba0ac;
    --ctp-red: #f38ba8;
    --ctp-mauve: #cba6f7;
    --ctp-pink: #f5c2e7;
    --ctp-flamingo: #f2cdcd;
    --ctp-rosewater: #f5e0dc;
}

.tb-wizard {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* ─── Wizard Header ─── */
.tb-wizard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--ctp-surface0);
}

.tb-wizard-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--ctp-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.tb-wizard-header .subtitle {
    color: var(--ctp-subtext0);
    font-size: 14px;
    margin-top: 4px;
}

.ai-badge {
    background: linear-gradient(135deg, var(--ctp-mauve), var(--ctp-pink));
    color: var(--ctp-crust);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* ─── Step Indicator ─── */
.step-indicator {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 40px;
}

.step-dot {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: var(--ctp-surface0);
    border-radius: 30px;
    color: var(--ctp-subtext0);
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
}

.step-dot .step-num {
    width: 26px;
    height: 26px;
    background: var(--ctp-surface1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

.step-dot.active {
    background: var(--ctp-blue);
    color: var(--ctp-crust);
}

.step-dot.active .step-num {
    background: rgba(0,0,0,0.2);
    color: var(--ctp-crust);
}

.step-dot.completed {
    background: var(--ctp-green);
    color: var(--ctp-crust);
}

.step-dot.completed .step-num {
    background: rgba(0,0,0,0.2);
}

.step-connector {
    width: 40px;
    height: 2px;
    background: var(--ctp-surface1);
    align-self: center;
}

.step-connector.completed {
    background: var(--ctp-green);
}

/* ─── Step Panels ─── */
.step-panel {
    display: none;
    animation: fadeIn 0.3s ease;
}

.step-panel.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ─── Form Card ─── */
.form-card {
    background: var(--ctp-surface0);
    border: 1px solid var(--ctp-surface1);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 24px;
}

.form-card-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--ctp-text);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-card-title .icon {
    font-size: 20px;
}

/* ─── Form Elements ─── */
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--ctp-subtext1);
    margin-bottom: 8px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    background: var(--ctp-base);
    border: 1px solid var(--ctp-surface1);
    border-radius: 10px;
    color: var(--ctp-text);
    font-size: 14px;
    transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--ctp-blue);
    box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.15);
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
    line-height: 1.6;
}

.form-group .hint {
    font-size: 11px;
    color: var(--ctp-overlay0);
    margin-top: 6px;
}

/* ─── Type Cards Grid ─── */
.type-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
}

.type-card {
    background: var(--ctp-base);
    border: 2px solid var(--ctp-surface1);
    border-radius: 14px;
    padding: 18px 14px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.type-card:hover {
    border-color: var(--ctp-blue);
    transform: translateY(-2px);
}

.type-card:has(input:checked) {
    border-color: var(--ctp-blue);
    background: rgba(137, 180, 250, 0.1);
}

.type-card input {
    display: none;
}

.type-card .icon {
    font-size: 32px;
    margin-bottom: 8px;
}

.type-card .label {
    font-size: 12px;
    font-weight: 500;
    color: var(--ctp-subtext1);
}

/* ─── Style Options ─── */
.style-options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.style-option {
    padding: 12px 18px;
    background: var(--ctp-base);
    border: 2px solid var(--ctp-surface1);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 13px;
    color: var(--ctp-subtext1);
}

.style-option:hover {
    border-color: var(--ctp-blue);
}

/* Generation Mode Toggle */
.generation-mode-toggle {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.mode-option {
    cursor: pointer;
}

.mode-option input {
    display: none;
}

.mode-card {
    background: var(--ctp-base);
    border: 2px solid var(--ctp-surface1);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    transition: all 0.2s ease;
}

.mode-option input:checked + .mode-card {
    border-color: var(--ctp-blue);
    background: rgba(137, 180, 250, 0.1);
}

.mode-icon {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
}

.mode-label {
    display: block;
    font-weight: 600;
    color: var(--ctp-text);
    margin-bottom: 4px;
}

.mode-desc {
    display: block;
    font-size: 11px;
    color: var(--ctp-overlay0);
}

.style-option:has(input:checked) {
    border-color: var(--ctp-blue);
    background: rgba(137, 180, 250, 0.1);
    color: var(--ctp-text);
}

.style-option input {
    display: none;
}

/* ─── Page Selection ─── */
.page-selection {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}

.page-item {
    background: var(--ctp-base);
    border: 2px solid var(--ctp-surface1);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.page-item:hover {
    border-color: var(--ctp-lavender);
}

.page-item:has(input:checked) {
    border-color: var(--ctp-lavender);
    background: rgba(180, 190, 254, 0.1);
}

.page-item input {
    display: none;
}

.page-item .icon {
    font-size: 24px;
    margin-bottom: 6px;
}

.page-item .label {
    font-size: 12px;
    font-weight: 500;
    color: var(--ctp-subtext1);
}

/* ─── Navigation Buttons ─── */
.wizard-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--ctp-surface0);
}

.btn {
    padding: 14px 28px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: none;
}

.btn-secondary {
    background: var(--ctp-surface0);
    color: var(--ctp-subtext1);
}

.btn-secondary:hover {
    background: var(--ctp-surface1);
    color: var(--ctp-text);
}

.btn-primary {
    background: linear-gradient(135deg, var(--ctp-blue), var(--ctp-sapphire));
    color: var(--ctp-crust);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(137, 180, 250, 0.3);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-success {
    background: linear-gradient(135deg, var(--ctp-green), var(--ctp-teal));
    color: var(--ctp-crust);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(166, 227, 161, 0.3);
}

/* ─── Generation Progress ─── */
.generation-progress {
    background: var(--ctp-surface0);
    border-radius: 16px;
    padding: 40px;
    text-align: center;
}

.progress-spinner {
    width: 80px;
    height: 80px;
    border: 4px solid var(--ctp-surface1);
    border-top-color: var(--ctp-blue);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 24px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.progress-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--ctp-text);
    margin-bottom: 12px;
}

.progress-status {
    color: var(--ctp-subtext0);
    font-size: 14px;
    margin-bottom: 24px;
}

.progress-steps {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 400px;
    margin: 0 auto;
    text-align: left;
}

.progress-step {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: var(--ctp-base);
    border-radius: 10px;
    font-size: 14px;
    color: var(--ctp-subtext0);
}

.progress-step.active {
    color: var(--ctp-blue);
    background: rgba(137, 180, 250, 0.1);
}

.progress-step.completed {
    color: var(--ctp-green);
}

.progress-step .check {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--ctp-surface1);
    font-size: 12px;
}

.progress-step.completed .check {
    background: var(--ctp-green);
    color: var(--ctp-crust);
}

.progress-step.active .check {
    background: var(--ctp-blue);
    color: var(--ctp-crust);
    animation: pulse 1s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* ─── Preview Panel ─── */
.preview-panel {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
}

.preview-card {
    background: var(--ctp-surface0);
    border: 1px solid var(--ctp-surface1);
    border-radius: 16px;
    overflow: hidden;
}

.preview-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: var(--ctp-base);
    border-bottom: 1px solid var(--ctp-surface1);
}

.preview-card-header h3 {
    font-size: 14px;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.preview-card-header .badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.badge-header { background: var(--ctp-blue); color: var(--ctp-crust); }
.badge-page { background: var(--ctp-lavender); color: var(--ctp-crust); }
.badge-footer { background: var(--ctp-mauve); color: var(--ctp-crust); }

.preview-card-body {
    padding: 20px;
    min-height: 250px;
    max-height: 400px;
    overflow-y: auto;
}

.preview-card-actions {
    display: flex;
    gap: 10px;
    padding: 16px 20px;
    background: var(--ctp-base);
    border-top: 1px solid var(--ctp-surface1);
}

.preview-card-actions .btn {
    flex: 1;
    justify-content: center;
    padding: 10px 16px;
    font-size: 13px;
}

/* Preview content styling */
.preview-content {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    line-height: 1.5;
}

.preview-content .section {
    padding: 16px;
    background: var(--ctp-base);
    border-radius: 8px;
    margin-bottom: 12px;
}

.preview-content .module {
    padding: 8px;
    background: rgba(137, 180, 250, 0.05);
    border: 1px dashed var(--ctp-surface1);
    border-radius: 6px;
    margin-bottom: 8px;
    font-size: 11px;
    color: var(--ctp-subtext0);
}

/* ─── Deploy Panel ─── */
.deploy-summary {
    background: var(--ctp-surface0);
    border-radius: 16px;
    padding: 32px;
}

.deploy-summary-header {
    text-align: center;
    margin-bottom: 32px;
}

.deploy-summary-header .icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.deploy-summary-header h2 {
    font-size: 24px;
    font-weight: 700;
    color: var(--ctp-text);
    margin: 0 0 8px 0;
}

.deploy-summary-header p {
    color: var(--ctp-subtext0);
    font-size: 14px;
}

.deploy-items {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.deploy-item {
    background: var(--ctp-base);
    border: 1px solid var(--ctp-surface1);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}

.deploy-item .icon {
    font-size: 28px;
    margin-bottom: 10px;
}

.deploy-item .label {
    font-size: 14px;
    font-weight: 500;
    color: var(--ctp-text);
    margin-bottom: 4px;
}

.deploy-item .count {
    font-size: 12px;
    color: var(--ctp-subtext0);
}

.deploy-options {
    background: var(--ctp-base);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.deploy-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid var(--ctp-surface0);
}

.deploy-option:last-child {
    border-bottom: none;
}

.deploy-option input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--ctp-blue);
}

.deploy-option label {
    font-size: 14px;
    color: var(--ctp-text);
    cursor: pointer;
}

.deploy-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
}

.deploy-actions .btn {
    min-width: 180px;
    justify-content: center;
}

/* ─── Success Panel ─── */
.success-panel {
    text-align: center;
    padding: 60px 40px;
    background: var(--ctp-surface0);
    border-radius: 20px;
}

.success-icon {
    font-size: 80px;
    margin-bottom: 24px;
}

.success-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--ctp-text);
    margin-bottom: 12px;
}

.success-message {
    color: var(--ctp-subtext0);
    font-size: 16px;
    margin-bottom: 32px;
}

.success-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
}

/* ─── Responsive ─── */
@media (max-width: 768px) {
    .tb-wizard {
        padding: 16px;
    }

    .step-indicator {
        flex-wrap: wrap;
    }

    .step-connector {
        display: none;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .preview-panel {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="tb-wizard">
    <!-- Header -->
    <div class="tb-wizard-header">
        <div>
            <a href="/admin/themes" style="color: var(--ctp-subtext0); text-decoration: none; font-size: 13px; display: inline-block; margin-bottom: 8px;">
                ← Back to Themes
            </a>
            <h1>✨ AI Theme Builder 4.0</h1>
            <p class="subtitle">Create professional TB 3.0 layouts with AI - Header, Pages, and Footer</p>
        </div>
        <span class="ai-badge">🤖 AI Powered</span>
    </div>

    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step-dot active" data-step="1">
            <span class="step-num">1</span>
            <span>Brief</span>
        </div>
        <div class="step-connector"></div>
        <div class="step-dot" data-step="2">
            <span class="step-num">2</span>
            <span>Generate</span>
        </div>
        <div class="step-connector"></div>
        <div class="step-dot" data-step="3">
            <span class="step-num">3</span>
            <span>Preview</span>
        </div>
        <div class="step-connector"></div>
        <div class="step-dot" data-step="4">
            <span class="step-num">4</span>
            <span>Deploy</span>
        </div>
    </div>

    <form id="theme-wizard-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

        <!-- ═══ STEP 1: BRIEF ═══ -->
        <div class="step-panel active" data-panel="1">
            <!-- Basic Info -->
            <div class="form-card">
                <div class="form-card-title">
                    <span class="icon">📋</span>
                    Project Details
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="project_name">Project Name *</label>
                        <input type="text" id="project_name" name="project_name" placeholder="my-awesome-website" required pattern="[a-z0-9\-]+" title="Lowercase letters, numbers and hyphens only">
                        <div class="hint">Lowercase, no spaces. Used for file naming.</div>
                    </div>
                    <div class="form-group">
                        <label for="brand_name">Brand / Company Name</label>
                        <input type="text" id="brand_name" name="brand_name" placeholder="Acme Corporation">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Describe Your Website *</label>
                    <textarea id="description" name="description" placeholder="E.g., A modern tech startup website showcasing our AI-powered analytics platform. Target audience is B2B enterprise clients. Should convey innovation, trust, and cutting-edge technology. Key sections needed: hero with product demo, features grid, testimonials from Fortune 500 clients, pricing tiers, and contact form." required></textarea>
                    <div class="hint">Be detailed! Include your industry, target audience, brand personality, key selling points, and specific sections you need.</div>
                </div>
            </div>

            <!-- Website Type -->
            <div class="form-card">
                <div class="form-card-title">
                    <span class="icon">🌐</span>
                    Industry / Website Type
                </div>

                <div class="type-cards">
                    <?php foreach ($websiteTypes as $value => $type): ?>
                    <label class="type-card">
                        <input type="radio" name="industry" value="<?php echo $value; ?>" <?php echo $value === 'business' ? 'checked' : ''; ?>>
                        <div class="icon"><?php echo $type['icon']; ?></div>
                        <div class="label"><?php echo $type['label']; ?></div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Design Style -->
            <div class="form-card">
                <div class="form-card-title">
                    <span class="icon">🎨</span>
                    Design Style
                </div>

                <div class="style-options">
                    <?php foreach ($designStyles as $value => $label): ?>
                    <label class="style-option">
                        <input type="radio" name="design_style" value="<?php echo $value; ?>" <?php echo $value === 'modern' ? 'checked' : ''; ?>>
                        <?php echo $label; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pages to Generate -->
            <div class="form-card">
                <div class="form-card-title">
                    <span class="icon">📄</span>
                    Pages to Generate
                </div>

                <div class="page-selection">
                    <?php foreach ($pageTemplates as $value => $page): ?>
                    <label class="page-item">
                        <input type="checkbox" name="pages[]" value="<?php echo $value; ?>" <?php echo isset($page['default']) && $page['default'] ? 'checked' : ''; ?>>
                        <div class="icon"><?php echo $page['icon']; ?></div>
                        <div class="label"><?php echo $page['label']; ?></div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- AI Settings -->
            <div class="form-card">
                <div class="form-card-title">
                    <span class="icon">🤖</span>
                    AI Settings
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ai_model">AI Model</label>
                        <select id="ai_model" name="ai_model">
                            <?php foreach ($availableModels as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo $value === 'gpt-4o' ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image_source">Image Source</label>
                        <select id="image_source" name="image_source">
                            <?php foreach ($imageSources as $value => $label): ?>
                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Generation Mode Toggle -->
                <div class="form-group" style="margin-top: 20px;">
                    <label>Generation Mode</label>
                    <div class="generation-mode-toggle">
                        <label class="mode-option">
                            <input type="radio" name="generation_mode" value="direct" checked>
                            <div class="mode-card">
                                <span class="mode-icon">⚡</span>
                                <span class="mode-label">Direct JSON</span>
                                <span class="mode-desc">AI generates TB modules directly</span>
                            </div>
                        </label>
                        <label class="mode-option">
                            <input type="radio" name="generation_mode" value="html">
                            <div class="mode-card">
                                <span class="mode-icon">🎨</span>
                                <span class="mode-label">HTML Converter</span>
                                <span class="mode-desc">AI generates HTML → auto-convert to TB</span>
                            </div>
                        </label>
                    </div>
                    <div class="hint">HTML Converter mode gives more creative freedom but may need adjustments.</div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="wizard-nav">
                <div></div>
                <button type="button" class="btn btn-primary" onclick="startGeneration()">
                    🚀 Generate Theme
                    <span>→</span>
                </button>
            </div>
        </div>

        <!-- ═══ STEP 2: GENERATE ═══ -->
        <div class="step-panel" data-panel="2">
            <div class="generation-progress" id="generation-progress">
                <div class="progress-spinner"></div>
                <div class="progress-title">Generating Your Theme...</div>
                <div class="progress-status" id="progress-status">Initializing AI...</div>

                <div class="progress-steps">
                    <div class="progress-step" id="step-header">
                        <span class="check">1</span>
                        <span>Generating Header Template</span>
                    </div>
                    <div class="progress-step" id="step-pages">
                        <span class="check">2</span>
                        <span>Generating Page Layouts</span>
                    </div>
                    <div class="progress-step" id="step-footer">
                        <span class="check">3</span>
                        <span>Generating Footer Template</span>
                    </div>
                    <div class="progress-step" id="step-images">
                        <span class="check">4</span>
                        <span>Fetching Images</span>
                    </div>
                </div>
            </div>

            <div class="wizard-nav" id="generate-nav" style="display: none;">
                <button type="button" class="btn btn-secondary" onclick="goToStep(1)">
                    <span>←</span>
                    Back to Brief
                </button>
                <button type="button" class="btn btn-primary" onclick="goToStep(3)">
                    Continue to Preview
                    <span>→</span>
                </button>
            </div>
        </div>

        <!-- ═══ STEP 3: PREVIEW ═══ -->
        <div class="step-panel" data-panel="3">
            <div class="preview-panel" id="preview-panel">
                <!-- Header Preview -->
                <div class="preview-card">
                    <div class="preview-card-header">
                        <h3>🔝 Header Template</h3>
                        <span class="badge badge-header">TB 3.0</span>
                    </div>
                    <div class="preview-card-body" id="preview-header">
                        <div class="preview-content">
                            <p style="color: var(--ctp-subtext0); text-align: center; padding: 40px;">
                                Header preview will appear here after generation
                            </p>
                        </div>
                    </div>
                    <div class="preview-card-actions">
                        <button type="button" class="btn btn-secondary" onclick="regenerateComponent('header')">
                            🔄 Regenerate
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="editComponent('header')">
                            ✏️ Edit TB3
                        </button>
                        <button type="button" class="btn btn-primary" onclick="openInJTB('header')" title="Edit in Jessie Theme Builder">
                            🎨 Edit in JTB
                        </button>
                    </div>
                </div>

                <!-- Pages Preview -->
                <div class="preview-card" id="pages-preview-card">
                    <div class="preview-card-header">
                        <h3>📄 Page Layouts</h3>
                        <span class="badge badge-page" id="pages-count">0 pages</span>
                    </div>
                    <div class="preview-card-body" id="preview-pages">
                        <div class="preview-content">
                            <p style="color: var(--ctp-subtext0); text-align: center; padding: 40px;">
                                Page layouts will appear here after generation
                            </p>
                        </div>
                    </div>
                    <div class="preview-card-actions">
                        <button type="button" class="btn btn-secondary" onclick="regenerateComponent('pages')">
                            🔄 Regenerate All
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="editComponent('pages')">
                            ✏️ Edit TB3
                        </button>
                        <button type="button" class="btn btn-primary" onclick="openInJTB('pages', 0)" title="Edit first page in Jessie Theme Builder">
                            🎨 Edit in JTB
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="openFullPreview(0)">
                            🔍 Preview
                        </button>
                    </div>
                </div>

                <!-- Footer Preview -->
                <div class="preview-card">
                    <div class="preview-card-header">
                        <h3>🔻 Footer Template</h3>
                        <span class="badge badge-footer">TB 3.0</span>
                    </div>
                    <div class="preview-card-body" id="preview-footer">
                        <div class="preview-content">
                            <p style="color: var(--ctp-subtext0); text-align: center; padding: 40px;">
                                Footer preview will appear here after generation
                            </p>
                        </div>
                    </div>
                    <div class="preview-card-actions">
                        <button type="button" class="btn btn-secondary" onclick="regenerateComponent('footer')">
                            🔄 Regenerate
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="editComponent('footer')">
                            ✏️ Edit TB3
                        </button>
                        <button type="button" class="btn btn-primary" onclick="openInJTB('footer')" title="Edit in Jessie Theme Builder">
                            🎨 Edit in JTB
                        </button>
                    </div>
                </div>
            </div>

            <div class="wizard-nav">
                <button type="button" class="btn btn-secondary" onclick="goToStep(2)">
                    <span>←</span>
                    Back
                </button>
                <button type="button" class="btn btn-primary" onclick="goToStep(4)">
                    Continue to Deploy
                    <span>→</span>
                </button>
            </div>
        </div>

        <!-- ═══ STEP 4: DEPLOY ═══ -->
        <div class="step-panel" data-panel="4">
            <div class="deploy-summary">
                <div class="deploy-summary-header">
                    <div class="icon">🚀</div>
                    <h2>Ready to Deploy</h2>
                    <p>Review your generated theme components and deploy to your CMS</p>
                </div>

                <div class="deploy-items" id="deploy-items">
                    <div class="deploy-item">
                        <div class="icon">🔝</div>
                        <div class="label">Header Template</div>
                        <div class="count">1 template</div>
                    </div>
                    <div class="deploy-item">
                        <div class="icon">📄</div>
                        <div class="label">Page Layouts</div>
                        <div class="count" id="deploy-pages-count">0 layouts</div>
                    </div>
                    <div class="deploy-item">
                        <div class="icon">🔻</div>
                        <div class="label">Footer Template</div>
                        <div class="count">1 template</div>
                    </div>
                </div>

                <div class="deploy-options">
                    <div class="deploy-option">
                        <input type="checkbox" id="save_to_templates" name="save_to_templates" checked>
                        <label for="save_to_templates">Save Header/Footer to TB Templates (tb_templates table)</label>
                    </div>
                    <div class="deploy-option">
                        <input type="checkbox" id="save_to_library" name="save_to_library" checked>
                        <label for="save_to_library">Save Page Layouts to Layout Library (tb_layout_library table)</label>
                    </div>
                    <div class="deploy-option">
                        <input type="checkbox" id="create_pages" name="create_pages">
                        <label for="create_pages">Create actual CMS pages from layouts</label>
                    </div>
                </div>

                <div class="deploy-actions">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(3)">
                        <span>←</span>
                        Back to Preview
                    </button>
                    <button type="submit" class="btn btn-success" id="deploy-btn">
                        <span>🚀</span>
                        Deploy Theme
                    </button>
                </div>
            </div>
        </div>

        <!-- ═══ SUCCESS PANEL ═══ -->
        <div class="step-panel" data-panel="success" id="success-panel">
            <div class="success-panel">
                <div class="success-icon">🎉</div>
                <h2 class="success-title">Theme Deployed Successfully!</h2>
                <p class="success-message" id="success-message">Your theme components have been saved.</p>

                <div class="success-actions">
                    <a href="/admin/theme-builder/templates" class="btn btn-secondary">
                        📁 View Templates
                    </a>
                    <a href="/admin/layout-library" class="btn btn-secondary">
                        📚 View Library
                    </a>
                    <button type="button" class="btn btn-primary" onclick="startNewTheme()">
                        ✨ Create Another Theme
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// ═══════════════════════════════════════════════════════════════════════════
// AI THEME BUILDER 4.0 - JAVASCRIPT
// ═══════════════════════════════════════════════════════════════════════════

let currentStep = 1;
let generatedData = {
    header: null,
    pages: [],
    footer: null
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('AI Theme Builder 4.0 initialized');
});

// ─── Step Navigation ───
function goToStep(step) {
    // Validate before proceeding
    if (step > currentStep) {
        if (currentStep === 1 && !validateStep1()) return;
        if (currentStep === 2 && !generatedData.header) {
            startGeneration();
            return;
        }
    }

    currentStep = step;
    updateStepIndicator();
    showPanel(step);
}

function updateStepIndicator() {
    document.querySelectorAll('.step-dot').forEach((dot, index) => {
        const stepNum = index + 1;
        dot.classList.remove('active', 'completed');

        if (stepNum < currentStep) {
            dot.classList.add('completed');
        } else if (stepNum === currentStep) {
            dot.classList.add('active');
        }
    });

    document.querySelectorAll('.step-connector').forEach((conn, index) => {
        conn.classList.toggle('completed', index < currentStep - 1);
    });
}

function showPanel(step) {
    document.querySelectorAll('.step-panel').forEach(panel => {
        panel.classList.remove('active');
    });

    const targetPanel = document.querySelector(`[data-panel="${step}"]`);
    if (targetPanel) {
        targetPanel.classList.add('active');
    }
}

// ─── Validation ───
function validateStep1() {
    const projectName = document.getElementById('project_name').value.trim();
    const description = document.getElementById('description').value.trim();
    const selectedPages = document.querySelectorAll('input[name="pages[]"]:checked');

    if (!projectName) {
        alert('Please enter a project name');
        document.getElementById('project_name').focus();
        return false;
    }

    if (!description) {
        alert('Please describe your website');
        document.getElementById('description').focus();
        return false;
    }

    if (selectedPages.length === 0) {
        alert('Please select at least one page to generate');
        return false;
    }

    return true;
}

// ─── Generation ───
function getSelectedPages() {
    const checkboxes = document.querySelectorAll('input[name="pages[]"]:checked');
    return Array.from(checkboxes).map(cb => {
        const label = cb.closest('.page-item')?.querySelector('.label')?.textContent || cb.value;
        return label;
    });
}

async function startGeneration() {
    if (!validateStep1()) return;

    currentStep = 2;
    updateStepIndicator();
    showPanel(2);

    // Reset progress
    document.querySelectorAll('.progress-step').forEach(step => {
        step.classList.remove('active', 'completed');
    });

    const formData = new FormData(document.getElementById('theme-wizard-form'));
    const generationMode = formData.get('generation_mode') || 'direct';

    try {
        // Check generation mode
        if (generationMode === 'html') {
            // HTML Converter mode - single API call
            updateProgress('step-header', 'active', 'Generating HTML design...');
            updateProgress('step-pages', 'active');
            updateProgress('step-footer', 'active');
            
            const response = await fetch('/admin/ai-theme-builder/generate-html', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    brief: formData.get('description'),
                    business_name: formData.get('brand_name') || 'My Business',
                    industry: formData.get('industry') || 'general',
                    style_preference: formData.get('design_style') || 'modern',
                    model: formData.get('ai_model') || '',
                    page_names: getSelectedPages(),
                    sections: ['hero', 'features', 'about', 'testimonials', 'cta', 'contact'],
                    csrf_token: formData.get('csrf_token')
                })
            });
            
            const data = await response.json();
            console.log('HTML Generation response:', data);
            
            if (data.success && data.theme) {
                generatedData = data.theme;
                updateProgress('step-header', 'completed');
                updateProgress('step-pages', 'completed');
                updateProgress('step-footer', 'completed');
                updateProgress('step-images', 'completed', 'Generation complete!');
                
                document.getElementById('generate-nav').style.display = 'flex';
                renderPreviews();
                return;
            } else {
                throw new Error(data.error || 'HTML generation failed');
            }
        }

        // Direct JSON mode (original logic)
        // Step 1: Generate Header
        updateProgress('step-header', 'active', 'Generating header template...');
        const headerResponse = await fetch('/admin/ai-theme-builder/generate-header', {
            method: 'POST',
            body: formData
        });
        const headerData = await headerResponse.json();
        console.log('Header response:', headerData);
        if (headerData.success) {
            generatedData.header = headerData.layout;
            updateProgress('step-header', 'completed');
        } else {
            throw new Error(headerData.error || 'Failed to generate header');
        }

        // Step 2: Generate Pages
        updateProgress('step-pages', 'active', 'Generating page layouts...');
        const pagesResponse = await fetch('/admin/ai-theme-builder/generate-pages', {
            method: 'POST',
            body: formData
        });
        const pagesData = await pagesResponse.json();
        console.log('Pages response:', pagesData);
        if (pagesData.success) {
            generatedData.pages = pagesData.layouts;
            updateProgress('step-pages', 'completed');
        } else {
            throw new Error(pagesData.error || 'Failed to generate pages');
        }

        // Step 3: Generate Footer
        updateProgress('step-footer', 'active', 'Generating footer template...');
        const footerResponse = await fetch('/admin/ai-theme-builder/generate-footer', {
            method: 'POST',
            body: formData
        });
        const footerData = await footerResponse.json();
        console.log('Footer response:', footerData);
        if (footerData.success) {
            generatedData.footer = footerData.layout;
            updateProgress('step-footer', 'completed');
        } else {
            throw new Error(footerData.error || 'Failed to generate footer');
        }

        // Step 4: Fetch images from selected source
        updateProgress('step-images', 'active', 'Fetching images...');
        const imageFormData = new FormData();
        imageFormData.append('generated_data', JSON.stringify(generatedData));
        imageFormData.append('image_source', formData.get('image_source') || 'unsplash');
        imageFormData.append('csrf_token', formData.get('csrf_token'));
        
        const imagesResponse = await fetch('/admin/ai-theme-builder/fetch-images', {
            method: 'POST',
            body: imageFormData
        });
        const imagesData = await imagesResponse.json();
        console.log('Images response:', imagesData);
        
        if (imagesData.success && imagesData.theme) {
            // Update generated data with images
            generatedData = imagesData.theme;
        }
        updateProgress('step-images', 'completed', 'Generation complete!');

        // Update previews
        updatePreviews();

        // Show navigation
        document.getElementById('generate-nav').style.display = 'flex';

        // Auto-advance after a moment
        setTimeout(() => goToStep(3), 1000);

    } catch (error) {
        console.error('Generation error:', error);
        document.getElementById('progress-status').textContent = 'Error: ' + error.message;
        document.getElementById('progress-status').style.color = 'var(--ctp-red)';
    }
}

function updateProgress(stepId, status, message) {
    const step = document.getElementById(stepId);
    if (step) {
        step.classList.remove('active', 'completed');
        step.classList.add(status);
    }

    if (message) {
        document.getElementById('progress-status').textContent = message;
    }
}

// ─── Preview Updates ───
function updatePreviews() {
    console.log('Generated data for preview:', generatedData);
    
    // Header preview
    if (generatedData.header) {
        document.getElementById('preview-header').innerHTML = renderLayoutPreview(generatedData.header);
    }

    // Pages preview
    if (generatedData.pages && generatedData.pages.length > 0) {
        let pagesHtml = '';
        generatedData.pages.forEach((page, index) => {
            pagesHtml += `<div style="margin-bottom: 16px;">
                <div style="font-weight: 600; margin-bottom: 8px; color: var(--ctp-lavender);">
                    ${page.title || page.name || 'Page ' + (index + 1)}
                </div>
                ${renderLayoutPreview(page)}
            </div>`;
        });
        document.getElementById('preview-pages').innerHTML = pagesHtml;
        document.getElementById('pages-count').textContent = generatedData.pages.length + ' pages';
        document.getElementById('deploy-pages-count').textContent = generatedData.pages.length + ' layouts';
    }

    // Footer preview
    if (generatedData.footer) {
        document.getElementById('preview-footer').innerHTML = renderLayoutPreview(generatedData.footer);
    }
}

function renderLayoutPreview(layout) {
    // Handle both structures: {sections: [...]} and {content: {sections: [...]}}
    const sections = layout?.content?.sections || layout?.sections;
    
    if (!layout || !sections || sections.length === 0) {
        return '<p style="color: var(--ctp-subtext0);">No layout data</p>';
    }

    let html = '<div class="preview-content">';

    sections.forEach(section => {
        html += `<div class="section" style="background: ${section.settings?.background_color || 'var(--ctp-base)'};">`;
        html += `<div style="font-size: 10px; color: var(--ctp-overlay0); margin-bottom: 8px;">Section: ${section.type || 'standard'}</div>`;

        if (section.rows) {
            section.rows.forEach(row => {
                html += '<div style="display: flex; gap: 8px; margin-bottom: 8px;">';
                if (row.columns) {
                    row.columns.forEach(column => {
                        html += `<div style="flex: 1; padding: 8px; background: var(--ctp-surface0); border-radius: 4px;">`;
                        if (column.modules) {
                            column.modules.forEach(module => {
                                html += `<div class="module">${module.type || 'module'}</div>`;
                            });
                        }
                        html += '</div>';
                    });
                }
                html += '</div>';
            });
        }

        html += '</div>';
    });

    html += '</div>';
    return html;
}

// ─── Component Actions ───
async function regenerateComponent(component) {
    const formData = new FormData(document.getElementById('theme-wizard-form'));
    formData.append('regenerate', component);

    try {
        const response = await fetch(`/admin/ai-theme-builder/generate-${component}`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            if (component === 'header') {
                generatedData.header = data.layout;
            } else if (component === 'footer') {
                generatedData.footer = data.layout;
            } else if (component === 'pages') {
                generatedData.pages = data.layouts;
            }
            updatePreviews();
        } else {
            alert('Error: ' + (data.error || 'Failed to regenerate'));
        }
    } catch (error) {
        alert('Network error: ' + error.message);
    }
}

function editComponent(component) {
    // Open editor modal or redirect to editor
    console.log('Edit component:', component);
    alert('Editor coming soon! For now, you can edit in the Theme Builder after deployment.');
}

// ─── Open in JTB (Jessie Theme Builder) ───
async function openInJTB(component, pageIndex = 0) {
    let sourceHtml = null;
    let title = 'AI Generated Layout';

    // Get source HTML based on component type
    if (component === 'header' && generatedData.header) {
        sourceHtml = generatedData.header.source_html;
        title = 'Header - ' + (generatedData.header.title || 'AI Generated');
    } else if (component === 'footer' && generatedData.footer) {
        sourceHtml = generatedData.footer.source_html;
        title = 'Footer - ' + (generatedData.footer.title || 'AI Generated');
    } else if (component === 'pages' && generatedData.pages && generatedData.pages[pageIndex]) {
        sourceHtml = generatedData.pages[pageIndex].source_html;
        title = generatedData.pages[pageIndex].title || 'Page ' + (pageIndex + 1);
    }

    // If no source_html (Direct JSON mode), convert TB JSON to HTML for JTB
    if (!sourceHtml) {
        alert('This layout was generated in Direct JSON mode. Source HTML is only available in HTML Converter mode.\n\nSwitch to "HTML Converter" mode in AI Settings to use JTB editing.');
        return;
    }

    try {
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

        // First, parse the HTML using JTB parser
        const formData = new FormData();
        formData.append('html', sourceHtml);
        formData.append('csrf_token', csrfToken);

        const parseResponse = await fetch('/api/jtb/parse-html', {
            method: 'POST',
            body: formData
        });

        const parseResult = await parseResponse.json();

        if (!parseResult.success) {
            throw new Error(parseResult.error || 'Failed to parse HTML');
        }

        // Store parsed content in localStorage for JTB to pick up
        // (localStorage is shared between tabs of the same domain)
        const jtbData = {
            title: title,
            content: parseResult.data.content,
            source: 'ai-theme-builder',
            timestamp: Date.now()
        };
        localStorage.setItem('jtb_import_data', JSON.stringify(jtbData));

        // Open JTB Builder with import flag
        // Using post_id=0 means "new" - JTB will check for sessionStorage data
        window.open('/admin/jessie-theme-builder/edit/0?import=ai-theme-builder', '_blank');

    } catch (error) {
        console.error('JTB open error:', error);
        alert('Error opening in JTB: ' + error.message);
    }
}

// ─── Preview in New Tab ───
async function openFullPreview(pageIndex = 0) {
    if (!generatedData.pages || generatedData.pages.length === 0) {
        alert('No pages generated yet. Please generate a theme first.');
        return;
    }

    try {
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        const projectName = document.querySelector('input[name="project_name"]')?.value || 'AI Theme';
        
        const response = await fetch('/admin/ai-theme-builder/preview-new-tab', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                csrf_token: csrfToken,
                header: generatedData.header,
                pages: generatedData.pages,
                footer: generatedData.footer,
                project_name: projectName
            })
        });
        
        const data = await response.json();
        
        if (data.success && data.preview_url) {
            window.open(data.preview_url + '&page=' + pageIndex, '_blank');
        } else {
            alert('Error: ' + (data.error || 'Failed to create preview'));
        }
    } catch (error) {
        console.error('Preview error:', error);
        alert('Network error: ' + error.message);
    }
}

// ─── Deploy ───
document.getElementById('theme-wizard-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('deploy-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="progress-spinner" style="width: 16px; height: 16px; border-width: 2px;"></span> Deploying...';

    const formData = new FormData(this);
    formData.append('generated_data', JSON.stringify(generatedData));

    try {
        const response = await fetch('/admin/ai-theme-builder/deploy', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        console.log('Deploy response:', data);

        if (data.success) {
            document.getElementById('success-message').textContent = data.message || 'Your theme has been deployed successfully!';
            showPanel('success');
        } else {
            alert('Error: ' + (data.error || 'Failed to deploy'));
            btn.disabled = false;
            btn.innerHTML = '<span>🚀</span> Deploy Theme';
        }
    } catch (error) {
        alert('Network error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<span>🚀</span> Deploy Theme';
    }
});

function startNewTheme() {
    generatedData = { header: null, pages: [], footer: null };
    document.getElementById('theme-wizard-form').reset();
    currentStep = 1;
    updateStepIndicator();
    showPanel(1);
}

// ─── Render Previews (HTML Converter Mode) ───
function renderPreviews() {
    console.log('Rendering previews with generatedData:', generatedData);

    // Header preview
    if (generatedData.header) {
        const headerPreview = document.getElementById('preview-header');
        if (generatedData.header.source_html) {
            // Show HTML preview in iframe or sanitized div
            headerPreview.innerHTML = `<div class="preview-content">
                <div style="font-size: 10px; color: var(--ctp-overlay0); margin-bottom: 8px;">HTML Preview (${generatedData.header.title || 'Header'})</div>
                <div style="border: 1px solid var(--ctp-surface1); border-radius: 8px; padding: 12px; background: #fff; color: #333; font-size: 11px; overflow: auto; max-height: 300px;">
                    ${sanitizeHtmlPreview(generatedData.header.source_html)}
                </div>
            </div>`;
        } else {
            headerPreview.innerHTML = renderLayoutPreview(generatedData.header);
        }
    }

    // Pages preview
    if (generatedData.pages && generatedData.pages.length > 0) {
        let pagesHtml = '';
        generatedData.pages.forEach((page, index) => {
            const pageTitle = page.title || page.name || 'Page ' + (index + 1);
            pagesHtml += `<div style="margin-bottom: 16px;">
                <div style="font-weight: 600; margin-bottom: 8px; color: var(--ctp-lavender);">
                    ${pageTitle}
                    ${page.source_html ? '<span style="font-size: 10px; color: var(--ctp-green); margin-left: 8px;">HTML Ready</span>' : ''}
                </div>`;

            if (page.source_html) {
                pagesHtml += `<div style="border: 1px solid var(--ctp-surface1); border-radius: 8px; padding: 12px; background: #fff; color: #333; font-size: 11px; overflow: auto; max-height: 200px;">
                    ${sanitizeHtmlPreview(page.source_html)}
                </div>`;
            } else {
                pagesHtml += renderLayoutPreview(page);
            }

            pagesHtml += '</div>';
        });
        document.getElementById('preview-pages').innerHTML = pagesHtml;
        document.getElementById('pages-count').textContent = generatedData.pages.length + ' pages';
        document.getElementById('deploy-pages-count').textContent = generatedData.pages.length + ' layouts';
    }

    // Footer preview
    if (generatedData.footer) {
        const footerPreview = document.getElementById('preview-footer');
        if (generatedData.footer.source_html) {
            footerPreview.innerHTML = `<div class="preview-content">
                <div style="font-size: 10px; color: var(--ctp-overlay0); margin-bottom: 8px;">HTML Preview (${generatedData.footer.title || 'Footer'})</div>
                <div style="border: 1px solid var(--ctp-surface1); border-radius: 8px; padding: 12px; background: #fff; color: #333; font-size: 11px; overflow: auto; max-height: 300px;">
                    ${sanitizeHtmlPreview(generatedData.footer.source_html)}
                </div>
            </div>`;
        } else {
            footerPreview.innerHTML = renderLayoutPreview(generatedData.footer);
        }
    }

    // Auto-advance to preview
    setTimeout(() => goToStep(3), 500);
}

// Sanitize HTML for safe preview display
function sanitizeHtmlPreview(html) {
    // Simple sanitization - escape script tags and show shortened HTML
    const escaped = html
        .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '[script removed]')
        .replace(/<style[^>]*>[\s\S]*?<\/style>/gi, '[styles]')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    // Truncate if too long
    const maxLength = 2000;
    if (escaped.length > maxLength) {
        return escaped.substring(0, maxLength) + '\n... [truncated]';
    }
    return escaped;
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
