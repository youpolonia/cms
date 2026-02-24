<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
ob_start();
?>
<style>
.ds-wrap{max-width:1000px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-ds{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.ds-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.ds-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.ds-table td{padding:12px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.ds-table tr:last-child td{border-bottom:none}
.ds-table tr:hover td{background:rgba(99,102,241,.04)}
.badge{padding:3px 10px;border-radius:4px;font-size:.72rem;font-weight:600}
.badge-active{background:rgba(16,185,129,.15);color:#34d399}
.badge-inactive{background:rgba(239,68,68,.15);color:#fca5a5}
.type-badge{background:rgba(99,102,241,.12);color:#a5b4fc;padding:2px 8px;border-radius:4px;font-size:.72rem}
.btn-sm{padding:5px 12px;font-size:.78rem;border-radius:6px}
</style>

<div class="ds-wrap">
    <div class="ds-header">
        <h1>🏭 Suppliers</h1>
        <div style="display:flex;gap:10px">
            <a href="/admin/dropshipping" class="btn-secondary">← Dashboard</a>
            <a href="/admin/dropshipping/suppliers/create" class="btn-ds">➕ Add Supplier</a>
        </div>
    </div>

    <?php if (empty($suppliers)): ?>
        <div style="text-align:center;padding:60px 20px;color:var(--muted,#94a3b8)">
            <p style="font-size:1.2rem">No suppliers yet.</p>
            <a href="/admin/dropshipping/suppliers/create" class="btn-ds" style="margin-top:12px;text-decoration:none">➕ Add Your First Supplier</a>
        </div>
    <?php else: ?>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Website</th>
                    <th>Products</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $s): ?>
                <tr>
                    <td><strong><?= h($s['name']) ?></strong></td>
                    <td><span class="type-badge"><?= h($s['type']) ?></span></td>
                    <td><?php if ($s['website']): ?><a href="<?= h($s['website']) ?>" target="_blank" style="color:#a5b4fc;font-size:.8rem"><?= h(parse_url($s['website'], PHP_URL_HOST) ?: $s['website']) ?></a><?php endif; ?></td>
                    <td><?= (int)($s['linked_products'] ?? $s['products_count'] ?? 0) ?></td>
                    <td style="font-size:.8rem;color:var(--muted,#94a3b8)"><?= h($s['contact_name'] ?? '') ?></td>
                    <td><span class="badge badge-<?= $s['status'] ?>"><?= h($s['status']) ?></span></td>
                    <td>
                        <a href="/admin/dropshipping/suppliers/<?= (int)$s['id'] ?>/edit" class="btn-ds btn-sm" style="text-decoration:none">✏️ Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Suppliers';
require CMS_APP . '/views/admin/layouts/topbar.php';
