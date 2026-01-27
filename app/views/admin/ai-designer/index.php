<?php
/**
 * AI Designer 4.0 - Wizard View
 * 
 * Multi-step wizard for AI-powered theme generation.
 * Creates complete website themes with pages, header, footer.
 *
 * @package Admin\Views
 * @version 4.0
 */

// Variables from controller
$designStyles = $designStyles ?? [];
$industries = $industries ?? [];
$pageTypes = $pageTypes ?? [];
$aiConfigured = $aiConfigured ?? false;
$imageApiConfigured = $imageApiConfigured ?? false;

// MVC Title
$title = 'AI Designer 4.0';
ob_start();
?>

<style>
/* AI Designer 4.0 Styles */
.ai-designer {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.ai-designer h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #cdd6f4;
}

.ai-designer .subtitle {
    color: #a6adc8;
    margin-bottom: 2rem;
}

/* Wizard Steps */
.wizard-steps {
    display: flex;
    justify-content: center;
    margin-bottom: 3rem;
    gap: 0;
}

.wizard-step {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: #313244;
    color: #a6adc8;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.wizard-step:first-child {
    border-radius: 8px 0 0 8px;
}

.wizard-step:last-child {
    border-radius: 0 8px 8px 0;
}

.wizard-step.active {
    background: #89b4fa;
    color: #1e1e2e;
}

.wizard-step.completed {
    background: #a6e3a1;
    color: #1e1e2e;
}

.wizard-step .step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.wizard-step.active .step-number,
.wizard-step.completed .step-number {
    background: rgba(255,255,255,0.3);
}

/* Panels */
.wizard-panel {
    display: none;
    background: #1e1e2e;
    border-radius: 12px;
    padding: 2rem;
    border: 1px solid #313244;
}

.wizard-panel.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.panel-title {
    font-size: 1.5rem;
    color: #cdd6f4;
    margin-bottom: 0.5rem;
}

.panel-description {
    color: #a6adc8;
    margin-bottom: 2rem;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    color: #cdd6f4;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem 1rem;
    background: #313244;
    border: 1px solid #45475a;
    border-radius: 8px;
    color: #cdd6f4;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #89b4fa;
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
}

.form-hint {
    font-size: 0.875rem;
    color: #6c7086;
    margin-top: 0.5rem;
}

/* Style Grid */
.style-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.style-card {
    background: #313244;
    border: 2px solid transparent;
    border-radius: 12px;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.style-card:hover {
    background: #45475a;
    transform: translateY(-2px);
}

.style-card.selected {
    border-color: #89b4fa;
    background: rgba(137, 180, 250, 0.1);
}

.style-card .icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.style-card .name {
    color: #cdd6f4;
    font-weight: 500;
    font-size: 0.875rem;
}

/* Page Selection */
.page-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
}

.page-checkbox {
    display: none;
}

.page-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #313244;
    border: 2px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.page-label:hover {
    background: #45475a;
}

.page-checkbox:checked + .page-label {
    border-color: #a6e3a1;
    background: rgba(166, 227, 161, 0.1);
}

.page-label .checkbox-icon {
    width: 20px;
    height: 20px;
    border: 2px solid #6c7086;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.page-checkbox:checked + .page-label .checkbox-icon {
    background: #a6e3a1;
    border-color: #a6e3a1;
    color: #1e1e2e;
}

/* Navigation Buttons */
.wizard-nav {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #313244;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    font-size: 1rem;
}

.btn-secondary {
    background: #313244;
    color: #cdd6f4;
}

.btn-secondary:hover {
    background: #45475a;
}

.btn-primary {
    background: #89b4fa;
    color: #1e1e2e;
}

.btn-primary:hover {
    background: #b4befe;
    transform: translateY(-2px);
}

.btn-success {
    background: #a6e3a1;
    color: #1e1e2e;
}

.btn-success:hover {
    background: #94e2d5;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Progress Section */
.generation-progress {
    display: none;
    text-align: center;
    padding: 3rem;
}

.generation-progress.active {
    display: block;
}

.progress-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #313244;
    border-top-color: #89b4fa;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1.5rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.progress-text {
    color: #cdd6f4;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.progress-steps {
    color: #a6adc8;
    font-size: 0.875rem;
}

/* Result Panel */
.result-panel {
    display: none;
}

.result-panel.active {
    display: block;
}

