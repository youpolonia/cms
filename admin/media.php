<?php
/**
 * Media Library - Modern Dark UI
 */
define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();
require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();
require_once CMS_ROOT . '/includes/getid3-lite.php';
require_once CMS_ROOT . '/admin/controllers/mediacontroller.php';

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$controller = new MediaController();
$sortBy = $_GET['sort_by'] ?? 'date';
$sortOrder = $_GET['sort_order'] ?? 'desc';
$entries = $controller->index($sortBy, strtoupper($sortOrder));

foreach ($entries as &$e) {
    $path = CMS_ROOT . '/uploads/media/' . $e['filename'];
    $e['resolution'] = $e['duration'] = $e['thumb'] = null;
    if (str_starts_with($e['mime_type'], 'image/') && file_exists($path)) {
        $info = @getimagesize($path);
        if ($info) $e['resolution'] = $info[0] . 'x' . $info[1];
    }
    if ($e['mime_type'] === 'video/mp4' && file_exists($path)) {
        $dur = extractMP4Duration($path);
        if ($dur) $e['duration'] = formatDuration($dur);
    }
    $thumbFile = pathinfo($e['filename'], PATHINFO_FILENAME) . '_thumb.jpg';
    if (file_exists(CMS_ROOT . '/uploads/media/thumbs/' . $thumbFile)) $e['thumb'] = $thumbFile;
}
unset($e);

