<?php
$username = \Core\Session::getAdminUsername() ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($currentDoc['title'] ?? 'Documentation') ?> — Jessie AI-CMS Docs</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/fontawesome.min.css">
<style>
:root {
    --bg:#1e1e2e;--mantle:#181825;--crust:#11111b;
    --s0:#313244;--s1:#45475a;--s2:#585b70;
    --o0:#6c7086;--o1:#7f849c;
    --text:#cdd6f4;--sub0:#a6adc8;--sub1:#bac2de;
    --blue:#89b4fa;--green:#a6e3a1;--mauve:#cba6f7;
    --red:#f38ba8;--peach:#fab387;--yellow:#f9e2af;
    --teal:#94e2d5;--lav:#b4befe;--sky:#89dceb;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column}

/* Topbar */
.doc-topbar{display:flex;align-items:center;justify-content:space-between;padding:0 24px;height:52px;background:var(--mantle);border-bottom:1px solid var(--s0);flex-shrink:0}
.doc-topbar-left{display:flex;align-items:center;gap:16px}
.doc-topbar-left a{color:var(--sub0);text-decoration:none;font-size:13px;display:flex;align-items:center;gap:6px;transition:color .2s}
.doc-topbar-left a:hover{color:var(--text)}
.doc-topbar-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px}

/* Layout */
.doc-layout{display:flex;flex:1;min-height:0}

/* Sidebar */
.doc-sidebar{width:280px;min-width:280px;background:var(--mantle);border-right:1px solid var(--s0);display:flex;flex-direction:column;overflow-y:auto}
.doc-sidebar::-webkit-scrollbar{width:5px}
.doc-sidebar::-webkit-scrollbar-thumb{background:var(--s1);border-radius:3px}
.doc-search{padding:16px;position:sticky;top:0;background:var(--mantle);z-index:2}
.doc-search input{width:100%;padding:9px 12px 9px 34px;background:var(--s0);border:1px solid var(--s1);border-radius:8px;color:var(--text);font-family:inherit;font-size:13px;transition:border-color .2s}
.doc-search input:focus{outline:none;border-color:var(--blue)}
.doc-search input::placeholder{color:var(--o0)}
.doc-search-icon{position:absolute;left:28px;top:50%;transform:translateY(-50%);color:var(--o0);font-size:13px;pointer-events:none}
.doc-search-wrap{position:relative}
.doc-search-results{position:absolute;top:100%;left:0;right:0;background:var(--s0);border:1px solid var(--s1);border-radius:8px;margin-top:4px;max-height:300px;overflow-y:auto;display:none;z-index:10;box-shadow:0 8px 24px rgba(0,0,0,.4)}
.doc-search-results.visible{display:block}
.doc-sr-item{padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--s1);transition:background .15s}
.doc-sr-item:hover{background:var(--bg)}
.doc-sr-item:last-child{border-bottom:none}
.doc-sr-title{font-size:13px;font-weight:600;color:var(--blue)}
.doc-sr-cat{font-size:10px;color:var(--o0);text-transform:uppercase;letter-spacing:.04em}
.doc-sr-snippet{font-size:11px;color:var(--sub0);margin-top:2px;line-height:1.4}

/* Nav */
.doc-nav{padding:0 12px 20px}
.doc-nav-cat{margin-bottom:8px}
.doc-nav-cat-head{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--o0);padding:8px 8px 4px;display:flex;align-items:center;gap:6px}
.doc-nav-item{display:block;padding:7px 12px;font-size:13px;color:var(--sub0);text-decoration:none;border-radius:6px;transition:all .15s;margin-bottom:1px}
.doc-nav-item:hover{background:var(--s0);color:var(--text)}
.doc-nav-item.active{background:rgba(137,180,250,.1);color:var(--blue);font-weight:500}