.result-success {
    background: rgba(166, 227, 161, 0.1);
    border: 1px solid #a6e3a1;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    margin-bottom: 2rem;
}

.result-success h2 {
    color: #a6e3a1;
    margin-bottom: 0.5rem;
}

.theme-info {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 2rem;
}

.theme-info-card {
    background: #313244;
    border-radius: 8px;
    padding: 1.5rem;
}

.theme-info-card h4 {
    color: #89b4fa;
    margin-bottom: 0.5rem;
}

.theme-info-card p {
    color: #a6adc8;
}

.result-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

/* Warning */
.warning-box {
    background: rgba(249, 226, 175, 0.1);
    border: 1px solid #f9e2af;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    color: #f9e2af;
}

/* Two Column Layout */
.two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

@media (max-width: 1024px) {
    .style-grid, .page-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .two-columns {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .style-grid, .page-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .wizard-steps {
        flex-wrap: wrap;
    }
}

/* Auto-fill Images Toggle */
.auto-fill-option {
    margin-top: 2rem;
    padding: 1.25rem;
    background: rgba(49, 50, 68, 0.5);
    border: 1px solid #45475a;
    border-radius: 12px;
    transition: all 0.2s ease;
}
.auto-fill-option.enabled {
    border-color: #89b4fa;
    background: rgba(137, 180, 250, 0.1);
}
.auto-fill-option.disabled {
    opacity: 0.7;
}
.auto-fill-label {
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
}
.auto-fill-option.disabled .auto-fill-label {
    cursor: not-allowed;
}
.auto-fill-checkbox {
    display: none;
}
.auto-fill-toggle {
    width: 52px;
    height: 28px;
    background: #45475a;
    border-radius: 14px;
    position: relative;
    transition: all 0.3s ease;
    flex-shrink: 0;
}
.auto-fill-toggle::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    background: #6c7086;
    border-radius: 50%;
    transition: all 0.3s ease;
}
.auto-fill-checkbox:checked + .auto-fill-toggle {
    background: #89b4fa;
}
.auto-fill-checkbox:checked + .auto-fill-toggle::after {
    left: 27px;
    background: #fff;
}
.auto-fill-checkbox:disabled + .auto-fill-toggle {
    background: #313244;
}
.auto-fill-checkbox:disabled + .auto-fill-toggle::after {
    background: #45475a;
}
.auto-fill-text {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.auto-fill-text strong {
    color: #cdd6f4;
    font-size: 1rem;
}
.auto-fill-desc {
    color: #a6adc8;
    font-size: 0.875rem;
}
</style>

<div class="ai-designer">
    <input type="hidden" id="csrf_token" value="<?= csrf_token() ?>">
    <h1>🎨 AI Designer 4.0</h1>
    <p class="subtitle">Create complete website themes with AI-powered design</p>
    
    <?php if (!$aiConfigured): ?>
    <div class="warning-box">
        ⚠️ AI provider not configured. Please go to <a href="/admin/ai-settings.php">AI Settings</a> to configure your API keys.
    </div>
    <?php endif; ?>
    
    <!-- Wizard Steps -->
    <div class="wizard-steps">
        <button class="wizard-step active" data-step="1">
            <span class="step-number">1</span>
            <span>Project Info</span>
        </button>
        <button class="wizard-step" data-step="2">
            <span class="step-number">2</span>
            <span>Design Style</span>
        </button>
        <button class="wizard-step" data-step="3">
            <span class="step-number">3</span>
            <span>Pages</span>
        </button>
        <button class="wizard-step" data-step="4">
            <span class="step-number">4</span>
            <span>Generate</span>
        </button>
    </div>
    
    <!-- Step 1: Project Info -->
    <div class="wizard-panel active" data-panel="1">
        <h2 class="panel-title">Tell us about your project</h2>
        <p class="panel-description">Provide basic information about your business and website goals.</p>
        
        <div class="two-columns">
            <div>
                <div class="form-group">
                    <label for="business_name">Business Name *</label>
                    <input type="text" id="business_name" placeholder="e.g., Stellar Coffee House" required>
                </div>
                
                <div class="form-group">
                    <label for="industry">Industry *</label>
                    <select id="industry">
                        <?php foreach ($industries as $key => $name): ?>
                        <option value="<?= $key ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div>
                <div class="form-group">
                    <label for="brief">Project Brief *</label>
                    <textarea id="brief" placeholder="Describe your business, target audience, and what you want to achieve with this website. The more details you provide, the better the AI can design your site."></textarea>
                    <p class="form-hint">Include: services, unique selling points, brand personality, target customers</p>
                </div>
            </div>
        </div>
        
        <div class="wizard-nav">
            <div></div>
            <button class="btn btn-primary" onclick="nextStep()">Next: Choose Style →</button>
        </div>
    </div>
    
    <!-- Step 2: Design Style -->
    <div class="wizard-panel" data-panel="2">
        <h2 class="panel-title">Choose your design style</h2>
        <p class="panel-description">Select the visual style that best represents your brand.</p>
        
        <!-- Category Filters -->
        <div class="style-categories">
            <button class="category-btn active" data-category="all">🎯 All Styles</button>
            <button class="category-btn" data-category="business">💼 Business</button>
            <button class="category-btn" data-category="creative">🎨 Creative</button>
            <button class="category-btn" data-category="luxury">💎 Luxury</button>
            <button class="category-btn" data-category="nature">🌿 Nature</button>
            <button class="category-btn" data-category="bold">⚡ Bold</button>
        </div>
        
        <div class="style-grid">
            <?php foreach ($designStyles as $key => $style): 
                $colors = $style['colors'] ?? ['#89b4fa', '#cba6f7', '#f5c2e7'];
                $category = $style['category'] ?? 'business';
            ?>
            <div class="style-card <?= $key === 'modern' ? 'selected' : '' ?>" data-style="<?= $key ?>" data-category="<?= $category ?>">
                <div class="icon"><?= $style['icon'] ?></div>
                <div class="name"><?= htmlspecialchars($style['name']) ?></div>
                <div class="color-palette">
                    <?php foreach ($colors as $color): ?>
                    <div class="color-dot" style="background: <?= $color ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" id="auto_style" checked>
                Let AI suggest the best style based on my industry
            </label>
        </div>
        
        <div class="wizard-nav">
            <button class="btn btn-secondary" onclick="prevStep()">← Back</button>
            <button class="btn btn-primary" onclick="nextStep()">Next: Select Pages →</button>
        </div>
    </div>
    
    <!-- Step 3: Pages -->
    <div class="wizard-panel" data-panel="3">
        <h2 class="panel-title">Select pages for your website</h2>
        <p class="panel-description">Choose which pages to include. You can always add more later.</p>
        
        <div class="page-grid">
            <?php 
            $defaultPages = ['homepage', 'about', 'services', 'contact'];
            foreach ($pageTypes as $key => $name): 
            ?>
            <div>
                <input type="checkbox" class="page-checkbox" id="page_<?= $key ?>" value="<?= $key ?>" 
                       <?= in_array($key, $defaultPages) ? 'checked' : '' ?>>
                <label class="page-label" for="page_<?= $key ?>">
                    <span class="checkbox-icon">✓</span>
                    <span><?= htmlspecialchars($name) ?></span>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Auto-fill Images Option -->
        <div class="auto-fill-option <?= $imageApiConfigured ? 'enabled' : 'disabled' ?>">
            <label class="auto-fill-label">
                <input type="checkbox" id="auto_fill_images" class="auto-fill-checkbox" <?= $imageApiConfigured ? 'checked' : '' ?> <?= !$imageApiConfigured ? 'disabled' : '' ?>>
                <span class="auto-fill-toggle"></span>
                <span class="auto-fill-text">
                    <strong>🖼️ Auto-fill with stock images</strong>
                    <span class="auto-fill-desc">
                        <?php if ($imageApiConfigured): ?>
                            Automatically fetch relevant images from Pexels/Unsplash
                        <?php else: ?>
                            <span style="color: #f9e2af;">⚠️ Not configured</span> — 
                            <a href="/admin/ai-settings" style="color: #89b4fa;">Add API keys in AI Settings</a>
                        <?php endif; ?>
                    </span>
                </span>
            </label>
        </div>
        
        <div class="wizard-nav">
            <button class="btn btn-secondary" onclick="prevStep()">← Back</button>
            <button class="btn btn-primary" onclick="nextStep()">Next: Review & Generate →</button>
        </div>
    </div>
    
    <!-- Step 4: Review & Generate -->
    <div class="wizard-panel" data-panel="4">
        <h2 class="panel-title">Review & Generate</h2>
        <p class="panel-description">Review your selections and start the AI generation process.</p>
        
        <div class="theme-info">
            <div class="theme-info-card">
                <h4>📋 Project</h4>
                <p id="review_business">-</p>
                <p id="review_industry" style="color: #6c7086; font-size: 0.875rem;">-</p>
            </div>
            <div class="theme-info-card">
                <h4>🎨 Style</h4>
                <p id="review_style">-</p>
            </div>
            <div class="theme-info-card">
                <h4>📄 Pages</h4>
                <p id="review_pages">-</p>
            </div>
        </div>
        
        <div class="generation-progress" id="progress">
            <div class="progress-spinner"></div>
            <p class="progress-text">Generating your theme...</p>
            <p class="progress-steps" id="progress_step">Step 1/5: Analyzing requirements</p>
        </div>
        
        <div class="result-panel" id="result">
            <div class="result-success">
                <h2>✅ Theme Generated Successfully!</h2>
                <p id="result_message">Your new theme is ready.</p>
            </div>
            
            <div class="result-actions">
                <a href="#" id="preview_link" class="btn btn-primary" target="_blank">👁️ Preview Theme</a>
                <button class="btn btn-success" id="deploy_btn">🚀 Deploy to Theme Builder</button>
                <button class="btn btn-secondary" onclick="resetWizard()">🔄 Create Another</button>
            </div>
        </div>
        
        <div class="wizard-nav" id="generate_nav">
            <button class="btn btn-secondary" onclick="prevStep()">← Back</button>
            <button class="btn btn-success" onclick="generateTheme()" <?= !$aiConfigured ? 'disabled' : '' ?>>
                🚀 Generate Theme
            </button>
        </div>
    </div>
</div>

<script>
// AI Designer 4.0 JavaScript
let currentStep = 1;
let selectedStyle = 'modern';
let generatedTheme = null;

// Style card selection
document.querySelectorAll('.style-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.style-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        selectedStyle = this.dataset.style;
        document.getElementById('auto_style').checked = false;
    });
});

