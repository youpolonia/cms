<?php
/**
 * Comments Section — include in article/page templates
 * Required vars: $articleId (int) OR $pageId (int)
 * Optional: $comments (array of approved comments)
 */
$articleId = $articleId ?? 0;
$pageId = $pageId ?? 0;
$targetField = $articleId > 0 ? 'article_id' : 'page_id';
$targetValue = $articleId > 0 ? $articleId : $pageId;
$currentUrl = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

// Load approved comments
if (!isset($comments)) {
    $comments = [];
    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, author_name, content, parent_id, created_at FROM comments WHERE {$targetField} = ? AND status = 'approved' ORDER BY created_at ASC");
        $stmt->execute([$targetValue]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
$commentCount = count($comments);
?>
<section id="comments" style="margin-top:48px;padding-top:32px;border-top:1px solid var(--border,#e2e8f0);">
    <h3 style="font-size:1.3rem;margin:0 0 24px;">
        💬 Comments<?= $commentCount > 0 ? " ({$commentCount})" : '' ?>
    </h3>

    <?php if ($commentSuccess = \Core\Session::getFlash('comment_success')): ?>
        <div style="background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($commentSuccess) ?></div>
    <?php endif; ?>
    <?php if ($commentError = \Core\Session::getFlash('comment_error')): ?>
        <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;"><?= h($commentError) ?></div>
    <?php endif; ?>

    <?php if ($commentCount > 0): ?>
        <div class="comments-list" style="margin-bottom:32px;">
            <?php foreach ($comments as $comment): ?>
                <div class="comment" id="comment-<?= (int)$comment['id'] ?>" style="padding:16px;margin-bottom:12px;background:var(--surface-alt,#f8fafc);border-radius:8px;border:1px solid var(--border,#e2e8f0);">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <strong style="font-size:0.95rem;"><?= h($comment['author_name']) ?></strong>
                        <span style="color:var(--text-secondary,#94a3b8);font-size:0.8rem;">
                            <?= date('M j, Y \a\t g:i A', strtotime($comment['created_at'])) ?>
                        </span>
                    </div>
                    <div style="font-size:0.9rem;line-height:1.6;color:var(--text,#334155);">
                        <?= nl2br(h($comment['content'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--text-secondary,#64748b);margin-bottom:24px;font-size:0.9rem;">No comments yet. Be the first!</p>
    <?php endif; ?>

    <!-- Comment Form -->
    <div style="background:var(--surface,#fff);border:1px solid var(--border,#e2e8f0);border-radius:12px;padding:24px;">
        <h4 style="margin:0 0 16px;font-size:1.1rem;">Leave a Comment</h4>
        <form method="post" action="/comment">
            <?= csrf_field() ?>
            <input type="hidden" name="<?= $targetField ?>" value="<?= $targetValue ?>">
            <input type="hidden" name="redirect" value="<?= h($currentUrl) ?>">
            <!-- Honeypot -->
            <div style="position:absolute;left:-9999px;opacity:0;height:0;overflow:hidden;" aria-hidden="true">
                <input type="text" name="website_url" tabindex="-1" autocomplete="off">
            </div>
            <?php if (!\Core\Session::isUserLoggedIn()): ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                        <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.85rem;">Name *</label>
                        <input type="text" name="author_name" required maxlength="100"
                               style="width:100%;padding:8px 10px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:0.9rem;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.85rem;">Email *</label>
                        <input type="email" name="author_email" required maxlength="255"
                               style="width:100%;padding:8px 10px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:0.9rem;">
                    </div>
                </div>
            <?php endif; ?>
            <div style="margin-bottom:12px;">
                <label style="display:block;margin-bottom:4px;font-weight:500;font-size:0.85rem;">Comment *</label>
                <textarea name="content" required rows="4" maxlength="5000"
                          style="width:100%;padding:8px 10px;border:1px solid var(--border,#d1d5db);border-radius:6px;font-size:0.9rem;resize:vertical;font-family:inherit;"></textarea>
            </div>
            <button type="submit" style="padding:10px 24px;background:var(--accent,#6366f1);color:#fff;border:none;border-radius:6px;font-size:0.9rem;font-weight:500;cursor:pointer;">
                Post Comment
            </button>
        </form>
    </div>
</section>