$total = $controller->count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Media Library - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.stats{display:flex;gap:16px;margin-bottom:20px}
.stat{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:16px 20px;display:flex;align-items:center;gap:14px}
.stat-icon{width:48px;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:24px;background:rgba(137,180,250,.15)}
.stat-val{font-size:24px;font-weight:700}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-danger{background:rgba(243,139,168,.2);color:var(--danger);border:1px solid rgba(243,139,168,.3) !important}
.btn-danger:hover{background:rgba(243,139,168,.35);border-color:var(--danger) !important}
.btn-sm{padding:6px 12px;font-size:12px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select{padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.form-group input:focus,.form-group select:focus{outline:none;border-color:var(--accent)}
#upload-status{margin-top:12px}
.alert{padding:12px 16px;border-radius:8px;font-size:13px}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-info{background:rgba(137,180,250,.15);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.toolbar{display:flex;gap:12px;align-items:center;margin-bottom:16px;flex-wrap:wrap}
.toolbar select{padding:8px 12px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:12px}
table{width:100%;border-collapse:collapse}
th,td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border)}
th{font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;background:var(--bg)}
tr:hover td{background:rgba(137,180,250,.03)}
.preview{width:80px;height:60px;object-fit:cover;border-radius:6px;background:var(--bg3)}
.tag{display:inline-flex;padding:4px 8px;border-radius:4px;font-size:10px;font-weight:500;background:var(--bg3);color:var(--muted)}
.empty{text-align:center;padding:40px;color:var(--muted)}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px}
.grid-item{background:var(--bg3);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:.15s}
.grid-item:hover{border-color:var(--accent)}
.grid-img{width:100%;height:140px;object-fit:cover;display:block}
.grid-placeholder{width:100%;height:140px;display:flex;align-items:center;justify-content:center;background:var(--bg);font-size:24px}
.grid-info{padding:10px}
.grid-name{font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.grid-meta{font-size:11px;color:var(--muted);margin-top:4px}
.grid-actions{padding:8px 10px;border-top:1px solid var(--border)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🖼️',
    'title' => 'Media Library',
    'description' => 'Upload and manage files',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--purple), var(--accent-color)',
    'actions' => [
        ['type' => 'html', 'html' => '<button class="btn btn-secondary" id="view-toggle">☰ Toggle View</button>'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="stats">
<div class="stat"><div class="stat-icon">📁</div><div><div class="stat-val"><?= $total ?></div><div class="stat-lbl">Total Files</div></div></div>
</div>

<!-- Upload -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📤</span> Upload</span></div>
<div class="card-body">
<form id="upload-form" method="post" enctype="multipart/form-data">
<?php csrf_field(); ?>
<div style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px;margin:0">
<label>Select File (max 5MB)</label>
<input type="file" name="file" required style="width:100%">
</div>
<button type="submit" class="btn btn-primary">📤 Upload</button>
</div>
<div id="upload-status"></div>
</form>
</div>
</div>

<!-- Files -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📂</span> Files</span></div>
<div class="card-body">
<?php if (empty($entries)): ?>
<div class="empty"><p>No files uploaded yet.</p></div>
<?php else: ?>
<div class="toolbar">
<span style="font-size:12px;color:var(--muted)">Sort:</span>
<form method="get" id="sort-form" style="display:flex;gap:8px">
<select name="sort_by" onchange="this.form.submit()">
<option value="date" <?= $sortBy === 'date' ? 'selected' : '' ?>>Date</option>
<option value="size" <?= $sortBy === 'size' ? 'selected' : '' ?>>Size</option>
<option value="type" <?= $sortBy === 'type' ? 'selected' : '' ?>>Type</option>
</select>
<select name="sort_order" onchange="this.form.submit()">
<option value="desc" <?= $sortOrder === 'desc' ? 'selected' : '' ?>>Desc</option>
<option value="asc" <?= $sortOrder === 'asc' ? 'selected' : '' ?>>Asc</option>
</select>
</form>
</div>

<!-- List View -->
<div id="list-view">
<table>
<thead><tr><th>Preview</th><th>File</th><th>Type</th><th>Size</th><th>Info</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($entries as $e): 
$url = '/uploads/media/' . rawurlencode($e['filename']);
$isImg = str_starts_with($e['mime_type'], 'image/');
$preview = $e['thumb'] ? '/uploads/media/thumbs/' . rawurlencode($e['thumb']) : $url;
$ext = strtoupper(pathinfo($e['filename'], PATHINFO_EXTENSION) ?: 'FILE');
?>
<tr>
<td><?php if ($isImg): ?><img src="<?= esc($preview) ?>" class="preview" alt=""><?php else: ?><span class="tag"><?= $ext ?></span><?php endif; ?></td>
<td><a href="<?= esc($url) ?>" target="_blank" style="color:var(--accent)"><?= esc($e['original_name'] ?: $e['filename']) ?></a></td>
<td><span class="tag"><?= esc($e['mime_type']) ?></span></td>
<td><?= $e['size'] ? round($e['size']/1024) . ' KB' : '—' ?></td>
<td><?= $e['resolution'] ? esc($e['resolution']) : '' ?><?= $e['duration'] ? esc($e['duration']) : '' ?></td>
<td style="font-size:12px;color:var(--muted)"><?= esc($e['created_at'] ?? '—') ?></td>
<td>
<form method="post" action="/admin/api/delete-media.php" onsubmit="return confirm('Delete?')" style="display:inline">
<?php csrf_field(); ?>
<input type="hidden" name="file" value="<?= esc($e['filename']) ?>">
<button type="submit" style="background:rgba(243,139,168,.2);color:#f38ba8;border:1px solid rgba(243,139,168,.3);padding:6px 12px;font-size:12px;border-radius:8px;cursor:pointer">✕ Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- Grid View -->
<div id="grid-view" class="grid" style="display:none">
<?php foreach ($entries as $e): 
$url = '/uploads/media/' . rawurlencode($e['filename']);
$isImg = str_starts_with($e['mime_type'], 'image/');
$preview = $e['thumb'] ? '/uploads/media/thumbs/' . rawurlencode($e['thumb']) : $url;
$ext = strtoupper(pathinfo($e['filename'], PATHINFO_EXTENSION) ?: 'FILE');
?>
<div class="grid-item">
<a href="<?= esc($url) ?>" target="_blank">
<?php if ($isImg): ?><img src="<?= esc($preview) ?>" class="grid-img" alt="">
<?php else: ?><div class="grid-placeholder"><?= $ext ?></div><?php endif; ?>
</a>
<div class="grid-info">
<div class="grid-name" title="<?= esc($e['original_name'] ?: $e['filename']) ?>"><?= esc($e['original_name'] ?: $e['filename']) ?></div>
<div class="grid-meta"><?= $e['size'] ? round($e['size']/1024) . ' KB' : '' ?> <?= $e['resolution'] ?? '' ?></div>
</div>
<div class="grid-actions">
<form method="post" action="/admin/api/delete-media.php" onsubmit="return confirm('Delete?')">
<?php csrf_field(); ?>
<input type="hidden" name="file" value="<?= esc($e['filename']) ?>">
<button type="submit" style="width:100%;background:rgba(243,139,168,.2);color:#f38ba8;border:1px solid rgba(243,139,168,.3);padding:6px 12px;font-size:12px;border-radius:8px;cursor:pointer">✕ Delete</button>
</form>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</div>

<script>
document.getElementById('view-toggle')?.addEventListener('click',()=>{
    const l=document.getElementById('list-view'),g=document.getElementById('grid-view');
    if(l.style.display==='none'){l.style.display='block';g.style.display='none'}
    else{l.style.display='none';g.style.display='grid'}
});

document.getElementById('upload-form')?.addEventListener('submit',async e=>{
    e.preventDefault();
    const s=document.getElementById('upload-status');
    s.innerHTML='<div class="alert alert-info">Uploading...</div>';
    try{
        const r=await fetch('/admin/api/media-upload.php',{method:'POST',body:new FormData(e.target)});
        const d=await r.json();
        if(d.ok){s.innerHTML='<div class="alert alert-success">✅ Uploaded! Refresh to see.</div>';e.target.reset()}
        else{s.innerHTML='<div class="alert alert-danger">❌ '+(d.error||'Failed')+'</div>'}
    }catch(err){s.innerHTML='<div class="alert alert-danger">❌ Network error</div>'}
});
</script>
</body>
</html>
