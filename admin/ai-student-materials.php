<?php
/**
 * AI Student Material Generator
 * Generate educational materials for teachers
 */

if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) {
        die('Cannot determine CMS_ROOT');
    }
    define('CMS_ROOT', $cmsRoot);
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_student_materials.php';

cms_session_start('admin');
csrf_boot('admin');


cms_require_admin_role();

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$message = '';
$messageType = 'info';
$generatedMaterial = null;
$viewMaterial = null;
$difficultyAnalysis = null;

// Get options
$types = ai_materials_get_types();
$subjects = ai_materials_get_subjects();
$grades = ai_materials_get_grades();
$difficulties = ai_materials_get_difficulties();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'generate':
            $type = $_POST['type'] ?? 'worksheet';
            $params = [
                'topic' => trim($_POST['topic'] ?? ''),
                'subject' => $_POST['subject'] ?? 'other',
                'grade' => $_POST['grade'] ?? '6',
                'difficulty' => $_POST['difficulty'] ?? 'medium',
                'question_count' => (int)($_POST['question_count'] ?? 10),
                'include_answers' => isset($_POST['include_answers']),
                'language' => $_POST['language'] ?? 'English',
                'instructions' => trim($_POST['instructions'] ?? ''),
            ];

            if (empty($params['topic'])) {
                $message = 'Please enter a topic.';
                $messageType = 'warning';
            } else {
                $generatedMaterial = ai_materials_generate($type, $params);

                if ($generatedMaterial['ok']) {
                    $difficultyAnalysis = ai_materials_analyze_difficulty($generatedMaterial['content']);
                    $message = 'Material generated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Generation failed: ' . ($generatedMaterial['error'] ?? 'Unknown error');
                    $messageType = 'danger';
                }
            }
            break;

        case 'save':
            $materialData = [
                'type' => $_POST['material_type'] ?? '',
                'type_label' => $_POST['material_type_label'] ?? '',
                'content' => $_POST['material_content'] ?? '',
                'params' => json_decode($_POST['material_params'] ?? '{}', true),
            ];

            $saveResult = ai_materials_save($materialData);

            if ($saveResult['ok']) {
                $message = 'Material saved! ID: ' . $saveResult['id'];
                $messageType = 'success';
            } else {
                $message = 'Save failed: ' . ($saveResult['error'] ?? 'Unknown error');
                $messageType = 'danger';
            }
            break;

        case 'delete':
            $id = $_POST['material_id'] ?? '';
            if (!empty($id) && ai_materials_delete($id)) {
                $message = 'Material deleted.';
                $messageType = 'success';
            } else {
                $message = 'Delete failed.';
                $messageType = 'danger';
            }
            break;
    }
}

// Handle view
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $viewMaterial = ai_materials_load($_GET['view']);
    if ($viewMaterial) {
        $difficultyAnalysis = ai_materials_analyze_difficulty($viewMaterial['content']);
    }
}

// Handle print/export
if (isset($_GET['print']) && !empty($_GET['print'])) {
    $printMaterial = ai_materials_load($_GET['print']);
    if ($printMaterial) {
        header('Content-Type: text/html; charset=UTF-8');
        echo ai_materials_to_html($printMaterial);
        exit;
    }
}

