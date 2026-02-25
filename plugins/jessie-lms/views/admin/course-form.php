<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-lms-lesson.php';
$isEdit = isset($course) && $course !== null;
$v = fn($k, $d = '') => h($isEdit ? ($course[$k] ?? $d) : $d);
$lessons = $isEdit ? \LmsLesson::getGrouped((int)$course['id']) : [];
ob_start();
?>
<style>
.lms-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.lms-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.lms-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.lms-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.lms-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:80px;resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.btn-lms{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
.lesson-item{display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);border-radius:8px;margin-bottom:6px}
.lesson-item .type{font-size:.7rem;padding:2px 6px;border-radius:4px;background:rgba(99,102,241,.1);color:#a5b4fc}
.lesson-item .title{flex:1;font-size:.85rem;color:var(--text)}
.lesson-item a{font-size:.75rem;color:#a5b4fc;text-decoration:none}
.section-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin:12px 0 6px;font-weight:700}
</style>
<div class="lms-wrap">
    <div class="lms-header"><h1><?= $isEdit ? '✏️ Edit Course' : '➕ New Course' ?></h1><a href="/admin/lms/courses" class="btn-secondary">← Courses</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/lms/courses/' . (int)$course['id'] . '/update' : '/admin/lms/courses/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="lms-card">
            <h3>📚 Course Details</h3>
            <div class="form-group"><label>Title *</label><input type="text" name="title" id="course-title" value="<?= $v('title') ?>" required></div>
            <div class="form-group"><label>Short Description</label><input type="text" name="short_description" value="<?= $v('short_description') ?>" maxlength="500"></div>
            <div class="form-group"><label>Full Description</label><textarea name="description" id="course-desc"><?= h($isEdit ? ($course['description'] ?? '') : '') ?></textarea>
            <button type="button" class="btn-ai" onclick="aiOutline()" style="margin-top:6px">✨ AI Generate Course Outline</button></div>
            <div class="form-row">
                <div class="form-group"><label>Category</label><input type="text" name="category" value="<?= $v('category') ?>" placeholder="e.g. Programming, Business"></div>
                <div class="form-group"><label>Difficulty</label><select name="difficulty"><?php foreach (['all'=>'All Levels','beginner'=>'Beginner','intermediate'=>'Intermediate','advanced'=>'Advanced'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($course['difficulty']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            </div>
        </div>
        <div class="lms-card">
            <h3>💰 Pricing & Settings</h3>
            <div class="form-row">
                <div class="form-group"><label>Price ($)</label><input type="number" name="price" step="0.01" value="<?= $v('price', '0') ?>" min="0"></div>
                <div class="form-group"><label>Status</label><select name="status"><option value="draft" <?= ($isEdit&&($course['status']??'')==='draft')?'selected':'' ?>>Draft</option><option value="published" <?= ($isEdit&&($course['status']??'')==='published')?'selected':'' ?>>Published</option></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Instructor Name</label><input type="text" name="instructor_name" value="<?= $v('instructor_name') ?>"></div>
                <div class="form-group"><label>Featured</label><select name="featured"><option value="0">No</option><option value="1" <?= ($isEdit&&($course['featured']??0))?'selected':'' ?>>Yes</option></select></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end"><a href="/admin/lms/courses" class="btn-secondary">Cancel</a><button type="submit" class="btn-lms"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button></div>
    </form>

    <?php if ($isEdit): ?>
    <div class="lms-card" style="margin-top:20px">
        <h3>📖 Lessons <a href="/admin/lms/courses/<?= $course['id'] ?>/lessons/create" class="btn-ai" style="float:right">➕ Add Lesson</a></h3>
        <?php if (empty($lessons)): ?>
            <p style="color:var(--muted);font-size:.85rem">No lessons yet.</p>
        <?php else: foreach ($lessons as $section => $sLessons): ?>
            <div class="section-label">📁 <?= h($section) ?></div>
            <?php foreach ($sLessons as $l): ?>
            <div class="lesson-item">
                <span class="type"><?= h($l['content_type']) ?></span>
                <span class="title"><?= h($l['title']) ?></span>
                <span style="font-size:.72rem;color:var(--muted)"><?= (int)$l['duration_minutes'] ?>min</span>
                <a href="/admin/lms/lessons/<?= $l['id'] ?>/edit">✏️</a>
            </div>
            <?php endforeach; endforeach; endif; ?>
    </div>
    <?php endif; ?>
</div>
<script>
function aiOutline(){
    var title=document.getElementById('course-title').value;if(!title){alert('Enter course title first');return;}
    fetch('/api/lms/ai-outline',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({topic:title}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        if(d.ok&&d.data){document.getElementById('course-desc').value=d.data.description||'';alert('Outline generated! Save course, then add lessons from the outline.');}
    });
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Course' : 'New Course'; require CMS_APP . '/views/admin/layouts/topbar.php';
