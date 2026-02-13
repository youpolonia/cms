<?php $title = 'Languages'; ob_start(); ?>
<style>
.lang-page{max-width:900px;margin:0 auto;padding:2rem}
.lang-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem}
.lang-header h1{font-size:1.5rem;font-weight:700;color:var(--text-primary)}
.lang-desc{color:var(--text-muted);font-size:.875rem;margin-bottom:2rem;line-height:1.6}

.lang-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;margin-bottom:2rem}
.lang-card{background:var(--bg-secondary);border:1px solid var(--border);border-radius:10px;padding:1rem;transition:border-color .15s}
.lang-card:hover{border-color:var(--accent)}
.lang-card.is-default{border-color:var(--accent);background:rgba(99,102,241,.05)}
.lang-card-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem}
.lang-card-name{font-weight:600;font-size:.95rem;color:var(--text-primary)}
.lang-card-code{font-family:monospace;font-size:.75rem;color:var(--text-muted);background:var(--bg-tertiary);padding:2px 6px;border-radius:4px}
.lang-card-native{font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem}
.lang-card-stats{display:flex;gap:1rem;font-size:.7rem;color:var(--text-muted);margin-bottom:.75rem}
.lang-card-actions{display:flex;gap:.5rem;flex-wrap:wrap}

.badge-default{background:rgba(99,102,241,.15);color:#818cf8;padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600}
.badge-on{background:rgba(16,185,129,.15);color:#10b981;padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600}
.badge-off{background:rgba(239,68,68,.15);color:#ef4444;padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600}
.badge-rtl{background:rgba(245,158,11,.15);color:#f59e0b;padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600}

.btn-xs{padding:4px 10px;font-size:.7rem;border-radius:5px;border:none;cursor:pointer;font-weight:500;transition:filter .15s}
.btn-xs.primary{background:var(--accent);color:white}
.btn-xs.primary:hover{filter:brightness(1.1)}
.btn-xs.secondary{background:var(--bg-tertiary);color:var(--text-secondary)}
.btn-xs.secondary:hover{background:var(--border)}
.btn-xs.danger{background:rgba(239,68,68,.15);color:#ef4444}

.add-form{background:var(--bg-secondary);border:1px solid var(--border);border-radius:10px;padding:1.25rem;margin-bottom:2rem;display:none}
.add-form.open{display:block}
.add-form .row{display:flex;gap:.75rem;flex-wrap:wrap;align-items:end}
.add-form label{display:block;font-size:.75rem;font-weight:500;color:var(--text-secondary);margin-bottom:.25rem}
.add-form input,.add-form select{padding:6px 10px;border:1px solid var(--border);border-radius:6px;background:var(--bg-primary);color:var(--text-primary);font-size:.8rem}
.add-form input:focus,.add-form select:focus{outline:none;border-color:var(--accent)}
</style>

<div class="lang-page">
    <div class="lang-header">
        <h1>🌐 Languages</h1>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('add-lang').classList.toggle('open')">+ Add Language</button>
    </div>

    <p class="lang-desc">
        Manage languages for your website. The <strong>default language</strong> is used for the original content.
        Active languages appear in the language switcher on the frontend.
        Translations can be file-based (<code>/lang/CODE/</code>) or stored in the database.
    </p>

    <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success" style="margin-bottom:1rem"><?= esc($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><?= esc($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); endif; ?>

    <form id="add-lang" class="add-form" method="POST" action="/admin/languages/add">
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
        <div class="row">
            <div><label>Code</label><input name="code" placeholder="e.g. pt" maxlength="10" required style="width:60px"></div>
            <div><label>Name</label><input name="name" placeholder="e.g. Portuguese" required style="width:140px"></div>
            <div><label>Native Name</label><input name="native_name" placeholder="e.g. Português" style="width:140px"></div>
            <div><label>Direction</label><select name="direction"><option value="ltr">LTR ←→</option><option value="rtl">RTL →←</option></select></div>
            <button type="submit" class="btn btn-primary btn-sm">Add</button>
        </div>
    </form>

    <div class="lang-grid">
    <?php foreach ($languages as $lang): ?>
        <div class="lang-card <?= $lang['is_default'] ? 'is-default' : '' ?>">
            <div class="lang-card-top">
                <span class="lang-card-name"><?= esc($lang['name']) ?></span>
                <span class="lang-card-code"><?= esc($lang['code']) ?></span>
            </div>
            <div class="lang-card-native">
                <?= esc($lang['native_name']) ?>
                <?php if ($lang['is_default']): ?><span class="badge-default">Default</span><?php endif; ?>
                <?php if ($lang['direction'] === 'rtl'): ?><span class="badge-rtl">RTL</span><?php endif; ?>
                <span class="<?= $lang['is_active'] ? 'badge-on' : 'badge-off' ?>"><?= $lang['is_active'] ? 'Active' : 'Inactive' ?></span>
            </div>
            <div class="lang-card-stats">
                <span>📝 <?= $translationCounts[$lang['code']] ?? 0 ?> translations</span>
                <span>📄 <?= $contentCounts[$lang['code']] ?? 0 ?> content</span>
                <?php if (is_dir(\CMS_ROOT . '/lang/' . $lang['code'])): ?>
                <span>📁 File-based</span>
                <?php endif; ?>
            </div>
            <div class="lang-card-actions">
                <?php if (!$lang['is_default']): ?>
                <form method="POST" action="/admin/languages/default/<?= $lang['id'] ?>" style="display:inline">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <button type="submit" class="btn-xs primary" onclick="return confirm('Set <?= esc($lang['name']) ?> as default language?')">⭐ Set Default</button>
                </form>
                <?php endif; ?>
                <form method="POST" action="/admin/languages/toggle/<?= $lang['id'] ?>" style="display:inline">
                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                    <button type="submit" class="btn-xs secondary"><?= $lang['is_active'] ? '⏸ Disable' : '▶ Enable' ?></button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
?>