function nextStep() {
    if (currentStep === 1 && !validateStep1()) return;
    if (currentStep === 3 && !validateStep3()) return;
    
    if (currentStep < 4) {
        setStep(currentStep + 1);
    }
    
    if (currentStep === 4) {
        updateReview();
    }
}

function prevStep() {
    if (currentStep > 1) {
        setStep(currentStep - 1);
    }
}

function setStep(step) {
    // Update step buttons
    document.querySelectorAll('.wizard-step').forEach((btn, idx) => {
        btn.classList.remove('active', 'completed');
        if (idx + 1 < step) btn.classList.add('completed');
        if (idx + 1 === step) btn.classList.add('active');
    });
    
    // Update panels
    document.querySelectorAll('.wizard-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    document.querySelector(`[data-panel="${step}"]`).classList.add('active');
    
    currentStep = step;
}

function validateStep1() {
    const name = document.getElementById('business_name').value.trim();
    const brief = document.getElementById('brief').value.trim();
    
    if (!name) {
        alert('Please enter your business name');
        return false;
    }
    if (!brief || brief.length < 20) {
        alert('Please provide a detailed project brief (at least 20 characters)');
        return false;
    }
    return true;
}

function validateStep3() {
    const pages = getSelectedPages();
    if (pages.length < 1) {
        alert('Please select at least one page');
        return false;
    }
    return true;
}

function getSelectedPages() {
    const pages = [];
    document.querySelectorAll('.page-checkbox:checked').forEach(cb => {
        pages.push(cb.value);
    });
    return pages;
}

function updateReview() {
    const businessName = document.getElementById('business_name').value;
    const industry = document.getElementById('industry');
    const industryName = industry.options[industry.selectedIndex].text;
    const pages = getSelectedPages();
    
    document.getElementById('review_business').textContent = businessName;
    document.getElementById('review_industry').textContent = industryName;
    
    const autoStyle = document.getElementById('auto_style').checked;
    const styleCard = document.querySelector('.style-card.selected');
    const styleName = autoStyle ? 'Auto (AI will choose)' : styleCard?.querySelector('.name')?.textContent || 'Modern';
    document.getElementById('review_style').textContent = styleName;
    
    document.getElementById('review_pages').textContent = pages.length + ' pages selected';
}

async function generateTheme() {
    const businessName = document.getElementById('business_name').value.trim();
    const industry = document.getElementById('industry').value;
    const brief = document.getElementById('brief').value.trim();
    const autoStyle = document.getElementById('auto_style').checked;
    const pages = getSelectedPages();
    const autoFillImages = document.getElementById('auto_fill_images').checked;
    
    // Show progress
    document.getElementById('progress').classList.add('active');
    document.getElementById('result').classList.remove('active');
    document.getElementById('generate_nav').style.display = 'none';
    
    try {
        const response = await fetch('/admin/ai-designer/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                business_name: businessName,
                industry: industry,
                brief: brief,
                design_style: autoStyle ? 'auto' : selectedStyle,
                pages: pages,
                auto_fill_images: autoFillImages,
                csrf_token: document.getElementById('csrf_token').value
            })
        });
        
        // Read SSE stream
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        
        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            
            buffer += decoder.decode(value, { stream: true });
            
            // Parse SSE events from buffer
            const lines = buffer.split('\n');
            buffer = lines.pop(); // Keep incomplete line in buffer
            
            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const event = JSON.parse(line.slice(6));
                        handleSSEEvent(event);
                    } catch (e) {
                        console.error('SSE parse error:', e);
                    }
                }
            }
        }
        
    } catch (error) {
        console.error('Generation error:', error);
        alert('Error: ' + error.message);
        document.getElementById('progress').classList.remove('active');
        document.getElementById('generate_nav').style.display = 'flex';
    }
}

