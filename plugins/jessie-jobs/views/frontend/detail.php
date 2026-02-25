<?php
/**
 * Jessie Jobs — Public Job Detail Page
 * URL: /jobs/{slug}
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-jobs/includes/class-job-listing.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$slug = $jobSlug ?? '';
$job = JobListing::getBySlug($slug);

if (!$job || $job['status'] !== 'active') {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Not Found</title></head><body style="background:#0f172a;color:#e2e8f0;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh"><div style="text-align:center"><h1>404</h1><p>Job not found.</p><a href="/jobs" style="color:#6366f1">← Back to Jobs</a></div></body></html>';
    exit;
}

JobListing::incrementViews((int)$job['id']);

// Related jobs (same category)
$related = [];
if ($job['category']) {
    $r = JobListing::getAll(['category' => $job['category'], 'status' => 'active'], 1, 4);
    $related = array_filter($r['listings'], fn($j) => (int)$j['id'] !== (int)$job['id']);
    $related = array_slice($related, 0, 3);
}

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: ''; } catch (\Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($job['title']) ?> at <?= h($job['company_name'] ?: 'Company') ?> — <?= h($siteTitle ?: 'Jobs') ?></title>
    <meta name="description" content="<?= h(substr(strip_tags($job['description']), 0, 160)) ?>">
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}

        .detail-header{background:linear-gradient(135deg,rgba(99,102,241,.12) 0%,rgba(139,92,246,.08) 100%);border-bottom:1px solid var(--border);padding:32px 20px}
        .detail-header-inner{max-width:1000px;margin:0 auto}
        .breadcrumb{font-size:.78rem;color:var(--muted);margin-bottom:12px}
        .breadcrumb a{color:var(--muted)}
        .breadcrumb a:hover{color:var(--accent)}
        .detail-header h1{font-size:1.8rem;font-weight:800;margin-bottom:4px}
        .detail-header .company-line{font-size:1rem;color:var(--muted);margin-bottom:12px}
        .detail-header .badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
        .badge{padding:3px 10px;border-radius:5px;font-size:.7rem;font-weight:700;text-transform:uppercase}
        .badge-type{background:rgba(99,102,241,.1);color:#a5b4fc}
        .badge-remote{background:rgba(16,185,129,.12);color:#34d399}
        .badge-level{background:rgba(245,158,11,.12);color:#fbbf24}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .detail-header .meta{display:flex;gap:16px;flex-wrap:wrap;font-size:.85rem;color:var(--muted)}
        .detail-header .meta span{display:flex;align-items:center;gap:4px}
        .salary-big{color:#10b981;font-weight:700;font-size:.95rem}

        .detail-container{max-width:1000px;margin:0 auto;padding:24px 20px}
        .detail-grid{display:grid;grid-template-columns:1fr 340px;gap:24px}
        @media(max-width:768px){.detail-grid{grid-template-columns:1fr}}

        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:22px;margin-bottom:20px}
        .card h3{font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)}

        .description{font-size:.92rem;line-height:1.75;color:var(--text)}
        .description p{margin-bottom:12px}

        .skills-list{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
        .skill-tag{background:rgba(99,102,241,.1);color:#a5b4fc;padding:4px 12px;border-radius:6px;font-size:.78rem;font-weight:500}

        /* Sidebar */
        .info-row{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:.85rem;border-bottom:1px solid rgba(51,65,85,.4)}
        .info-row:last-child{border-bottom:none}
        .info-row .icon{font-size:1rem;width:24px;text-align:center}

        /* Apply form */
        .apply-form label{display:block;font-size:.78rem;font-weight:600;color:var(--text);margin-bottom:4px}
        .apply-form input,.apply-form textarea{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit;margin-bottom:12px}
        .apply-form textarea{min-height:80px;resize:vertical}
        .btn-apply{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px 24px;border-radius:10px;font-weight:700;cursor:pointer;font-size:.95rem;width:100%}
        .btn-apply:hover{opacity:.9}
        .msg-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#34d399;padding:12px;border-radius:8px;font-size:.85rem;margin-bottom:12px}

        /* Related */
        .related-list{display:flex;flex-direction:column;gap:10px}
        .related-card{background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:14px;transition:.2s}
        .related-card:hover{border-color:var(--accent)}
        .related-card a{color:var(--text);display:block}
        .related-card h4{font-size:.88rem;margin-bottom:2px}
        .related-card .meta{font-size:.72rem;color:var(--muted)}
    </style>
