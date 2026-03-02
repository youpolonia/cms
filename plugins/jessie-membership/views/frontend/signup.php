<?php
/**
 * Jessie Membership — Signup Page with Payment Gateway
 * URL: /membership/signup, /membership/signup/success, /membership/signup/cancel
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-membership/includes/class-membership-plan.php';
require_once CMS_ROOT . '/plugins/jessie-membership/includes/class-membership-member.php';
require_once CMS_ROOT . '/core/payment-gateway.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$action = $membershipAction ?? '';
$planId = (int)($_GET['plan'] ?? 0);
$plan = $planId ? \MembershipPlan::get($planId) : null;
$allPlans = \MembershipPlan::getPublicPlans();
$paymentMethods = PaymentGateway::getAvailableMethods();
$currency = function_exists('get_setting') ? get_setting('payment_currency', 'USD') : 'USD';

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['ms_csrf'])) { $_SESSION['ms_csrf'] = bin2hex(random_bytes(16)); }
$csrfToken = $_SESSION['ms_csrf'];

$error = '';
$success = false;
$offlineInstructions = '';
$activatedMember = null;

// ─── PAYMENT SUCCESS ───
if ($action === 'success') {
    $provider = $_GET['provider'] ?? '';
    $memberId = (int)($_GET['member'] ?? 0);
    $verifyParams = [];
    if ($provider === 'stripe') { $verifyParams['session_id'] = $_GET['session_id'] ?? ''; }
    elseif ($provider === 'paypal') { $verifyParams['order_id'] = $_GET['token'] ?? ''; }

    if ($memberId > 0 && $provider) {
        $result = PaymentGateway::verifyAndComplete($provider, $verifyParams);
        if (!empty($result['success'])) {
            \MembershipMember::update($memberId, ['status' => 'active']);
            $activatedMember = \MembershipMember::get($memberId);
            if ($activatedMember) {
                $mPlan = \MembershipPlan::get((int)$activatedMember['plan_id']);
                \MembershipMember::recordTransaction($memberId, (int)$activatedMember['plan_id'], (float)($mPlan['price'] ?? 0), 'payment', $provider, $result['transaction_id'] ?? '');
            }
            $success = true;
        } else {
            $error = $result['error'] ?? 'Payment verification failed';
        }
    } else {
        $error = 'Invalid callback';
    }
}

// ─── PAYMENT CANCEL ───
if ($action === 'cancel') {
    $cancelId = (int)($_GET['member'] ?? 0);
    if ($cancelId > 0) { \MembershipMember::update($cancelId, ['status' => 'cancelled']); }
    $error = 'Payment was cancelled. Please try again.';
}

// ─── POST HANDLER ───
if ($action === '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $selectedPlan = (int)($_POST['plan_id'] ?? 0);
        $payMethod = $_POST['payment_method'] ?? '';

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Valid email is required'; }
        elseif (!$selectedPlan) { $error = 'Please select a plan'; }
        else {
            $existing = \MembershipMember::getByEmail($email);
            if ($existing && $existing['status'] === 'active') { $error = 'This email already has an active membership'; }
            else {
                $chosenPlan = \MembershipPlan::get($selectedPlan);
                $price = (float)($chosenPlan['price'] ?? 0);
                $needsPayment = $price > 0 && !empty($paymentMethods);

                $initialStatus = 'active';
                if ($needsPayment && in_array($payMethod, ['stripe', 'paypal'])) {
                    $initialStatus = 'pending_payment';
                } elseif ($needsPayment && $payMethod === 'bank_transfer') {
                    $initialStatus = 'pending';
                }

                // Calculate expiry
                $period = $chosenPlan['billing_period'] ?? 'month';
                $expiresAt = match($period) {
                    'year'  => date('Y-m-d H:i:s', strtotime('+1 year')),
                    'week'  => date('Y-m-d H:i:s', strtotime('+1 week')),
                    default => date('Y-m-d H:i:s', strtotime('+1 month')),
                };

                $memberId = \MembershipMember::create([
                    'plan_id'    => $selectedPlan,
                    'email'      => $email,
                    'name'       => $name,
                    'status'     => $initialStatus,
                    'expires_at' => $expiresAt,
                    'payment_method' => $payMethod ?: null,
                ]);

                if ($memberId > 0) {
                    if ($needsPayment && in_array($payMethod, ['stripe', 'paypal'])) {
                        $siteUrl = rtrim(function_exists('get_setting') ? get_setting('site_url', '') : '', '/');
                        $payResult = PaymentGateway::processPayment($payMethod, $price, [
                            'items' => [['name' => $chosenPlan['name'] . ' Membership', 'price' => $price, 'quantity' => 1]],
                            'customer_email' => $email,
                            'reference_id'   => 'membership_' . $memberId,
                            'description'    => 'Membership: ' . $chosenPlan['name'],
                            'metadata'       => ['member_id' => (string)$memberId, 'plan_id' => (string)$selectedPlan, 'type' => 'membership'],
                            'success_url'    => $siteUrl . '/membership/signup/success?provider=' . $payMethod . '&member=' . $memberId . ($payMethod === 'stripe' ? '&session_id={CHECKOUT_SESSION_ID}' : ''),
                            'cancel_url'     => $siteUrl . '/membership/signup/cancel?member=' . $memberId,
                        ]);
                        if (!empty($payResult['redirect'])) {
                            header('Location: ' . $payResult['redirect']);
                            exit;
                        } elseif (!empty($payResult['error'])) {
                            $error = 'Payment error: ' . $payResult['error'];
                            \MembershipMember::update($memberId, ['status' => 'cancelled']);
                        }
                    } elseif ($needsPayment && $payMethod === 'bank_transfer') {
                        $offlineInstructions = PaymentGateway::processPayment('bank_transfer', $price, [])['instructions'] ?? '';
                        \MembershipMember::recordTransaction($memberId, $selectedPlan, $price, 'payment', 'bank_transfer', 'pending');
                        $success = true;
                    } else {
                        // Free plan
                        if ($price > 0) {
                            \MembershipMember::recordTransaction($memberId, $selectedPlan, $price, 'payment', $payMethod ?: 'cod', '');
                        }
                        $success = true;
                    }
                    $_SESSION['ms_csrf'] = bin2hex(random_bytes(16));
                } else {
                    $error = 'Failed to create membership. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Join — Membership</title>
<style>
:root{--bg:#0f172a;--card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#8b5cf6;--green:#22c55e;--danger:#ef4444}
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.signup{max-width:520px;width:100%}
.signup h1{font-size:1.8rem;font-weight:800;text-align:center;margin-bottom:8px}
.signup .sub{text-align:center;color:var(--muted);margin-bottom:32px}
.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:32px}
label{display:block;font-size:.85rem;color:var(--muted);margin-bottom:4px;margin-top:16px}
input,select{width:100%;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:1rem}
input:focus,select:focus{outline:none;border-color:var(--accent)}
.btn{display:block;width:100%;padding:14px;background:linear-gradient(135deg,var(--accent),#6366f1);color:#fff;border:none;border-radius:10px;font-size:1.1rem;font-weight:700;cursor:pointer;margin-top:24px}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(139,92,246,.3)}
.btn:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
.error{background:#ef444420;border:1px solid #ef4444;color:#fca5a5;padding:12px;border-radius:8px;margin-bottom:16px;font-size:.9rem}
.success{background:#22c55e20;border:1px solid #22c55e;color:#86efac;padding:20px;border-radius:8px;text-align:center;font-size:1rem}
.instructions{background:#f59e0b20;border:1px solid #f59e0b;color:#fcd34d;padding:16px;border-radius:8px;margin-top:16px;font-size:.9rem}
.plan-option{background:var(--bg);border:2px solid var(--border);border-radius:10px;padding:12px 16px;margin-top:8px;cursor:pointer;transition:.2s}
.plan-option:hover,.plan-option.selected{border-color:var(--accent)}
.plan-option .name{font-weight:600}.plan-option .price{color:var(--accent);font-weight:700}
.plan-option .desc{color:var(--muted);font-size:.8rem;margin-top:2px}
.pay-section{margin-top:20px;padding-top:16px;border-top:1px solid var(--border)}
.pay-section h3{font-size:.9rem;color:var(--muted);margin-bottom:12px}
.pay-option{background:var(--bg);border:2px solid var(--border);border-radius:10px;padding:12px 16px;margin-bottom:8px;cursor:pointer;transition:.2s;display:flex;align-items:center;gap:12px}
.pay-option:hover,.pay-option.selected{border-color:var(--accent)}
.pay-option input[type=radio]{display:none}
.pay-option .icon{font-size:1.3rem;width:32px;text-align:center}
.pay-option .info{flex:1}.pay-option .pm-name{font-weight:600;font-size:.9rem}.pay-option .pm-desc{font-size:.75rem;color:var(--muted)}
.price-tag{text-align:center;background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px;margin-top:16px}
.price-tag .amount{font-size:1.6rem;font-weight:800;color:var(--accent)}.price-tag .period{color:var(--muted);font-size:.85rem}
a{color:var(--accent);text-decoration:none}
</style></head><body>
<div class="signup">
    <h1>🎫 Join Membership</h1>
    <p class="sub">Get access to exclusive content</p>

    <?php if ($action === 'success' && $success && $activatedMember): ?>
    <div class="success">
        ✅ Payment successful! Your membership is now active.<br><br>
        <strong>Plan:</strong> <?php $mPlan = \MembershipPlan::get((int)$activatedMember['plan_id']); echo h($mPlan['name'] ?? 'N/A'); ?><br>
        <strong>Expires:</strong> <?= date('M j, Y', strtotime($activatedMember['expires_at'] ?? '+1 month')) ?><br><br>
        <a href="/membership/portal">Go to Member Portal →</a>
    </div>

    <?php elseif ($success): ?>
    <div class="success">
        ✅ <?= $offlineInstructions ? 'Membership created — pending payment.' : 'Welcome! Your membership is now active.' ?><br><br>
        <a href="/membership/portal">Go to Member Portal →</a>
    </div>
    <?php if ($offlineInstructions): ?>
    <div class="instructions">
        <strong>🏦 Payment Instructions:</strong><br><?= nl2br(h($offlineInstructions)) ?>
        <br><br><strong>Reference:</strong> MEMBER-<?= $memberId ?? 0 ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="card">
        <?php if ($error): ?><div class="error">⚠️ <?= h($error) ?></div><?php endif; ?>
        <form method="POST" id="signupForm">
            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
            <input type="hidden" name="payment_method" id="fPayMethod" value="">

            <label>Full Name</label>
            <input type="text" name="name" placeholder="John Doe" value="<?= h($_POST['name'] ?? '') ?>">

            <label>Email *</label>
            <input type="email" name="email" placeholder="you@example.com" required value="<?= h($_POST['email'] ?? '') ?>">

            <label>Select Plan *</label>
            <?php foreach ($allPlans as $p): $isFree = (float)$p['price'] === 0.0; ?>
            <label class="plan-option <?= $planId == $p['id'] ? 'selected' : '' ?>" data-price="<?= (float)$p['price'] ?>" data-period="<?= h($p['billing_period'] ?? 'month') ?>" onclick="selectPlan(this,<?= $p['id'] ?>)">
                <input type="radio" name="plan_id" value="<?= $p['id'] ?>" <?= $planId == $p['id'] ? 'checked' : '' ?> style="display:none">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span class="name"><?= h($p['name']) ?></span>
                    <span class="price"><?= $isFree ? 'Free' : $currency . ' ' . number_format((float)$p['price'], 2) . '/' . h($p['billing_period'] ?? 'month') ?></span>
                </div>
                <?php if (!empty($p['description'])): ?><div class="desc"><?= h($p['description']) ?></div><?php endif; ?>
            </label>
            <?php endforeach; ?>

            <!-- Payment section (shown for paid plans) -->
            <div class="pay-section" id="paySection" style="display:<?= ($plan && (float)($plan['price'] ?? 0) > 0) ? 'block' : 'none' ?>">
                <h3>💳 Payment Method</h3>
                <div class="price-tag" id="priceTag">
                    <div class="amount" id="priceAmount"><?= $currency ?> 0.00</div>
                    <div class="period" id="pricePeriod">per month</div>
                </div>
                <?php if (!empty($paymentMethods)): ?>
                <?php foreach ($paymentMethods as $idx => $pm): ?>
                <label class="pay-option <?= $idx === 0 ? 'selected' : '' ?>" data-method="<?= h($pm['id']) ?>" onclick="selectPayMethod(this)">
                    <input type="radio" name="_pay" value="<?= h($pm['id']) ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                    <span class="icon"><?= $pm['icon'] ?></span>
                    <div class="info"><div class="pm-name"><?= h($pm['name']) ?></div><div class="pm-desc"><?= h($pm['description']) ?></div></div>
                </label>
                <?php endforeach; ?>
                <?php else: ?>
                <p style="color:var(--muted);font-size:.85rem;margin-top:8px">No payment methods configured.</p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn" id="btnSubmit">🚀 Join Now</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--muted)">Already a member? <a href="/membership/portal">Sign in</a></p>
    </div>
    <?php endif; ?>
</div>

<script>
var currency=<?= json_encode($currency) ?>;
var selectedPrice=0,selectedPeriod='month',hasPay=<?= !empty($paymentMethods)?'true':'false' ?>;

function selectPlan(el,pid){
    document.querySelectorAll('.plan-option').forEach(function(e){e.classList.remove('selected')});
    el.classList.add('selected');el.querySelector('input').checked=true;
    selectedPrice=parseFloat(el.dataset.price);selectedPeriod=el.dataset.period||'month';
    var ps=document.getElementById('paySection');
    if(selectedPrice>0&&hasPay){
        ps.style.display='block';
        document.getElementById('priceAmount').textContent=currency+' '+selectedPrice.toFixed(2);
        document.getElementById('pricePeriod').textContent='per '+selectedPeriod;
        var first=document.querySelector('.pay-option.selected');
        if(first)document.getElementById('fPayMethod').value=first.dataset.method;
    }else{
        ps.style.display='none';document.getElementById('fPayMethod').value='';
    }
}
function selectPayMethod(el){
    document.querySelectorAll('.pay-option').forEach(function(e){e.classList.remove('selected')});
    el.classList.add('selected');el.querySelector('input').checked=true;
    document.getElementById('fPayMethod').value=el.dataset.method;
}

// Init if plan pre-selected
<?php if ($plan && (float)($plan['price'] ?? 0) > 0): ?>
selectedPrice=<?= (float)$plan['price'] ?>;selectedPeriod=<?= json_encode($plan['billing_period'] ?? 'month') ?>;
document.getElementById('priceAmount').textContent=currency+' '+selectedPrice.toFixed(2);
document.getElementById('pricePeriod').textContent='per '+selectedPeriod;
var fp=document.querySelector('.pay-option.selected');
if(fp)document.getElementById('fPayMethod').value=fp.dataset.method;
<?php endif; ?>

document.getElementById('signupForm')?.addEventListener('submit',function(){
    var b=document.getElementById('btnSubmit');b.disabled=true;b.textContent='⏳ Processing...';
});
</script>
</body></html>
