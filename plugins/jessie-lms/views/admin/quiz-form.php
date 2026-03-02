<?php
/**
 * LMS — Quiz Builder (create/edit quiz for a lesson)
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-lesson.php';

$lessonId = (int)($lessonId ?? $_GET['lesson_id'] ?? 0);
$lesson = $lessonId ? \LmsLesson::get($lessonId) : null;
if (!$lesson) { \Core\Session::flash('error', 'Lesson not found.'); \Core\Response::redirect('/admin/lms/courses'); }

// Load existing quiz
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS `lms_quizzes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lesson_id` INT NOT NULL,
    `questions` JSON NOT NULL,
    `passing_score` INT DEFAULT 70,
    `max_attempts` INT DEFAULT 3,
    `time_limit_minutes` INT DEFAULT 0,
    `shuffle_questions` TINYINT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $pdo->prepare("SELECT * FROM lms_quizzes WHERE lesson_id = ?");
$stmt->execute([$lessonId]);
$quiz = $stmt->fetch(\PDO::FETCH_ASSOC);
$questions = $quiz ? json_decode($quiz['questions'], true) : [];

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap" style="max-width:800px">
    <div class="j-settings-header">
        <h1>📝 Quiz: <?= h($lesson['title'] ?? '') ?></h1>
        <a href="/admin/lms/lessons/<?= $lessonId ?>/edit" class="j-btn-secondary">← Back to Lesson</a>
    </div>

    <?php if (!empty($_GET['saved'])): ?><div class="j-alert j-alert-success">✅ Quiz saved!</div><?php endif; ?>

    <form method="post" action="/admin/lms/quiz/save" id="quizForm">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">

        <!-- Quiz Settings -->
        <div class="j-card">
            <h3>Quiz Settings</h3>
            <div class="j-form-row">
                <div class="j-form-group">
                    <label>Passing Score (%)</label>
                    <input type="number" name="passing_score" min="0" max="100" value="<?= (int)($quiz['passing_score'] ?? 70) ?>">
                </div>
                <div class="j-form-group">
                    <label>Max Attempts</label>
                    <input type="number" name="max_attempts" min="0" value="<?= (int)($quiz['max_attempts'] ?? 3) ?>">
                    <div class="hint">0 = unlimited</div>
                </div>
            </div>
            <div class="j-form-row">
                <div class="j-form-group">
                    <label>Time Limit (minutes)</label>
                    <input type="number" name="time_limit_minutes" min="0" value="<?= (int)($quiz['time_limit_minutes'] ?? 0) ?>">
                    <div class="hint">0 = no limit</div>
                </div>
                <div class="j-toggle" style="padding-top:24px">
                    <input type="checkbox" name="shuffle_questions" id="shuffle" value="1" <?= ($quiz['shuffle_questions'] ?? 0) ? 'checked' : '' ?>>
                    <label for="shuffle">Shuffle Questions</label>
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div id="questionsContainer">
            <?php if (empty($questions)): ?>
            <!-- Empty state -->
            <?php else: ?>
            <?php foreach ($questions as $qi => $q): ?>
            <div class="j-card quiz-question" data-index="<?= $qi ?>">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <h3 style="border:none;padding:0;margin:0">Question <?= $qi + 1 ?></h3>
                    <button type="button" onclick="removeQuestion(this)" style="background:none;border:none;color:var(--j-danger);cursor:pointer;font-size:1.1rem">🗑️</button>
                </div>
                <div class="j-form-group">
                    <label>Question Text</label>
                    <input type="text" name="q[<?= $qi ?>][text]" value="<?= h($q['text'] ?? '') ?>" required>
                </div>
                <div class="j-form-group">
                    <label>Type</label>
                    <select name="q[<?= $qi ?>][type]" onchange="toggleOptions(this,<?= $qi ?>)">
                        <option value="multiple" <?= ($q['type'] ?? 'multiple') === 'multiple' ? 'selected' : '' ?>>Multiple Choice</option>
                        <option value="boolean" <?= ($q['type'] ?? '') === 'boolean' ? 'selected' : '' ?>>True / False</option>
                        <option value="text" <?= ($q['type'] ?? '') === 'text' ? 'selected' : '' ?>>Short Answer</option>
                    </select>
                </div>
                <div class="options-area" id="opts-<?= $qi ?>">
                    <?php if (($q['type'] ?? 'multiple') === 'multiple'): ?>
                    <?php foreach ($q['options'] ?? ['', '', '', ''] as $oi => $opt): ?>
                    <div class="j-form-group" style="display:flex;gap:8px;align-items:center">
                        <input type="radio" name="q[<?= $qi ?>][correct]" value="<?= $oi ?>" <?= (int)($q['correct'] ?? 0) === $oi ? 'checked' : '' ?> style="accent-color:var(--j-accent)">
                        <input type="text" name="q[<?= $qi ?>][options][]" value="<?= h($opt) ?>" placeholder="Option <?= $oi + 1 ?>" style="flex:1">
                    </div>
                    <?php endforeach; ?>
                    <?php elseif ($q['type'] === 'boolean'): ?>
                    <div class="j-form-group">
                        <label>Correct Answer</label>
                        <select name="q[<?= $qi ?>][correct]">
                            <option value="true" <?= ($q['correct'] ?? '') === 'true' ? 'selected' : '' ?>>True</option>
                            <option value="false" <?= ($q['correct'] ?? '') === 'false' ? 'selected' : '' ?>>False</option>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="j-form-group">
                        <label>Expected Answer</label>
                        <input type="text" name="q[<?= $qi ?>][correct]" value="<?= h($q['correct'] ?? '') ?>">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="margin-bottom:20px">
            <button type="button" onclick="addQuestion()" class="j-btn-secondary" style="width:100%;text-align:center;padding:14px">+ Add Question</button>
        </div>

        <button type="submit" class="j-btn" style="width:100%">💾 Save Quiz</button>
    </form>
</div>

<script>
var qIndex = <?= count($questions) ?>;

function addQuestion() {
    var container = document.getElementById('questionsContainer');
    var html = '<div class="j-card quiz-question" data-index="' + qIndex + '">'
        + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">'
        + '<h3 style="border:none;padding:0;margin:0;font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8">Question ' + (qIndex + 1) + '</h3>'
        + '<button type="button" onclick="removeQuestion(this)" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1.1rem">🗑️</button></div>'
        + '<div class="j-form-group"><label>Question Text</label><input type="text" name="q[' + qIndex + '][text]" required></div>'
        + '<div class="j-form-group"><label>Type</label><select name="q[' + qIndex + '][type]" onchange="toggleOptions(this,' + qIndex + ')">'
        + '<option value="multiple">Multiple Choice</option><option value="boolean">True / False</option><option value="text">Short Answer</option></select></div>'
        + '<div class="options-area" id="opts-' + qIndex + '">'
        + buildMultipleChoiceHtml(qIndex)
        + '</div></div>';
    container.insertAdjacentHTML('beforeend', html);
    qIndex++;
}

function buildMultipleChoiceHtml(qi) {
    var html = '';
    for (var i = 0; i < 4; i++) {
        html += '<div class="j-form-group" style="display:flex;gap:8px;align-items:center">'
            + '<input type="radio" name="q[' + qi + '][correct]" value="' + i + '" ' + (i === 0 ? 'checked' : '') + ' style="accent-color:#6366f1">'
            + '<input type="text" name="q[' + qi + '][options][]" placeholder="Option ' + (i + 1) + '" style="flex:1"></div>';
    }
    return html;
}

function toggleOptions(sel, qi) {
    var area = document.getElementById('opts-' + qi);
    if (sel.value === 'multiple') {
        area.innerHTML = buildMultipleChoiceHtml(qi);
    } else if (sel.value === 'boolean') {
        area.innerHTML = '<div class="j-form-group"><label>Correct Answer</label><select name="q[' + qi + '][correct]"><option value="true">True</option><option value="false">False</option></select></div>';
    } else {
        area.innerHTML = '<div class="j-form-group"><label>Expected Answer</label><input type="text" name="q[' + qi + '][correct]"></div>';
    }
}

function removeQuestion(btn) {
    btn.closest('.quiz-question').remove();
}
</script>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
