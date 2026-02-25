<?php /** Copywriter Admin — All Content */ ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>AI Copywriter — Content</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;line-height:1.6}
.wrap{max-width:1200px;margin:0 auto;padding:2rem}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
h1{font-size:1.5rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.nav a{color:#94a3b8;text-decoration:none;margin-left:1rem;font-size:.875rem}.nav a:hover{color:#e2e8f0}.nav a.active{color:#8b5cf6}
table{width:100%;border-collapse:collapse;background:#1e293b;border:1px solid #334155;border-radius:.75rem;overflow:hidden}
th{background:#334155;text-align:left;padding:.75rem 1rem;font-size:.75rem;text-transform:uppercase;color:#94a3b8}
td{padding:.75rem 1rem;border-top:1px solid #334155;font-size:.875rem}
.badge{display:inline-block;padding:.125rem .5rem;border-radius:9999px;font-size:.7rem;font-weight:600}
.badge-ok{background:#065f46;color:#6ee7b7}.badge-fail{background:#7f1d1d;color:#fca5a5}.badge-wait{background:#713f12;color:#fde68a}
.back{display:inline-block;margin-bottom:1rem;color:#8b5cf6;text-decoration:none;font-size:.875rem}
</style></head><body><div class="wrap">
<a href="/admin" class="back">← Admin</a>
<div class="top"><h1>✍️ AI Copywriter — All Content</h1><div class="nav"><a href="/admin/copywriter">Dashboard</a><a href="/admin/copywriter/users">Users</a><a href="/admin/copywriter/content" class="active">Content</a></div></div>
<table><thead><tr><th>ID</th><th>Product</th><th>User</th><th>Platform</th><th>Tone</th><th>Title</th><th>Status</th><th>Created</th></tr></thead><tbody>
<?php foreach ($content as $c): ?>
<tr><td><?= $c['id'] ?></td><td><?= htmlspecialchars(mb_substr($c['product_name'] ?? '', 0, 30)) ?></td><td><?= htmlspecialchars($c['email'] ?? 'N/A') ?></td>
<td><?= htmlspecialchars($c['platform']) ?></td><td><?= htmlspecialchars($c['tone']) ?></td>
<td><?= htmlspecialchars(mb_substr($c['title'] ?? '', 0, 40)) ?></td>
<td><span class="badge <?= $c['status'] === 'completed' ? 'badge-ok' : ($c['status'] === 'failed' ? 'badge-fail' : 'badge-wait') ?>"><?= $c['status'] ?></span></td>
<td><?= substr($c['created_at'], 0, 16) ?></td></tr>
<?php endforeach; ?>
<?php if (empty($content)): ?><tr><td colspan="8" style="text-align:center;color:#64748b">No content yet</td></tr><?php endif; ?>
</tbody></table>
</div></body></html>
