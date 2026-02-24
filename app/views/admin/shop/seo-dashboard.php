<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$products = $products ?? [];
$summary = $summary ?? [];
ob_start();
?>
<style>
.seo-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.seo-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.seo-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.seo-actions{display:flex;gap:10px;flex-wrap:wrap}

/* Summary Cards */
.seo-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px}
.seo-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;text-align:center}
.seo-stat .stat-value{font-size:2rem;font-weight:800;line-height:1}
.seo-stat .stat-label{font-size:.78rem;color:var(--muted,#94a3b8);margin-top:6px;text-transform:uppercase;letter-spacing:.05em}
.seo-stat.good .stat-value{color:#10b981}
.seo-stat.warning .stat-value{color:#f59e0b}
.seo-stat.critical .stat-value{color:#ef4444}
.seo-stat.avg .stat-value{color:#6366f1}

/* Score bar */
.score-bar{display:flex;align-items:center;gap:8px}
.score-fill{height:8px;border-radius:4px;transition:width .3s}
.score-track{flex:1;height:8px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden}
.score-num{font-size:.82rem;font-weight:700;min-width:32px;text-align:right}
.score-A .score-fill,.score-B .score-fill{background:#10b981}
.score-C .score-fill{background:#f59e0b}
.score-D .score-fill,.score-F .score-fill{background:#ef4444}
.score-A .score-num{color:#10b981}
.score-B .score-num{color:#10b981}
.score-C .score-num{color:#f59e0b}
.score-D .score-num,.score-F .score-num{color:#ef4444}

/* Grade badge */
.grade{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;font-weight:800;font-size:.8rem}
.grade-A{background:rgba(16,185,129,.2);color:#10b981}
.grade-B{background:rgba(16,185,129,.15);color:#34d399}
.grade-C{background:rgba(245,158,11,.2);color:#f59e0b}
.grade-D{background:rgba(239,68,68,.2);color:#ef4444}
.grade-F{background:rgba(239,68,68,.25);color:#ef4444}

/* Table */
.seo-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.seo-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.seo-table td{padding:12px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0);vertical-align:middle}
.seo-table tr:last-child td{border-bottom:none}
.seo-table tr:hover td{background:rgba(99,102,241,.04)}
.product-name{font-weight:600;max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.product-name a{color:#a5b4fc;text-decoration:none}
.product-name a:hover{text-decoration:underline}
.check-icon{font-size:.85rem}
.check-yes{color:#10b981}
.check-no{color:#ef4444}
.category-tag{background:rgba(99,102,241,.12);color:#a5b4fc;padding:2px 8px;border-radius:4px;font-size:.75rem;white-space:nowrap}
.issues-list{display:flex;flex-wrap:wrap;gap:4px}
.issue-tag{background:rgba(245,158,11,.12);color:#fbbf24;padding:2px 8px;border-radius:4px;font-size:.7rem;white-space:nowrap}
.issue-tag.critical{background:rgba(239,68,68,.12);color:#fca5a5}

/* Buttons */
.btn-ai{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s;text-decoration:none}
.btn-ai:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(99,102,241,.4)}
.btn-ai:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-sm{padding:6px 14px;font-size:.78rem;border-radius:6px}

/* Bulk toolbar */
.bulk-bar{display:none;background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%);border:1px solid #4338ca;border-radius:10px;padding:14px 18px;margin-bottom:16px;align-items:center;gap:12px;flex-wrap:wrap}
.bulk-bar.show{display:flex}
.bulk-count{color:#c7d2fe;font-weight:700;font-size:.9rem}
.bulk-actions{display:flex;gap:8px;margin-left:auto}

/* Modal */
.seo-modal-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center}
.seo-modal-overlay.show{display:flex}
.seo-modal{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:16px;width:90%;max-width:700px;max-height:85vh;overflow-y:auto;padding:28px}
.seo-modal h2{font-size:1.2rem;font-weight:700;color:var(--text,#e2e8f0);margin:0 0 20px;display:flex;align-items:center;gap:8px}
.seo-modal .close-btn{position:absolute;top:16px;right:16px;background:none;border:none;color:var(--muted,#94a3b8);font-size:1.4rem;cursor:pointer;padding:4px 8px}
.seo-modal .close-btn:hover{color:var(--text,#e2e8f0)}

.ai-spinner{display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:aispin .6s linear infinite}
@keyframes aispin{to{transform:rotate(360deg)}}

/* Filter */
.seo-filter{display:flex;gap:10px;align-items:center;margin-bottom:16px;flex-wrap:wrap}
.seo-filter select,.seo-filter input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}

@media(max-width:768px){
    .seo-table{font-size:.78rem}
    .seo-table th,.seo-table td{padding:8px 10px}
}
</style>

<div class="seo-wrap">
    <div class="seo-header">
        <h1>🔍 Product SEO Dashboard</h1>
        <div class="seo-actions">
            <a href="/admin/shop/products" class="btn-secondary btn-sm">← Products</a>
            <button type="button" class="btn-ai btn-sm" onclick="refreshScan()" id="btn-refresh">
                <span class="ai-spinner" id="refresh-spinner"></span>
                🔄 Refresh Scan
            </button>
        </div>
    </div>

    <!-- Summary -->
    <div class="seo-summary">
        <div class="seo-stat avg">
            <div class="stat-value"><?= (int)($summary['avg_score'] ?? 0) ?></div>
            <div class="stat-label">Avg Score</div>
        </div>
        <div class="seo-stat">
            <div class="stat-value" style="color:var(--text,#e2e8f0)"><?= (int)($summary['total'] ?? 0) ?></div>
            <div class="stat-label">Products</div>
        </div>
        <div class="seo-stat good">
            <div class="stat-value"><?= (int)($summary['good'] ?? 0) ?></div>
            <div class="stat-label">Good (70+)</div>
        </div>
        <div class="seo-stat warning">
            <div class="stat-value"><?= (int)($summary['warning'] ?? 0) ?></div>
            <div class="stat-label">Needs Work</div>
        </div>
        <div class="seo-stat critical">
            <div class="stat-value"><?= (int)($summary['critical'] ?? 0) ?></div>
            <div class="stat-label">Critical</div>
        </div>
    </div>

    <!-- Filter -->
    <div class="seo-filter">
        <select id="filter-grade" onchange="filterTable()">
            <option value="">All Grades</option>
            <option value="A">A — Excellent</option>
            <option value="B">B — Good</option>
            <option value="C">C — Needs Work</option>
            <option value="D">D — Poor</option>
            <option value="F">F — Critical</option>
        </select>
        <input type="text" id="filter-search" placeholder="Search products..." oninput="filterTable()">
        <label style="display:flex;align-items:center;gap:6px;font-size:.82rem;color:var(--muted,#94a3b8);cursor:pointer">
            <input type="checkbox" id="select-all" onchange="toggleSelectAll()" style="width:16px;height:16px;accent-color:#6366f1"> Select All
        </label>
    </div>

    <!-- Bulk Toolbar -->
    <div class="bulk-bar" id="bulk-bar">
        <span class="bulk-count"><span id="bulk-count">0</span> selected</span>
        <div class="bulk-actions">
            <button type="button" class="btn-ai btn-sm" onclick="bulkGenerateSeo()">
                <span class="ai-spinner" id="bulk-seo-spinner"></span>
                ✨ Generate SEO Meta
            </button>
            <button type="button" class="btn-ai btn-sm" onclick="bulkRewrite()" style="background:linear-gradient(135deg,#059669 0%,#10b981 100%)">
                <span class="ai-spinner" id="bulk-rewrite-spinner"></span>
                📝 Rewrite Descriptions
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <?php if (empty($products)): ?>
        <div style="text-align:center;padding:60px 20px;color:var(--muted,#94a3b8)">
            <p style="font-size:1.2rem">No active products found.</p>
            <a href="/admin/shop/products/create" class="btn-ai" style="margin-top:12px">➕ Create Product</a>
        </div>
    <?php else: ?>
        <table class="seo-table">
            <thead>
                <tr>
                    <th style="width:36px"><input type="checkbox" id="th-select-all" onchange="toggleSelectAll()" style="width:16px;height:16px;accent-color:#6366f1"></th>
                    <th>Product</th>
                    <th>Category</th>
                    <th style="width:80px">Score</th>
                    <th style="width:44px">Grade</th>
                    <th style="width:36px" title="Meta Title">MT</th>
                    <th style="width:36px" title="Meta Description">MD</th>
                    <th style="width:36px" title="Description">Desc</th>
                    <th style="width:36px" title="Image">Img</th>
                    <th>Issues</th>
                    <th style="width:80px">Actions</th>
                </tr>
            </thead>
            <tbody id="seo-tbody">
                <?php foreach ($products as $p): ?>
                <tr data-id="<?= (int)$p['id'] ?>" data-grade="<?= h($p['grade']) ?>" data-name="<?= h(strtolower($p['name'])) ?>">
                    <td><input type="checkbox" class="product-cb" value="<?= (int)$p['id'] ?>" onchange="updateBulkBar()" style="width:16px;height:16px;accent-color:#6366f1"></td>
                    <td class="product-name"><a href="/admin/shop/products/<?= (int)$p['id'] ?>/edit"><?= h($p['name']) ?></a></td>
                    <td><?php if ($p['category']): ?><span class="category-tag"><?= h($p['category']) ?></span><?php endif; ?></td>
                    <td>
                        <div class="score-bar score-<?= h($p['grade']) ?>">
                            <div class="score-track"><div class="score-fill" style="width:<?= (int)$p['score'] ?>%"></div></div>
                            <span class="score-num"><?= (int)$p['score'] ?></span>
                        </div>
                    </td>
                    <td><span class="grade grade-<?= h($p['grade']) ?>"><?= h($p['grade']) ?></span></td>
                    <td><span class="check-icon <?= $p['has_meta_title'] ? 'check-yes' : 'check-no' ?>"><?= $p['has_meta_title'] ? '✓' : '✗' ?></span></td>
                    <td><span class="check-icon <?= $p['has_meta_desc'] ? 'check-yes' : 'check-no' ?>"><?= $p['has_meta_desc'] ? '✓' : '✗' ?></span></td>
                    <td><span class="check-icon <?= $p['has_description'] ? 'check-yes' : 'check-no' ?>"><?= $p['has_description'] ? '✓' : '✗' ?></span></td>
                    <td><span class="check-icon <?= $p['has_image'] ? 'check-yes' : 'check-no' ?>"><?= $p['has_image'] ? '✓' : '✗' ?></span></td>
                    <td>
                        <div class="issues-list">
                            <?php foreach (array_slice($p['issues'], 0, 2) as $issue): ?>
                                <span class="issue-tag <?= $p['score'] < 40 ? 'critical' : '' ?>"><?= h($issue) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($p['issues']) > 2): ?>
                                <span class="issue-tag">+<?= count($p['issues']) - 2 ?> more</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn-ai btn-sm" onclick="deepAnalyze(<?= (int)$p['id'] ?>)" title="Deep AI SEO Analysis" style="padding:4px 10px;font-size:.72rem">🔬 Analyze</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Deep Analysis Modal -->
<div class="seo-modal-overlay" id="analysis-modal">
    <div class="seo-modal" style="position:relative">
        <button class="close-btn" onclick="closeModal()">&times;</button>
        <h2>🔬 <span id="modal-title">SEO Analysis</span></h2>
        <div id="modal-body" style="color:var(--text,#e2e8f0);font-size:.85rem;line-height:1.6">
            <div style="text-align:center;padding:40px;color:var(--muted,#94a3b8)">
                <div class="ai-spinner" style="display:inline-block;width:24px;height:24px;margin-bottom:12px"></div>
                <p>Running AI SEO analysis...</p>
            </div>
        </div>
    </div>
</div>

<script>
function getCsrf(){var e=document.querySelector('input[name="csrf_token"]');return e?e.value:'';}

function filterTable(){
    var grade=document.getElementById('filter-grade').value;
    var search=document.getElementById('filter-search').value.toLowerCase();
    document.querySelectorAll('#seo-tbody tr').forEach(function(tr){
        var matchGrade=!grade||tr.dataset.grade===grade;
        var matchSearch=!search||tr.dataset.name.indexOf(search)!==-1;
        tr.style.display=(matchGrade&&matchSearch)?'':'none';
    });
}

function getSelectedIds(){
    return Array.from(document.querySelectorAll('.product-cb:checked')).map(function(cb){return parseInt(cb.value);});
}

function updateBulkBar(){
    var ids=getSelectedIds();
    var bar=document.getElementById('bulk-bar');
    var count=document.getElementById('bulk-count');
    if(ids.length>0){
        bar.classList.add('show');
        count.textContent=ids.length;
    } else {
        bar.classList.remove('show');
    }
}

function toggleSelectAll(){
    var checked=event.target.checked;
    document.querySelectorAll('.product-cb').forEach(function(cb){
        var tr=cb.closest('tr');
        if(tr.style.display!=='none') cb.checked=checked;
    });
    // Sync both select-all checkboxes
    document.getElementById('select-all').checked=checked;
    document.getElementById('th-select-all').checked=checked;
    updateBulkBar();
}

function apiCall(url,payload,callback){
    fetch(url,{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':getCsrf()},
        body:JSON.stringify(payload),
        credentials:'same-origin'
    })
    .then(function(r){return r.json();})
    .then(function(d){callback(null,d);})
    .catch(function(e){callback(e.message||'Network error',null);});
}

function refreshScan(){
    var spinner=document.getElementById('refresh-spinner');
    spinner.style.display='inline-block';
    document.getElementById('btn-refresh').disabled=true;
    location.reload();
}

function bulkGenerateSeo(){
    var ids=getSelectedIds();
    if(!ids.length){alert('Select products first');return;}
    if(!confirm('Generate AI SEO meta for '+ids.length+' products? This will call AI for each product.')){return;}

    var spinner=document.getElementById('bulk-seo-spinner');
    spinner.style.display='inline-block';

    apiCall('/api/shop/ai/bulk-generate-seo',{product_ids:ids,overwrite:false},function(err,data){
        spinner.style.display='none';
        if(err||!data||!data.ok){
            alert('Error: '+(data&&data.error?data.error:err||'Failed'));
            return;
        }
        alert('Done! Generated: '+data.generated+', Skipped: '+data.skipped+', Failed: '+data.failed);
        location.reload();
    });
}

function bulkRewrite(){
    var ids=getSelectedIds();
    if(!ids.length){alert('Select products first');return;}
    var mode=prompt('Rewrite mode:\nparaphrase, summarize, expand, simplify, formalize, casual, seo, kids','seo');
    if(!mode)return;
    if(!confirm('Rewrite descriptions for '+ids.length+' products in "'+mode+'" mode? This will NOT auto-save (preview only).')){return;}

    var spinner=document.getElementById('bulk-rewrite-spinner');
    spinner.style.display='inline-block';

    apiCall('/api/shop/ai/bulk-rewrite',{product_ids:ids,mode:mode,apply:false},function(err,data){
        spinner.style.display='none';
        if(err||!data||!data.ok){
            alert('Error: '+(data&&data.error?data.error:err||'Failed'));
            return;
        }
        var msg='Rewrite complete!\n\nRewritten: '+data.rewritten+', Failed: '+data.failed;
        if(data.results&&data.results.length>0){
            msg+='\n\nPreviews:';
            data.results.forEach(function(r){
                if(r.status==='ok') msg+='\n• #'+r.id+': '+r.preview.substring(0,80)+'...';
            });
        }
        alert(msg);
    });
}

function deepAnalyze(productId){
    var modal=document.getElementById('analysis-modal');
    var body=document.getElementById('modal-body');
    var title=document.getElementById('modal-title');
    modal.classList.add('show');
    title.textContent='SEO Analysis — Loading...';
    body.innerHTML='<div style="text-align:center;padding:40px;color:var(--muted,#94a3b8)"><div class="ai-spinner" style="display:inline-block;width:24px;height:24px;margin-bottom:12px"></div><p>Running deep AI SEO analysis... This may take 15-30 seconds.</p></div>';

    apiCall('/api/shop/ai/seo-analyze',{product_id:productId},function(err,data){
        if(err||!data||!data.ok){
            title.textContent='SEO Analysis — Error';
            body.innerHTML='<div style="color:#fca5a5;padding:20px">'+(data&&data.error?data.error:err||'Analysis failed')+'</div>';
            return;
        }
        var d=data.data||data;
        title.textContent='SEO Analysis — Score: '+(d.health_score||'?')+'/100';
        var html='';

        // Health score
        var score=d.health_score||0;
        var color=score>=70?'#10b981':score>=40?'#f59e0b':'#ef4444';
        html+='<div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;padding:16px;background:rgba(99,102,241,.08);border-radius:10px">';
        html+='<div style="font-size:2.5rem;font-weight:800;color:'+color+'">'+score+'</div>';
        html+='<div><div style="font-weight:600;margin-bottom:4px">'+('Summary' )+'</div><div style="color:var(--muted,#94a3b8);font-size:.82rem">'+(d.summary||'')+'</div></div>';
        html+='</div>';

        // Quick wins
        if(d.quick_wins&&d.quick_wins.length){
            html+='<h3 style="font-size:.9rem;color:#a5b4fc;margin:16px 0 8px">⚡ Quick Wins</h3><ul style="margin:0;padding-left:20px">';
            d.quick_wins.forEach(function(w){html+='<li style="margin-bottom:4px">'+esc(w)+'</li>';});
            html+='</ul>';
        }

        // On-page checks
        if(d.on_page_checks){
            var opc=d.on_page_checks;
            html+='<h3 style="font-size:.9rem;color:#a5b4fc;margin:16px 0 8px">📋 On-Page Checks</h3>';
            if(opc.meta_suggestions){
                html+='<div style="background:rgba(16,185,129,.08);border-radius:8px;padding:12px;margin-bottom:8px">';
                html+='<div style="font-weight:600;font-size:.8rem;margin-bottom:6px">Recommended Meta</div>';
                html+='<div style="font-size:.8rem"><strong>Title:</strong> '+esc(opc.meta_suggestions.recommended_title||'')+'</div>';
                html+='<div style="font-size:.8rem"><strong>Description:</strong> '+esc(opc.meta_suggestions.recommended_meta_description||'')+'</div>';
                html+='</div>';
            }
            if(opc.keyword_usage){
                html+='<div style="font-size:.82rem;margin-bottom:6px">📊 '+esc(opc.keyword_usage.density_comment||'')+'</div>';
            }
        }

        // Content ideas
        if(d.content_ideas&&d.content_ideas.length){
            html+='<h3 style="font-size:.9rem;color:#a5b4fc;margin:16px 0 8px">💡 Content Ideas</h3><ul style="margin:0;padding-left:20px">';
            d.content_ideas.forEach(function(i){html+='<li style="margin-bottom:4px">'+esc(i)+'</li>';});
            html+='</ul>';
        }

        // Actionable tasks
        if(d.actionable_tasks&&d.actionable_tasks.length){
            html+='<h3 style="font-size:.9rem;color:#a5b4fc;margin:16px 0 8px">✅ Actionable Tasks</h3>';
            d.actionable_tasks.forEach(function(t){
                var pColor=t.priority==='critical'?'#ef4444':t.priority==='high'?'#f59e0b':'#94a3b8';
                html+='<div style="background:rgba(255,255,255,.03);border-left:3px solid '+pColor+';padding:8px 12px;margin-bottom:6px;border-radius:0 6px 6px 0">';
                html+='<div style="font-weight:600;font-size:.82rem">'+esc(t.task||'')+'</div>';
                html+='<div style="font-size:.75rem;color:var(--muted,#94a3b8);margin-top:2px">'+esc(t.details||'')+'</div>';
                html+='</div>';
            });
        }

        // NLP terms
        if(d.nlp_terms&&d.nlp_terms.length){
            html+='<h3 style="font-size:.9rem;color:#a5b4fc;margin:16px 0 8px">🧠 NLP Terms</h3>';
            html+='<div style="display:flex;flex-wrap:wrap;gap:6px">';
            d.nlp_terms.forEach(function(t){
                var bg=t.found_in_content?'rgba(16,185,129,.15)':'rgba(245,158,11,.15)';
                var clr=t.found_in_content?'#34d399':'#fbbf24';
                html+='<span style="background:'+bg+';color:'+clr+';padding:3px 10px;border-radius:4px;font-size:.75rem" title="Importance: '+(t.importance||'')+', Found: '+(t.found_in_content?'Yes':'No')+'">'+esc(t.term||'')+'</span>';
            });
            html+='</div>';
        }

        body.innerHTML=html;
    });
}

function closeModal(){
    document.getElementById('analysis-modal').classList.remove('show');
}

document.getElementById('analysis-modal').addEventListener('click',function(e){
    if(e.target===this) closeModal();
});

function esc(s){
    if(!s)return '';
    var d=document.createElement('div');
    d.textContent=s;
    return d.innerHTML;
}
</script>

<?php
$content = ob_get_clean();
$title = 'Product SEO Dashboard';
require CMS_APP . '/views/admin/layouts/topbar.php';
