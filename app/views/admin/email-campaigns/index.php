<?php
/**
 * AI Email Campaigns - Main View
 * Modern Catppuccin dark theme with AI model selector
 */
$title = 'AI Email Campaigns';
ob_start();
?>

<style>
/* Catppuccin color variables */
:root {
    --ctp-rosewater: #f5e0dc;
    --ctp-flamingo: #f2cdcd;
    --ctp-pink: #f5c2e7;
    --ctp-mauve: #cba6f7;
    --ctp-red: #f38ba8;
    --ctp-maroon: #eba0ac;
    --ctp-peach: #fab387;
    --ctp-yellow: #f9e2af;
    --ctp-green: #a6e3a1;
    --ctp-teal: #94e2d5;
    --ctp-sky: #89dceb;
    --ctp-sapphire: #74c7ec;
    --ctp-blue: #89b4fa;
    --ctp-lavender: #b4befe;
    --ctp-text: #cdd6f4;
    --ctp-subtext1: #bac2de;
    --ctp-subtext0: #a6adc8;
    --ctp-overlay2: #9399b2;
    --ctp-overlay1: #7f849c;
    --ctp-overlay0: #6c7086;
    --ctp-surface2: #585b70;
    --ctp-surface1: #45475a;
    --ctp-surface0: #313244;
    --ctp-base: #1e1e2e;
    --ctp-mantle: #181825;
    --ctp-crust: #11111b;
}

/* Page header */
.page-header-custom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--ctp-surface0);
}
.page-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.page-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--ctp-mauve), var(--ctp-pink));
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}
.page-title-group h1 {
    font-size: 26px;
    font-weight: 700;
    color: var(--ctp-text);
    margin: 0 0 4px 0;
}
.page-title-group p {
    font-size: 14px;
    color: var(--ctp-subtext0);
    margin: 0;
}
.stats-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: var(--ctp-surface0);
    border-radius: 20px;
    font-size: 13px;
    color: var(--ctp-subtext1);
}

/* Main layout - two column grid */
.campaigns-layout {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 24px;
    margin-bottom: 32px;
}
@media (max-width: 1200px) {
    .campaigns-layout {
        grid-template-columns: 1fr;
    }
}

/* Panel styling */
.panel {
    background: var(--ctp-base);
    border: 1px solid var(--ctp-surface0);
    border-radius: 16px;
    overflow: hidden;
}
.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: var(--ctp-mantle);
    border-bottom: 1px solid var(--ctp-surface0);
}
.panel-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    color: var(--ctp-text);
}
.panel-header-left svg {
    width: 20px;
    height: 20px;
    color: var(--ctp-mauve);
}
.panel-body {
    padding: 20px;
}

/* Model selector section */
.model-selector-section {
    background: linear-gradient(135deg, rgba(203, 166, 247, 0.08), rgba(137, 180, 250, 0.08));
    border: 1px solid rgba(203, 166, 247, 0.3);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}
.model-selector-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--ctp-mauve);
    margin-bottom: 14px;
}
.model-selector-header .icon {
    font-size: 18px;
}
.model-selector-row {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}
.model-selector-row:last-of-type {
    margin-bottom: 0;
}
.model-selector-row label {
    font-size: 12px;
    font-weight: 500;
    color: var(--ctp-subtext0);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.model-selector-row select {
    padding: 10px 12px;
    background: var(--ctp-surface0);
    border: 1px solid var(--ctp-surface1);
    border-radius: 8px;
    color: var(--ctp-text);
    font-size: 14px;
    cursor: pointer;
    transition: border-color 0.2s;
}
.model-selector-row select:hover {
    border-color: var(--ctp-mauve);
}
.model-selector-row select:focus {
    outline: none;
    border-color: var(--ctp-mauve);
    box-shadow: 0 0 0 3px rgba(203, 166, 247, 0.2);
}
.model-info {
    display: flex;
    gap: 16px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(203, 166, 247, 0.2);
}
.model-info-item {
    font-size: 12px;
    color: var(--ctp-subtext0);
}
.model-info-item .value {
    color: var(--ctp-text);
    font-weight: 500;
}

/* Form section */
.form-section {
    margin-bottom: 20px;
}
.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--ctp-subtext1);
    margin-bottom: 6px;
}
.form-group label .required {
    color: var(--ctp-red);
}
.form-group input[type="text"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    background: var(--ctp-surface0);
    border: 1px solid var(--ctp-surface1);
    border-radius: 8px;
    color: var(--ctp-text);
    font-size: 14px;
    transition: border-color 0.2s;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--ctp-mauve);
    box-shadow: 0 0 0 3px rgba(203, 166, 247, 0.15);
}
.form-group textarea {
    min-height: 70px;
    resize: vertical;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

/* Range slider */
.range-group {
    display: flex;
    align-items: center;
    gap: 12px;
}
.range-group input[type="range"] {
    flex: 1;
    -webkit-appearance: none;
    background: var(--ctp-surface0);
    border-radius: 4px;
    height: 6px;
}
.range-group input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    background: var(--ctp-mauve);
    border-radius: 50%;
    cursor: pointer;
}
.range-display {
    min-width: 70px;
    text-align: center;
    font-size: 13px;
    color: var(--ctp-text);
    background: var(--ctp-surface0);
    padding: 4px 10px;
    border-radius: 6px;
}

