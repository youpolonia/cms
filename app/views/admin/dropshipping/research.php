<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
ob_start();
?>
<style>
.ds-wrap{max-width:1000px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.ds-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.ds-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-group input:focus,.form-group select:focus{outline:none;border-color:#6366f1}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.btn-ds{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px}
.btn-ds:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(99,102,241,.4)}
.btn-ds:disabled{opacity:.5;cursor:not-allowed;transform:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.ai-spinner{display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:aispin .6s linear infinite}
@keyframes aispin{to{transform:rotate(360deg)}}

/* Tabs */
.r-tabs{display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid var(--border,#334155)}
.r-tab{background:transparent;border:none;color:var(--muted,#94a3b8);padding:10px 18px;font-size:.85rem;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px}
.r-tab:hover{color:var(--text,#e2e8f0)}
.r-tab.active{color:#a5b4fc;border-bottom-color:#6366f1}
.r-panel{display:none}
.r-panel.active{display:block}

/* Results */
.result-box{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);border-radius:10px;padding:20px;margin-top:16px;display:none}
.result-box.show{display:block}

.score-row{display:flex;gap:16px;flex-wrap:wrap;margin:12px 0}
.score-item{text-align:center;flex:1;min-width:80px}
.score-circle{width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;margin:0 auto 4px;border:3px solid}
.score-item .label{font-size:.7rem;color:var(--muted,#94a3b8);text-transform:uppercase}

.niche-card{background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.15);border-radius:10px;padding:16px;margin-bottom:12px}
.niche-card h4{color:var(--text,#e2e8f0);margin:0 0 6px;font-size:.95rem}
.niche-card .meta{font-size:.78rem;color:var(--muted,#94a3b8);display:flex;gap:12px;flex-wrap:wrap;margin-bottom:8px}
.niche-card .tag{background:rgba(99,102,241,.12);color:#a5b4fc;padding:2px 8px;border-radius:4px;font-size:.72rem}
.niche-card p{font-size:.82rem;color:var(--text,#e2e8f0);margin:6px 0}

.verdict-badge{display:inline-block;padding:4px 14px;border-radius:6px;font-weight:700;font-size:.82rem;text-transform:uppercase}
.verdict-BUY{background:rgba(16,185,129,.15);color:#34d399}
.verdict-MAYBE{background:rgba(245,158,11,.15);color:#fbbf24}
.verdict-SKIP{background:rgba(239,68,68,.15);color:#fca5a5}

.profit-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:12px}
.profit-box{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-align:center}
.profit-box .val{font-size:1.4rem;font-weight:800;line-height:1}
.profit-box .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase}
</style>

<div class="ds-wrap">
    <div class="ds-header">
        <h1>🔬 AI Product Research</h1>
        <a href="/admin/dropshipping" class="btn-secondary">← Dashboard</a>
    </div>

    <div class="r-tabs">
        <button class="r-tab active" onclick="switchTab('scout',this)">🔍 Product Scout</button>
        <button class="r-tab" onclick="switchTab('niche',this)">💡 Niche Finder</button>
        <button class="r-tab" onclick="switchTab('compete',this)">⚔️ Competition</button>
        <button class="r-tab" onclick="switchTab('profit',this)">💰 Profit Calculator</button>
        <button class="r-tab" onclick="switchTab('trends',this)">📈 Trends</button>
    </div>

    <!-- Product Scout -->
    <div class="r-panel active" id="panel-scout">
        <div class="ds-card">
            <h3>🔍 AI Product Scout</h3>
            <p style="color:var(--muted,#94a3b8);font-size:.82rem;margin:0 0 16px">Paste a product URL → AI analyzes profitability, competition, demand, and gives a Buy/Skip verdict.</p>
            <div class="form-group">
                <label>Product URL</label>
                <input type="url" id="scout-url" placeholder="https://aliexpress.com/item/...">
            </div>
            <div class="form-row">
                <div class="form-group"><label>Target Market</label><select id="scout-market"><option value="global">Global</option><option value="us">US</option><option value="eu">Europe</option><option value="uk">UK</option></select></div>
                <div class="form-group"><label>Language</label><select id="scout-lang"><option value="en">English</option><option value="pl">Polish</option></select></div>
            </div>
            <button type="button" class="btn-ds" id="btn-scout" onclick="runScout()"><span class="ai-spinner" id="scout-spin"></span>🔍 Analyze Product</button>
            <div class="result-box" id="scout-result"></div>
        </div>
    </div>

    <!-- Niche Finder -->
    <div class="r-panel" id="panel-niche">
        <div class="ds-card">
            <h3>💡 AI Niche Finder</h3>
            <p style="color:var(--muted,#94a3b8);font-size:.82rem;margin:0 0 16px">AI suggests profitable dropshipping niches based on your criteria.</p>
            <div class="form-row">
                <div class="form-group"><label>Budget Level</label><select id="niche-budget"><option value="low">Low (&lt;$500)</option><option value="medium" selected>Medium ($500-2000)</option><option value="high">High ($2000+)</option></select></div>
                <div class="form-group"><label>Market</label><select id="niche-market"><option value="global">Global</option><option value="us">US</option><option value="eu">Europe</option><option value="uk">UK</option></select></div>
            </div>
            <div class="form-group"><label>Interest Area (optional)</label><input type="text" id="niche-interest" placeholder="e.g. fitness, home decor, pets, tech"></div>
            <button type="button" class="btn-ds" id="btn-niche" onclick="runNiche()"><span class="ai-spinner" id="niche-spin"></span>💡 Find Niches</button>
            <div class="result-box" id="niche-result"></div>
        </div>
    </div>

    <!-- Competition -->
    <div class="r-panel" id="panel-compete">
        <div class="ds-card">
            <h3>⚔️ Competition Analyzer</h3>
            <p style="color:var(--muted,#94a3b8);font-size:.82rem;margin:0 0 16px">Analyze the competitive landscape for any product or niche.</p>
            <div class="form-group"><label>Product or Niche</label><input type="text" id="comp-query" placeholder="e.g. wireless earbuds, yoga accessories, pet grooming tools"></div>
            <div class="form-group"><label>Market</label><select id="comp-market"><option value="global">Global</option><option value="us">US</option><option value="eu">Europe</option></select></div>
            <button type="button" class="btn-ds" id="btn-comp" onclick="runCompetition()"><span class="ai-spinner" id="comp-spin"></span>⚔️ Analyze Competition</button>
            <div class="result-box" id="comp-result"></div>
        </div>
    </div>

    <!-- Profit Calculator -->
    <div class="r-panel" id="panel-profit">
        <div class="ds-card">
            <h3>💰 Full Profit Calculator</h3>
            <p style="color:var(--muted,#94a3b8);font-size:.82rem;margin:0 0 16px">Calculate real profit including ALL costs (shipping, ads, payment fees, returns, overhead).</p>
            <div class="form-row">
                <div class="form-group"><label>Supplier Price ($)</label><input type="number" id="pc-supplier" step="0.01" value="10.00"></div>
                <div class="form-group"><label>Sell Price ($)</label><input type="number" id="pc-sell" step="0.01" value="29.99"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Shipping Cost ($)</label><input type="number" id="pc-shipping" step="0.01" value="3.00"></div>
                <div class="form-group"><label>Ad Spend per Sale ($)</label><input type="number" id="pc-ads" step="0.01" value="5.00"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Return Rate (%)</label><input type="number" id="pc-returns" step="0.1" value="5"></div>
                <div class="form-group"><label>Expected Sales/Month</label><input type="number" id="pc-sales" value="30"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Monthly Overhead ($)</label><input type="number" id="pc-overhead" step="0.01" value="100"></div>
                <div class="form-group"><label>Payment Fee (%)</label><input type="number" id="pc-payfee" step="0.1" value="2.9"></div>
            </div>
            <button type="button" class="btn-ds" onclick="runProfitCalc()">💰 Calculate</button>
            <div class="result-box" id="profit-result"></div>
        </div>
    </div>

    <!-- Trends -->
    <div class="r-panel" id="panel-trends">
        <div class="ds-card">
            <h3>📈 Trend Analysis</h3>
            <p style="color:var(--muted,#94a3b8);font-size:.82rem;margin:0 0 16px">AI analyzes market trends, emerging categories, and predictions.</p>
            <div class="form-group"><label>Category</label><input type="text" id="trend-cat" placeholder="e.g. smart home, sustainable products, pet tech"></div>
            <div class="form-group"><label>Market</label><select id="trend-market"><option value="global">Global</option><option value="us">US</option><option value="eu">Europe</option></select></div>
            <button type="button" class="btn-ds" id="btn-trend" onclick="runTrends()"><span class="ai-spinner" id="trend-spin"></span>📈 Analyze Trends</button>
            <div class="result-box" id="trend-result"></div>
        </div>
    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.r-tab').forEach(function(t){ t.classList.remove('active'); });
    document.querySelectorAll('.r-panel').forEach(function(p){ p.classList.remove('active'); });
    btn.classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
}

function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function apiPost(url, payload, spinnerId) {
    var spin = document.getElementById(spinnerId);
    if (spin) spin.style.display = 'inline-block';
    return fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload),
        credentials: 'same-origin'
    }).then(function(r){ return r.json(); })
    .finally(function(){ if (spin) spin.style.display = 'none'; });
}

function scoreColor(v) {
    if (v >= 70) return '#34d399';
    if (v >= 40) return '#fbbf24';
    return '#fca5a5';
}

function runScout() {
    var url = document.getElementById('scout-url').value.trim();
    if (!url) return alert('Enter URL');
    var el = document.getElementById('scout-result');
    el.classList.add('show');
    el.innerHTML = '<div style="color:#a5b4fc">⏳ AI is analyzing this product...</div>';

    apiPost('/api/dropshipping/ai-scout', {url: url, market: document.getElementById('scout-market').value}, 'scout-spin')
    .then(function(data) {
        if (!data.ok) { el.innerHTML = '<span style="color:#fca5a5">❌ ' + esc(data.error) + '</span>'; return; }
        var a = data.analysis;
        var html = '<div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">';
        html += '<strong style="font-size:1.1rem">' + esc(a.product_name) + '</strong>';
        html += '<span class="verdict-badge verdict-' + (a.verdict || 'MAYBE') + '">' + (a.verdict || '?') + '</span>';
        html += '</div>';
        html += '<p style="font-size:.85rem;color:var(--muted,#94a3b8)">' + esc(a.reasoning) + '</p>';

        if (a.scores) {
            html += '<div class="score-row">';
            ['profitability','demand','competition','shipping_ease','overall'].forEach(function(k) {
                var v = a.scores[k] || 0;
                html += '<div class="score-item"><div class="score-circle" style="color:' + scoreColor(v) + ';border-color:' + scoreColor(v) + '">' + v + '</div><div class="label">' + k.replace('_',' ') + '</div></div>';
            });
            html += '</div>';
        }

        if (a.pros) { html += '<div style="margin-top:12px"><strong style="color:#34d399;font-size:.82rem">✅ Pros:</strong>'; a.pros.forEach(function(p){ html += '<div style="font-size:.82rem;padding:2px 0;color:var(--text,#e2e8f0)">• ' + esc(p) + '</div>'; }); html += '</div>'; }
        if (a.cons) { html += '<div style="margin-top:8px"><strong style="color:#fca5a5;font-size:.82rem">❌ Cons:</strong>'; a.cons.forEach(function(c){ html += '<div style="font-size:.82rem;padding:2px 0;color:var(--text,#e2e8f0)">• ' + esc(c) + '</div>'; }); html += '</div>'; }
        if (a.marketing_angles) { html += '<div style="margin-top:8px"><strong style="color:#a5b4fc;font-size:.82rem">📣 Marketing Angles:</strong>'; a.marketing_angles.forEach(function(m){ html += '<div style="font-size:.82rem;padding:2px 0;color:var(--text,#e2e8f0)">• ' + esc(m) + '</div>'; }); html += '</div>'; }
        el.innerHTML = html;
    });
}

function runNiche() {
    var el = document.getElementById('niche-result');
    el.classList.add('show');
    el.innerHTML = '<div style="color:#a5b4fc">⏳ AI is finding niches...</div>';

    apiPost('/api/dropshipping/ai-niches', {
        budget: document.getElementById('niche-budget').value,
        market: document.getElementById('niche-market').value,
        interest: document.getElementById('niche-interest').value
    }, 'niche-spin')
    .then(function(data) {
        if (!data.ok) { el.innerHTML = '<span style="color:#fca5a5">❌ ' + esc(data.error) + '</span>'; return; }
        var html = '';
        (data.niches || []).forEach(function(n) {
            html += '<div class="niche-card">';
            html += '<h4>' + esc(n.name) + '</h4>';
            html += '<div class="meta">';
            html += '<span>' + esc(n.avg_product_price) + '</span>';
            html += '<span>Margin: ' + esc(n.avg_margin) + '</span>';
            html += '<span class="tag">Competition: ' + esc(n.competition_level) + '</span>';
            html += '<span class="tag">Trend: ' + esc(n.trend) + '</span>';
            html += '<span>Difficulty: ' + (n.difficulty || '?') + '/10</span>';
            html += '<span>Profit: ' + (n.profit_potential || '?') + '/10</span>';
            html += '</div>';
            html += '<p>' + esc(n.why) + '</p>';
            if (n.example_products) { html += '<div style="font-size:.78rem;color:var(--muted,#94a3b8)">Products: ' + n.example_products.map(esc).join(', ') + '</div>'; }
            html += '</div>';
        });
        el.innerHTML = html || '<span style="color:#fca5a5">No niches found</span>';
    });
}

function runCompetition() {
    var q = document.getElementById('comp-query').value.trim();
    if (!q) return alert('Enter product/niche');
    var el = document.getElementById('comp-result');
    el.classList.add('show');
    el.innerHTML = '<div style="color:#a5b4fc">⏳ Analyzing competition...</div>';

    apiPost('/api/dropshipping/ai-competition', {query: q, market: document.getElementById('comp-market').value}, 'comp-spin')
    .then(function(data) {
        if (!data.ok) { el.innerHTML = '<span style="color:#fca5a5">❌ ' + esc(data.error) + '</span>'; return; }
        var a = data.analysis;
        var html = '<div style="margin-bottom:12px"><strong>' + esc(a.query) + '</strong> — Market: ' + esc(a.market_size) + ', Saturation: ' + esc(a.saturation_level) + '</div>';
        if (a.verdict) { html += '<div style="margin-bottom:12px"><span class="verdict-badge ' + (a.verdict.enter_market ? 'verdict-BUY' : 'verdict-SKIP') + '">' + (a.verdict.enter_market ? 'ENTER' : 'AVOID') + '</span> <span style="color:var(--muted,#94a3b8);font-size:.82rem">(Confidence: ' + (a.verdict.confidence || '?') + '%) — ' + esc(a.verdict.reasoning) + '</span></div>'; }
        if (a.opportunities) { html += '<strong style="color:#34d399;font-size:.82rem">Opportunities:</strong>'; a.opportunities.forEach(function(o){ html += '<div style="font-size:.82rem;padding:2px 0">• ' + esc(o) + '</div>'; }); }
        if (a.differentiation_ideas) { html += '<div style="margin-top:8px"><strong style="color:#a5b4fc;font-size:.82rem">Differentiation:</strong>'; a.differentiation_ideas.forEach(function(d){ html += '<div style="font-size:.82rem;padding:2px 0">• ' + esc(d) + '</div>'; }); html += '</div>'; }
        if (a.recommended_strategy) { html += '<div style="margin-top:8px;font-size:.85rem;color:var(--text,#e2e8f0)"><strong>Strategy:</strong> ' + esc(a.recommended_strategy) + '</div>'; }
        el.innerHTML = html;
    });
}

function runProfitCalc() {
    var el = document.getElementById('profit-result');
    var params = {
        supplier_price: parseFloat(document.getElementById('pc-supplier').value) || 0,
        sell_price: parseFloat(document.getElementById('pc-sell').value) || 0,
        shipping_cost: parseFloat(document.getElementById('pc-shipping').value) || 0,
        ad_spend_per_sale: parseFloat(document.getElementById('pc-ads').value) || 0,
        return_rate: parseFloat(document.getElementById('pc-returns').value) || 0,
        expected_sales: parseInt(document.getElementById('pc-sales').value) || 30,
        monthly_overhead: parseFloat(document.getElementById('pc-overhead').value) || 0,
        payment_fee: parseFloat(document.getElementById('pc-payfee').value) || 2.9
    };

    apiPost('/api/dropshipping/profit-calc', params, null)
    .then(function(data) {
        if (!data.ok) { el.classList.add('show'); el.innerHTML = '<span style="color:#fca5a5">Error</span>'; return; }
        var u = data.per_unit, m = data.monthly, a = data.annual;
        var vc = u.profit >= 0 ? '#34d399' : '#fca5a5';
        var html = '<div class="profit-grid">';
        html += '<div class="profit-box"><div class="val" style="color:' + vc + '">$' + u.profit.toFixed(2) + '</div><div class="lbl">Profit/Unit</div></div>';
        html += '<div class="profit-box"><div class="val" style="color:' + vc + '">' + u.margin_pct + '%</div><div class="lbl">Margin</div></div>';
        html += '<div class="profit-box"><div class="val" style="color:#a5b4fc">$' + u.total_cost.toFixed(2) + '</div><div class="lbl">Total Cost/Unit</div></div>';
        html += '<div class="profit-box"><div class="val" style="color:' + (m.profit >= 0 ? '#34d399' : '#fca5a5') + '">$' + m.profit.toFixed(2) + '</div><div class="lbl">Monthly Profit</div></div>';
        html += '<div class="profit-box"><div class="val" style="color:#f59e0b">$' + a.profit.toFixed(2) + '</div><div class="lbl">Annual Profit</div></div>';
        html += '<div class="profit-box"><div class="val" style="color:var(--text,#e2e8f0)">' + u.roi_pct + '%</div><div class="lbl">ROI</div></div>';
        html += '</div>';

        html += '<div style="margin-top:16px;font-size:.82rem;color:var(--muted,#94a3b8)">';
        html += '<strong>Cost Breakdown:</strong> Product: $' + u.supplier_cost.toFixed(2);
        html += ' + Shipping: $' + u.shipping.toFixed(2);
        html += ' + Ads: $' + u.ad_spend.toFixed(2);
        html += ' + Payment: $' + u.payment_fee.toFixed(2);
        html += ' + Returns: $' + u.return_cost.toFixed(2);
        html += ' + Overhead: $' + u.overhead.toFixed(2);
        html += '</div>';

        var verdict = data.verdict;
        var vColor = {'highly_profitable':'#34d399','profitable':'#10b981','risky':'#fbbf24','unprofitable':'#ef4444'}[verdict] || '#94a3b8';
        html += '<div style="margin-top:12px;font-weight:700;color:' + vColor + ';text-transform:uppercase">' + verdict.replace('_',' ') + '</div>';

        if (data.breakeven > 0) { html += '<div style="font-size:.78rem;color:var(--muted,#94a3b8)">Break-even: ' + data.breakeven + ' sales/month to cover overhead</div>'; }

        el.classList.add('show');
        el.innerHTML = html;
    });
}

function runTrends() {
    var cat = document.getElementById('trend-cat').value.trim();
    if (!cat) return alert('Enter category');
    var el = document.getElementById('trend-result');
    el.classList.add('show');
    el.innerHTML = '<div style="color:#a5b4fc">⏳ Analyzing trends...</div>';

    apiPost('/api/dropshipping/ai-trends', {category: cat, market: document.getElementById('trend-market').value}, 'trend-spin')
    .then(function(data) {
        if (!data.ok) { el.innerHTML = '<span style="color:#fca5a5">❌ ' + esc(data.error) + '</span>'; return; }
        var t = data.trends;
        var html = '<div style="margin-bottom:12px"><strong>' + esc(t.category) + '</strong> — ';
        html += 'Trend: <span style="color:#a5b4fc">' + esc(t.trend_direction) + '</span>';
        html += ' • Growth: <span style="color:#34d399">' + esc(t.growth_rate) + '</span></div>';

        if (t.emerging_subcategories) { html += '<div style="margin-bottom:8px"><strong style="color:#34d399;font-size:.82rem">🔥 Emerging:</strong> ' + t.emerging_subcategories.map(esc).join(', ') + '</div>'; }
        if (t.dying_subcategories) { html += '<div style="margin-bottom:8px"><strong style="color:#fca5a5;font-size:.82rem">💀 Declining:</strong> ' + t.dying_subcategories.map(esc).join(', ') + '</div>'; }
        if (t.predictions) { html += '<div style="margin-top:10px"><strong style="font-size:.82rem">🔮 Predictions:</strong>'; t.predictions.forEach(function(p){ html += '<div style="font-size:.82rem;padding:2px 0">• ' + esc(p) + '</div>'; }); html += '</div>'; }
        if (t.recommended_products) { html += '<div style="margin-top:10px"><strong style="font-size:.82rem">📦 Product Ideas:</strong>'; t.recommended_products.forEach(function(p){ html += '<div style="font-size:.82rem;padding:2px 0">• ' + esc(p) + '</div>'; }); html += '</div>'; }
        el.innerHTML = html;
    });
}
</script>

<?php
$content = ob_get_clean();
$title = 'AI Product Research';
require CMS_APP . '/views/admin/layouts/topbar.php';
