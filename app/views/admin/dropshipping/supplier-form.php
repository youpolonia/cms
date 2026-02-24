<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$isEdit = $supplier !== null;
$v = fn($key, $default = '') => h($isEdit ? ($supplier[$key] ?? $default) : $default);
ob_start();
?>
<style>
.ds-wrap{max-width:700px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.ds-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.ds-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#6366f1}
.form-group textarea{min-height:80px;resize:vertical;font-family:inherit}
.form-group .hint{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.btn-ds{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.form-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:24px}
.btn-danger{background:#ef4444;color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer}
</style>

<div class="ds-wrap">
    <div class="ds-header">
        <h1><?= $isEdit ? '✏️ Edit Supplier' : '➕ Add Supplier' ?></h1>
        <a href="/admin/dropshipping/suppliers" class="btn-secondary">← Back</a>
    </div>

    <form method="post" action="<?= $isEdit ? '/admin/dropshipping/suppliers/' . (int)$supplier['id'] . '/update' : '/admin/dropshipping/suppliers/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="ds-card">
            <h3>📋 Basic Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Supplier Name *</label>
                    <input type="text" name="name" value="<?= $v('name') ?>" required placeholder="e.g. AliExpress Store XYZ">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <?php foreach (['manual' => 'Manual', 'aliexpress' => 'AliExpress', 'cjdropshipping' => 'CJ Dropshipping', 'generic_api' => 'Generic API', 'csv' => 'CSV/Spreadsheet'] as $tk => $tl): ?>
                        <option value="<?= $tk ?>" <?= ($isEdit && ($supplier['type'] ?? '') === $tk) ? 'selected' : '' ?>><?= $tl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Website</label>
                <input type="url" name="website" value="<?= $v('website') ?>" placeholder="https://supplier-website.com">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?= ($isEdit && ($supplier['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($isEdit && ($supplier['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="ds-card">
            <h3>🔑 API Configuration (Optional)</h3>
            <div class="form-group">
                <label>API Key</label>
                <input type="text" name="api_key" value="<?= $v('api_key') ?>" placeholder="For automated integrations">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>API Secret</label>
                    <input type="text" name="api_secret" value="<?= $v('api_secret') ?>">
                </div>
                <div class="form-group">
                    <label>API Base URL</label>
                    <input type="url" name="api_base_url" value="<?= $v('api_base_url') ?>" placeholder="https://api.supplier.com/v1">
                </div>
            </div>
        </div>

        <div class="ds-card">
            <h3>👤 Contact</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Contact Name</label>
                    <input type="text" name="contact_name" value="<?= $v('contact_name') ?>">
                </div>
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email" value="<?= $v('contact_email') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3"><?= h($isEdit ? ($supplier['notes'] ?? '') : '') ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <?php if ($isEdit): ?>
            <form method="post" action="/admin/dropshipping/suppliers/<?= (int)$supplier['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this supplier? Products will be unlinked.')">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <button type="submit" class="btn-danger">🗑️ Delete</button>
            </form>
            <?php endif; ?>
            <a href="/admin/dropshipping/suppliers" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-ds"><?= $isEdit ? '💾 Update Supplier' : '➕ Create Supplier' ?></button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$title = $isEdit ? 'Edit Supplier' : 'Add Supplier';
require CMS_APP . '/views/admin/layouts/topbar.php';