/* Buttons */
.btn-generate {
    width: 100%;
    padding: 14px 24px;
    font-size: 15px;
    font-weight: 600;
    background: linear-gradient(135deg, var(--ctp-mauve), var(--ctp-pink));
    border: none;
    border-radius: 10px;
    color: var(--ctp-crust);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.2s;
}
.btn-generate:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(203, 166, 247, 0.3);
}
.btn-generate:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
.btn-success {
    background: var(--ctp-green);
    color: var(--ctp-crust);
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    font-size: 13px;
}
.btn-success:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.btn-icon {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--ctp-surface0);
    border: 1px solid var(--ctp-surface1);
    border-radius: 8px;
    color: var(--ctp-subtext1);
    cursor: pointer;
    transition: all 0.2s;
}
.btn-icon:hover {
    background: var(--ctp-surface1);
    color: var(--ctp-text);
}
.btn-icon.btn-danger:hover {
    background: rgba(243, 139, 168, 0.2);
    border-color: var(--ctp-red);
    color: var(--ctp-red);
}

/* Spinner */
.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(17, 17, 27, 0.3);
    border-top-color: var(--ctp-crust);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    display: none;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--ctp-subtext0);
}
.empty-state svg {
    width: 64px;
    height: 64px;
    color: var(--ctp-surface2);
    margin-bottom: 16px;
}
.empty-state h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0 0 8px 0;
}
.empty-state p {
    font-size: 14px;
    margin: 0;
}

/* Campaign preview */
.campaign-meta {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--ctp-surface0);
}
.campaign-meta h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0 0 10px 0;
}
.meta-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 12px;
    text-transform: uppercase;
}
.badge-language {
    background: var(--ctp-blue);
    color: var(--ctp-crust);
}
.badge-tone {
    background: var(--ctp-peach);
    color: var(--ctp-crust);
}
.badge-emails {
    background: var(--ctp-green);
    color: var(--ctp-crust);
}

/* Email timeline */
.email-timeline {
    position: relative;
    padding-left: 24px;
}
.email-timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--ctp-surface1);
}
.timeline-item {
    position: relative;
    margin-bottom: 16px;
}
.timeline-item:last-child {
    margin-bottom: 0;
}
.timeline-dot {
    position: absolute;
    left: -24px;
    top: 16px;
    width: 16px;
    height: 16px;
    background: var(--ctp-mauve);
    border: 3px solid var(--ctp-base);
    border-radius: 50%;
}
.timeline-day {
    position: absolute;
    left: -80px;
    top: 12px;
    font-size: 11px;
    font-weight: 600;
    color: var(--ctp-subtext0);
    text-transform: uppercase;
    width: 50px;
    text-align: right;
}
.timeline-card {
    background: var(--ctp-surface0);
    border: 1px solid var(--ctp-surface1);
    border-radius: 10px;
    padding: 14px;
    margin-left: 8px;
}
.email-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.email-index {
    background: var(--ctp-mauve);
    color: var(--ctp-crust);
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
}
.email-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--ctp-text);
}
.email-subject {
    font-size: 13px;
    color: var(--ctp-subtext1);
    margin-bottom: 4px;
}
.email-preview {
    font-size: 12px;
    color: var(--ctp-subtext0);
    margin-bottom: 8px;
    font-style: italic;
}
.email-cta {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--ctp-blue);
    background: rgba(137, 180, 250, 0.1);
    padding: 3px 8px;
    border-radius: 6px;
}
.email-actions {
    display: flex;
    gap: 6px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--ctp-surface1);
}

