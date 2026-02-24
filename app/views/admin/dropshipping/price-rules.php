<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$rules = $rules ?? [];
$suppliers = $suppliers ?? [];
ob_start();
?>
<style>
.ds-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.ds-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.ds-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-group input:focus,.form-group select:focus{outline:none;border-color:#6366f1}
.form-group .hint{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.btn-ds{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.btn-sm{padding:6px 14px;font-size:.78rem;border-radius:6px}
.btn-danger{background:#ef4444;color:#fff;border:none;padding:5px 12px;border-radius:6px;font-size:.78rem;cursor:pointer}

.rule-card{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;margin-bottom:12px;display:flex;align-items:center;gap:16px}
.rule-card .rule-info{flex:1}
.rule-card .rule-name{font-weight:700;font-size:.9rem;color:var(--text,#e2e8f0)}
.rule-card .rule-desc{font-size:.78rem;color:var(--muted,#94a3b8);margin-top:2px}
.rule-card .rule-formula{background:rgba(99,102,241,.1);color:#a5b4fc;padding:4px 12px;border-radius:6px;font-size:.82rem;font-weight:600;white-space:nowrap}

/* Calculator */
.calc-panel{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%);border:1px solid #4338ca;border-radius:12px;padding:20px;margin-bottom:20px}
.calc-panel h3{color:#c7d2fe;font-size:.9rem;margin:0 0 12px;border:none;padding:0;text-transform:none;letter-spacing:0}
.calc-result{background:rgba(15,23,42,.5);border-radius:8px;padding:14px;margin-top:12px;display:none}
.calc-result.show{display:block}
</style>

<div class="ds-wrap">
    <div class="ds-header">
        <h1>💰 Price Rules</h1>
        <a href="/admin/dropshipping" class="btn-secondary">← Dashboard</a>
    </div>

    <!-- Price Calculator -->
    <div class="calc-panel">
        <h3>🧮 Price Calculator — Preview</h3>
        <div style="display:flex;gap:10px;align-items:flex-end">
            <div class="form-group" style="flex:1;margin:0">
                <label style="color:#c7d2fe;font-size:.78rem">Supplier Price ($)</label>
                <input type="number" id="calc-price" step="0.01" min="0" value="10.00" style="background:rgba(15,23,42,.6);border-color:#4338ca;color:#e2e8f0">
            </div>
            <button type="button" class="btn-ds btn-sm" onclick="calculatePrice()" style="margin-bottom:16px">Calculate</button>
        </div>
        <div class="calc-result" id="calc-result"></div>
    </div>

    <!-- Existing Rules -->
    <div class="ds-card">
        <h3>📋 Active Rules</h3>
        <?php if (empty($rules)): ?>
            <p style="color:var(--muted,#94a3b8);font-size:.85rem">No price rules. Products will use default 2× markup.</p>
        <?php else: ?>
            <?php foreach ($rules as $r):
                $typeLabels = ['multiplier' => '×', 'fixed_markup' => '+$', 'percentage_markup' => '+%'];
                $formula = ($typeLabels[$r['type']] ?? '') . number_format((float)$r['value'], 2);
            ?>
            <div class="rule-card">
                <div class="rule-formula"><?= h($formula) ?></div>
                <div class="rule-info">
                    <div class="rule-name"><?= h($r['name']) ?></div>
                    <div class="rule-desc">
                        Applies to: <?= h($r['apply_to']) ?>
                        <?= $r['apply_to_id'] ? ' #' . (int)$r['apply_to_id'] : '' ?>
                        • Priority: <?= (int)$r['priority'] ?>
                        • Round: <?= h($r['round_to']) ?>
                        <?php if ((float)$r['min_price'] > 0): ?> • Min: $<?= number_format((float)$r['min_price'], 2) ?><?php endif; ?>
                    </div>
                </div>
                <span style="padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;<?= $r['status'] === 'active' ? 'background:rgba(16,185,129,.15);color:#34d399' : 'background:rgba(239,68,68,.15);color:#fca5a5' ?>"><?= h($r['status']) ?></span>
                <form method="post" action="/admin/dropshipping/price-rules/<?= (int)$r['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this rule?')">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <button type="submit" class="btn-danger">✕</button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Add New Rule -->
    <div class="ds-card">
        <h3>➕ Add New Rule</h3>
        <form method="post" action="/admin/dropshipping/price-rules/store">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Rule Name</label>
                    <input type="text" name="name" required placeholder="e.g. Default 2.5x Markup">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="multiplier">Multiplier (cost × value)</option>
                        <option value="fixed_markup">Fixed Markup (cost + $value)</option>
                        <option value="percentage_markup">Percentage Markup (cost + value%)</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Value</label>
                    <input type="number" name="value" step="0.01" min="0" value="2.50" required>
                    <div class="hint">For multiplier: 2.5 = 150% markup. For fixed: dollar amount. For %: percentage.</div>
                </div>
                <div class="form-group">
                    <label>Apply To</label>
                    <select name="apply_to">
                        <option value="all">All Products</option>
                        <option value="supplier">Specific Supplier</option>
                        <option value="category">Specific Category</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Apply To ID (if specific)</label>
                    <input type="number" name="apply_to_id" min="0" placeholder="Supplier or Category ID">
                </div>
                <div class="form-group">
                    <label>Round To</label>
                    <select name="round_to">
                        <option value="0.99">$X.99</option>
                        <option value="0.95">$X.95</option>
                        <option value="0.00">Round dollar</option>
                        <option value="none">No rounding</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Min Price ($)</label>
                    <input type="number" name="min_price" step="0.01" min="0" value="0" placeholder="0 = no minimum">
                </div>
                <div class="form-group">
                    <label>Priority (higher = wins)</label>
                    <input type="number" name="priority" min="0" max="100" value="0">
                </div>
            </div>

            <button type="submit" class="btn-ds">➕ Create Rule</button>
        </form>
    </div>
</div>

<script>
function calculatePrice() {
    var price = parseFloat(document.getElementById('calc-price').value) || 0;
    var resEl = document.getElementById('calc-result');

    fetch('/api/dropshipping/calculate-price', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({supplier_price: price}),
        credentials: 'same-origin'
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (!data.ok) { resEl.innerHTML = '<span style="color:#fca5a5">Error</span>'; resEl.classList.add('show'); return; }
        var r = data.result;
        var html = '<div style="display:flex;gap:20px;align-items:center;margin-bottom:10px">';
        html += '<div><div style="font-size:.72rem;color:#818cf8;text-transform:uppercase">Sell Price</div><div style="font-size:1.5rem;font-weight:800;color:#10b981">$' + r.price.toFixed(2) + '</div></div>';
        html += '<div><div style="font-size:.72rem;color:#818cf8;text-transform:uppercase">Margin</div><div style="font-size:1.2rem;font-weight:700;color:#a5b4fc">$' + r.margin.toFixed(2) + ' (' + r.margin_pct + '%)</div></div>';
        html += '<div><div style="font-size:.72rem;color:#818cf8;text-transform:uppercase">Rule</div><div style="font-size:.85rem;color:#c7d2fe">' + (r.rule_name || 'Default') + '</div></div>';
        html += '</div>';

        if (data.all_rules && data.all_rules.length > 0) {
            html += '<div style="font-size:.75rem;color:#818cf8;margin-top:8px">All rules preview:';
            data.all_rules.forEach(function(p) {
                html += '<div style="color:#c7d2fe;padding:2px 0">' + p.rule_name + ': $' + p.sell_price.toFixed(2) + ' (margin: $' + p.margin.toFixed(2) + ')</div>';
            });
            html += '</div>';
        }

        resEl.innerHTML = html;
        resEl.classList.add('show');
    })
    .catch(function(e) {
        resEl.innerHTML = '<span style="color:#fca5a5">Network error</span>';
        resEl.classList.add('show');
    });
}
</script>

<?php
$content = ob_get_clean();
$title = 'Price Rules';
require CMS_APP . '/views/admin/layouts/topbar.php';
