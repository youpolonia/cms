<?php
/**
 * AI Image Generator - Modern UI
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';

cms_session_start('admin');
csrf_boot('admin');


cms_require_admin_role();

// Check if AI images module exists
$aiConfigured = false;
if (file_exists(CMS_ROOT . '/core/ai_images.php')) {
    require_once CMS_ROOT . '/core/ai_images.php';
    $aiConfigured = function_exists('ai_images_is_configured') ? ai_images_is_configured() : false;
}

$form = [
    'prompt' => '',
    'style' => 'photorealistic',
    'aspect' => '1:1',
    'quality' => 'standard',
    'notes' => '',
    'seo_name' => ''
];

$generationResult = null;
$generationError = null;

// Initialize save result variables
$gallerySaveSuccess = false;
$gallerySaveError = null;
$gallerySaveUrl = null;

// Handle Save to Gallery action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_to_gallery') {
    csrf_validate_or_403();

    $imagePath = trim($_POST['image_path'] ?? '');
    $imageTitle = trim($_POST['image_title'] ?? '');
    $imageAlt = trim($_POST['image_alt'] ?? '');

    if (function_exists('ai_images_save_to_gallery')) {
        $saveResult = ai_images_save_to_gallery($imagePath, $imageTitle, $imageAlt);
        if ($saveResult['ok']) {
            $gallerySaveSuccess = true;
            $gallerySaveUrl = $saveResult['url'];
        } else {
            $gallerySaveError = $saveResult['error'];
        }
    } else {
        $gallerySaveError = 'Save to gallery function not available.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_image') {
    csrf_validate_or_403();

    // Check if AJAX request
    $isAjax = !empty($_POST['ajax']);

    $form['prompt'] = trim($_POST['prompt'] ?? '');
    $form['style'] = trim($_POST['style'] ?? 'photorealistic');
    $form['aspect'] = trim($_POST['aspect'] ?? '1:1');
    $form['quality'] = trim($_POST['quality'] ?? 'standard');
    $form['notes'] = trim($_POST['notes'] ?? '');
    $form['seo_name'] = trim($_POST['seo_name'] ?? '');

    if (function_exists('ai_images_generate')) {
        $result = ai_images_generate($form);

        // Return JSON for AJAX requests
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($result['ok']) {
                echo json_encode([
                    'ok' => true,
                    'path' => $result['path'],
                    'model' => $result['model'] ?? 'dall-e-3'
                ]);
            } else {
                echo json_encode([
                    'ok' => false,
                    'error' => $result['error'] ?? 'Generation failed'
                ]);
            }
            exit;
        }

        if ($result['ok']) {
            $generationResult = $result;
        } else {
            $generationError = $result['error'];
        }
    } else {
        // Return JSON error for AJAX requests
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok' => false,
                'error' => 'AI Images module not available. Please configure OpenAI API.'
            ]);
            exit;
        }
        $generationError = 'AI Images module not available. Please configure OpenAI API.';
    }
}

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$csrf = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Image Generator - CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --bg-primary: #1e1e2e;
        --bg-secondary: #181825;
        --bg-tertiary: #313244;
        --bg-hover: #45475a;
        --bg-elevated: #313244;
        --text-primary: #cdd6f4;
        --text-secondary: #a6adc8;
        --text-muted: #6c7086;
        --accent: #89b4fa;
        --accent-color: #89b4fa;
        --accent-hover: #b4befe;
        --success: #a6e3a1;
        --success-color: #a6e3a1;
        --warning: #f9e2af;
        --warning-color: #f9e2af;
        --danger: #f38ba8;
        --danger-color: #f38ba8;
        --border: #313244;
        --border-color: #313244;
        --border-light: #45475a;
        --purple: #cba6f7;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-size: 14px;
        line-height: 1.6;
        min-height: 100vh;
    }

    /* Main Layout */
    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 32px;
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 32px;
    }

    @media (max-width: 1024px) {
        .main-container {
            grid-template-columns: 1fr;
        }
    }

    /* Cards */
    .card {
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-title {
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-body {
        padding: 24px;
    }

    /* Form */
    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .form-label .required {
        color: var(--danger);
    }

    .form-hint {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 12px 16px;
        background: var(--bg-secondary);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text-primary);
        font-family: inherit;
        font-size: 14px;
        transition: all 0.15s;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.15);
    }

    .form-input::placeholder,
    .form-textarea::placeholder {
        color: var(--text-muted);
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
    }

    /* Style Selector */
    .style-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .style-option {
        position: relative;
    }

    .style-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .style-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 16px 12px;
        background: var(--bg-secondary);
        border: 2px solid var(--border);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.15s;
        text-align: center;
    }

    .style-option label:hover {
        border-color: var(--border-light);
        background: var(--bg-tertiary);
    }

    .style-option input:checked + label {
        border-color: var(--accent);
        background: rgba(137, 180, 250, 0.1);
    }

    .style-option .icon {
        font-size: 28px;
    }

    .style-option .name {
        font-size: 12px;
        font-weight: 500;
    }

    /* Aspect Ratio */
    .aspect-grid {
        display: flex;
        gap: 10px;
    }

    .aspect-option {
        position: relative;
        flex: 1;
    }

    .aspect-option input {
        position: absolute;
        opacity: 0;
    }

    .aspect-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 14px;
        background: var(--bg-secondary);
        border: 2px solid var(--border);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .aspect-option label:hover {
        border-color: var(--border-light);
    }

    .aspect-option input:checked + label {
        border-color: var(--accent);
        background: rgba(137, 180, 250, 0.1);
    }

    .aspect-preview {
        background: var(--bg-tertiary);
        border-radius: 4px;
    }

    .aspect-preview.square { width: 32px; height: 32px; }
    .aspect-preview.landscape { width: 40px; height: 28px; }
    .aspect-preview.portrait { width: 28px; height: 40px; }
    .aspect-preview.wide { width: 48px; height: 24px; }

    .aspect-option .name {
        font-size: 11px;
        color: var(--text-secondary);
    }

    /* Submit Button */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 14px 28px;
        font-family: inherit;
        font-size: 15px;
        font-weight: 600;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--accent), #cba6f7);
        color: #000;
        width: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(137, 180, 250, 0.3);
    }

    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-secondary {
        background: var(--bg-tertiary);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: var(--bg-hover);
    }

    /* Alerts */
    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert-icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-weight: 600;
        margin-bottom: 4px;
    }

    .alert-warning {
        background: rgba(249, 226, 175, 0.1);
        border: 1px solid rgba(249, 226, 175, 0.3);
        color: var(--warning);
    }

    .alert-error {
        background: rgba(243, 139, 168, 0.1);
        border: 1px solid rgba(243, 139, 168, 0.3);
        color: var(--danger);
    }

    .alert-success {
        background: rgba(166, 227, 161, 0.1);
        border: 1px solid rgba(166, 227, 161, 0.3);
        color: var(--success);
    }

    /* Right Sidebar */
    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Preview Card */
    .preview-area {
        aspect-ratio: 1;
        background: var(--bg-secondary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .preview-placeholder {
        text-align: center;
        color: var(--text-muted);
    }

    .preview-placeholder .icon {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    .preview-area img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Tips Card */
    .tips-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .tip-item {
        display: flex;
        gap: 12px;
        padding: 12px;
        background: var(--bg-secondary);
        border-radius: 10px;
    }

    .tip-icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .tip-text {
        font-size: 13px;
        color: var(--text-secondary);
    }

    /* History */
    .history-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .history-item {
        aspect-ratio: 1;
        background: var(--bg-secondary);
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.15s;
    }

    .history-item:hover {
        transform: scale(1.05);
    }

    .history-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .history-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 24px;
        color: var(--text-muted);
        font-size: 13px;
    }

    /* Result Modal */
    .result-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 32px;
    }

    .result-overlay.active {
        display: flex;
    }

    .result-modal {
        background: var(--bg-primary);
        border-radius: 20px;
        max-width: 800px;
        width: 100%;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .result-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .result-title {
        font-size: 18px;
        font-weight: 600;
    }

    .result-close {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-tertiary);
        border: none;
        border-radius: 8px;
        color: var(--text-secondary);
        font-size: 20px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .result-close:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }

    .result-body {
        padding: 24px;
        overflow-y: auto;
    }

    .result-image {
        width: 100%;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .result-actions {
        display: flex;
        gap: 12px;
    }

    .result-actions .btn {
        flex: 1;
    }

    /* Loading */
    .generating {
        position: relative;
    }

    .generating::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(24, 24, 37, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    @keyframes pulse {
        0%, 100% { opacity: 0.4; }
        50% { opacity: 1; }
    }

    .loading-dots {
        display: flex;
        gap: 8px;
    }

    .loading-dots span {
        width: 12px;
        height: 12px;
        background: var(--accent);
        border-radius: 50%;
        animation: pulse 1.4s infinite;
    }

    .loading-dots span:nth-child(2) { animation-delay: 0.2s; }
    .loading-dots span:nth-child(3) { animation-delay: 0.4s; }
    </style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🎨',
    'title' => 'AI Image Generator',
    'description' => 'Create stunning images with AI',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--accent-color), var(--purple)',
    'actions' => $aiConfigured
        ? []
        : [['type' => 'link', 'url' => '/admin/ai-settings', 'text' => '⚙️ Configure AI', 'class' => 'secondary']]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Left: Form -->
        <div class="form-area">
            <?php if (!$aiConfigured): ?>
                <div class="alert alert-warning">
                    <span class="alert-icon">⚠️</span>
                    <div class="alert-content">
                        <div class="alert-title">API Not Configured</div>
                        <p>Please configure OpenAI API in <a href="/admin/ai-settings.php" style="color: inherit; text-decoration: underline;">AI Settings</a> to generate images.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($generationError): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">❌</span>
                    <div class="alert-content">
                        <div class="alert-title">Generation Failed</div>
                        <p><?php echo esc($generationError); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($generationResult): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✅</span>
                    <div class="alert-content">
                        <div class="alert-title">Image Generated!</div>
                        <p>Your image has been created successfully.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($gallerySaveSuccess): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✅</span>
                    <div class="alert-content">
                        <div class="alert-title">Saved to Gallery!</div>
                        <p>Image is now available in <a href="/admin/media" style="color: inherit; text-decoration: underline;">Media Library</a>.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($gallerySaveError): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">❌</span>
                    <div class="alert-content">
                        <div class="alert-title">Save Failed</div>
                        <p><?php echo esc($gallerySaveError); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <span class="card-title">
                        <span>✏️</span>
                        Create New Image
                    </span>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo esc($csrf); ?>">
                        <input type="hidden" name="action" value="generate_image">

                        <div class="form-group">
                            <label class="form-label">
                                Describe your image <span class="required">*</span>
                            </label>
                            <textarea 
                                name="prompt" 
                                class="form-textarea" 
                                placeholder="A serene mountain landscape at sunset with a calm lake reflecting the orange and purple sky, pine trees in the foreground..."
                                required
                            ><?php echo esc($form['prompt']); ?></textarea>
                            <p class="form-hint">Be specific and descriptive. Include details about subject, setting, lighting, mood, and colors.</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Style</label>
                            <div class="style-grid">
                                <div class="style-option">
                                    <input type="radio" name="style" value="photorealistic" id="style-photo" <?php echo $form['style'] === 'photorealistic' ? 'checked' : ''; ?>>
                                    <label for="style-photo">
                                        <span class="icon">📷</span>
                                        <span class="name">Photorealistic</span>
                                    </label>
                                </div>
                                <div class="style-option">
                                    <input type="radio" name="style" value="illustration" id="style-illust" <?php echo $form['style'] === 'illustration' ? 'checked' : ''; ?>>
                                    <label for="style-illust">
                                        <span class="icon">🎨</span>
                                        <span class="name">Illustration</span>
                                    </label>
                                </div>
                                <div class="style-option">
                                    <input type="radio" name="style" value="3d" id="style-3d" <?php echo $form['style'] === '3d' ? 'checked' : ''; ?>>
                                    <label for="style-3d">
                                        <span class="icon">🧊</span>
                                        <span class="name">3D Render</span>
                                    </label>
                                </div>
                                <div class="style-option">
                                    <input type="radio" name="style" value="anime" id="style-anime" <?php echo $form['style'] === 'anime' ? 'checked' : ''; ?>>
                                    <label for="style-anime">
                                        <span class="icon">🌸</span>
                                        <span class="name">Anime</span>
                                    </label>
                                </div>
                                <div class="style-option">
                                    <input type="radio" name="style" value="watercolor" id="style-water" <?php echo $form['style'] === 'watercolor' ? 'checked' : ''; ?>>
                                    <label for="style-water">
                                        <span class="icon">💧</span>
                                        <span class="name">Watercolor</span>
                                    </label>
                                </div>
                                <div class="style-option">
                                    <input type="radio" name="style" value="minimalist" id="style-minimal" <?php echo $form['style'] === 'minimalist' ? 'checked' : ''; ?>>
                                    <label for="style-minimal">
                                        <span class="icon">◻️</span>
                                        <span class="name">Minimalist</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Aspect Ratio</label>
                            <div class="aspect-grid">
                                <div class="aspect-option">
                                    <input type="radio" name="aspect" value="1:1" id="aspect-1" <?php echo $form['aspect'] === '1:1' ? 'checked' : ''; ?>>
                                    <label for="aspect-1">
                                        <div class="aspect-preview square"></div>
                                        <span class="name">1:1</span>
                                    </label>
                                </div>
                                <div class="aspect-option">
                                    <input type="radio" name="aspect" value="16:9" id="aspect-16" <?php echo $form['aspect'] === '16:9' ? 'checked' : ''; ?>>
                                    <label for="aspect-16">
                                        <div class="aspect-preview landscape"></div>
                                        <span class="name">16:9</span>
                                    </label>
                                </div>
                                <div class="aspect-option">
                                    <input type="radio" name="aspect" value="9:16" id="aspect-9" <?php echo $form['aspect'] === '9:16' ? 'checked' : ''; ?>>
                                    <label for="aspect-9">
                                        <div class="aspect-preview portrait"></div>
                                        <span class="name">9:16</span>
                                    </label>
                                </div>
                                <div class="aspect-option">
                                    <input type="radio" name="aspect" value="21:9" id="aspect-21" <?php echo $form['aspect'] === '21:9' ? 'checked' : ''; ?>>
                                    <label for="aspect-21">
                                        <div class="aspect-preview wide"></div>
                                        <span class="name">21:9</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Additional Notes</label>
                            <input 
                                type="text" 
                                name="notes" 
                                class="form-input" 
                                placeholder="e.g., high contrast, warm tones, no text..."
                                value="<?php echo esc($form['notes']); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">SEO Filename <span style="color: var(--text-muted); font-weight: normal;">(optional)</span></label>
                            <input 
                                type="text" 
                                name="seo_name" 
                                class="form-input" 
                                placeholder="e.g., sunset-beach-vacation"
                                value="<?php echo esc($form['seo_name']); ?>"
                            >
                            <p class="form-hint">Use lowercase letters, numbers, and hyphens. Auto-generated from prompt if empty.</p>
                        </div>

                        <button id="generateBtn" onclick="this.closest('form').submit(); return false;" type="submit" class="btn btn-primary" <?php echo !$aiConfigured ? 'disabled' : ''; ?>>
                            <span>✨</span>
                            Generate Image
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Sidebar -->
        <div class="sidebar">
            <!-- Preview -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">
                        <span>🖼️</span>
                        Preview
                    </span>
                </div>
                <div class="card-body">
                    <div class="preview-area" id="preview-area">
                        <?php if ($generationResult && !empty($generationResult['path'])): ?>
                            <img src="<?php echo esc($generationResult['path']); ?>" alt="Generated image">
                        <?php else: ?>
                            <div class="preview-placeholder">
                                <div class="icon">🎨</div>
                                <p>Your generated image<br>will appear here</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($generationResult && !empty($generationResult['path'])): ?>
                        <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; gap: 8px;">
                                <a href="<?php echo esc($generationResult['path']); ?>" download class="btn btn-secondary" style="flex: 1;">
                                    ⬇️ Download
                                </a>
                                <button class="btn btn-secondary" style="flex: 1;" onclick="copyPath()">
                                    📋 Copy Path
                                </button>
                            </div>

                            <!-- Save to Gallery Form -->
                            <form method="POST" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px; padding-top: 12px; border-top: 1px solid var(--border);">
                                <input type="hidden" name="csrf_token" value="<?php echo esc($csrf); ?>">
                                <input type="hidden" name="action" value="save_to_gallery">
                                <input type="hidden" name="image_path" value="<?php echo esc($generationResult['path']); ?>">
                                <input type="text" name="image_title" class="form-input" placeholder="Image title (optional)" style="font-size: 12px; padding: 8px;">
                                <input type="text" name="image_alt" class="form-input" placeholder="Alt text (optional)" style="font-size: 12px; padding: 8px;">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">
                                    📁 Save to Gallery
                                </button>
                            </form>
                        </div>
                        <input type="hidden" id="image-path" value="<?php echo esc($generationResult['path']); ?>">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tips -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">
                        <span>💡</span>
                        Tips
                    </span>
                </div>
                <div class="card-body">
                    <div class="tips-list">
                        <div class="tip-item">
                            <span class="tip-icon">🎯</span>
                            <span class="tip-text">Be specific about subject, setting, and mood</span>
                        </div>
                        <div class="tip-item">
                            <span class="tip-icon">🌈</span>
                            <span class="tip-text">Mention colors and lighting for better results</span>
                        </div>
                        <div class="tip-item">
                            <span class="tip-icon">📐</span>
                            <span class="tip-text">Use 16:9 for headers, 1:1 for thumbnails</span>
                        </div>
                        <div class="tip-item">
                            <span class="tip-icon">🚫</span>
                            <span class="tip-text">Add "no text" to avoid unwanted text in images</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">
                        <span>🕐</span>
                        Recent
                    </span>
                </div>
                <div class="card-body">
                    <div class="history-grid">
                        <div class="history-empty">
                            No recent generations
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function copyPath() {
        const path = document.getElementById('image-path');
        if (path) {
            navigator.clipboard.writeText(path.value);
            alert('Path copied to clipboard!');
        }
    }
    </script>
</body>
</html>
