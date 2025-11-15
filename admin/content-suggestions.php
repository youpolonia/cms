<?php
/**
 * Content Suggestions Panel
 * 
 * Provides AI-powered content improvement suggestions
 */

// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';
// Verify admin access
require_once __DIR__ . '/security/admin-check.php';
require_once __DIR__ . '/../core/csrf.php';

// Load AI configuration
$configFile = __DIR__ . '/../config/ai-config.json';
if (!file_exists($configFile)) {
    header('Location: config-ai.php');
    exit;
}
$config = json_decode(file_get_contents($configFile), true);

$pageTitle = "Content Suggestions";
require_once __DIR__ . '/security/admin-header.php';

// Handle content analysis request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $originalContent = trim($_POST['content'] ?? '');
    $action = $_POST['action'] ?? 'analyze';
    
    if (!empty($originalContent)) {
        cms_session_start('admin');
        
        if ($action === 'analyze') {
            // Simulate AI analysis (will be replaced with actual API call)
            $suggestions = [
                'Improved readability' => "This is a simulated readability improvement suggestion.",
                'SEO optimization' => "This is a simulated SEO suggestion.",
                'Tone adjustment' => "This is a simulated tone adjustment suggestion."
            ];
            
            $_SESSION['content_suggestions'] = [
                'original' => $originalContent,
                'suggestions' => $suggestions,
                'timestamp' => time()
            ];
        } elseif ($action === 'apply' && isset($_POST['suggestion_key'])) {
            // Apply selected suggestion
            $key = $_POST['suggestion_key'];
            if (isset($_SESSION['content_suggestions']['suggestions'][$key])) {
                $_SESSION['content_history'][] = [
                    'original' => $_SESSION['content_suggestions']['original'],
                    'improved' => $_SESSION['content_suggestions']['suggestions'][$key],
                    'applied_suggestion' => $key,
                    'timestamp' => time()
                ];
                $originalContent = $_SESSION['content_suggestions']['suggestions'][$key];
            }
        }
    }
}

?><div class="admin-container">
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
    <div class="content-suggestions">
        <form method="post" class="suggestion-form">
            <?= csrf_field(); ?> 
            <div class="form-group">
                <label for="content">Content to Analyze:</label>
                <textarea id="content" name="content" class="form-control" 
                          rows="8" required><?= 
                    isset($_SESSION['content_suggestions']['original']) 
                    ? htmlspecialchars($_SESSION['content_suggestions']['original']) 
                    : '' 
                ?></textarea>
            </div>

            <button type="submit" name="action" value="analyze" class="btn btn-primary">
                Analyze Content
            </button>
        </form>

        <?php if (!empty($_SESSION['content_suggestions'])): ?>
        <div class="suggestions-results mt-4">
            <h3>Improvement Suggestions</h3>
            
            <div class="original-content mb-3">
                <h5>Original Content:</h5>
                <div class="content-preview">
                    <?= nl2br(htmlspecialchars($_SESSION['content_suggestions']['original'])) ?>
                </div>
            </div>

            <form method="post">
                <?= csrf_field(); ?> 
                <input type="hidden" name="content" 
                       value="<?= htmlspecialchars($_SESSION['content_suggestions']['original']) ?>">
                
                <?php foreach ($_SESSION['content_suggestions']['suggestions'] as $key => $suggestion): ?>
                <div class="suggestion-item">
                    <h5><?= htmlspecialchars($key) ?></h5>
                    <div class="suggestion-preview">
                        <?= nl2br(htmlspecialchars($suggestion)) ?>
                    </div>
                    <button type="submit" name="action" value="apply" 
                            class="btn btn-sm btn-success mt-2">
                        Apply This Suggestion
                    </button>
                </div>
                <?php endforeach; ?>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="content-history mt-5">
        <h3>Improvement History</h3>
        <?php
        cms_session_start('admin');
        if (!empty($_SESSION['content_history'])): ?>            <?php foreach (array_reverse($_SESSION['content_history']) as $item): ?>
            <div class="history-item">
                <small class="text-muted">
                    <?= date('Y-m-d H:i', $item['timestamp']) ?>                    <?= isset($item['applied_suggestion'])
                        ? '(Applied: ' . htmlspecialchars($item['applied_suggestion']) . ')'
                        : '' ?> 
                </small>
                <div class="original"><?= nl2br(htmlspecialchars($item['original'])) ?></div>
                <div class="improved"><?= nl2br(htmlspecialchars($item['improved'])) ?></div>
            </div>
        <?php 
            endforeach; ?>        <?php else: ?>
            <p>No improvement history yet.</p>
        <?php endif; ?> 
    </div>
</div>

<?php require_once __DIR__ . '/security/admin-footer.php';
