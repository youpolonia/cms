<?php
/**
 * JTB AI Panel View
 * AI-powered layout and content generation interface
 * Includes Compositional System UI
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

$csrfToken = function_exists('csrf_token') ? csrf_token() : ($_SESSION['csrf_token'] ?? '');
$devMode = defined('DEV_MODE') && DEV_MODE === true;

// Stage 25: DEV_MODE Cache Bust - get file mtimes for cache busting
$assetVersionJs = '';
$assetVersionCss = '';
if ($devMode) {
    $jsPath = dirname(__DIR__) . '/assets/js/ai-panel.js';
    $cssPath = dirname(__DIR__) . '/assets/css/ai-panel.css';
    if (file_exists($jsPath)) {
        $assetVersionJs = filemtime($jsPath);
    }
    if (file_exists($cssPath)) {
        $assetVersionCss = filemtime($cssPath);
    }
}
?>

<!-- DEV_MODE flag for JavaScript -->
<?php if ($devMode): ?>
<script>
window.JTB_DEV_MODE = true;
// Stage 25: Asset versions for parity check
window.JTB_ASSET_V = {
    js: <?php echo json_encode($assetVersionJs); ?>,
    css: <?php echo json_encode($assetVersionCss); ?>
};
</script>
<?php endif; ?>

<!-- AI Panel -->
<div id="jtb-ai-panel" class="jtb-ai-panel">
    <div class="jtb-ai-panel-overlay"></div>

    <!-- Loading Overlay (NEW - covers entire panel during generation) -->
    <div id="jtb-ai-loading-overlay" class="jtb-ai-loading-overlay">
        <div class="jtb-ai-loading-content">
            <div class="jtb-ai-loading-spinner">
                <svg viewBox="0 0 50 50">
                    <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round">
                        <animate attributeName="stroke-dasharray" dur="1.5s" repeatCount="indefinite" values="1,150;90,150;90,150"/>
                        <animate attributeName="stroke-dashoffset" dur="1.5s" repeatCount="indefinite" values="0;-35;-124"/>
                    </circle>
                </svg>
            </div>
            <div class="jtb-ai-loading-title" id="jtb-ai-loading-title">Generating your layout...</div>
            <div class="jtb-ai-loading-steps">
                <div class="jtb-ai-loading-step active" data-step="1">
                    <span class="step-icon">✓</span>
                    <span class="step-text">Analyzing your requirements</span>
                </div>
                <div class="jtb-ai-loading-step" data-step="2">
                    <span class="step-icon">○</span>
                    <span class="step-text">Designing page structure</span>
                </div>
                <div class="jtb-ai-loading-step" data-step="3">
                    <span class="step-icon">○</span>
                    <span class="step-text">Creating content & styling</span>
                </div>
                <div class="jtb-ai-loading-step" data-step="4">
                    <span class="step-icon">○</span>
                    <span class="step-text">Finalizing layout</span>
                </div>
            </div>
            <div class="jtb-ai-loading-tip" id="jtb-ai-loading-tip">
                This usually takes 10-30 seconds depending on complexity...
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="jtb-ai-panel-header">
        <div class="jtb-ai-panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"/>
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 6v2M12 16v2M6 12h2M16 12h2"/>
            </svg>
            <span>AI Composer</span>
        </div>
        <button type="button" class="jtb-ai-panel-close" title="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Tabs -->
    <div class="jtb-ai-panel-tabs">
        <button type="button" class="jtb-ai-tab active" data-tab="compose">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/>
                <rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/>
            </svg>
            Compose
        </button>
        <button type="button" class="jtb-ai-tab" data-tab="patterns">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5z"/>
                <path d="M4 13a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6z"/>
                <path d="M16 13a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-6z"/>
            </svg>
            Patterns
        </button>
        <button type="button" class="jtb-ai-tab" data-tab="section">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M3 9h18M3 15h18"/>
            </svg>
            Section
        </button>
        <button type="button" class="jtb-ai-tab" data-tab="generate">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
            </svg>
            Quick
        </button>
    </div>

    <!-- Body -->
    <div class="jtb-ai-panel-body">

        <!-- Compose Tab (NEW - Business-focused interface like Divi AI) -->
        <div class="jtb-ai-tab-content active" data-tab="compose">
            <div class="jtb-ai-form">
                <!-- MAIN INPUT: Business Description (Most Important!) -->
                <div class="jtb-ai-field jtb-ai-field-primary">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Describe your business
                    </label>
                    <textarea id="jtb-ai-compose-prompt" rows="3" placeholder="Example: We are TechFlow, a project management SaaS for remote teams. We help companies organize tasks, track time, and collaborate in real-time. Our main features are AI-powered task suggestions, Gantt charts, and Slack integration. We target startups and SMBs."></textarea>
                    <div class="jtb-ai-field-hint">
                        Tell AI about your business, products/services, target audience, and unique value proposition. The more detail you provide, the better the result.
                    </div>
                </div>

                <!-- Page Goal -->
                <div class="jtb-ai-field">
                    <label>Page Goal</label>
                    <div class="jtb-ai-intent-grid jtb-ai-intent-grid-compact">
                        <button type="button" class="jtb-ai-intent-btn" data-intent="saas_landing">
                            <div class="jtb-ai-intent-icon">🚀</div>
                            <div class="jtb-ai-intent-label">SaaS Landing</div>
                        </button>
                        <button type="button" class="jtb-ai-intent-btn" data-intent="service_showcase">
                            <div class="jtb-ai-intent-icon">💼</div>
                            <div class="jtb-ai-intent-label">Services</div>
                        </button>
                        <button type="button" class="jtb-ai-intent-btn" data-intent="brand_story">
                            <div class="jtb-ai-intent-icon">📖</div>
                            <div class="jtb-ai-intent-label">About Us</div>
                        </button>
                        <button type="button" class="jtb-ai-intent-btn" data-intent="portfolio">
                            <div class="jtb-ai-intent-icon">🎨</div>
                            <div class="jtb-ai-intent-label">Portfolio</div>
                        </button>
                        <button type="button" class="jtb-ai-intent-btn" data-intent="product_launch">
                            <div class="jtb-ai-intent-icon">📦</div>
                            <div class="jtb-ai-intent-label">Product</div>
                        </button>
                        <button type="button" class="jtb-ai-intent-btn" data-intent="agency">
                            <div class="jtb-ai-intent-icon">🏢</div>
                            <div class="jtb-ai-intent-label">Agency</div>
                        </button>
                    </div>
                </div>

                <!-- Style & Industry Row -->
                <div class="jtb-ai-field-row">
                    <div class="jtb-ai-field jtb-ai-field-half">
                        <label>Visual Style</label>
                        <select id="jtb-ai-compose-style">
                            <option value="modern">Modern</option>
                            <option value="minimal">Minimal</option>
                            <option value="bold">Bold</option>
                            <option value="elegant">Elegant</option>
                            <option value="playful">Playful</option>
                            <option value="corporate">Corporate</option>
                        </select>
                    </div>
                    <div class="jtb-ai-field jtb-ai-field-half">
                        <label>Industry</label>
                        <select id="jtb-ai-compose-industry">
                            <option value="auto">Auto-detect from description</option>
                            <optgroup label="Trade & Construction">
                                <option value="paving">Paving / Driveways</option>
                                <option value="construction">Construction</option>
                                <option value="roofing">Roofing</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical</option>
                                <option value="landscaping">Landscaping</option>
                                <option value="cleaning">Cleaning Services</option>
                            </optgroup>
                            <optgroup label="Professional Services">
                                <option value="legal">Legal / Law Firm</option>
                                <option value="finance">Finance / Accounting</option>
                                <option value="healthcare">Healthcare</option>
                                <option value="dental">Dental</option>
                                <option value="education">Education</option>
                            </optgroup>
                            <optgroup label="Lifestyle & Beauty">
                                <option value="salon">Salon / Beauty</option>
                                <option value="spa">Spa / Wellness</option>
                                <option value="fitness">Fitness / Gym</option>
                                <option value="restaurant">Restaurant / Food</option>
                            </optgroup>
                            <optgroup label="Tech & Digital">
                                <option value="technology">Technology</option>
                                <option value="saas">SaaS / Software</option>
                                <option value="agency">Agency / Creative</option>
                            </optgroup>
                            <optgroup label="Retail">
                                <option value="retail">Retail</option>
                                <option value="ecommerce">E-commerce</option>
                                <option value="real_estate">Real Estate</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <!-- Advanced Options (Collapsed by default) -->
                <div class="jtb-ai-advanced-toggle">
                    <button type="button" id="jtb-ai-advanced-btn" class="jtb-ai-advanced-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                        Advanced Options
                    </button>
                </div>
                <div id="jtb-ai-advanced-options" class="jtb-ai-advanced-options" style="display:none;">
                    <!-- AI Provider Selection -->
                    <div class="jtb-ai-field">
                        <label>AI Provider</label>
                        <select id="jtb-ai-provider" onchange="JTB_AI.handleProviderChange(this.value)">
                            <option value="anthropic" selected>Anthropic Claude (Recommended)</option>
                            <option value="openai">OpenAI</option>
                            <option value="google">Google Gemini</option>
                            <option value="deepseek">DeepSeek</option>
                        </select>
                    </div>

                    <!-- Claude Access Mode (only visible when Anthropic selected) -->
                    <div class="jtb-ai-field" id="jtb-ai-access-mode-field">
                        <label>Access Mode</label>
                        <select id="jtb-ai-access-mode" onchange="JTB_AI.handleAccessModeChange(this.value)">
                            <option value="api">API (pay-per-use)</option>
                            <option value="max_pro">Max Pro Subscription (unlimited)</option>
                        </select>
                        <div class="jtb-ai-field-hint" id="jtb-ai-access-mode-hint">
                            API mode uses your Anthropic API key. Max Pro uses your claude.ai subscription.
                        </div>
                    </div>

                    <!-- AI Model Selection -->
                    <div class="jtb-ai-field">
                        <label>AI Model</label>
                        <select id="jtb-ai-model">
                            <option value="claude-opus-4-5-20251101" selected>Claude Opus 4.5 (Best)</option>
                            <option value="claude-sonnet-4-20250514">Claude Sonnet 4</option>
                            <option value="claude-3-5-sonnet-20241022">Claude 3.5 Sonnet</option>
                            <option value="claude-3-haiku-20240307">Claude 3 Haiku (Fast)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patterns Tab (Browse individual patterns) -->
        <div class="jtb-ai-tab-content" data-tab="patterns">
            <div class="jtb-ai-form">
                <!-- Pattern Category Filter -->
                <div class="jtb-ai-field">
                    <label>Category</label>
                    <div class="jtb-ai-pattern-categories">
                        <button type="button" class="jtb-ai-category-btn active" data-category="all">All</button>
                        <button type="button" class="jtb-ai-category-btn" data-category="hero">Hero</button>
                        <button type="button" class="jtb-ai-category-btn" data-category="content_flow">Content</button>
                        <button type="button" class="jtb-ai-category-btn" data-category="social_proof">Proof</button>
                        <button type="button" class="jtb-ai-category-btn" data-category="pricing">Pricing</button>
                        <button type="button" class="jtb-ai-category-btn" data-category="interaction">Interactive</button>
                        <button type="button" class="jtb-ai-category-btn" data-category="closure">Closure</button>
                    </div>
                </div>

                <!-- Pattern Grid -->
                <div class="jtb-ai-field">
                    <label>Select Pattern</label>
                    <div id="jtb-ai-pattern-grid" class="jtb-ai-pattern-grid">
                        <!-- Patterns will be loaded dynamically -->
                        <div class="jtb-ai-loading">Loading patterns...</div>
                    </div>
                </div>

                <!-- Variant Selection (appears after pattern selected) -->
                <div id="jtb-ai-variant-section" class="jtb-ai-field" style="display:none;">
                    <label>Variant</label>
                    <div id="jtb-ai-variant-grid" class="jtb-ai-variant-grid">
                        <!-- Variants will be loaded dynamically -->
                    </div>
                </div>

                <!-- Style for single pattern -->
                <div class="jtb-ai-field">
                    <label>Style</label>
                    <select id="jtb-ai-pattern-style">
                        <option value="modern">Modern</option>
                        <option value="minimal">Minimal</option>
                        <option value="bold">Bold</option>
                        <option value="elegant">Elegant</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section Tab (Legacy - quick section generation) -->
        <div class="jtb-ai-tab-content" data-tab="section">
            <div class="jtb-ai-form">
                <div class="jtb-ai-field">
                    <label>Section Type</label>
                    <div class="jtb-ai-section-grid">
                        <button type="button" class="jtb-ai-section-btn" data-section="hero">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <path d="M3 9h18"/>
                            </svg>
                            <span>Hero</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="features">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"/>
                                <rect x="14" y="3" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/>
                            </svg>
                            <span>Features</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="testimonials">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            <span>Testimonials</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="pricing">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            <span>Pricing</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="cta">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <path d="M22 4L12 14.01l-3-3"/>
                            </svg>
                            <span>CTA</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="faq">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/>
                            </svg>
                            <span>FAQ</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="team">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <span>Team</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="contact">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <path d="M22 6l-10 7L2 6"/>
                            </svg>
                            <span>Contact</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="stats">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 20V10M12 20V4M6 20v-6"/>
                            </svg>
                            <span>Stats</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="portfolio">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"/>
                                <rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/>
                            </svg>
                            <span>Portfolio</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="blog">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                            </svg>
                            <span>Blog</span>
                        </button>
                        <button type="button" class="jtb-ai-section-btn" data-section="newsletter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            <span>Newsletter</span>
                        </button>
                    </div>
                </div>

                <div class="jtb-ai-field">
                    <label>Additional context (optional)</label>
                    <textarea id="jtb-ai-section-context" rows="2" placeholder="Describe what this section should contain..."></textarea>
                </div>
            </div>
        </div>

        <!-- Quick Generate Tab (Legacy - prompt-based) -->
        <div class="jtb-ai-tab-content" data-tab="generate">
            <div class="jtb-ai-form">
                <div class="jtb-ai-field">
                    <label>Describe your page</label>
                    <textarea id="jtb-ai-prompt" rows="4" placeholder="E.g., Create a modern SaaS landing page with hero section, features, pricing, testimonials, and contact form..."></textarea>
                </div>

                <div class="jtb-ai-field">
                    <label>Industry</label>
                    <select id="jtb-ai-industry">
                        <option value="">Auto-detect</option>
                        <option value="technology">Technology</option>
                        <option value="healthcare">Healthcare</option>
                        <option value="finance">Finance</option>
                        <option value="education">Education</option>
                        <option value="retail">Retail / E-commerce</option>
                        <option value="real_estate">Real Estate</option>
                        <option value="restaurant">Restaurant / Food</option>
                        <option value="fitness">Fitness / Health</option>
                        <option value="legal">Legal Services</option>
                        <option value="creative">Creative / Agency</option>
                    </select>
                </div>

                <div class="jtb-ai-field">
                    <label>Tone</label>
                    <select id="jtb-ai-tone">
                        <option value="professional">Professional</option>
                        <option value="friendly">Friendly</option>
                        <option value="formal">Formal</option>
                        <option value="casual">Casual</option>
                        <option value="enthusiastic">Enthusiastic</option>
                        <option value="authoritative">Authoritative</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Progress -->
        <div class="jtb-ai-progress">
            <div class="jtb-ai-progress-bar">
                <div class="jtb-ai-progress-fill"></div>
            </div>
            <div class="jtb-ai-progress-text">Generating...</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="jtb-ai-panel-footer">
        <button type="button" class="jtb-ai-btn jtb-ai-btn-secondary" id="jtb-ai-cancel-btn">
            Cancel
        </button>
        <button type="button" class="jtb-ai-btn jtb-ai-btn-primary" id="jtb-ai-generate-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
            </svg>
            <span id="jtb-ai-generate-btn-text">Compose Page</span>
        </button>
    </div>
</div>

<!-- Preview Modal -->
<div id="jtb-ai-preview-modal" class="jtb-ai-modal">
    <div class="jtb-ai-modal-overlay"></div>
    <div class="jtb-ai-modal-container">
        <div class="jtb-ai-modal-header">
            <h3>Preview Composed Layout</h3>
            <div class="jtb-ai-modal-meta" id="jtb-ai-preview-meta">
                <!-- Meta info about patterns used -->
            </div>
            <?php if ($devMode): ?>
            <!-- DEV_MODE only: Debug toggle -->
            <label class="jtb-ai-debug-toggle" title="Visual Debug Mode (DEV only)">
                <input type="checkbox" id="jtb-ai-debug-mode">
                <span class="jtb-ai-debug-toggle-label">Debug</span>
            </label>
            <?php endif; ?>
            <button type="button" class="jtb-ai-modal-close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="jtb-ai-modal-content">
            <div class="jtb-ai-preview-frame">
                <!-- Preview content will be inserted here -->
            </div>
        </div>
        <div class="jtb-ai-modal-footer">
            <button type="button" class="jtb-ai-btn jtb-ai-btn-secondary" id="jtb-ai-preview-cancel">
                Cancel
            </button>
            <button type="button" class="jtb-ai-btn jtb-ai-btn-outline" id="jtb-ai-preview-regenerate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 4v6h-6M1 20v-6h6"/>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                </svg>
                Regenerate
            </button>
            <button type="button" class="jtb-ai-btn jtb-ai-btn-primary" id="jtb-ai-preview-insert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <path d="M22 4L12 14.01l-3-3"/>
                </svg>
                Insert Layout
            </button>
        </div>
    </div>
</div>

<!-- Pattern Info Modal -->
<div id="jtb-ai-pattern-modal" class="jtb-ai-modal jtb-ai-pattern-info-modal">
    <div class="jtb-ai-modal-overlay"></div>
    <div class="jtb-ai-modal-container jtb-ai-modal-sm">
        <div class="jtb-ai-modal-header">
            <h3 id="jtb-ai-pattern-modal-title">Pattern Name</h3>
            <button type="button" class="jtb-ai-modal-close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="jtb-ai-modal-content">
            <div id="jtb-ai-pattern-modal-content">
                <!-- Pattern info will be inserted here -->
            </div>
        </div>
        <div class="jtb-ai-modal-footer">
            <button type="button" class="jtb-ai-btn jtb-ai-btn-secondary jtb-ai-pattern-modal-close">
                Close
            </button>
            <button type="button" class="jtb-ai-btn jtb-ai-btn-primary" id="jtb-ai-pattern-modal-insert">
                Insert Pattern
            </button>
        </div>
    </div>
</div>

<!-- Hidden CSRF token -->
<input type="hidden" id="jtb-ai-csrf" value="<?php echo htmlspecialchars($csrfToken); ?>">

<!-- AI Toggle Button (FAB) -->
<button type="button" id="jtb-ai-toggle" class="jtb-ai-toggle" title="AI Composer">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"/>
        <rect x="14" y="3" width="7" height="7"/>
        <rect x="3" y="14" width="7" height="7"/>
        <rect x="14" y="14" width="7" height="7"/>
    </svg>
</button>