/* Saved campaigns section */
.campaigns-section {
    margin-top: 32px;
}
.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.section-header h2 {
    font-size: 18px;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0;
}
.count-badge {
    background: var(--ctp-surface0);
    color: var(--ctp-subtext1);
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 10px;
}
.campaigns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}
.campaign-card {
    background: var(--ctp-base);
    border: 1px solid var(--ctp-surface0);
    border-radius: 12px;
    padding: 16px;
    transition: border-color 0.2s;
}
.campaign-card:hover {
    border-color: var(--ctp-mauve);
}
.campaign-card .card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 0;
    background: none;
    border: none;
}
.campaign-card h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0;
}
.campaign-card .card-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: var(--ctp-subtext0);
    margin-bottom: 12px;
}
.campaign-card .card-actions {
    display: flex;
    gap: 8px;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(17, 17, 27, 0.8);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-overlay.show {
    display: flex;
}
.modal {
    background: var(--ctp-base);
    border: 1px solid var(--ctp-surface0);
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow: auto;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--ctp-surface0);
}
.modal-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0;
}
.modal-close {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--ctp-surface0);
    border: none;
    border-radius: 8px;
    color: var(--ctp-subtext1);
    cursor: pointer;
    font-size: 18px;
}
.modal-close:hover {
    background: var(--ctp-surface1);
    color: var(--ctp-text);
}
.modal-body {
    padding: 20px;
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 20px;
    border-top: 1px solid var(--ctp-surface0);
}
.info-text {
    font-size: 12px;
    color: var(--ctp-subtext0);
    margin-top: 8px;
}

/* Alerts */
.alert {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert-success {
    background: rgba(166, 227, 161, 0.15);
    border: 1px solid rgba(166, 227, 161, 0.3);
    color: var(--ctp-green);
}
.alert-error {
    background: rgba(243, 139, 168, 0.15);
    border: 1px solid rgba(243, 139, 168, 0.3);
    color: var(--ctp-red);
}

/* Email content modal */
.email-body-content {
    background: var(--ctp-mantle);
    border: 1px solid var(--ctp-surface0);
    border-radius: 8px;
    padding: 16px;
    max-height: 400px;
    overflow-y: auto;
    font-size: 14px;
    line-height: 1.6;
    color: var(--ctp-text);
}
.email-body-content p {
    margin: 0 0 12px 0;
}

/* Toast notifications */
.toast-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1100;
}
.toast {
    background: var(--ctp-surface0);
    border: 1px solid var(--ctp-surface1);
    border-radius: 10px;
    padding: 12px 16px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: var(--ctp-text);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.3s ease;
}
.toast.success {
    border-color: var(--ctp-green);
}
.toast.error {
    border-color: var(--ctp-red);
}
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Empty campaigns state */
.empty-campaigns {
    text-align: center;
    padding: 40px 20px;
    color: var(--ctp-subtext0);
    background: var(--ctp-base);
    border: 1px dashed var(--ctp-surface1);
    border-radius: 12px;
}
</style>

<!-- Alerts -->
<?php if (!empty($error)): ?>
<div class="alert alert-error">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
    <span><?= esc($error) ?></span>
</div>
<?php endif; ?>

<?php if (!empty($successMessage)): ?>
<div class="alert alert-success">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    <span><?= $successMessage ?></span>
</div>
<?php endif; ?>

<?php if (!empty($testQueueResult) && $testQueueResult['ok']): ?>
<div class="alert alert-success">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    <span>Test email queued! Sent to <?= esc(implode(', ', $testQueueResult['recipients'])) ?></span>
</div>
<?php endif; ?>

<?php if (!empty($testQueueErrors)): ?>
<?php foreach ($testQueueErrors as $tqe): ?>
<div class="alert alert-error">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
    <span><?= esc($tqe) ?></span>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header-custom">
    <div class="page-header-left">
        <div class="page-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
        </div>
        <div class="page-title-group">
            <h1>AI Email Campaigns</h1>
            <p>Generate complete email sequences with AI</p>
        </div>
    </div>
    <div class="stats-badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span><?= count($savedCampaigns ?? []) ?> Campaigns</span>
    </div>
