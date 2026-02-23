<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = 'Pop-ups';
ob_start();
$typeIcons = ['modal'=>'🪟','slide_in'=>'📌','bar'=>'📢','fullscreen'=>'🖥️'];
$triggerLabels = ['delay'=>'⏱ Delay','scroll'=>'📜 Scroll','exit_intent'=>'🚪 Exit Intent','click'=>'👆 Click'];
?>
<style>
.pu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;margin-bottom:24px}
.pu-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:border-color .15s}
.pu-card:hover{border-color:var(--accent)}
.pu-card-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.pu-card-head h3{font-size:.95rem;font-weight:600;margin:0;display:flex;align-items:center;gap:8px}
.pu-card-body{padding:16px 20px}
.pu-card-foot{display:flex;gap:6px;padding:12px 20px;border-top:1px solid var(--border);flex-wrap:wrap}
.pu-type{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:600;background:var(--accent-muted);color:var(--accent)}
.pu-status{width:10px;height:10px;border-radius:50%;display:inline-block;flex-shrink:0}
.pu-status.on{background:var(--success)}
.pu-status.off{background:var(--text-muted)}
.pu-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:12px}
.pu-stat{text-align:center}
.pu-stat .n{font-size:1.1rem;font-weight:700;color:var(--text-primary)}
.pu-stat .l{font-size:.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em}
.pu-trigger{font-size:.75rem;color:var(--text-secondary);margin-top:8px}
.pu-btn{padding:6px 12px;border-radius:6px;font-size:.75rem;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;font-weight:500}
.pu-btn-edit{background:var(--bg-tertiary);color:var(--text-primary);border:1px solid var(--border)}
.pu-btn-edit:hover{border-color:var(--accent);color:var(--accent)}
.pu-btn-toggle{background:var(--accent-muted);color:var(--accent)}
.pu-btn-subs{background:rgba(166,227,161,.12);color:var(--success)}
.pu-btn-del{background:var(--danger-bg);color:var(--danger)}
.pu-btn-del:hover{background:rgba(243,139,168,.3)}
.pu-empty{padding:60px 20px;text-align:center;color:var(--text-muted);background:var(--bg-primary);border:1px solid var(--border);border-radius:12px}
.pu-empty p{margin:8px 0}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 style="font-size:1.5rem;font-weight:700">🎯 Pop-ups</h1>
    <a href="/admin/popups/create" class="btn btn-primary" style="font-size:.85rem">+ New Pop-up</a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div style="padding:12px 16px;background:var(--success-bg);color:var(--success);border-radius:8px;margin-bottom:16px;font-size:.85rem">
        ✅ <?= h($_SESSION['flash_success']) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div style="padding:12px 16px;background:var(--danger-bg);color:var(--danger);border-radius:8px;margin-bottom:16px;font-size:.85rem">
        ❌ <?= h($_SESSION['flash_error']) ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">🎯</div>
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-label">Total Pop-ups</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">✅</div>
        <div class="stat-value"><?= $stats['active'] ?></div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">👁️</div>
        <div class="stat-value"><?= number_format($stats['views']) ?></div>
        <div class="stat-label">Total Views</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger">📩</div>
        <div class="stat-value"><?= number_format($stats['submissions']) ?></div>
        <div class="stat-label">Total Submissions</div>
    </div>
</div>

<?php if (empty($popups)): ?>
<div class="pu-empty">
    <p style="font-size:2.5rem">🎯</p>
    <p style="font-size:1rem;font-weight:600">No pop-ups yet</p>
    <p style="font-size:.85rem">Create your first popup to start capturing leads.</p>
    <a href="/admin/popups/create" class="btn btn-primary" style="margin-top:12px">+ Create Pop-up</a>
</div>
<?php else: ?>
<div class="pu-grid">
    <?php foreach ($popups as $p): ?>
    <div class="pu-card">
        <div class="pu-card-head">
            <h3>
                <span class="pu-status <?= $p['active'] ? 'on' : 'off' ?>" title="<?= $p['active'] ? 'Active' : 'Inactive' ?>"></span>
                <?= h($p['name']) ?>
            </h3>
            <span class="pu-type"><?= $typeIcons[$p['type']] ?? '🪟' ?> <?= h(str_replace('_', ' ', ucfirst($p['type']))) ?></span>
        </div>
        <div class="pu-card-body">
            <div class="pu-trigger"><?= $triggerLabels[$p['trigger_type']] ?? $p['trigger_type'] ?>
                <?php if ($p['trigger_type'] === 'delay'): ?> (<?= h($p['trigger_value']) ?>s)<?php endif; ?>
                <?php if ($p['trigger_type'] === 'scroll'): ?> (<?= h($p['trigger_value']) ?>%)<?php endif; ?>
            </div>
            <div class="pu-stats">
                <div class="pu-stat"><div class="n"><?= number_format((int)$p['views']) ?></div><div class="l">Views</div></div>
                <div class="pu-stat"><div class="n"><?= number_format((int)$p['clicks']) ?></div><div class="l">Clicks</div></div>
                <div class="pu-stat"><div class="n"><?= number_format((int)$p['submissions']) ?></div><div class="l">Leads</div></div>
                <div class="pu-stat"><div class="n"><?= $p['conversion'] ?>%</div><div class="l">Conv.</div></div>
            </div>
        </div>
        <div class="pu-card-foot">
            <a href="/admin/popups/<?= $p['id'] ?>/edit" class="pu-btn pu-btn-edit">✏️ Edit</a>
            <form method="post" action="/admin/popups/<?= $p['id'] ?>/toggle" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button class="pu-btn pu-btn-toggle"><?= $p['active'] ? '⏸ Deactivate' : '▶️ Activate' ?></button>
            </form>
            <?php if ((int)$p['submissions'] > 0): ?>
            <a href="/admin/popups/<?= $p['id'] ?>/submissions" class="pu-btn pu-btn-subs">📩 <?= $p['submissions'] ?> Submissions</a>
            <?php endif; ?>
            <form method="post" action="/admin/popups/<?= $p['id'] ?>/delete" style="display:inline;margin-left:auto" onsubmit="return confirm('Delete this popup and all its submissions?')">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button class="pu-btn pu-btn-del">🗑️</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); require CMS_APP . '/views/admin/layouts/topbar.php';
