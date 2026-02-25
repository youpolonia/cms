<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-lms-course.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \LmsCourse::getAll($_GET, $page);
ob_start();
?>
<style>
.lms-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.lms-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.lms-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-lms{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.course-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
.course-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden;transition:.2s}
.course-card:hover{border-color:#6366f1;transform:translateY(-2px)}
.course-card .thumb{height:120px;background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);display:flex;align-items:center;justify-content:center;font-size:2rem}
.course-card .info{padding:16px}
.course-card h4{margin:0 0 6px;font-size:.95rem;color:var(--text,#e2e8f0)}
.course-card .meta{font-size:.75rem;color:var(--muted,#94a3b8);display:flex;gap:10px;margin-bottom:8px}
.course-card .desc{font-size:.82rem;color:var(--muted,#94a3b8);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:10px}
.course-card .footer{display:flex;align-items:center;justify-content:space-between}
.course-card .footer .price{font-weight:700;color:#10b981}
.course-card .footer a{font-size:.78rem;color:#a5b4fc;text-decoration:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-published{background:rgba(16,185,129,.15);color:#34d399}
.status-draft{background:rgba(107,114,128,.15);color:#9ca3af}
</style>
<div class="lms-wrap">
    <div class="lms-header"><h1>📚 Courses</h1><div style="display:flex;gap:10px"><a href="/admin/lms" class="btn-secondary">← Dashboard</a><a href="/admin/lms/courses/create" class="btn-lms">➕ New Course</a></div></div>
    <?php if (empty($result['courses'])): ?>
        <div style="text-align:center;padding:60px;color:var(--muted)"><p style="font-size:1.2rem">No courses yet.</p><a href="/admin/lms/courses/create" class="btn-lms" style="margin-top:12px">➕ Create First Course</a></div>
    <?php else: ?>
    <div class="course-grid">
        <?php foreach ($result['courses'] as $c): ?>
        <div class="course-card">
            <div class="thumb"><?= $c['thumbnail'] ? '<img src="'.h($c['thumbnail']).'" style="width:100%;height:100%;object-fit:cover">' : '📚' ?></div>
            <div class="info">
                <h4><?= h($c['title']) ?> <span class="status-badge status-<?= h($c['status']) ?>"><?= $c['status'] ?></span></h4>
                <div class="meta"><span>🎯 <?= ucfirst($c['difficulty']) ?></span><span>⏱ <?= $c['duration_hours'] ?>h</span><span>👥 <?= $c['enrollment_count'] ?></span></div>
                <div class="desc"><?= h($c['short_description'] ?: $c['description'] ?: '') ?></div>
                <div class="footer">
                    <span class="price"><?= $c['is_free'] ? 'Free' : '$' . number_format((float)$c['price'], 2) ?></span>
                    <a href="/admin/lms/courses/<?= $c['id'] ?>/edit">✏️ Edit</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Courses'; require CMS_APP . '/views/admin/layouts/topbar.php';