</div>

<!-- Main Layout -->
<div class="campaigns-layout">
    <!-- Left Panel - Generator -->
    <div class="panel panel-generator">
        <div class="panel-header">
            <div class="panel-header-left">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
                <span>Generate Campaign</span>
            </div>
        </div>
        <div class="panel-body">
            <!-- AI Model Selector -->
            <div class="model-selector-section">
                <div class="model-selector-header">
                    <span class="icon">🧠</span>
                    <span>AI Model Selection</span>
                </div>
                <div class="model-selector-row">
                    <label>Provider</label>
                    <select id="ai-provider">
                        <?php foreach ($availableModels ?? [] as $key => $provider): ?>
                        <option value="<?= esc($key) ?>"
                                <?= $key === ($defaultProvider ?? 'openai') ? 'selected' : '' ?>
                                <?= !($provider['enabled'] ?? false) ? 'disabled' : '' ?>
                                data-icon="<?= esc($provider['icon'] ?? '') ?>">
                            <?= esc($provider['icon'] ?? '') ?> <?= esc($provider['name'] ?? ucfirst($key)) ?>
                            <?= !($provider['enabled'] ?? false) ? ' (Not Configured)' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="model-selector-row">
                    <label>Model</label>
                    <select id="ai-model"></select>
                </div>
                <div class="model-info">
                    <span class="model-info-item">Context: <span class="value" id="model-context">-</span></span>
                    <span class="model-info-item">Cost: <span class="value" id="model-cost">-</span></span>
                </div>
            </div>

            <!-- Campaign Form -->
            <div class="form-section">
                <div class="form-group">
                    <label>Campaign Name <span class="required">*</span></label>
                    <input type="text" id="campaign-name" placeholder="e.g., Free Trial Conversion">
                </div>

                <div class="form-group">
                    <label>Campaign Goal <span class="required">*</span></label>
                    <textarea id="campaign-goal" placeholder="e.g., Convert free trial users to paid subscriptions"></textarea>
                </div>

                <div class="form-group">
                    <label>Target Audience</label>
                    <textarea id="campaign-audience" placeholder="e.g., Small business owners who signed up for free trial"></textarea>
                </div>

                <div class="form-group">
                    <label>Offer / Product</label>
                    <textarea id="campaign-offer" placeholder="e.g., AI-powered CMS with n8n automation"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Language</label>
                        <select id="campaign-language">
                            <option value="en">English</option>
                            <option value="pl">Polish</option>
                            <option value="de">German</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tone</label>
                        <select id="campaign-tone">
                            <option value="professional">Professional</option>
                            <option value="friendly">Friendly</option>
                            <option value="persuasive">Persuasive</option>
                            <option value="neutral">Neutral</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Number of Emails</label>
                    <div class="range-group">
                        <input type="range" id="campaign-num-emails" min="3" max="10" value="5">
                        <span class="range-display" id="num-emails-display">5 emails</span>
                    </div>
                </div>
            </div>

            <!-- Generate Button -->
            <button id="btn-generate" class="btn-generate">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
                <span class="btn-text">Generate Campaign</span>
                <span class="spinner"></span>
            </button>
        </div>
    </div>

    <!-- Right Panel - Preview -->
    <div class="panel panel-preview">
        <div class="panel-header">
            <div class="panel-header-left">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span>Campaign Preview</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button id="btn-save" class="btn-success" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save Draft
                </button>
            </div>
        </div>
        <div class="panel-body">
            <!-- Empty State -->
            <div id="preview-empty" class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                    <line x1="12" y1="13" x2="12" y2="20"></line>
                    <line x1="9" y1="17" x2="15" y2="17"></line>
                </svg>
                <h3>No Campaign Generated</h3>
                <p>Fill in the campaign details and click Generate</p>
            </div>

            <!-- Campaign Preview Content -->
            <div id="preview-content" style="display: none;">
                <div class="campaign-meta">
                    <h3 id="preview-name">Campaign Name</h3>
                    <div class="meta-badges">
                        <span class="badge badge-language" id="preview-language">EN</span>
                        <span class="badge badge-tone" id="preview-tone">Professional</span>
                        <span class="badge badge-emails" id="preview-email-count">5 emails</span>
                    </div>
                </div>

                <!-- Email Timeline -->
                <div class="email-timeline" id="email-timeline">
                    <!-- Emails will be rendered here by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Saved Campaigns Section -->
