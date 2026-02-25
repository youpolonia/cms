<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$isEdit = isset($lesson) && $lesson !== null;
$v = fn($k, $d = '') => h($isEdit ? ($lesson[$k] ?? $d) : $d);
$courseId = $isEdit ? (int)$lesson['course_id'] : (int)($_GET['course_id'] ?? 0);
ob_start();
?>
<style>
.lms-wrap{max-width:800px;margin:0 auto;padding:24px 20px}
.lms-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.lms-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.lms-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.lms-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:200px;resize:vertical;font-family:monospace}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.btn-lms{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
</style>
<div class="lms-wrap">
    <div class="lms-header"><h1><?= $isEdit ? '✏️ Edit Lesson' : '➕ New Lesson' ?></h1><a href="/admin/lms/courses/<?= $courseId ?>/edit" class="btn-secondary">← Course</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/lms/lessons/' . (int)$lesson['id'] . '/update' : '/admin/lms/lessons/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="course_id" value="<?= $courseId ?>">
        <div class="lms-card">
            <h3>📖 Lesson</h3>
            <div class="form-group"><label>Title *</label><input type="text" name="title" id="lesson-title" value="<?= $v('title') ?>" required></div>
            <div class="form-row">
                <div class="form-group"><label>Type</label><select name="content_type"><option value="text" <?= ($isEdit&&($lesson['content_type']??'')==='text')?'selected':'' ?>>📝 Text</option><option value="video" <?= ($isEdit&&($lesson['content_type']??'')==='video')?'selected':'' ?>>🎥 Video</option><option value="quiz" <?= ($isEdit&&($lesson['content_type']??'')==='quiz')?'selected':'' ?>>📋 Quiz</option><option value="download" <?= ($isEdit&&($lesson['content_type']??'')==='download')?'selected':'' ?>>📥 Download</option></select></div>
                <div class="form-group"><label>Duration (min)</label><input type="number" name="duration_minutes" value="<?= $v('duration_minutes', '10') ?>" min="0"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Section</label><input type="text" name="section" value="<?= $v('section') ?>" placeholder="e.g. Introduction, Advanced Topics"></div>
                <div class="form-group"><label>Free Preview?</label><select name="is_preview"><option value="0">No</option><option value="1" <?= ($isEdit&&($lesson['is_preview']??0))?'selected':'' ?>>Yes</option></select></div>
            </div>
            <div class="form-group"><label>Video URL</label><input type="url" name="video_url" value="<?= $v('video_url') ?>" placeholder="YouTube/Vimeo URL"></div>
        </div>
        <div class="lms-card">
            <h3>📝 Content <button type="button" class="btn-ai" onclick="aiContent()" style="float:right">✨ AI Generate</button></h3>
            <div class="form-group"><textarea name="content_html" id="lesson-content"><?= h($isEdit ? ($lesson['content_html'] ?? '') : '') ?></textarea></div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end"><a href="/admin/lms/courses/<?= $courseId ?>/edit" class="btn-secondary">Cancel</a><button type="submit" class="btn-lms"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button></div>
    </form>
</div>
<script>
function aiContent(){
    var title=document.getElementById('lesson-title').value;if(!title){alert('Enter lesson title first');return;}
    fetch('/api/lms/ai-lesson',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title:title,course_title:''}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){if(d.ok&&d.data)document.getElementById('lesson-content').value=d.data.content_html||'';});
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Lesson' : 'New Lesson'; require CMS_APP . '/views/admin/layouts/topbar.php';