/* Content */
.doc-content{flex:1;overflow-y:auto;padding:40px 60px;max-width:900px}
.doc-content::-webkit-scrollbar{width:6px}
.doc-content::-webkit-scrollbar-thumb{background:var(--s1);border-radius:3px}
.doc-breadcrumb{font-size:12px;color:var(--o0);margin-bottom:24px;display:flex;align-items:center;gap:6px}
.doc-breadcrumb a{color:var(--blue);text-decoration:none}
.doc-breadcrumb a:hover{text-decoration:underline}

/* Content styling */
.doc-body h2{font-size:26px;font-weight:700;margin-bottom:16px;color:var(--text);border-bottom:1px solid var(--s0);padding-bottom:12px}
.doc-body h3{font-size:17px;font-weight:600;margin:28px 0 10px;color:var(--sub1)}
.doc-body p{font-size:14px;line-height:1.7;margin-bottom:14px;color:var(--sub0)}
.doc-body a{color:var(--blue);text-decoration:none}
.doc-body a:hover{text-decoration:underline}
.doc-body ul,.doc-body ol{margin:0 0 16px 24px;font-size:14px;line-height:1.8;color:var(--sub0)}
.doc-body li{margin-bottom:4px}
.doc-body strong{color:var(--text);font-weight:600}
.doc-body code{background:var(--s0);padding:2px 6px;border-radius:4px;font-size:12px;color:var(--peach);font-family:'JetBrains Mono',monospace}

/* Table */
.doc-table{width:100%;border-collapse:collapse;margin:12px 0 20px;font-size:13px}
.doc-table th{background:var(--s0);padding:10px 14px;text-align:left;font-weight:600;color:var(--text);border:1px solid var(--s1)}
.doc-table td{padding:9px 14px;border:1px solid var(--s0);color:var(--sub0)}
.doc-table tr:hover td{background:rgba(137,180,250,.03)}

/* Features grid */
.doc-features{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:16px 0 24px}
.doc-feature{display:flex;gap:14px;padding:16px;background:var(--s0);border-radius:10px;border:1px solid var(--s1)}
.doc-feature-icon{font-size:28px;flex-shrink:0;line-height:1}
.doc-feature strong{display:block;font-size:14px;color:var(--text);margin-bottom:4px}
.doc-feature p{font-size:12px;line-height:1.5;color:var(--sub0);margin:0}

/* Nav links at bottom */
.doc-nav-links{display:flex;justify-content:space-between;margin-top:48px;padding-top:20px;border-top:1px solid var(--s0)}
.doc-nav-link{text-decoration:none;color:var(--sub0);font-size:13px;display:flex;align-items:center;gap:6px;padding:10px 16px;border-radius:8px;border:1px solid var(--s0);transition:all .15s}
.doc-nav-link:hover{background:var(--s0);color:var(--text);border-color:var(--s1)}
.doc-nav-link strong{color:var(--blue);font-size:14px}

/* Responsive */
@media(max-width:900px){
    .doc-sidebar{width:240px;min-width:240px}
    .doc-content{padding:24px 28px}
    .doc-features{grid-template-columns:1fr}
}
@media(max-width:640px){
    .doc-layout{flex-direction:column}
    .doc-sidebar{width:100%;min-width:0;max-height:40vh;border-right:none;border-bottom:1px solid var(--s0)}
    .doc-content{max-width:100%}
}
</style>
</head>
<body>

<div class="doc-topbar">
    <div class="doc-topbar-left">
        <a href="/admin"><i class="fas fa-arrow-left"></i> Admin</a>
        <div class="doc-topbar-title">📖 Documentation</div>
    </div>
    <div style="font-size:12px;color:var(--sub0)"><?= esc($username) ?></div>
</div>

<div class="doc-layout">