<div class="campaigns-section">
    <div class="section-header">
        <h2>Saved Campaigns</h2>
        <span class="count-badge"><?= count($savedCampaigns ?? []) ?></span>
    </div>

    <?php if (empty($savedCampaigns)): ?>
    <div class="empty-campaigns">
        <p>No saved campaigns yet. Generate your first campaign above!</p>
    </div>
    <?php else: ?>
    <div class="campaigns-grid">
        <?php foreach ($savedCampaigns as $campaign): ?>
        <div class="campaign-card" data-id="<?= esc($campaign['id']) ?>">
            <div class="card-header">
                <h3><?= esc($campaign['campaign_name']) ?></h3>
                <span class="badge badge-language"><?= esc(strtoupper($campaign['language'])) ?></span>
            </div>
            <div class="card-meta">
                <span><?= (int)$campaign['num_emails'] ?> emails</span>
                <span><?= esc($campaign['created_at'] ?? 'Unknown date') ?></span>
            </div>
            <div class="card-actions">
                <button class="btn btn-secondary btn-sm" onclick="viewCampaign('<?= esc($campaign['id']) ?>')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    View
                </button>
                <button class="btn-icon" onclick="duplicateCampaign('<?= esc($campaign['id']) ?>')" title="Duplicate">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteCampaign('<?= esc($campaign['id']) ?>')" title="Delete">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Test Email Modal -->
<div class="modal-overlay" id="test-email-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Send Test Email</h3>
            <button class="modal-close" onclick="closeTestModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Recipients (comma separated)</label>
                <input type="text" id="test-recipients" placeholder="email@example.com, another@example.com">
            </div>
            <p class="info-text">Emails will be prefixed with [TEST] in the subject line.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeTestModal()">Cancel</button>
            <button class="btn btn-primary" onclick="sendTestEmail()">Send Test</button>
        </div>
    </div>
</div>

<!-- Email Content Modal -->
<div class="modal-overlay" id="email-content-modal">
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <h3 id="email-modal-title">Email Content</h3>
            <button class="modal-close" onclick="closeEmailModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="email-body-content" id="email-modal-content"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeEmailModal()">Close</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toast-container"></div>

<script>
// Model data from PHP
const modelData = <?= json_encode($availableModels ?? []) ?>;
const csrfToken = '<?= csrf_token() ?>';
let currentSpec = null;
let currentCampaignId = null;
let testEmailIndex = 0;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initModelSelector();
    initRangeSlider();
    initEventListeners();
});

// Model selector initialization
function initModelSelector() {
    const providerSelect = document.getElementById('ai-provider');
    const modelSelect = document.getElementById('ai-model');

    if (!providerSelect || !modelSelect) return;

    providerSelect.addEventListener('change', updateModels);
    modelSelect.addEventListener('change', updateModelInfo);

    updateModels();
}

function updateModels() {
    const providerSelect = document.getElementById('ai-provider');
    const modelSelect = document.getElementById('ai-model');
    const providerKey = providerSelect.value;
    const provider = modelData[providerKey];

    if (!provider || !provider.models) {
        modelSelect.innerHTML = '<option value="">No models available</option>';
        updateModelInfo();
        return;
    }

    modelSelect.innerHTML = '';
    const models = provider.models;

    // Check if models have standard structure
    if (typeof models === 'object') {
        Object.entries(models).forEach(([modelKey, modelInfo]) => {
            const option = document.createElement('option');
            option.value = modelKey;
            option.textContent = typeof modelInfo === 'object' && modelInfo.name
                ? modelInfo.name
                : modelKey;
            if (modelKey === provider.default_model) {
                option.selected = true;
            }
            modelSelect.appendChild(option);
        });
    }

    updateModelInfo();
}

function updateModelInfo() {
    const providerKey = document.getElementById('ai-provider').value;
    const modelKey = document.getElementById('ai-model').value;
    const provider = modelData[providerKey];

    const contextEl = document.getElementById('model-context');
    const costEl = document.getElementById('model-cost');

    if (!provider || !provider.models || !provider.models[modelKey]) {
        if (contextEl) contextEl.textContent = '-';
        if (costEl) costEl.textContent = '-';
        return;
    }

    const model = provider.models[modelKey];

    if (contextEl && model.max_tokens) {
        contextEl.textContent = formatTokens(model.max_tokens);
    }

    if (costEl && model.cost_per_1k_input !== undefined) {
        costEl.textContent = formatCost(model.cost_per_1k_input) + ' in';
    }
}