function handleSSEEvent(event) {
    console.log('SSE event:', event);
    
    switch (event.type) {
        case 'progress':
            document.getElementById('progress_step').textContent = 
                `Step ${event.step}/${event.total}: ${event.message}`;
            if (event.detail) {
                // Could update a subtitle element if exists
            }
            break;
            
        case 'complete':
            if (event.success && event.theme) {
                generatedTheme = event.theme;
                showResult(event.theme);
            } else {
                alert('Generation completed but no theme returned');
                document.getElementById('progress').classList.remove('active');
                document.getElementById('generate_nav').style.display = 'flex';
            }
            break;
            
        case 'error':
            alert('Generation failed: ' + (event.error || 'Unknown error'));
            document.getElementById('progress').classList.remove('active');
            document.getElementById('generate_nav').style.display = 'flex';
            break;
    }
}

function showResult(theme) {
    document.getElementById('progress').classList.remove('active');
    document.getElementById('result').classList.add('active');
    
    document.getElementById('result_message').textContent = 
        `Theme "${theme.name}" created with ${theme.pages.length} pages.`;
    
    document.getElementById('preview_link').href = theme.preview_url;
    
    document.getElementById('deploy_btn').onclick = () => deployTheme(theme.slug);
}

async function deployTheme(slug) {
    if (!confirm('Deploy this theme to Theme Builder?')) return;
    
    try {
        const response = await fetch('/admin/ai-designer/deploy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slug: slug, csrf_token: document.getElementById('csrf_token').value })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Theme deployed successfully! You can now edit it in Theme Builder.');
        } else {
            alert('Deploy failed: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

function resetWizard() {
    document.getElementById('business_name').value = '';
    document.getElementById('brief').value = '';
    document.getElementById('industry').selectedIndex = 0;
    document.getElementById('auto_style').checked = true;
    
    document.querySelectorAll('.style-card').forEach(c => c.classList.remove('selected'));
    document.querySelector('[data-style="modern"]').classList.add('selected');
    selectedStyle = 'modern';
    
    document.querySelectorAll('.page-checkbox').forEach(cb => {
        cb.checked = ['homepage', 'about', 'services', 'contact'].includes(cb.value);
    });
    
    document.getElementById('progress').classList.remove('active');
    document.getElementById('result').classList.remove('active');
    document.getElementById('generate_nav').style.display = 'flex';
    
    generatedTheme = null;
    setStep(1);
}

// Category filtering
document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const category = this.dataset.category;
        
        // Update active button
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Filter cards
        document.querySelectorAll('.style-card').forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    });
});

// Initialize
document.querySelectorAll('.wizard-step').forEach(btn => {
    btn.addEventListener('click', function() {
        const step = parseInt(this.dataset.step);
        if (step < currentStep || this.classList.contains('completed')) {
            setStep(step);
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
?>
