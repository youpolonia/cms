<?php /** Image Studio Admin — All Images */ ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Image Studio — Images</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;line-height:1.6}
.wrap{max-width:1200px;margin:0 auto;padding:2rem}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
h1{font-size:1.5rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.nav a{color:#94a3b8;text-decoration:none;margin-left:1rem;font-size:.875rem}.nav a:hover{color:#e2e8f0}.nav a.active{color:#8b5cf6}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem}
.thumb{background:#1e293b;border:1px solid #334155;border-radius:.75rem;overflow:hidden}
.thumb img{width:100%;height:160px;object-fit:cover;display:block}
.thumb-info{padding:.75rem;font-size:.75rem;color:#94a3b8}
.thumb-info .type{color:#8b5cf6;font-weight:600;text-transform:uppercase}
.thumb-info .size{color:#64748b}
.back{display:inline-block;margin-bottom:1rem;color:#8b5cf6;text-decoration:none;font-size:.875rem}
</style></head><body><div class="wrap">
<a href="/admin" class="back">← Admin</a>
<div class="top"><h1>🖼️ Image Studio — All Images</h1><div class="nav"><a href="/admin/imagestudio">Dashboard</a><a href="/admin/imagestudio/users">Users</a><a href="/admin/imagestudio/images" class="active">Images</a></div></div>
<p style="margin-bottom:1rem;color:#94a3b8;font-size:.875rem"><?= count($images) ?> images</p>
<div class="grid">
<?php foreach ($images as $img): ?>
<div class="thumb">
<?php if ($img['file_url']): ?><img src="<?= htmlspecialchars($img['file_url']) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?? '') ?>" loading="lazy"><?php endif; ?>
<div class="thumb-info">
<span class="type"><?= $img['type'] ?></span> · <?= $img['width'] ?>×<?= $img['height'] ?>
<br><?= htmlspecialchars($img['email'] ?? 'N/A') ?>
<br><span class="size"><?= $img['file_size'] ? round($img['file_size']/1024) . 'KB' : '' ?></span> · <?= substr($img['created_at'], 0, 16) ?>
</div></div>
<?php endforeach; ?>
<?php if (empty($images)): ?><p style="color:#64748b">No images yet</p><?php endif; ?>
</div>
</div></body></html>
