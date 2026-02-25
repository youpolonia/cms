<?php /** SEO Writer Admin Dashboard */ ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>SEO Writer — Admin</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;line-height:1.6}
.wrap{max-width:1200px;margin:0 auto;padding:2rem}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
h1{font-size:1.5rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.nav a{color:#94a3b8;text-decoration:none;margin-left:1rem;font-size:.875rem}.nav a:hover{color:#e2e8f0}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem}
.card{background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.25rem}
.card .num{font-size:1.75rem;font-weight:700;color:#8b5cf6}.card .label{font-size:.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em}
table{width:100%;border-collapse:collapse;background:#1e293b;border:1px solid #334155;border-radius:.75rem;overflow:hidden}
th{background:#334155;text-align:left;padding:.75rem 1rem;font-size:.75rem;text-transform:uppercase;color:#94a3b8}
td{padding:.75rem 1rem;border-top:1px solid #334155;font-size:.875rem}
.badge{display:inline-block;padding:.125rem .5rem;border-radius:9999px;font-size:.7rem;font-weight:600}
.badge-good{background:#065f46;color:#6ee7b7}.badge-warn{background:#713f12;color:#fde68a}.badge-bad{background:#7f1d1d;color:#fca5a5}
.back{display:inline-block;margin-bottom:1rem;color:#8b5cf6;text-decoration:none;font-size:.875rem}
</style></head><body><div class="wrap">
<a href="/admin" class="back">← Admin</a>
<div class="top"><h1>🔍 SEO Writer — Dashboard</h1><div class="nav"><a href="/admin/seowriter">Dashboard</a><a href="/admin/seowriter/users">Users</a><a href="/admin/seowriter/content">Content</a></div></div>
<div class="cards">
<div class="card"><div class="num"><?= $totalProjects ?></div><div class="label">Projects</div></div>
<div class="card"><div class="num"><?= $totalContent ?></div><div class="label">Articles</div></div>
<div class="card"><div class="num"><?= $totalAudits ?></div><div class="label">Audits</div></div>
<div class="card"><div class="num"><?= $avgScore ?>%</div><div class="label">Avg SEO Score</div></div>
</div>
<h2 style="margin-bottom:1rem;font-size:1.1rem">Recent Content</h2>
<table><thead><tr><th>ID</th><th>Title</th><th>User</th><th>Keyword</th><th>Score</th><th>Words</th><th>Status</th><th>Created</th></tr></thead><tbody>
<?php foreach ($recentContent as $c): ?>
<tr><td><?= $c['id'] ?></td><td><?= htmlspecialchars($c['title'] ?: '(untitled)') ?></td><td><?= htmlspecialchars($c['email'] ?? 'N/A') ?></td>
<td><?= htmlspecialchars($c['target_keyword'] ?? '') ?></td>
<td><span class="badge <?= $c['seo_score'] >= 70 ? 'badge-good' : ($c['seo_score'] >= 40 ? 'badge-warn' : 'badge-bad') ?>"><?= $c['seo_score'] ?>%</span></td>
<td><?= number_format($c['word_count']) ?></td><td><?= $c['status'] ?></td><td><?= substr($c['created_at'], 0, 16) ?></td></tr>
<?php endforeach; ?>
<?php if (empty($recentContent)): ?><tr><td colspan="8" style="text-align:center;color:#64748b">No content yet</td></tr><?php endif; ?>
</tbody></table>
</div></body></html>