</head>
<body>
    <div class="detail-header">
        <div class="detail-header-inner">
            <div class="breadcrumb">
                <a href="/jobs">Jobs</a>
                <?php if ($job['category']): ?> › <a href="/jobs?category=<?= urlencode($job['category']) ?>"><?= h($job['category']) ?></a><?php endif; ?>
                › <?= h($job['title']) ?>
            </div>
            <div class="badges">
                <?php if ($job['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
                <span class="badge badge-type"><?= h($job['job_type']) ?></span>
                <span class="badge badge-remote"><?= h($job['remote_type']) ?></span>
                <span class="badge badge-level"><?= h($job['experience_level']) ?></span>
            </div>
            <h1><?= h($job['title']) ?></h1>
            <div class="company-line">
                🏢 <?= h($job['company_name'] ?: 'Company') ?>
                <?php if ($job['location']): ?> · 📍 <?= h($job['location']) ?><?php endif; ?>
            </div>
            <div class="meta">
                <?php if ($job['salary_min'] || $job['salary_max']): ?>
                <span class="salary-big">💰 <?= $job['salary_currency'] ?> <?= $job['salary_min']?number_format((float)$job['salary_min']):'' ?><?= ($job['salary_min']&&$job['salary_max'])?' – ':'' ?><?= $job['salary_max']?number_format((float)$job['salary_max']):'' ?></span>
                <?php endif; ?>
                <span>📅 Posted <?= date('M j, Y', strtotime($job['created_at'])) ?></span>
                <?php if ($job['expires_at']): ?><span>⏰ Expires <?= date('M j, Y', strtotime($job['expires_at'])) ?></span><?php endif; ?>
                <span style="color:var(--muted);font-size:.75rem">👁 <?= number_format($job['view_count']) ?> views</span>
            </div>
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-grid">
            <div>
                <!-- Description -->
                <div class="card">
                    <h3>📝 Job Description</h3>
                    <div class="description"><?= nl2br(h($job['description'])) ?></div>
                </div>

                <!-- Requirements -->
                <?php if ($job['requirements']): ?>
                <div class="card">
                    <h3>📋 Requirements</h3>
                    <div class="description"><?= nl2br(h($job['requirements'])) ?></div>
                </div>
                <?php endif; ?>

                <!-- Benefits -->
                <?php if ($job['benefits']): ?>
                <div class="card">
                    <h3>🎁 Benefits</h3>
                    <div class="description"><?= nl2br(h($job['benefits'])) ?></div>
                </div>
                <?php endif; ?>

                <!-- Skills -->
                <?php $skills = $job['skills'] ?? []; if (!empty($skills)): ?>
                <div class="card">
                    <h3>🛠️ Skills</h3>
                    <div class="skills-list">
                        <?php foreach ($skills as $skill): ?>
                        <span class="skill-tag"><?= h($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Related -->
                <?php if (!empty($related)): ?>
                <div class="card">
                    <h3>🔗 Similar Jobs</h3>
                    <div class="related-list">
                        <?php foreach ($related as $rel): ?>
                        <div class="related-card">
                            <a href="/jobs/<?= h($rel['slug']) ?>">
                                <h4><?= h($rel['title']) ?></h4>
                                <div class="meta">🏢 <?= h($rel['company_name'] ?: 'Company') ?> · <?= h($rel['location'] ?: $rel['remote_type']) ?></div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <aside>
                <!-- Job Details Sidebar -->
                <div class="card">
                    <h3>💼 Job Details</h3>
                    <div class="info-row"><span class="icon">🏢</span><span><strong>Company:</strong> <?= h($job['company_name'] ?: '—') ?></span></div>
                    <div class="info-row"><span class="icon">📍</span><span><strong>Location:</strong> <?= h($job['location'] ?: '—') ?></span></div>
                    <div class="info-row"><span class="icon">🌐</span><span><strong>Remote:</strong> <?= ucfirst(h($job['remote_type'])) ?></span></div>
                    <div class="info-row"><span class="icon">⏰</span><span><strong>Type:</strong> <?= ucfirst(h($job['job_type'])) ?></span></div>
                    <div class="info-row"><span class="icon">📊</span><span><strong>Level:</strong> <?= ucfirst(h($job['experience_level'])) ?></span></div>
                    <?php if ($job['category']): ?>
                    <div class="info-row"><span class="icon">📁</span><span><strong>Category:</strong> <?= h($job['category']) ?></span></div>
                    <?php endif; ?>
                    <?php if ($job['salary_min'] || $job['salary_max']): ?>
                    <div class="info-row"><span class="icon">💰</span><span><strong>Salary:</strong> <span style="color:#10b981"><?= $job['salary_currency'] ?> <?= $job['salary_min']?number_format((float)$job['salary_min']):'' ?><?= ($job['salary_min']&&$job['salary_max'])?' – ':'' ?><?= $job['salary_max']?number_format((float)$job['salary_max']):'' ?></span></span></div>
                    <?php endif; ?>
                </div>

                <!-- External Apply -->
                <?php if ($job['application_url']): ?>
                <div class="card" style="text-align:center">
                    <a href="<?= h($job['application_url']) ?>" target="_blank" rel="noopener" class="btn-apply" style="display:inline-block;padding:14px 28px">🔗 Apply on Company Site</a>
                </div>
                <?php endif; ?>

                <!-- Apply Form -->
                <div class="card">
                    <h3>📩 Apply Now</h3>
                    <div id="apply-msg"></div>
                    <form class="apply-form" id="applyForm" onsubmit="submitApplication(event)">
                        <label>Full Name *</label>
                        <input type="text" id="app-name" required>
                        <label>Email *</label>
                        <input type="email" id="app-email" required>
                        <label>Phone</label>
                        <input type="tel" id="app-phone">
                        <label>Cover Letter</label>
                        <textarea id="app-cover" placeholder="Tell us why you're a great fit..."></textarea>
                        <button type="submit" class="btn-apply">📤 Submit Application</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>

    <script>
    function submitApplication(e) {
        e.preventDefault();
        var data = {
            job_id: <?= (int)$job['id'] ?>,
            applicant_name: document.getElementById('app-name').value,
            applicant_email: document.getElementById('app-email').value,
            applicant_phone: document.getElementById('app-phone').value,
            cover_letter: document.getElementById('app-cover').value
        };
        fetch('/api/jobs/apply', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data),
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.ok) {
                document.getElementById('apply-msg').innerHTML = '<div class="msg-success">✅ Application submitted successfully! We\'ll be in touch.</div>';
                document.getElementById('applyForm').reset();
            } else {
                alert(d.error || 'Error submitting application.');
            }
        })
        .catch(function() { alert('Network error. Please try again.'); });
    }
    </script>
</body>
</html>
