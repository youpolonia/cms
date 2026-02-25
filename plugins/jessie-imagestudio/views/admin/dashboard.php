<?php /** Image Studio Admin Dashboard */ ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Image Studio — Admin</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;line-height:1.6}
.wrap{max-width:1200px;margin:0 auto;padding:2rem}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
h1{font-size:1.5rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.nav a{color:#94a3b8;text-decoration:none;margin-left:1rem;font-size:.875rem}.nav a:hover{color:#e2e8f0}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem}
.card{background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.25rem}
.card .num{font-size:1.75rem;font-weight:700;color:#8b5cf6}.card .label{font-size:.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-bottom:2rem}
.thumb{background:#1e293b;border:1px solid #334155;border-radius:.75rem;overflow:hidden}
.thumb img{width:100%;height:140px;object-fit:cover;display:block}
.thumb-info{padding:.75rem;font-size:.75rem;color:#94a3b8}
.thumb-info .type{color:#8b5cf6;font-weight:600;text-transform:uppercase}
.back{display:inline-block;margin-bottom:1rem;color:#8b5cf6;text-decoration:none;font-size:.875rem}
h2{margin-bottom:1rem;font-size:1.1rem}
.types{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:2rem}
.type-badge{background:#334155;padding:.25rem .75rem;border-radius:9999px;font-size:.75rem}
</style></head><body><div class="wrap">
<a href="/admin" class="back">← Admin</a>
<div class="top"><h1>🖼️ Image Studio — Dashboard</h1><div class="nav"><a href="/admin/imagestudio">Dashboard</a><a href="/admin/imagestudio/users">Users</a><a href="/admin/imagestudio/images">Images</a></div></div>
<div class="cards">
<div class="card"><div class="num"><?= $totalImages ?></div><div class="label">Total Images</div></div>
<div class="card"><div class="num"><?= $totalJobs ?></div><div class="label">Jobs Processed</div></div>
<div class="card"><div class="num"><?= $totalCredits ?></div><div class="label">Credits Used</div></div>
</div>
<?php if (!empty($byType)): ?>
<h2>By Type</h2>
<div class="types"><?php foreach ($byType as $t): ?><span class="type-badge"><?= htmlspecialchars($t['type']) ?>: <?= $t['cnt'] ?></span><?php endforeach; ?></div>
<?php endif; ?>
<h2>Recent Images</h2>
<div class="grid">
<?php foreach ($recentImages as $img): ?>
<div class="thumb">
<?php if ($img['file_url']): ?><img src="<?= htmlspecialchars($img['file_url']) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?? '') ?>" loading="lazy"><?php endif; ?>
<div class="thumb-info"><span class="type"><?= $img['type'] ?></span> · <?= htmlspecialchars($img['email'] ?? 'N/A') ?><br><?= substr($img['created_at'], 0, 16) ?></div>
</div>
<?php endforeach; ?>
<?php if (empty($recentImages)): ?><p style="color:#64748b">No images yet</p><?php endif; ?>
</div>
</div></body></html>
