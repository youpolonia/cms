<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Product Categories';
ob_start();
?>
<style>
.shop-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.shop-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.shop-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.shop-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.shop-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-row{display:grid;grid-template-columns:2fr 1fr 2fr 80px 1fr;gap:10px;align-items:end;margin-bottom:16px}
@media(max-width:768px){.form-row{grid-template-columns:1fr;gap:8px}}
.form-group label{display:block;font-size:.75rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:4px}
.form-group input{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 10px;border-radius:6px;font-size:.85rem;box-sizing:border-box}
.form-group input:focus{outline:none;border-color:#6366f1}
.btn-primary{background:#6366f1;color:#fff;padding:8px 14px;border-radius:6px;font-size:.8rem;font-weight:600;border:none;cursor:pointer}
.btn-primary:hover{background:#4f46e5}
.btn-sm{padding:4px 10px;font-size:.75rem;border-radius:6px;text-decoration:none;border:none;cursor:pointer;font-weight:600}
.btn-edit{background:#3b82f622;color:#3b82f6}
.btn-del{background:#ef444422;color:#ef4444}
.shop-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.shop-tbl th,.shop-tbl td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.shop-tbl th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase}
.shop-tbl tr:hover{background:rgba(99,102,241,.04)}
.text-muted{color:var(--muted,#94a3b8)}
.inline-edit{display:none;gap:6px;align-items:center}
.inline-edit.active{display:flex}
.inline-edit input{padding:6px 8px;font-size:.8rem;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);border-radius:6px}
.cat-display.hidden{display:none}
</style>

<div class="shop-wrap">
    <div class="shop-header">
        <h1>📁 Product Categories</h1>
        <a href="/admin/shop" style="color:#6366f1;text-decoration:none;font-size:.85rem">← Back to Shop</a>
    </div>

    <div class="shop-card">
        <h3>➕ Add New Category</h3>
        <form method="post" action="/admin/shop/categories/store">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" required placeholder="Category name">
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" placeholder="auto">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Optional description">
                </div>
                <div class="form-group">
                    <label>Sort</label>
                    <input type="number" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-primary">+ Add</button>
                </div>
            </div>
        </form>
    </div>

    <div class="shop-card">
        <h3>📋 Categories (<?= count($categories) ?>)</h3>
        <?php if (empty($categories)): ?>
            <p class="text-muted" style="font-size:.85rem">No categories yet. Create your first one above.</p>
        <?php else: ?>
            <table class="shop-tbl">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Sort</th>
                        <th style="width:150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr id="cat-row-<?= (int)$cat['id'] ?>">
                        <td>
                            <span class="cat-display" id="cat-display-<?= (int)$cat['id'] ?>">
                                <strong><?= h($cat['name']) ?></strong>
                            </span>
                            <form class="inline-edit" id="cat-edit-<?= (int)$cat['id'] ?>" method="post" action="/admin/shop/categories/<?= (int)$cat['id'] ?>/update" style="display:none;gap:6px;align-items:center">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <input type="text" name="name" value="<?= h($cat['name']) ?>" style="width:100px" required>
                                <input type="text" name="slug" value="<?= h($cat['slug']) ?>" style="width:80px">
                                <input type="text" name="description" value="<?= h($cat['description'] ?? '') ?>" style="width:120px">
                                <input type="number" name="sort_order" value="<?= (int)($cat['sort_order'] ?? 0) ?>" style="width:50px" min="0">
                                <button type="submit" class="btn-sm btn-edit">💾</button>
                                <button type="button" class="btn-sm" onclick="toggleEdit(<?= (int)$cat['id'] ?>)" style="background:var(--border,#334155);color:var(--text,#e2e8f0)">✕</button>
                            </form>
                        </td>
                        <td class="text-muted"><?= h($cat['slug']) ?></td>
                        <td class="text-muted"><?= h($cat['description'] ?? '—') ?></td>
                        <td><?= (int)($cat['product_count'] ?? 0) ?></td>
                        <td><?= (int)($cat['sort_order'] ?? 0) ?></td>
                        <td>
                            <button type="button" class="btn-sm btn-edit" onclick="toggleEdit(<?= (int)$cat['id'] ?>)">✏️ Edit</button>
                            <form method="post" action="/admin/shop/categories/<?= (int)$cat['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this category? Products in this category will become uncategorized.')">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <button type="submit" class="btn-sm btn-del">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleEdit(id) {
    var display = document.getElementById('cat-display-' + id);
    var edit = document.getElementById('cat-edit-' + id);
    if (edit.style.display === 'none' || edit.style.display === '') {
        edit.style.display = 'flex';
        display.style.display = 'none';
    } else {
        edit.style.display = 'none';
        display.style.display = '';
    }
}
</script>

<?php
$content = ob_get_clean();
$title = $pageTitle;
require CMS_APP . '/views/admin/layouts/topbar.php';
