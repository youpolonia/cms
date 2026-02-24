<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-lms-course.php';
require_once $pluginDir . '/includes/class-lms-enrollment.php';
$cStats = \LmsCourse::getStats();
$eStats = \LmsEnrollment::getStats();
$courses = \LmsCourse::getAll(['status' => 'published'], 1, 6);
ob_start();
?>
<style>
.lms-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.lms-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.lms-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.lms-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px}
.lms-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.lms-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.lms-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.lms-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.lms-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-lms{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#6366f1;transform:translateY(-2px)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
.course-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.course-row:last-child{border-bottom:none}
</style>
<div class="lms-wrap">
    <div class="lms-header"><h1>🎓 LMS Dashboard</h1><a href="/admin/lms/courses/create" class="btn-lms">➕ New Course</a></div>
    <div class="lms-stats">
        <div class="lms-stat"><div class="val" style="color:#6366f1"><?= $cStats['published'] ?></div><div class="lbl">Published</div></div>
        <div class="lms-stat"><div class="val" style="color:#a5b4fc"><?= $cStats['drafts'] ?></div><div class="lbl">Drafts</div></div>
        <div class="lms-stat"><div class="val" style="color:#10b981"><?= $cStats['enrollments'] ?></div><div class="lbl">Enrollments</div></div>
        <div class="lms-stat"><div class="val" style="color:#f59e0b"><?= $eStats['completed'] ?></div><div class="lbl">Completed</div></div>
        <div class="lms-stat"><div class="val" style="color:var(--text)"><?= $eStats['avg_progress'] ?>%</div><div class="lbl">Avg Progress</div></div>
    </div>
    <div class="quick-links">
        <a href="/admin/lms/courses" class="quick-link"><span class="icon">📚</span><div><div class="text">Courses</div><div class="desc"><?= $cStats['total'] ?> total</div></div></a>
        <a href="/admin/lms/enrollments" class="quick-link"><span class="icon">👥</span><div><div class="text">Enrollments</div><div class="desc"><?= $eStats['total'] ?> students</div></div></a>
    </div>
    <div class="lms-card">
        <h3>📚 Active Courses</h3>
        <?php if (empty($courses['courses'])): ?>
            <p style="color:var(--muted);font-size:.85rem">No courses yet. <a href="/admin/lms/courses/create" style="color:#a5b4fc">Create your first →</a></p>
        <?php else: foreach ($courses['courses'] as $c): ?>
            <div class="course-row">
                <div style="flex:1"><strong style="font-size:.85rem;color:var(--text)"><?= h($c['title']) ?></strong><br><span style="font-size:.72rem;color:var(--muted)"><?= h($c['category']) ?> · <?= $c['difficulty'] ?> · <?= $c['duration_hours'] ?>h</span></div>
                <div style="text-align:center;min-width:60px"><strong style="font-size:.95rem"><?= $c['enrollment_count'] ?></strong><br><span style="font-size:.65rem;color:var(--muted)">Students</span></div>
                <a href="/admin/lms/courses/<?= $c['id'] ?>/edit" style="color:#a5b4fc;font-size:.78rem;text-decoration:none">✏️ Edit</a>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'LMS Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
