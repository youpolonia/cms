<?php
/**
 * Newsletter Subscribe Widget
 * Include in footer, sidebar, or any template
 */
$currentUrl = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
?>
<div class="newsletter-widget" style="background:var(--surface-alt,#f1f5f9);border-radius:12px;padding:24px;text-align:center;">
    <?php if ($nsSuccess = \Core\Session::getFlash('newsletter_success')): ?>
        <div style="background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:6px;margin-bottom:12px;font-size:0.9rem;"><?= h($nsSuccess) ?></div>
    <?php endif; ?>
    <?php if ($nsError = \Core\Session::getFlash('newsletter_error')): ?>
        <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:12px;font-size:0.9rem;"><?= h($nsError) ?></div>
    <?php endif; ?>
    <h4 style="margin:0 0 8px;font-size:1.1rem;">📧 Stay in the loop</h4>
    <p style="color:var(--text-secondary,#64748b);font-size:0.9rem;margin:0 0 16px;">Get the latest updates delivered to your inbox.</p>
    <form method="post" action="/newsletter/subscribe" style="display:flex;gap:8px;max-width:400px;margin:0 auto;">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= h($currentUrl) ?>">
        <div style="position:absolute;left:-9999px;opacity:0;" aria-hidden="true">
            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
        </div>
        <input type="email" name="email" required placeholder="you@example.com"
               style="flex:1;padding:10px 12px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:0.9rem;">
        <button type="submit" style="padding:10px 20px;background:var(--accent,#6366f1);color:#fff;border:none;border-radius:6px;font-size:0.9rem;font-weight:500;cursor:pointer;white-space:nowrap;">
            Subscribe
        </button>
    </form>
</div>
