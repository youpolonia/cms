<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = $title ?? 'Form Builder';
$forms = $forms ?? [];
?>

<div class="page-header">
    <h1 class="page-title">📋 Form Builder</h1>
    <a href="/admin/form-builder/create" class="btn btn-primary">+ Create Form</a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="inline-help" style="background:var(--success-bg);border-color:var(--success);">
        <span class="inline-help-icon">✅</span>
        <span><?= h($_SESSION['flash_success']) ?></span>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="inline-help" style="background:var(--danger-bg);border-color:var(--danger);">
        <span class="inline-help-icon">❌</span>
        <span><?= h($_SESSION['flash_error']) ?></span>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (empty($forms)): ?>
    <div class="card">
        <div class="card-body" style="text-align:center;padding:60px 20px;">
            <div style="font-size:48px;margin-bottom:16px;">📋</div>
            <h2 style="margin-bottom:8px;">No forms yet</h2>
            <p style="color:var(--text-muted);margin-bottom:24px;">Create your first form with the drag &amp; drop builder.</p>
            <a href="/admin/form-builder/create" class="btn btn-primary">+ Create Form</a>
        </div>
    </div>
<?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(380px,1fr));gap:20px;">
        <?php foreach ($forms as $f): ?>
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title"><?= h($f['name']) ?></div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                            /<strong><?= h($f['slug']) ?></strong>
                        </div>
                    </div>
                    <div>
                        <?php if ($f['active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="stats-grid" style="margin-bottom:16px;">
                        <div style="display:flex;gap:24px;">
                            <div>
                                <div style="font-size:24px;font-weight:700;"><?= (int)$f['submissions_count'] ?></div>
                                <div style="font-size:12px;color:var(--text-muted);">Submissions</div>
                            </div>
                            <div>
                                <div style="font-size:13px;color:var(--text-muted);">Created</div>
                                <div style="font-size:13px;"><?= date('M j, Y', strtotime($f['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Embed code -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;">Embed Code</label>
                        <div style="display:flex;gap:8px;margin-top:4px;">
                            <input type="text" readonly value='&lt;script src="/form-embed/<?= h($f['slug']) ?>.js"&gt;&lt;/script&gt;' class="form-input" style="font-size:12px;font-family:monospace;" id="embed-<?= (int)$f['id'] ?>">
                            <button class="btn btn-secondary btn-sm" onclick="copyEmbed(<?= (int)$f['id'] ?>)" title="Copy">📋</button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <a href="/admin/form-builder/edit/<?= (int)$f['id'] ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
                        <a href="/admin/form-builder/submissions/<?= (int)$f['id'] ?>" class="btn btn-secondary btn-sm">📊 Submissions</a>
                        <form method="POST" action="/admin/form-builder/delete/<?= (int)$f['id'] ?>" style="display:inline;" onsubmit="return confirm('Delete this form and all its submissions?');">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function copyEmbed(id) {
    const input = document.getElementById('embed-' + id);
    // Decode HTML entities for clipboard
    const tmp = document.createElement('textarea');
    tmp.innerHTML = input.value;
    navigator.clipboard.writeText(tmp.value).then(() => {
        const btn = input.nextElementSibling;
        btn.textContent = '✅';
        setTimeout(() => btn.textContent = '📋', 1500);
    });
}
</script>
