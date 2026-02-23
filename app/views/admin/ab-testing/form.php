<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$t = $test;
$pageTitle = ($t ? 'Edit' : 'New') . ' A/B Test';
ob_start();
$v = fn($k, $d='') => h($t[$k] ?? $d);
?>
<style>
.abf-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:24px;margin-bottom:20px}
.abf-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin-bottom:16px}
.abf-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.abf-field{margin-bottom:0}
.abf-field.full{grid-column:1/-1}
.abf-field label{display:block;font-size:.8rem;color:var(--text,#e2e8f0);margin-bottom:4px;font-weight:500}
.abf-field small{display:block;font-size:.7rem;color:var(--muted,#94a3b8);margin-top:2px}
.abf-field input,.abf-field select,.abf-field textarea{width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border,#334155);background:var(--bg,#0f172a);color:var(--text,#e2e8f0);font-size:.9rem;font-family:inherit;box-sizing:border-box}
.abf-field textarea{min-height:80px;resize:vertical}
.abf-variant{padding:16px;border-radius:8px;border:2px solid}
.abf-variant.a{border-color:#6366f1;background:#6366f108}
.abf-variant.b{border-color:#f59e0b;background:#f59e0b08}
.abf-variant h4{font-size:.9rem;font-weight:600;margin-bottom:8px}
.abf-btn{padding:12px 24px;border-radius:8px;background:var(--primary,#6366f1);color:#fff;border:none;cursor:pointer;font-size:.95rem;font-weight:600}
</style>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <a href="/admin/ab-testing" style="color:var(--muted);text-decoration:none;font-size:1.2rem">←</a>
    <h1 style="font-size:1.5rem;font-weight:700"><?= $t ? 'Edit Test' : 'New A/B Test' ?></h1>
</div>

<form method="post" action="<?= $t ? '/admin/ab-testing/' . $t['id'] . '/update' : '/admin/ab-testing/store' ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="abf-card">
        <h3>Test Setup</h3>
        <div class="abf-grid">
            <div class="abf-field full"><label>Test Name *</label><input type="text" name="name" value="<?= $v('name') ?>" required placeholder="e.g. Homepage hero headline test"></div>
            <div class="abf-field">
                <label>Test Type</label>
                <select name="type">
                    <?php foreach (['headline'=>'Headline','cta'=>'Call to Action','image'=>'Image','layout'=>'Layout','custom'=>'Custom HTML'] as $k=>$l): ?>
                        <option value="<?= $k ?>" <?= ($t['type'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="abf-field">
                <label>Page (optional)</label>
                <select name="page_id">
                    <option value="">All pages</option>
                    <?php foreach ($pages as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($t['page_id'] ?? 0) == $p['id'] ? 'selected' : '' ?>><?= h($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Leave empty to run on all pages</small>
            </div>
            <div class="abf-field full">
                <label>CSS Selector *</label>
                <input type="text" name="selector" value="<?= $v('selector') ?>" required placeholder="e.g. .hero h1, #main-cta, .pricing-section">
                <small>The element to modify. Use browser DevTools to find the right selector.</small>
            </div>
        </div>
    </div>

    <div class="abf-card">
        <h3>Variants</h3>
        <div class="abf-grid">
            <div class="abf-variant a">
                <h4 style="color:#6366f1">🅰️ Variant A (Control)</h4>
                <div class="abf-field"><label>Content</label>
                    <textarea name="variant_a" required placeholder="Original content or HTML"><?= $v('variant_a') ?></textarea>
                    <small>The original/control version</small>
                </div>
            </div>
            <div class="abf-variant b">
                <h4 style="color:#f59e0b">🅱️ Variant B (Challenger)</h4>
                <div class="abf-field"><label>Content</label>
                    <textarea name="variant_b" required placeholder="Alternative content or HTML"><?= $v('variant_b') ?></textarea>
                    <small>The version you want to test against A</small>
                </div>
            </div>
        </div>
    </div>

    <div class="abf-card">
        <h3>Conversion Goal</h3>
        <div class="abf-grid">
            <div class="abf-field">
                <label>Goal Type</label>
                <select name="goal">
                    <?php foreach (['click'=>'Click on element','form_submit'=>'Form submission','scroll'=>'Scroll to element','time_on_page'=>'Time on page (30s+)'] as $k=>$l): ?>
                        <option value="<?= $k ?>" <?= ($t['goal'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="abf-field">
                <label>Goal Selector</label>
                <input type="text" name="goal_selector" value="<?= $v('goal_selector') ?>" placeholder="e.g. .cta-button, #signup-form">
                <small>Element to track for click/scroll goals</small>
            </div>
        </div>
    </div>

    <button type="submit" class="abf-btn">💾 <?= $t ? 'Update Test' : 'Create & Start Test' ?></button>
</form>

<?php $content = ob_get_clean(); require CMS_APP . '/views/admin/layouts/topbar.php';
