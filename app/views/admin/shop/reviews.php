<?php
if (!function_exists('h')) { function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Product Reviews';
ob_start();

$statusFilter = $_GET['status'] ?? '';
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'avg_rating' => 0];
$reviews = $reviews ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;

$starsFull = function(int $rating): string {
    $s = '';
    for ($i = 1; $i <= 5; $i++) {
        $s .= $i <= $rating ? '★' : '☆';
    }
    return $s;
};
?>
<style>
.rv-wrap{max-width:1200px;margin:0 auto;padding:24px 20px}
.rv-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.rv-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#cdd6f4);margin:0}
.rv-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px}
.rv-stat{background:var(--bg-card,#1e1e2e);border:1px solid var(--border,#313244);border-radius:12px;padding:18px}
.rv-stat .num{font-size:1.5rem;font-weight:700;color:var(--text,#cdd6f4)}
.rv-stat .lbl{font-size:.75rem;color:var(--muted,#6c7086);margin-top:4px}
.rv-stat.warn{border-color:#f9e2af}
.rv-stat.ok{border-color:#a6e3a1}

.rv-tabs{display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid var(--border,#313244);padding-bottom:0}
.rv-tabs a{padding:10px 18px;text-decoration:none;font-size:.85rem;font-weight:600;color:var(--muted,#6c7086);border-bottom:2px solid transparent;transition:all .2s}
.rv-tabs a:hover{color:var(--text,#cdd6f4)}
.rv-tabs a.active{color:#cba6f7;border-bottom-color:#cba6f7}

.rv-card{background:var(--bg-card,#1e1e2e);border:1px solid var(--border,#313244);border-radius:12px;overflow:hidden}
.rv-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.rv-tbl th,.rv-tbl td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border,#313244)}
.rv-tbl th{color:var(--muted,#6c7086);font-weight:600;font-size:.75rem;text-transform:uppercase;background:rgba(49,50,68,.5)}
.rv-tbl tr:hover{background:rgba(203,166,247,.05)}
.rv-tbl .stars{color:#f9e2af;letter-spacing:1px}

.badge{display:inline-block;padding:2px 10px;border-radius:10px;font-size:.7rem;font-weight:600}
.badge-pending{background:#f9e2af22;color:#f9e2af}
.badge-approved{background:#a6e3a122;color:#a6e3a1}
.badge-rejected{background:#f38ba822;color:#f38ba8}
.badge-verified{background:#89b4fa22;color:#89b4fa;font-size:.7rem}

.rv-btn{display:inline-block;padding:4px 12px;border-radius:6px;font-size:.75rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:opacity .2s}
.rv-btn:hover{opacity:.85}
.rv-btn-approve{background:#a6e3a1;color:#1e1e2e}
.rv-btn-reject{background:#f38ba8;color:#1e1e2e}
.rv-btn-delete{background:#45475a;color:#cdd6f4}
.rv-btn-reply{background:#89b4fa;color:#1e1e2e}

.rv-reply{margin-top:6px;padding:8px 12px;background:#313244;border-radius:8px;font-size:.8rem;color:#a6adc8;border-left:3px solid #cba6f7}
.rv-reply strong{color:#cba6f7}

.rv-pagination{display:flex;justify-content:center;gap:6px;padding:16px}
.rv-pagination a{padding:6px 12px;border-radius:6px;text-decoration:none;font-size:.8rem;background:#313244;color:#cdd6f4}
.rv-pagination a.active{background:#cba6f7;color:#1e1e2e}

/* Reply modal */
.rv-modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center}
.rv-modal-bg.show{display:flex}
.rv-modal{background:#1e1e2e;border:1px solid #313244;border-radius:16px;padding:24px;width:500px;max-width:90vw}
.rv-modal h3{margin:0 0 16px;color:#cdd6f4;font-size:1.1rem}
.rv-modal textarea{width:100%;min-height:100px;background:#181825;border:1px solid #313244;border-radius:8px;color:#cdd6f4;padding:10px;font-size:.85rem;resize:vertical}
.rv-modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:12px}
</style>

<div class="rv-wrap">
    <div class="rv-header">
        <h1>⭐ Product Reviews</h1>
    </div>

    <div class="rv-stats">
        <div class="rv-stat">
            <div class="num"><?= (int)$stats['total'] ?></div>
            <div class="lbl">📝 Total Reviews</div>
        </div>
        <div class="rv-stat warn">
            <div class="num"><?= (int)$stats['pending'] ?></div>
            <div class="lbl">⏳ Pending</div>
        </div>
        <div class="rv-stat ok">
            <div class="num"><?= (int)$stats['approved'] ?></div>
            <div class="lbl">✅ Approved</div>
        </div>
        <div class="rv-stat">
            <div class="num"><?= (int)$stats['rejected'] ?></div>
            <div class="lbl">❌ Rejected</div>
        </div>
        <div class="rv-stat">
            <div class="num"><?= $stats['avg_rating'] > 0 ? number_format((float)$stats['avg_rating'], 1) : '—' ?></div>
            <div class="lbl">⭐ Avg Rating</div>
        </div>
    </div>

    <div class="rv-tabs">
        <a href="/admin/shop/reviews" class="<?= $statusFilter === '' ? 'active' : '' ?>">All (<?= (int)$stats['total'] ?>)</a>
        <a href="/admin/shop/reviews?status=pending" class="<?= $statusFilter === 'pending' ? 'active' : '' ?>">Pending (<?= (int)$stats['pending'] ?>)</a>
        <a href="/admin/shop/reviews?status=approved" class="<?= $statusFilter === 'approved' ? 'active' : '' ?>">Approved (<?= (int)$stats['approved'] ?>)</a>
        <a href="/admin/shop/reviews?status=rejected" class="<?= $statusFilter === 'rejected' ? 'active' : '' ?>">Rejected (<?= (int)$stats['rejected'] ?>)</a>
    </div>

    <div class="rv-card">
        <?php if (empty($reviews)): ?>
            <p style="padding:40px;text-align:center;color:var(--muted,#6c7086)">No reviews found.</p>
        <?php else: ?>
            <table class="rv-tbl">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reviews as $rv): ?>
                    <tr>
                        <td>
                            <a href="/shop/<?= h($rv['product_slug'] ?? '') ?>" target="_blank" style="color:#89b4fa;text-decoration:none"><?= h($rv['product_name'] ?? 'Unknown') ?></a>
                        </td>
                        <td>
                            <?= h($rv['customer_name']) ?>
                            <?php if (!empty($rv['is_verified_purchase'])): ?>
                                <span class="badge badge-verified">✓ Verified</span>
                            <?php endif; ?>
                        </td>
                        <td class="stars"><?= $starsFull((int)$rv['rating']) ?></td>
                        <td>
                            <?= h($rv['title'] ?: '—') ?>
                            <?php if (!empty($rv['review_text'])): ?>
                                <div style="font-size:.75rem;color:var(--muted,#6c7086);margin-top:2px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= h($rv['review_text']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($rv['admin_reply'])): ?>
                                <div class="rv-reply"><strong>Admin:</strong> <?= h($rv['admin_reply']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?= h($rv['status']) ?>"><?= h(ucfirst($rv['status'])) ?></span></td>
                        <td style="color:var(--muted,#6c7086);font-size:.8rem;white-space:nowrap"><?= date('M j, Y', strtotime($rv['created_at'])) ?></td>
                        <td style="white-space:nowrap">
                            <?php if ($rv['status'] !== 'approved'): ?>
                                <form method="POST" action="/admin/shop/reviews/<?= (int)$rv['id'] ?>/approve" style="display:inline">
                                    <?= csrf_token_html() ?>
                                    <button type="submit" class="rv-btn rv-btn-approve" title="Approve">✓</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($rv['status'] !== 'rejected'): ?>
                                <form method="POST" action="/admin/shop/reviews/<?= (int)$rv['id'] ?>/reject" style="display:inline">
                                    <?= csrf_token_html() ?>
                                    <button type="submit" class="rv-btn rv-btn-reject" title="Reject">✗</button>
                                </form>
                            <?php endif; ?>
                            <button type="button" class="rv-btn rv-btn-reply" title="Reply" onclick="openReplyModal(<?= (int)$rv['id'] ?>, this)" data-reply="<?= h($rv['admin_reply'] ?? '') ?>">💬</button>
                            <form method="POST" action="/admin/shop/reviews/<?= (int)$rv['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this review?')">
                                <?= csrf_token_html() ?>
                                <button type="submit" class="rv-btn rv-btn-delete" title="Delete">🗑</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="rv-pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php $qs = $_GET; $qs['page'] = $i; ?>
            <a href="/admin/shop/reviews?<?= http_build_query($qs) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Reply Modal -->
<div class="rv-modal-bg" id="replyModal">
    <div class="rv-modal">
        <h3>💬 Admin Reply</h3>
        <form method="POST" id="replyForm">
            <?= csrf_token_html() ?>
            <textarea name="admin_reply" id="replyText" placeholder="Write your reply..."></textarea>
            <div class="rv-modal-actions">
                <button type="button" class="rv-btn rv-btn-delete" onclick="closeReplyModal()">Cancel</button>
                <button type="submit" class="rv-btn rv-btn-reply">Save Reply</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReplyModal(id, btn) {
    var modal = document.getElementById('replyModal');
    var form = document.getElementById('replyForm');
    var text = document.getElementById('replyText');
    form.action = '/admin/shop/reviews/' + id + '/reply';
    text.value = btn.getAttribute('data-reply') || '';
    modal.classList.add('show');
    text.focus();
}
function closeReplyModal() {
    document.getElementById('replyModal').classList.remove('show');
}
document.getElementById('replyModal').addEventListener('click', function(e) {
    if (e.target === this) closeReplyModal();
});
</script>

<?php
$content = ob_get_clean();
$title = $pageTitle;
require CMS_ROOT . '/app/views/admin/layouts/topbar.php';
