<?php
/**
 * Social Sharing Buttons
 * Required: $shareTitle (string), $shareUrl (string)
 * Optional: $shareDescription (string)
 */
$shareTitle = urlencode($shareTitle ?? '');
$shareUrl = urlencode($shareUrl ?? ('https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/')));
$shareDescription = urlencode($shareDescription ?? '');
?>
<div class="share-buttons" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
    <span style="font-weight:500;font-size:0.85rem;color:var(--text-secondary,#64748b);">Share:</span>
    <a href="https://twitter.com/intent/tweet?text=<?= $shareTitle ?>&url=<?= $shareUrl ?>" target="_blank" rel="noopener"
       style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#1DA1F2;color:#fff;border-radius:6px;font-size:0.8rem;text-decoration:none;">
        𝕏 Twitter
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener"
       style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#1877F2;color:#fff;border-radius:6px;font-size:0.8rem;text-decoration:none;">
        f Facebook
    </a>
    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= $shareUrl ?>&title=<?= $shareTitle ?>" target="_blank" rel="noopener"
       style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#0A66C2;color:#fff;border-radius:6px;font-size:0.8rem;text-decoration:none;">
        in LinkedIn
    </a>
    <a href="mailto:?subject=<?= $shareTitle ?>&body=<?= $shareDescription ?>%0A%0A<?= $shareUrl ?>"
       style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#64748b;color:#fff;border-radius:6px;font-size:0.8rem;text-decoration:none;">
        ✉ Email
    </a>
</div>
