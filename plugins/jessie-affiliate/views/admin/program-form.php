<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$isEdit = !empty($program);
$action = $isEdit ? "/admin/affiliate/programs/{$program['id']}/update" : '/admin/affiliate/programs/store';
ob_start();
?>
<style>
.aff-wrap{max-width:800px;margin:0 auto;padding:24px 20px}
.aff-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.aff-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-aff{background:linear-gradient(135deg,#7c3aed 0%,#a855f7 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-danger{background:rgba(239,68,68,.15);color:#fca5a5;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid rgba(239,68,68,.3);cursor:pointer}
.form-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.form-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.82rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 14px;border-radius:8px;font-size:.85rem}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#7c3aed}
.form-group textarea{min-height:100px;resize:vertical}
.form-group .hint{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
</style>
<div class="aff-wrap">
    <div class="aff-header"><h1><?= $isEdit ? '✏️ Edit Program' : '➕ New Program' ?></h1><a href="/admin/affiliate/programs" class="btn-secondary">← Programs</a></div>
    <form method="POST" action="<?= h($action) ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-card">
            <h3>📋 Program Details</h3>
            <div class="form-group">
                <label>Program Name *</label>
                <input type="text" name="name" value="<?= h($program['name'] ?? '') ?>" required placeholder="e.g. Premium Referral Program">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Describe the affiliate program..."><?= h($program['description'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="form-card">
            <h3>💰 Commission Settings</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Commission Type</label>
                    <select name="commission_type">
                        <option value="percentage" <?= ($program['commission_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                        <option value="fixed" <?= ($program['commission_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed Amount ($)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Commission Value</label>
                    <input type="number" name="commission_value" value="<?= h($program['commission_value'] ?? '10') ?>" step="0.01" min="0" placeholder="e.g. 10">
                    <div class="hint">Percentage (e.g. 10 = 10%) or fixed dollar amount</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Cookie Duration (days)</label>
                    <input type="number" name="cookie_days" value="<?= h($program['cookie_days'] ?? '30') ?>" min="1" max="365">
                    <div class="hint">How long the referral cookie lasts</div>
                </div>
                <div class="form-group">
                    <label>Minimum Payout ($)</label>
                    <input type="number" name="min_payout" value="<?= h($program['min_payout'] ?? '50') ?>" step="0.01" min="0">
                    <div class="hint">Minimum earnings before payout is available</div>
                </div>
            </div>
        </div>
        <div class="form-card">
            <h3>⚙️ Status</h3>
            <div class="form-group">
                <select name="status">
                    <option value="active" <?= ($program['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($program['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:12px;align-items:center">
            <button type="submit" class="btn-aff"><?= $isEdit ? '💾 Update Program' : '➕ Create Program' ?></button>
            <?php if ($isEdit): ?>
            <form method="POST" action="/admin/affiliate/programs/<?= $program['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this program? All affiliates and conversions under it will also be deleted.')">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" class="btn-danger">🗑️ Delete</button>
            </form>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php $content = ob_get_clean(); $title = ($isEdit ? 'Edit' : 'New') . ' Program'; require CMS_APP . '/views/admin/layouts/topbar.php';