function formatTokens(tokens) {
    if (tokens >= 1000000) return (tokens / 1000000).toFixed(1) + 'M';
    if (tokens >= 1000) return Math.round(tokens / 1000) + 'K';
    return tokens.toString();
}

function formatCost(cost) {
    if (cost < 0.001) return '$' + (cost * 1000).toFixed(2) + '/M';
    return '$' + cost.toFixed(4) + '/1K';
}

// Range slider
function initRangeSlider() {
    const rangeInput = document.getElementById('campaign-num-emails');
    const rangeDisplay = document.getElementById('num-emails-display');

    if (rangeInput && rangeDisplay) {
        rangeInput.addEventListener('input', function() {
            rangeDisplay.textContent = this.value + ' emails';
        });
    }
}

// Event listeners
function initEventListeners() {
    // Generate button
    document.getElementById('btn-generate')?.addEventListener('click', generateCampaign);

    // Save button
    document.getElementById('btn-save')?.addEventListener('click', saveCampaign);
}

// Generate campaign
async function generateCampaign() {
    const btn = document.getElementById('btn-generate');
    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner');

    // Validation
    const campaignName = document.getElementById('campaign-name').value.trim();
    const campaignGoal = document.getElementById('campaign-goal').value.trim();

    if (!campaignName || !campaignGoal) {
        showToast('Please fill in campaign name and goal', 'error');
        return;
    }

    btn.disabled = true;
    btnText.textContent = 'Generating...';
    spinner.style.display = 'block';

    const formData = new FormData();
    formData.append('ajax_action', 'generate');
    formData.append('csrf_token', csrfToken);
    formData.append('provider', document.getElementById('ai-provider').value);
    formData.append('model', document.getElementById('ai-model').value);
    formData.append('campaign_name', campaignName);
    formData.append('goal', campaignGoal);
    formData.append('audience', document.getElementById('campaign-audience').value);
    formData.append('offer', document.getElementById('campaign-offer').value);
    formData.append('language', document.getElementById('campaign-language').value);
    formData.append('tone', document.getElementById('campaign-tone').value);
    formData.append('num_emails', document.getElementById('campaign-num-emails').value);

    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.ok) {
            currentSpec = result.spec;
            currentCampaignId = null;
            renderPreview(currentSpec);
            document.getElementById('btn-save').disabled = false;
            showToast('Campaign generated successfully!', 'success');
        } else {
            showToast(result.error || 'Generation failed', 'error');
        }
    } catch (err) {
        console.error('Generate error:', err);
        showToast('Network error occurred', 'error');
    }

    btn.disabled = false;
    btnText.textContent = 'Generate Campaign';
    spinner.style.display = 'none';
}

// Render preview
function renderPreview(spec) {
    document.getElementById('preview-empty').style.display = 'none';
    document.getElementById('preview-content').style.display = 'block';

    document.getElementById('preview-name').textContent = spec.campaign_name || 'Untitled Campaign';
    document.getElementById('preview-language').textContent = (spec.language || 'EN').toUpperCase();
    document.getElementById('preview-tone').textContent = spec.tone || 'Professional';
    document.getElementById('preview-email-count').textContent = (spec.emails?.length || 0) + ' emails';

    // Render email timeline
    const timeline = document.getElementById('email-timeline');
    timeline.innerHTML = '';

    if (spec.emails && spec.emails.length > 0) {
        spec.emails.forEach((email, idx) => {
            const item = document.createElement('div');
            item.className = 'timeline-item';
            item.innerHTML = `
                <div class="timeline-dot"></div>
                <div class="timeline-day">Day ${email.send_after_days || 0}</div>
                <div class="timeline-card">
                    <div class="email-header">
                        <span class="email-index">#${idx + 1}</span>
                        <span class="email-name">${escapeHtml(email.internal_name || 'Email ' + (idx + 1))}</span>
                    </div>
                    <div class="email-subject">Subject: ${escapeHtml(email.subject || '')}</div>
                    <div class="email-preview">${escapeHtml((email.preview_text || '').substring(0, 100))}...</div>
                    <div class="email-cta">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                        CTA: ${escapeHtml(email.primary_cta || 'Learn More')}
                    </div>
                    <div class="email-actions">
                        <button class="btn-icon" onclick="expandEmail(${idx})" title="View Full Content">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path>
                            </svg>
                        </button>
                        ${currentCampaignId ? `
                        <button class="btn-icon" onclick="openTestModal(${idx})" title="Send Test">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>` : ''}
                    </div>
                </div>
            `;
            timeline.appendChild(item);
        });
    }
}

