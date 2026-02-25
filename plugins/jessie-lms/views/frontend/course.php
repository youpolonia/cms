<?php
/**
 * Jessie LMS — Course Detail Page (Public)
 * URL: /courses/{slug}
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-course.php';
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-lesson.php';
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-review.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$slug = $routeParams['slug'] ?? '';
$course = \LmsCourse::getBySlug($slug);
if (!$course || $course['status'] !== 'published') { http_response_code(404); echo '<h1>Course not found</h1>'; exit; }

$sections = \LmsLesson::getGrouped($course['id']);
$reviews = \LmsReview::getForCourse($course['id']);
$totalLessons = array_sum(array_map('count', $sections));
$stars = str_repeat('⭐', max(1, (int)round((float)$course['avg_rating'])));
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($course['title']) ?></title>
<meta name="description" content="<?= h($course['short_description'] ?: mb_substr(strip_tags($course['description']), 0, 160)) ?>">
<style>
:root{--bg:#0f172a;--card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#8b5cf6;--accent2:#6366f1;--green:#22c55e;--amber:#f59e0b}
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}a{color:var(--accent);text-decoration:none}

.hero{background:linear-gradient(135deg,rgba(139,92,246,.2),rgba(99,102,241,.1));border-bottom:1px solid var(--border);padding:48px 20px}
.hero-inner{max-width:900px;margin:0 auto}
.hero h1{font-size:2rem;font-weight:800;margin-bottom:12px;line-height:1.3}
.hero .meta{display:flex;gap:16px;flex-wrap:wrap;color:var(--muted);font-size:.9rem;margin-bottom:16px}
.hero .desc{color:var(--muted);font-size:1rem;line-height:1.7;margin-bottom:20px}

.container{max-width:900px;margin:0 auto;padding:24px 20px}
.layout{display:grid;grid-template-columns:1fr 320px;gap:24px}
@media(max-width:768px){.layout{grid-template-columns:1fr}}

.card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:20px}

.sidebar{position:sticky;top:24px;align-self:start}
.enroll-box{text-align:center;padding:24px}
.enroll-price{font-size:2.5rem;font-weight:800;color:var(--accent);margin-bottom:4px}
.enroll-price.free{color:var(--green)}
.enroll-btn{display:block;width:100%;padding:14px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;border-radius:10px;font-size:1.1rem;font-weight:700;cursor:pointer;margin:16px 0}
.enroll-btn:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(139,92,246,.3)}

.section-title{font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--accent);font-weight:700;margin:20px 0 10px;padding-bottom:8px;border-bottom:1px solid var(--border)}
.lesson-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5);font-size:.9rem}
.lesson-item .icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.lesson-item .icon.text{background:rgba(139,92,246,.15)}.lesson-item .icon.video{background:rgba(239,68,68,.15)}
.lesson-item .icon.quiz{background:rgba(245,158,11,.15)}.lesson-item .icon.preview{background:rgba(34,197,94,.15)}
.lesson-item .title{flex:1}.lesson-item .dur{color:var(--muted);font-size:.8rem}
.lesson-item .lock{color:var(--muted);font-size:.75rem}

.review{border-bottom:1px solid var(--border);padding:16px 0}
.review:last-child{border:none}
.review .header{display:flex;justify-content:space-between;margin-bottom:6px}
.review .name{font-weight:600}.review .date{color:var(--muted);font-size:.8rem}
.review .text{color:var(--muted);font-size:.9rem;line-height:1.6}

.badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:600;background:rgba(139,92,246,.15);color:var(--accent)}
.back{display:inline-block;color:var(--accent);margin-bottom:16px;font-size:.875rem}

.enroll-form{display:none;margin-top:12px;text-align:left}
.enroll-form input{width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);margin-bottom:8px;font-size:.9rem}
.enroll-form input:focus{outline:none;border-color:var(--accent)}
</style>
</head>
<body>
<div class="hero">
    <div class="hero-inner">
        <a href="/courses" class="back">← All Courses</a>
        <div style="display:flex;gap:6px;margin-bottom:12px">
            <span class="badge"><?= h($course['difficulty']) ?></span>
            <?php if ($course['category']): ?><span class="badge"><?= h($course['category']) ?></span><?php endif; ?>
        </div>
        <h1><?= h($course['title']) ?></h1>
        <div class="meta">
            <?php if ($course['instructor_name']): ?><span>👨‍🏫 <?= h($course['instructor_name']) ?></span><?php endif; ?>
            <span>📚 <?= $totalLessons ?> lessons</span>
            <?php if ($course['duration_hours'] > 0): ?><span>⏱ <?= $course['duration_hours'] ?> hours</span><?php endif; ?>
            <span>👥 <?= (int)$course['enrollment_count'] ?> students</span>
            <span><?= $stars ?> (<?= (int)$course['review_count'] ?> reviews)</span>
        </div>
        <?php if ($course['short_description']): ?>
        <div class="desc"><?= h($course['short_description']) ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="container">
<div class="layout">
<div>
    <!-- About -->
    <?php if ($course['description']): ?>
    <div class="card">
        <h2 style="margin-bottom:12px;font-size:1.2rem">About This Course</h2>
        <div style="color:var(--muted);line-height:1.7;font-size:.9rem"><?= nl2br(h($course['description'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- Curriculum -->
    <div class="card">
        <h2 style="margin-bottom:12px;font-size:1.2rem">📋 Curriculum</h2>
        <div style="color:var(--muted);font-size:.85rem;margin-bottom:12px"><?= $totalLessons ?> lessons • <?= $course['duration_hours'] ?> hours total</div>
        <?php foreach ($sections as $sectionName => $lessons): ?>
        <div class="section-title"><?= h($sectionName) ?></div>
        <?php foreach ($lessons as $i => $lesson):
            $type = $lesson['content_type'] ?? 'text';
            $icons = ['text' => '📝', 'video' => '🎬', 'quiz' => '❓', 'download' => '📥', 'assignment' => '📋'];
        ?>
        <div class="lesson-item">
            <div class="icon <?= $lesson['is_preview'] ? 'preview' : h($type) ?>"><?= $icons[$type] ?? '📝' ?></div>
            <div class="title"><?= h($lesson['title']) ?></div>
            <?php if ($lesson['duration_minutes'] > 0): ?><div class="dur"><?= $lesson['duration_minutes'] ?>min</div><?php endif; ?>
            <?php if ($lesson['is_preview']): ?><span class="badge" style="background:rgba(34,197,94,.15);color:var(--green)">Preview</span>
            <?php else: ?><span class="lock">🔒</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <!-- Instructor -->
    <?php if ($course['instructor_name']): ?>
    <div class="card">
        <h2 style="margin-bottom:12px;font-size:1.2rem">👨‍🏫 Instructor</h2>
        <div style="font-weight:600;font-size:1.1rem;margin-bottom:6px"><?= h($course['instructor_name']) ?></div>
        <?php if ($course['instructor_bio']): ?>
        <div style="color:var(--muted);font-size:.9rem;line-height:1.6"><?= nl2br(h($course['instructor_bio'])) ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Reviews -->
    <div class="card">
        <h2 style="margin-bottom:12px;font-size:1.2rem">💬 Reviews (<?= count($reviews) ?>)</h2>
        <?php if (empty($reviews)): ?>
        <p style="color:var(--muted)">No reviews yet. Be the first!</p>
        <?php else: ?>
        <?php foreach ($reviews as $r): ?>
        <div class="review">
            <div class="header">
                <span class="name"><?= h($r['name'] ?: 'Student') ?> — <?= str_repeat('⭐', (int)$r['rating']) ?></span>
                <span class="date"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
            </div>
            <div class="text"><?= nl2br(h($r['review'])) ?></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Sidebar -->
<div class="sidebar">
    <div class="card enroll-box">
        <div class="enroll-price <?= $course['is_free'] ? 'free' : '' ?>"><?= $course['is_free'] ? 'Free' : '$' . number_format((float)$course['price'], 0) ?></div>
        <div style="color:var(--muted);font-size:.85rem;margin-bottom:8px"><?= $course['is_free'] ? 'Free access to all lessons' : 'Lifetime access' ?></div>
        <button class="enroll-btn" onclick="toggleEnroll()">🎓 Enroll Now</button>
        <div class="enroll-form" id="enrollForm">
            <input type="text" id="enrollName" placeholder="Your Name">
            <input type="email" id="enrollEmail" placeholder="Your Email *" required>
            <button class="enroll-btn" onclick="submitEnroll()" id="enrollSubmit" style="margin-top:4px">✅ Confirm Enrollment</button>
            <div id="enrollMsg" style="text-align:center;font-size:.85rem;margin-top:8px"></div>
        </div>
        <div style="text-align:left;margin-top:16px;font-size:.85rem;color:var(--muted)">
            <div style="padding:4px 0">✅ <?= $totalLessons ?> lessons</div>
            <div style="padding:4px 0">✅ <?= $course['duration_hours'] ?> hours of content</div>
            <div style="padding:4px 0">✅ Certificate on completion</div>
            <div style="padding:4px 0">✅ Quizzes & assessments</div>
            <div style="padding:4px 0">✅ Lifetime access</div>
        </div>
    </div>

    <!-- Certificate Verify -->
    <div class="card" style="font-size:.85rem">
        <h3 style="margin-bottom:8px;font-size:1rem">🏅 Verify Certificate</h3>
        <input type="text" id="certCode" placeholder="Enter certificate code" style="width:100%;padding:8px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);margin-bottom:8px;font-size:.85rem">
        <button onclick="verifyCert()" style="width:100%;padding:8px;background:var(--card);border:1px solid var(--border);border-radius:8px;color:var(--text);cursor:pointer;font-size:.85rem">Verify</button>
        <div id="certResult" style="margin-top:8px"></div>
    </div>
</div>
</div>
</div>

<script>
function toggleEnroll(){document.getElementById('enrollForm').style.display=document.getElementById('enrollForm').style.display==='block'?'none':'block';}
function submitEnroll(){
    var email=document.getElementById('enrollEmail').value;if(!email)return;
    var btn=document.getElementById('enrollSubmit');btn.disabled=true;btn.textContent='Enrolling...';
    fetch('/api/lms/enroll',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({course_id:<?= $course['id'] ?>,email:email,name:document.getElementById('enrollName').value})}).then(function(r){return r.json()}).then(function(d){
        if(d.ok){document.getElementById('enrollMsg').innerHTML='<span style="color:#22c55e">✅ Enrolled! Check your email to start learning.</span>';btn.style.display='none';if(d.id)localStorage.setItem('lms_enrollment_<?= $course['id'] ?>',d.id);localStorage.setItem('lms_email',email);}
        else document.getElementById('enrollMsg').innerHTML='<span style="color:#ef4444">'+d.error+'</span>';
        btn.disabled=false;btn.textContent='✅ Confirm Enrollment';
    });
}
function verifyCert(){
    var code=document.getElementById('certCode').value;if(!code)return;
    fetch('/api/lms/verify-cert?code='+encodeURIComponent(code)).then(function(r){return r.json()}).then(function(d){
        document.getElementById('certResult').innerHTML=d.valid?'<div style="color:#22c55e">✅ Valid! Issued to '+d.certificate.student_name+' for '+d.certificate.course_title+'</div>':'<div style="color:#ef4444">❌ Invalid certificate code</div>';
    });
}
</script>
</body></html>
