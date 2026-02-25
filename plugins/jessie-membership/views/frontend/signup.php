<?php
/**
 * Jessie Membership — Signup Page
 * URL: /membership/signup?plan={id}
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-membership/includes/class-membership-plan.php';
require_once CMS_ROOT . '/plugins/jessie-membership/includes/class-membership-member.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$planId = (int)($_GET['plan'] ?? 0);
$plan = $planId ? \MembershipPlan::get($planId) : null;
$allPlans = \MembershipPlan::getPublicPlans();
$error = ''; $success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $selectedPlan = (int)($_POST['plan_id'] ?? 0);
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Valid email is required'; }
    elseif (!$selectedPlan) { $error = 'Please select a plan'; }
    else {
        $existing = \MembershipMember::getByEmail($email);
        if ($existing && $existing['status'] === 'active') { $error = 'This email already has an active membership'; }
        else {
            $memberId = \MembershipMember::create(['plan_id' => $selectedPlan, 'email' => $email, 'name' => $name]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Join — Membership</title>
<style>
:root{--bg:#0f172a;--card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#8b5cf6;--green:#22c55e}
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.signup{max-width:480px;width:100%}
.signup h1{font-size:1.8rem;font-weight:800;text-align:center;margin-bottom:8px}
.signup .sub{text-align:center;color:var(--muted);margin-bottom:32px}
.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:32px}
label{display:block;font-size:.85rem;color:var(--muted);margin-bottom:4px;margin-top:16px}
input,select{width:100%;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:1rem}
input:focus,select:focus{outline:none;border-color:var(--accent)}
.btn{display:block;width:100%;padding:14px;background:linear-gradient(135deg,var(--accent),#6366f1);color:#fff;border:none;border-radius:10px;font-size:1.1rem;font-weight:700;cursor:pointer;margin-top:24px}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(139,92,246,.3)}
.error{background:#ef444420;border:1px solid #ef4444;color:#fca5a5;padding:12px;border-radius:8px;margin-bottom:16px;font-size:.9rem}
.success{background:#22c55e20;border:1px solid #22c55e;color:#86efac;padding:20px;border-radius:8px;text-align:center;font-size:1rem}
.plan-option{background:var(--bg);border:2px solid var(--border);border-radius:10px;padding:12px 16px;margin-top:8px;cursor:pointer;transition:.2s}
.plan-option:hover,.plan-option.selected{border-color:var(--accent)}
.plan-option .name{font-weight:600}.plan-option .price{color:var(--accent);font-weight:700}
.plan-option .desc{color:var(--muted);font-size:.8rem;margin-top:2px}
a{color:var(--accent);text-decoration:none}
</style></head><body>
<div class="signup">
    <h1>🎫 Join Membership</h1>
    <p class="sub">Get access to exclusive content</p>
    <?php if ($success): ?>
    <div class="success">✅ Welcome! Your membership is now active.<br><br><a href="/membership/portal">Go to Member Portal →</a></div>
    <?php else: ?>
    <div class="card">
        <?php if ($error): ?><div class="error">⚠️ <?= h($error) ?></div><?php endif; ?>
        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="John Doe" value="<?= h($_POST['name'] ?? '') ?>">
            <label>Email *</label>
            <input type="email" name="email" placeholder="you@example.com" required value="<?= h($_POST['email'] ?? '') ?>">
            <label>Select Plan *</label>
            <?php foreach ($allPlans as $p): $isFree = (float)$p['price'] === 0.0; ?>
            <label class="plan-option <?= $planId == $p['id'] ? 'selected' : '' ?>" onclick="this.querySelector('input').checked=true;document.querySelectorAll('.plan-option').forEach(function(e){e.classList.remove('selected')});this.classList.add('selected')">
                <input type="radio" name="plan_id" value="<?= $p['id'] ?>" <?= $planId == $p['id'] ? 'checked' : '' ?> style="display:none">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span class="name"><?= h($p['name']) ?></span>
                    <span class="price"><?= $isFree ? 'Free' : '$' . number_format((float)$p['price'], 0) . '/' . h($p['billing_period'] ?? 'month') ?></span>
                </div>
                <?php if ($p['description']): ?><div class="desc"><?= h($p['description']) ?></div><?php endif; ?>
            </label>
            <?php endforeach; ?>
            <button type="submit" class="btn">🚀 Join Now</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--muted)">Already a member? <a href="/membership/portal">Sign in</a></p>
    </div>
    <?php endif; ?>
</div></body></html>
