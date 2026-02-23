<?php
if (!function_exists('h')) { function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
$isEdit = !empty($coupon);
ob_start();
?>
<style>
.shop-wrap{max-width:800px;margin:0 auto;padding:24px 20px}
.shop-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.shop-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.shop-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:.85rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 12px;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);border-radius:8px;font-size:.9rem;box-sizing:border-box}
.form-group input:focus,.form-group select:focus{border-color:#6366f1;outline:none;box-shadow:0 0 0 2px #6366f133}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-hint{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.btn-primary{background:#6366f1;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-primary:hover{background:#4f46e5}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;text-decoration:none;font-size:.9rem;font-weight:600;border:1px solid var(--border,#334155)}
.btn-gen{background:#10b98133;color:#10b981;padding:8px 14px;border-radius:8px;border:none;cursor:pointer;font-size:.8rem;font-weight:600}
.btn-gen:hover{background:#10b98155}
.code-input-wrap{display:flex;gap:8px}
.code-input-wrap input{flex:1}
.text-muted{color:var(--muted,#94a3b8)}
</style>

<div class="shop-wrap">
    <div class="shop-header">
        <h1><?= $isEdit ? '✏️ Edit Coupon' : '🏷️ Create Coupon' ?></h1>
        <a href="/admin/shop/coupons" class="btn-secondary">← Back</a>
    </div>

    <form method="post" action="<?= $isEdit ? '/admin/shop/coupons/' . (int)$coupon['id'] . '/update' : '/admin/shop/coupons/store' ?>">
        <?= csrf_token_html() ?>

        <div class="shop-card">
            <h3 style="margin:0 0 16px;font-size:1rem;color:var(--text,#e2e8f0)">Coupon Details</h3>

            <div class="form-group">
                <label>Code</label>
                <div class="code-input-wrap">
                    <input type="text" name="code" id="coupon-code" value="<?= h($coupon['code'] ?? '') ?>" required placeholder="e.g. SUMMER20" style="text-transform:uppercase;font-family:monospace;letter-spacing:1px">
                    <button type="button" class="btn-gen" onclick="generateCode()">🎲 Generate</button>
                </div>
                <div class="form-hint">Unique code customers will enter at checkout</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Discount Type</label>
                    <select name="type" id="coupon-type" onchange="toggleValueField()">
                        <option value="percentage" <?= ($coupon['type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                        <option value="fixed" <?= ($coupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed Amount</option>
                        <option value="free_shipping" <?= ($coupon['type'] ?? '') === 'free_shipping' ? 'selected' : '' ?>>Free Shipping</option>
                    </select>
                </div>
                <div class="form-group" id="value-group">
                    <label>Value</label>
                    <input type="number" name="value" step="0.01" min="0" value="<?= h($coupon['value'] ?? '0') ?>" placeholder="e.g. 10">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Min. Order Amount</label>
                    <input type="number" name="min_order" step="0.01" min="0" value="<?= h($coupon['min_order'] ?? '') ?>" placeholder="No minimum">
                    <div class="form-hint">Leave empty for no minimum</div>
                </div>
                <div class="form-group">
                    <label>Max Discount</label>
                    <input type="number" name="max_discount" step="0.01" min="0" value="<?= h($coupon['max_discount'] ?? '') ?>" placeholder="No cap">
                    <div class="form-hint">Cap on percentage discounts</div>
                </div>
            </div>
        </div>

        <div class="shop-card">
            <h3 style="margin:0 0 16px;font-size:1rem;color:var(--text,#e2e8f0)">Usage Limits</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Max Uses (Total)</label>
                    <input type="number" name="max_uses" min="0" value="<?= h($coupon['max_uses'] ?? '') ?>" placeholder="Unlimited">
                </div>
                <div class="form-group">
                    <label>Per Customer Limit</label>
                    <input type="number" name="per_customer_limit" min="0" value="<?= h($coupon['per_customer_limit'] ?? '1') ?>">
                </div>
            </div>
        </div>

        <div class="shop-card">
            <h3 style="margin:0 0 16px;font-size:1rem;color:var(--text,#e2e8f0)">Validity Period</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Valid From</label>
                    <input type="datetime-local" name="valid_from" value="<?= !empty($coupon['valid_from']) ? date('Y-m-d\TH:i', strtotime($coupon['valid_from'])) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Valid Until</label>
                    <input type="datetime-local" name="valid_until" value="<?= !empty($coupon['valid_until']) ? date('Y-m-d\TH:i', strtotime($coupon['valid_until'])) : '' ?>">
                </div>
            </div>
        </div>

        <div class="shop-card">
            <h3 style="margin:0 0 16px;font-size:1rem;color:var(--text,#e2e8f0)">Scope & Status</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Applies To</label>
                    <select name="applies_to">
                        <option value="all" <?= ($coupon['applies_to'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Products</option>
                        <option value="category" <?= ($coupon['applies_to'] ?? '') === 'category' ? 'selected' : '' ?>>Specific Categories</option>
                        <option value="product" <?= ($coupon['applies_to'] ?? '') === 'product' ? 'selected' : '' ?>>Specific Products</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?= ($coupon['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($coupon['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Applies To IDs <span class="text-muted">(comma-separated category/product IDs)</span></label>
                <input type="text" name="applies_to_ids" value="<?= h($coupon['applies_to_ids'] ?? '') ?>" placeholder="e.g. 1,3,5">
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px">
            <a href="/admin/shop/coupons" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary"><?= $isEdit ? '💾 Update Coupon' : '🏷️ Create Coupon' ?></button>
        </div>
    </form>
</div>

<script>
function generateCode() {
    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    var code = '';
    for (var i = 0; i < 8; i++) code += chars.charAt(Math.floor(Math.random() * chars.length));
    document.getElementById('coupon-code').value = code;
}
function toggleValueField() {
    var t = document.getElementById('coupon-type').value;
    document.getElementById('value-group').style.display = (t === 'free_shipping') ? 'none' : '';
}
toggleValueField();
</script>
<?php
$content = ob_get_clean();
$title = ($isEdit ? 'Edit' : 'Create') . ' Coupon — Shop';
require CMS_ROOT . '/app/views/admin/layouts/topbar.php';
