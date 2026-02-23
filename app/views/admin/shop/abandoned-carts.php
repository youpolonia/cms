<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = 'Abandoned Carts';
ob_start();
?>
<style>
.ac-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.ac-stat{background:var(--bg-secondary);border:1px solid var(--border);border-radius:12px;padding:20px;text-align:center}
.ac-stat-value{font-size:1.8rem;font-weight:700;color:var(--text-primary)}
.ac-stat-label{font-size:.8rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-top:4px}
.ac-stat-accent .ac-stat-value{color:var(--accent)}
.ac-stat-success .ac-stat-value{color:var(--success)}
.ac-stat-warning .ac-stat-value{color:var(--warning)}

.ac-actions{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.ac-badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:600}
.ac-badge-recovered{background:rgba(16,185,129,.15);color:#10b981}
.ac-badge-pending{background:rgba(245,158,11,.15);color:#f59e0b}
.ac-badge-no-email{background:rgba(100,116,139,.15);color:#64748b}
.ac-age{font-size:.8rem;color:var(--text-muted)}
</style>

<div style="max-width:1200px;margin:0 auto">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary)">🛒 Abandoned Carts</h1>
        <a href="/admin/shop" style="color:var(--text-muted);text-decoration:none;font-size:.9rem">← Back to Shop</a>
    </div>

    <!-- Stats -->
    <div class="ac-stats">
        <div class="ac-stat">
            <div class="ac-stat-value"><?= (int)$stats['total_abandoned'] ?></div>
            <div class="ac-stat-label">Total Abandoned</div>
        </div>
        <div class="ac-stat ac-stat-warning">
            <div class="ac-stat-value"><?= (int)$stats['pending'] ?></div>
            <div class="ac-stat-label">Pending Recovery</div>
        </div>
        <div class="ac-stat ac-stat-success">
            <div class="ac-stat-value"><?= (int)$stats['recovered'] ?></div>
            <div class="ac-stat-label">Recovered</div>
        </div>
        <div class="ac-stat ac-stat-accent">
            <div class="ac-stat-value"><?= h((string)$stats['recovery_rate']) ?>%</div>
            <div class="ac-stat-label">Recovery Rate</div>
        </div>
    </div>

    <!-- Actions -->
    <div class="ac-actions">
        <div style="color:var(--text-secondary);font-size:.9rem">
            Showing <?= count($carts) ?> of <?= (int)$total ?> carts
        </div>
        <form method="post" action="/admin/shop/abandoned-carts/send-reminders" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" class="btn btn-primary" onclick="return confirm('Send reminder emails to all eligible abandoned carts?')">
                📧 Send Reminders Now
            </button>
        </form>
    </div>

    <!-- Table -->
    <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:12px;overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--bg-tertiary)">
                    <th style="padding:12px 16px;text-align:left;font-size:.75rem;text-transform:uppercase;color:var(--text-muted);letter-spacing:.04em">Email</th>
                    <th style="padding:12px 16px;text-align:center;font-size:.75rem;text-transform:uppercase;color:var(--text-muted)">Items</th>
                    <th style="padding:12px 16px;text-align:right;font-size:.75rem;text-transform:uppercase;color:var(--text-muted)">Subtotal</th>
                    <th style="padding:12px 16px;text-align:center;font-size:.75rem;text-transform:uppercase;color:var(--text-muted)">Age</th>
                    <th style="padding:12px 16px;text-align:center;font-size:.75rem;text-transform:uppercase;color:var(--text-muted)">Reminders</th>
                    <th style="padding:12px 16px;text-align:center;font-size:.75rem;text-transform:uppercase;color:var(--text-muted)">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($carts)): ?>
                <tr><td colspan="6" style="padding:32px;text-align:center;color:var(--text-muted)">No abandoned carts yet.</td></tr>
            <?php else: ?>
                <?php foreach ($carts as $cart):
                    $items = json_decode($cart['items'] ?? '{}', true);
                    $itemCount = is_array($items) ? count($items) : 0;
                    $createdAt = strtotime($cart['created_at']);
                    $age = time() - $createdAt;
                    if ($age < 3600) {
                        $ageStr = (int)($age / 60) . 'm ago';
                    } elseif ($age < 86400) {
                        $ageStr = (int)($age / 3600) . 'h ago';
                    } else {
                        $ageStr = (int)($age / 86400) . 'd ago';
                    }
                ?>
                <tr style="border-top:1px solid var(--border)">
                    <td style="padding:12px 16px;color:var(--text-primary);font-size:.9rem">
                        <?= $cart['customer_email'] ? h($cart['customer_email']) : '<span style="color:var(--text-muted);font-style:italic">No email</span>' ?>
                    </td>
                    <td style="padding:12px 16px;text-align:center;color:var(--text-secondary)"><?= $itemCount ?></td>
                    <td style="padding:12px 16px;text-align:right;font-weight:600;color:var(--text-primary)"><?= h(number_format((float)$cart['subtotal'], 2)) ?></td>
                    <td style="padding:12px 16px;text-align:center" class="ac-age"><?= h($ageStr) ?></td>
                    <td style="padding:12px 16px;text-align:center;color:var(--text-secondary)">
                        <?= (int)$cart['reminder_count'] ?>/3
                        <?php if ($cart['reminder_sent_at']): ?>
                            <br><span style="font-size:.7rem;color:var(--text-muted)">Last: <?= h(date('M j, H:i', strtotime($cart['reminder_sent_at']))) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        <?php if ($cart['recovered']): ?>
                            <span class="ac-badge ac-badge-recovered">✅ Recovered</span>
                        <?php elseif (empty($cart['customer_email'])): ?>
                            <span class="ac-badge ac-badge-no-email">No Email</span>
                        <?php else: ?>
                            <span class="ac-badge ac-badge-pending">⏳ Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;margin-top:20px">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" style="padding:6px 12px;border-radius:6px;border:1px solid var(--border);text-decoration:none;<?= $i === $page ? 'background:var(--accent);color:#fff;border-color:var(--accent)' : 'color:var(--text-secondary)' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