<!-- Sidebar -->
<div class="doc-sidebar">
    <div class="doc-search">
        <div class="doc-search-wrap">
            <i class="fas fa-search doc-search-icon"></i>
            <input type="text" id="docSearch" placeholder="Search docs..." autocomplete="off">
            <div class="doc-search-results" id="searchResults"></div>
        </div>
    </div>
    <nav class="doc-nav">
        <?php foreach ($docs as $cat): ?>
        <div class="doc-nav-cat">
            <div class="doc-nav-cat-head"><?= $cat['icon'] ?> <?= esc($cat['label']) ?></div>
            <?php foreach ($cat['items'] as $item): ?>
            <a href="/admin/docs?section=<?= esc($item['slug']) ?>"
               class="doc-nav-item<?= $item['slug'] === $section ? ' active' : '' ?>">
                <?= esc($item['title']) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </nav>
</div>

<!-- Content -->
<div class="doc-content">
    <div class="doc-breadcrumb">
        <a href="/admin/docs">Docs</a>
        <span>›</span>
        <span><?= esc($currentCategory['label'] ?? '') ?></span>
        <span>›</span>
        <span><?= esc($currentDoc['title'] ?? '') ?></span>
    </div>

    <div class="doc-body">
        <?= $currentDoc['content'] ?? '' ?>
    </div>

    <?php
    // Find prev/next
    $allItems = [];
    foreach ($docs as $cat) {
        foreach ($cat['items'] as $item) {
            $allItems[] = $item;
        }
    }
    $currentIdx = null;
    foreach ($allItems as $i => $item) {
        if ($item['slug'] === $section) { $currentIdx = $i; break; }
    }
    $prev = $currentIdx !== null && $currentIdx > 0 ? $allItems[$currentIdx - 1] : null;
    $next = $currentIdx !== null && $currentIdx < count($allItems) - 1 ? $allItems[$currentIdx + 1] : null;
    ?>
    <div class="doc-nav-links">
        <?php if ($prev): ?>
        <a href="/admin/docs?section=<?= esc($prev['slug']) ?>" class="doc-nav-link">
            <i class="fas fa-arrow-left"></i>
            <div><span>Previous</span><br><strong><?= esc($prev['title']) ?></strong></div>
        </a>
        <?php else: ?><span></span><?php endif; ?>

        <?php if ($next): ?>
        <a href="/admin/docs?section=<?= esc($next['slug']) ?>" class="doc-nav-link">
            <div style="text-align:right"><span>Next</span><br><strong><?= esc($next['title']) ?></strong></div>
            <i class="fas fa-arrow-right"></i>
        </a>
        <?php endif; ?>
    </div>
</div>

</div>

<script>
(function(){
    const input = document.getElementById('docSearch');
    const results = document.getElementById('searchResults');
    let timer = null;

    input.addEventListener('input', function(){
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { results.classList.remove('visible'); return; }
        timer = setTimeout(() => searchDocs(q), 250);
    });

    input.addEventListener('blur', () => setTimeout(() => results.classList.remove('visible'), 200));
    input.addEventListener('focus', () => { if (results.children.length > 0) results.classList.add('visible'); });

    async function searchDocs(q) {
        try {
            const r = await fetch('/api/docs/search?q=' + encodeURIComponent(q));
            const d = await r.json();
            results.innerHTML = '';
            if (d.results && d.results.length > 0) {
                d.results.forEach(item => {
                    const el = document.createElement('div');
                    el.className = 'doc-sr-item';
                    el.innerHTML = '<div class="doc-sr-cat">' + esc(item.category) + '</div>'
                        + '<div class="doc-sr-title">' + esc(item.title) + '</div>'
                        + (item.snippet ? '<div class="doc-sr-snippet">' + esc(item.snippet) + '</div>' : '');
                    el.addEventListener('click', () => {
                        window.location.href = '/admin/docs?section=' + encodeURIComponent(item.slug);
                    });
                    results.appendChild(el);
                });
                results.classList.add('visible');
            } else {
                results.innerHTML = '<div style="padding:12px 14px;font-size:12px;color:var(--o0)">No results found</div>';
                results.classList.add('visible');
            }
        } catch(e) {}
    }

    function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
})();
</script>
</body>
</html>
