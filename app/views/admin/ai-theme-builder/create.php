<?php
/**
 * AI Theme Builder - Create New Theme
 */
ob_start();
?>

<style>
.theme-wizard {
    max-width: 800px;
    margin: 0 auto;
}
.wizard-step {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
}
.wizard-step h2 {
    font-size: 20px;
    font-weight: 600;
    margin: 0 0 24px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 8px;
    color: var(--text-secondary);
}
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 14px;
    transition: border-color 0.2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--accent);
}
.form-group textarea {
    min-height: 100px;
    resize: vertical;
}
.color-input-wrapper {
    display: flex;
    gap: 12px;
    align-items: center;
}
.color-input-wrapper input[type="color"] {
    width: 60px;
    height: 44px;
    padding: 4px;
    border-radius: 8px;
    cursor: pointer;
}
.color-input-wrapper input[type="text"] {
    flex: 1;
}
.type-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}
.type-card {
    background: var(--bg-tertiary);
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 20px 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}
.type-card:hover {
    border-color: var(--accent);
}
.type-card:has(input:checked) {
    border-color: var(--accent);
    background: rgba(99, 102, 241, 0.1);
}
.type-card input {
    display: none;
}
.type-card .icon {
    font-size: 32px;
    margin-bottom: 8px;
}
.type-card .label {
    font-size: 13px;
    font-weight: 500;
}
.style-options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.style-option {
    padding: 10px 20px;
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
    background: rgba(99, 102, 241, 0.1);
}
.style-option input {
    display: none;
}
.generate-btn {
    width: 100%;
    padding: 16px 32px;
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
    transition: all 0.2s;
}
.generate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
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
.result-card {
    background: var(--bg-secondary);
    border: 2px solid var(--accent);
    border-radius: 16px;
    padding: 32px;
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
</style>

<div class="theme-wizard">
    <div style="margin-bottom: 24px;">
        <a href="/admin/themes" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
            ← Back to Themes
        </a>
    </div>
    
    <h1 style="font-size: 28px; font-weight: 700; margin: 0 0 8px 0;">✨ Create New Theme with AI</h1>
    <p style="color: var(--text-muted); margin: 0 0 32px 0;">Describe your website and let AI generate a complete theme for you.</p>
    
    <form id="theme-form">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        
        <!-- Step 1: Basic Info -->
        <div class="wizard-step">
            <h2><span style="background: var(--accent); color: #000; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">1</span> Basic Information</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="theme-name">Theme Name *</label>
                    <input type="text" id="theme-name" name="name" placeholder="my-awesome-theme" required pattern="[a-z0-9\-]+" title="Lowercase letters, numbers and hyphens only">
                </div>
                <div class="form-group">
                    <label for="primary-color">Primary Brand Color</label>
                    <div class="color-input-wrapper">
                        <input type="color" id="primary-color" name="primary_color" value="#6366f1">
                        <input type="text" id="primary-color-text" value="#6366f1" placeholder="#6366f1">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Describe Your Website *</label>
                <textarea id="description" name="description" placeholder="E.g., A professional law firm website with a modern, trustworthy look. Should convey expertise and reliability. Target audience is corporate clients seeking legal services." required></textarea>
            </div>
        </div>
        
        <!-- Step 2: Website Type -->
        <div class="wizard-step">
            <h2><span style="background: var(--accent); color: #000; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">2</span> Website Type</h2>
            
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
                    <div class="label">Landing Page</div>
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
            </div>
        </div>
        
        <!-- Step 3: Style -->
        <div class="wizard-step">
            <h2><span style="background: var(--accent); color: #000; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">3</span> Visual Style</h2>
            
            <div class="form-group">
                <label>Style</label>
                <div class="style-options">
                    <label class="style-option">
                        <input type="radio" name="style" value="modern" checked>
                        Modern & Clean
                    </label>
                    <label class="style-option">
                        <input type="radio" name="style" value="minimalist">
                        Minimalist
                    </label>
                    <label class="style-option">
                        <input type="radio" name="style" value="bold">
                        Bold & Dynamic
                    </label>
                    <label class="style-option">
                        <input type="radio" name="style" value="elegant">
                        Elegant & Luxurious
                    </label>
                    <label class="style-option">
                        <input type="radio" name="style" value="playful">
                        Playful & Creative
                    </label>
                    <label class="style-option">
                        <input type="radio" name="style" value="corporate">
                        Corporate & Professional
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 24px;">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('AI Theme Builder JS loaded');
    
    // Sync color inputs
    const colorPicker = document.getElementById('primary-color');
    const colorText = document.getElementById('primary-color-text');
    if (colorPicker && colorText) {
        colorPicker.addEventListener('input', () => colorText.value = colorPicker.value);
        colorText.addEventListener('input', () => {
            if (/^#[0-9a-f]{6}$/i.test(colorText.value)) {
                colorPicker.value = colorText.value;
            }
        });
        console.log('Color inputs initialized');
    }
    
    // Type cards selection
    const typeCards = document.querySelectorAll('.type-card');
    console.log('Found type-cards:', typeCards.length);
    typeCards.forEach(card => {
        card.addEventListener('click', function(e) {
            console.log('Type card clicked');
            document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // Style options selection
    const styleOptions = document.querySelectorAll('.style-option');
    console.log('Found style-options:', styleOptions.length);
    styleOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            console.log('Style option clicked');
            const group = this.closest('.style-options');
            group.querySelectorAll('.style-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

// Form submission
const themeForm = document.getElementById('theme-form');
console.log('Form found:', themeForm);

if (themeForm) {
    themeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Form submitted');
        
        const btn = document.getElementById('generate-btn');
        const btnText = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.spinner');
        
        btn.disabled = true;
        btnText.textContent = 'Generating... This may take a minute';
        spinner.style.display = 'block';
        
        const formData = new FormData(e.target);
        console.log('Sending request...');
        
        try {
            const response = await fetch('/admin/ai-theme-builder/generate', {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status);
            const text = await response.text();
            console.log('Response text:', text.substring(0, 500));
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseErr) {
                alert('Invalid JSON response. Check console for details.');
                console.error('JSON parse error:', parseErr);
                console.error('Full response:', text);
                btn.disabled = false;
                btnText.textContent = '✨ Generate Theme with AI';
                spinner.style.display = 'none';
                return;
            }
            
            if (data.success) {
                document.getElementById('theme-form').style.display = 'none';
                document.getElementById('result-card').classList.add('show');
                document.getElementById('result-message').textContent = 'Theme "' + data.theme_name + '" has been created.';
                document.getElementById('preview-link').href = data.preview_url;
            } else {
                alert('Error: ' + (data.error || 'Failed to generate theme'));
                btn.disabled = false;
                btnText.textContent = '✨ Generate Theme with AI';
                spinner.style.display = 'none';
            }
        } catch (err) {
            alert('Network error: ' + err.message);
            console.error('Fetch error:', err);
            btn.disabled = false;
            btnText.textContent = '✨ Generate Theme with AI';
            spinner.style.display = 'none';
        }
    });
} // end if(themeForm)
}); // end DOMContentLoaded
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