// Get saved materials and stats
$savedMaterials = ai_materials_list(20);
$stats = ai_materials_get_stats();

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <div>
                <h1 class="mb-0">🎓 AI Student Material Generator</h1>
                <p class="text-muted mb-0">Create worksheets, tests, quizzes, and lesson plans with AI</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= esc($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- View Material Modal Content -->
        <?php if ($viewMaterial): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= esc($viewMaterial['type_label'] ?? 'Material') ?>: <?= esc($viewMaterial['params']['topic'] ?? 'Unknown') ?></h5>
                <div>
                    <a href="?print=<?= esc($viewMaterial['id']) ?>" target="_blank" class="btn btn-sm btn-light">🖨️ Print</a>
                    <a href="?" class="btn btn-sm btn-outline-light">✕ Close</a>
                </div>
            </div>
            <div class="card-body">
                <div style="background:var(--bg3);padding:12px;border-radius:8px;color:var(--text)" style="white-space: pre-wrap; font-family: monospace;">
<?= esc($viewMaterial['content']) ?>
                </div>
                <?php if ($difficultyAnalysis): ?>
                <hr>
                <div class="row text-center">
                    <div class="col-md-2">
                        <small class="text-muted">Words</small>
                        <div class="fs-5"><?= $difficultyAnalysis['word_count'] ?></div>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted">Sentences</small>
                        <div class="fs-5"><?= $difficultyAnalysis['sentence_count'] ?></div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Reading Level</small>
                        <div class="fs-5"><?= $difficultyAnalysis['difficulty_label'] ?></div>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted">FK Grade</small>
                        <div class="fs-5"><?= $difficultyAnalysis['flesch_kincaid_grade'] ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Generator Form -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">📝 Generate New Material</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="generate">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Material Type *</label>
                                    <select name="type" class="form-select" required>
                                        <?php foreach ($types as $key => $type): ?>
                                            <option value="<?= $key ?>" <?= ($_POST['type'] ?? '') === $key ? 'selected' : '' ?>>
                                                <?= $type['icon'] ?> <?= esc($type['label']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subject *</label>
                                    <select name="subject" class="form-select" required>
                                        <?php foreach ($subjects as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= ($_POST['subject'] ?? '') === $key ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Topic / Title *</label>
                                <input type="text" name="topic" class="form-control" required
                                       value="<?= esc($_POST['topic'] ?? '') ?>"
                                       placeholder="e.g., Photosynthesis, World War II, Fractions">
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Grade Level</label>
                                    <select name="grade" class="form-select">
                                        <?php foreach ($grades as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= ($_POST['grade'] ?? '6') === $key ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Difficulty</label>
                                    <select name="difficulty" class="form-select">
                                        <?php foreach ($difficulties as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= ($_POST['difficulty'] ?? 'medium') === $key ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Questions/Items</label>
                                    <input type="number" name="question_count" class="form-control"
                                           value="<?= esc($_POST['question_count'] ?? '10') ?>"
                                           min="3" max="50">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Language</label>
                                    <select name="language" class="form-select">
                                        <option value="English" <?= ($_POST['language'] ?? '') === 'English' ? 'selected' : '' ?>>English</option>
                                        <option value="Polish" <?= ($_POST['language'] ?? '') === 'Polish' ? 'selected' : '' ?>>Polish</option>
                                        <option value="Spanish" <?= ($_POST['language'] ?? '') === 'Spanish' ? 'selected' : '' ?>>Spanish</option>
                                        <option value="German" <?= ($_POST['language'] ?? '') === 'German' ? 'selected' : '' ?>>German</option>
                                        <option value="French" <?= ($_POST['language'] ?? '') === 'French' ? 'selected' : '' ?>>French</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="include_answers" id="include_answers"
                                               <?= isset($_POST['include_answers']) || !isset($_POST['action']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="include_answers">
                                            Include Answer Key
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Additional Instructions (optional)</label>
                                <textarea name="instructions" class="form-control" rows="2"
                                          placeholder="Any specific requirements..."><?= esc($_POST['instructions'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg">
                                🚀 Generate Material
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Generated Result -->
                <?php if ($generatedMaterial && $generatedMaterial['ok']): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">✅ Generated: <?= esc($generatedMaterial['type_label']) ?></h5>
                        <button class="btn btn-sm btn-light" onclick="copyMaterial()">📋 Copy</button>
                    </div>
                    <div class="card-body">
                        <div style="background:var(--bg3);padding:12px;border-radius:8px;color:var(--text);margin-bottom:12px" id="generated-content" style="white-space: pre-wrap; font-family: monospace; max-height: 500px; overflow-y: auto;">
<?= esc($generatedMaterial['content']) ?>
                        </div>

                        <?php if ($difficultyAnalysis): ?>
                        <div class="row text-center mb-3">
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <small class="text-muted">Words</small>
                                    <div class="fs-5"><?= $difficultyAnalysis['word_count'] ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <small class="text-muted">Reading Level</small>
                                    <div class="fs-5"><?= $difficultyAnalysis['difficulty_label'] ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <small class="text-muted">FK Grade</small>
                                    <div class="fs-5"><?= $difficultyAnalysis['flesch_kincaid_grade'] ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <small class="text-muted">Avg Words/Sentence</small>
                                    <div class="fs-5"><?= $difficultyAnalysis['avg_words_per_sentence'] ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Save Form -->
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="material_type" value="<?= esc($generatedMaterial['type']) ?>">
                            <input type="hidden" name="material_type_label" value="<?= esc($generatedMaterial['type_label']) ?>">
                            <input type="hidden" name="material_content" value="<?= esc($generatedMaterial['content']) ?>">
                            <input type="hidden" name="material_params" value="<?= esc(json_encode($generatedMaterial['params'])) ?>">
                            <button type="submit" class="btn btn-success">💾 Save Material</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">📊 Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="display-4"><?= $stats['total'] ?></div>
                            <small class="text-muted">Materials Created</small>
                        </div>
                        <?php if (!empty($stats['by_type'])): ?>
                        <h6>By Type</h6>
                        <ul class="list-unstyled small">
                            <?php foreach ($stats['by_type'] as $type => $count): ?>
                                <li><?= $types[$type]['icon'] ?? '📄' ?> <?= $types[$type]['label'] ?? $type ?>: <strong><?= $count ?></strong></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Saved Materials -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">📚 Saved Materials</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($savedMaterials)): ?>
                            <p class="text-muted small">No saved materials yet.</p>
                        <?php else: ?>
                            <?php foreach ($savedMaterials as $mat): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong class="small"><?= esc($mat['topic']) ?></strong>
                                        <span class="badge bg-secondary"><?= esc($mat['type_label']) ?></span>
                                    </div>
                                    <small class="text-muted"><?= esc($mat['created_at']) ?></small>
                                    <div class="mt-1">
                                        <a href="?view=<?= esc($mat['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="?print=<?= esc($mat['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Print</a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this material?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="material_id" value="<?= esc($mat['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">×</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Material Types Guide -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">📋 Material Types</h5>
                    </div>
                    <div class="card-body small">
                        <?php foreach ($types as $type): ?>
                            <div class="mb-2">
                                <strong><?= $type['icon'] ?> <?= esc($type['label']) ?></strong>
                                <br><span class="text-muted"><?= esc($type['description']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4">
            <a href="/admin/" class="btn btn-secondary">← Dashboard</a>
        </div>

    </div>
</div>

<script>
function copyMaterial() {
    const content = document.getElementById('generated-content').innerText;
    navigator.clipboard.writeText(content).then(() => {
        alert('Material copied to clipboard!');
    });
}
</script>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
