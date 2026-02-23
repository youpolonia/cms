<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = ($contact ? 'Edit' : 'New') . ' Contact';
ob_start();
$c = $contact ?? [];
$val = fn($k, $def = '') => h($c[$k] ?? $def);
?>
<style>
.cf-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:24px;margin-bottom:20px}
.cf-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin-bottom:16px}
.cf-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.cf-field{margin-bottom:0}
.cf-field.full{grid-column:1/-1}
.cf-field label{display:block;font-size:.8rem;color:var(--text,#e2e8f0);margin-bottom:4px;font-weight:500}
.cf-field input,.cf-field select,.cf-field textarea{width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border,#334155);background:var(--bg,#0f172a);color:var(--text,#e2e8f0);font-size:.9rem;font-family:inherit;box-sizing:border-box}
.cf-field textarea{min-height:100px;resize:vertical}
.cf-btn{padding:12px 24px;border-radius:8px;background:var(--primary,#6366f1);color:#fff;border:none;cursor:pointer;font-size:.95rem;font-weight:600}
</style>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <a href="<?= $contact ? '/admin/crm/contacts/' . $c['id'] : '/admin/crm/contacts' ?>" style="color:var(--muted);text-decoration:none;font-size:1.2rem">←</a>
    <h1 style="font-size:1.5rem;font-weight:700"><?= $contact ? 'Edit Contact' : 'New Contact' ?></h1>
</div>

<form method="post" action="<?= $contact ? '/admin/crm/contacts/' . $c['id'] . '/update' : '/admin/crm/contacts/store' ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="cf-card">
        <h3>Personal Info</h3>
        <div class="cf-grid">
            <div class="cf-field"><label>First Name *</label><input type="text" name="first_name" value="<?= $val('first_name') ?>" required></div>
            <div class="cf-field"><label>Last Name</label><input type="text" name="last_name" value="<?= $val('last_name') ?>"></div>
            <div class="cf-field"><label>Email</label><input type="email" name="email" value="<?= $val('email') ?>"></div>
            <div class="cf-field"><label>Phone</label><input type="tel" name="phone" value="<?= $val('phone') ?>"></div>
            <div class="cf-field"><label>Company</label><input type="text" name="company" value="<?= $val('company') ?>"></div>
            <div class="cf-field"><label>Job Title</label><input type="text" name="job_title" value="<?= $val('job_title') ?>"></div>
        </div>
    </div>

    <div class="cf-card">
        <h3>CRM Details</h3>
        <div class="cf-grid">
            <div class="cf-field">
                <label>Status</label>
                <select name="status">
                    <?php foreach (['new','contacted','qualified','proposal','won','lost'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($c['status'] ?? 'new') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="cf-field">
                <label>Lead Score (0-100)</label>
                <input type="number" name="score" min="0" max="100" value="<?= $val('score', '0') ?>">
            </div>
            <div class="cf-field full">
                <label>Tags <small style="color:var(--muted)">(comma-separated)</small></label>
                <input type="text" name="tags" value="<?= $val('tags') ?>" placeholder="e.g. premium, enterprise, follow-up">
            </div>
            <div class="cf-field full">
                <label>Notes</label>
                <textarea name="notes" placeholder="Any relevant notes about this contact..."><?= $val('notes') ?></textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="cf-btn">💾 <?= $contact ? 'Update Contact' : 'Create Contact' ?></button>
</form>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