// Save campaign
async function saveCampaign() {
    if (!currentSpec) return;

    const formData = new FormData();
    formData.append('ajax_action', 'save');
    formData.append('csrf_token', csrfToken);
    formData.append('spec', JSON.stringify(currentSpec));

    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.ok) {
            currentCampaignId = result.id;
            showToast('Campaign saved! ID: ' + result.id, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(result.error || 'Save failed', 'error');
        }
    } catch (err) {
        console.error('Save error:', err);
        showToast('Network error occurred', 'error');
    }
}

// View campaign
async function viewCampaign(id) {
    const formData = new FormData();
    formData.append('ajax_action', 'load');
    formData.append('csrf_token', csrfToken);
    formData.append('campaign_id', id);

    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.ok) {
            currentSpec = result.spec;
            currentCampaignId = id;
            renderPreview(currentSpec);
            document.getElementById('btn-save').disabled = true;

            // Scroll to preview
            document.querySelector('.panel-preview').scrollIntoView({ behavior: 'smooth' });
        } else {
            showToast(result.error || 'Failed to load campaign', 'error');
        }
    } catch (err) {
        console.error('Load error:', err);
        showToast('Network error occurred', 'error');
    }
}

// Duplicate campaign
async function duplicateCampaign(id) {
    await viewCampaign(id);
    currentCampaignId = null;
    document.getElementById('btn-save').disabled = false;
    showToast('Campaign loaded. Make changes and save as new.', 'success');
}

// Delete campaign
async function deleteCampaign(id) {
    if (!confirm('Delete this campaign? This cannot be undone.')) return;

    const formData = new FormData();
    formData.append('ajax_action', 'delete');
    formData.append('csrf_token', csrfToken);
    formData.append('campaign_id', id);

    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.ok) {
            showToast('Campaign deleted', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'Delete failed', 'error');
        }
    } catch (err) {
        console.error('Delete error:', err);
        showToast('Network error occurred', 'error');
    }
}

// Expand email content
function expandEmail(index) {
    if (!currentSpec || !currentSpec.emails || !currentSpec.emails[index]) return;

    const email = currentSpec.emails[index];
    document.getElementById('email-modal-title').textContent = email.internal_name || 'Email ' + (index + 1);
    document.getElementById('email-modal-content').innerHTML = email.body_html || '<p>No content available.</p>';
    document.getElementById('email-content-modal').classList.add('show');
}

function closeEmailModal() {
    document.getElementById('email-content-modal').classList.remove('show');
}

// Test email modal
function openTestModal(index) {
    testEmailIndex = index;
    document.getElementById('test-email-modal').classList.add('show');
    document.getElementById('test-recipients').focus();
}

function closeTestModal() {
    document.getElementById('test-email-modal').classList.remove('show');
}

async function sendTestEmail() {
    if (!currentCampaignId) {
        showToast('Please save the campaign first', 'error');
        return;
    }

    const recipients = document.getElementById('test-recipients').value.trim();
    if (!recipients) {
        showToast('Please enter recipient email(s)', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('ajax_action', 'queue_test');
    formData.append('csrf_token', csrfToken);
    formData.append('campaign_id', currentCampaignId);
    formData.append('email_index', testEmailIndex);
    formData.append('test_recipients', recipients);

    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.ok) {
            showToast('Test email queued to ' + result.recipients.join(', '), 'success');
            closeTestModal();
        } else {
            showToast(result.error || 'Failed to queue test email', 'error');
        }
    } catch (err) {
        console.error('Test email error:', err);
        showToast('Network error occurred', 'error');
    }
}

// Toast notifications
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = `
        ${type === 'success' ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : ''}
        ${type === 'error' ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>' : ''}
        <span>${escapeHtml(message)}</span>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Escape HTML helper
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTestModal();
        closeEmailModal();
    }
});

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            closeTestModal();
            closeEmailModal();
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
