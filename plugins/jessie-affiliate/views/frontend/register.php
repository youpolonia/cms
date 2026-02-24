<?php
/**
 * Jessie Affiliate — Public Registration + Affiliate Dashboard
 * URL: /affiliate/register (signup form)
 * URL: /affiliate/dashboard (affiliate stats via session cookie)
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-affiliate/includes/class-affiliate-program.php';
require_once CMS_ROOT . '/plugins/jessie-affiliate/includes/class-affiliate.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$programs = AffiliateProgram::getActive();
$message = '';
$messageType = '';
$registeredCode = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $programId = (int)($_POST['program_id'] ?? 0);
    $website = trim(($_POST['website'] ?? null) ?: '');
    $paymentMethod = ($_POST['payment_method'] ?? null) ?: '';
    $paymentDetails = trim(($_POST['payment_details'] ?? null) ?: '');

    if (!$name || !$email || !$programId) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $existing = Affiliate::getByEmail($email);
        if ($existing) {
            $message = 'This email is already registered. Check your email for your referral code.';
            $messageType = 'error';
        } else {
            $affId = Affiliate::create([
                'program_id' => $programId,
                'name' => $name,
                'email' => $email,
                'website' => $website,
                'payment_method' => $paymentMethod,
                'payment_details' => $paymentDetails,
            ]);
            $aff = Affiliate::get($affId);
            $registeredCode = $aff['referral_code'];
            if ($aff['status'] === 'active') {
                $message = 'Welcome! Your affiliate account is active. Your referral code: ' . $registeredCode;
                $messageType = 'success';
                // Set session cookie for affiliate dashboard
                setcookie('aff_session', $aff['email'], time() + (365 * 86400), '/', '', false, true);
            } else {
                $message = 'Application submitted! You will receive your referral code once approved.';
                $messageType = 'success';
            }
        }
    }
}

// Site settings
$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: 'Affiliate Program'; } catch (\Exception $e) { $siteTitle = 'Affiliate Program'; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become an Affiliate — <?= h($siteTitle) ?></title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#7c3aed;--accent2:#a855f7}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}a:hover{color:var(--accent2)}

        .hero{background:linear-gradient(135deg,rgba(124,58,237,.15) 0%,rgba(168,85,247,.1) 100%);border-bottom:1px solid var(--border);padding:48px 20px;text-align:center}
        .hero h1{font-size:2rem;font-weight:800;margin-bottom:8px}
        .hero p{color:var(--muted);font-size:1rem;max-width:600px;margin:0 auto}

        .container{max-width:700px;margin:0 auto;padding:32px 20px}

        .benefits{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:32px}
        .benefit{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;text-align:center}
        .benefit .icon{font-size:2rem;margin-bottom:8px}
        .benefit h3{font-size:.9rem;font-weight:700;margin-bottom:4px}
        .benefit p{font-size:.78rem;color:var(--muted)}

        .form-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:28px}
        .form-card h2{font-size:1.2rem;font-weight:700;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--border)}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-size:.85rem;font-weight:600;margin-bottom:6px}
        .form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:12px 16px;border-radius:10px;font-size:.9rem}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent)}
        .form-group .hint{font-size:.72rem;color:var(--muted);margin-top:4px}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        @media(max-width:600px){.form-row{grid-template-columns:1fr}}

        .btn-submit{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:14px 32px;border-radius:10px;font-weight:700;font-size:1rem;cursor:pointer;width:100%;margin-top:8px;transition:.2s}
        .btn-submit:hover{transform:translateY(-1px);box-shadow:0 4px 20px rgba(124,58,237,.3)}

        .alert{padding:14px 18px;border-radius:10px;font-size:.85rem;margin-bottom:20px}
        .alert-success{background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.3)}
        .alert-error{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.3)}

        .code-display{background:rgba(124,58,237,.1);border:2px dashed rgba(124,58,237,.4);border-radius:12px;padding:24px;text-align:center;margin-top:20px}
        .code-display .code{font-family:monospace;font-size:2rem;font-weight:800;color:#c4b5fd;letter-spacing:4px}
        .code-display .label{font-size:.8rem;color:var(--muted);margin-top:8px}

        .programs-preview{margin-bottom:24px}
        .program-option{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:10px;display:flex;align-items:center;gap:14px}
        .program-option .comm{font-size:1.3rem;font-weight:800;color:var(--accent)}
        .program-option .name{font-weight:600;font-size:.9rem}
        .program-option .desc{font-size:.75rem;color:var(--muted);margin-top:2px}
    </style>
</head>
<body>
    <div class="hero">
        <h1>🤝 Become an Affiliate</h1>
        <p>Earn commissions by referring customers to us. Join our affiliate program today!</p>
    </div>

    <div class="container">
        <div class="benefits">
            <div class="benefit"><div class="icon">💰</div><h3>Earn Commissions</h3><p>Get paid for every successful referral</p></div>
            <div class="benefit"><div class="icon">🔗</div><h3>Easy Tracking</h3><p>Unique referral code with cookie tracking</p></div>
            <div class="benefit"><div class="icon">📊</div><h3>Real-time Stats</h3><p>Track clicks, conversions & earnings</p></div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= h($message) ?></div>
        <?php endif; ?>

        <?php if ($registeredCode): ?>
        <div class="code-display">
            <div class="code"><?= h($registeredCode) ?></div>
            <div class="label">Your Referral Code — Share this with your audience!</div>
            <div style="margin-top:12px;font-size:.82rem;color:var(--muted)">
                Your referral link: <strong style="color:var(--text)"><?= h((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?>/?ref=<?= h($registeredCode) ?></strong>
            </div>
            <div style="margin-top:16px"><a href="/affiliate/dashboard" style="color:var(--accent);font-weight:600">→ Go to your Dashboard</a></div>
        </div>
        <?php else: ?>

        <?php if (!empty($programs)): ?>
        <div class="programs-preview">
            <h3 style="font-size:.85rem;text-transform:uppercase;color:var(--muted);margin-bottom:12px;letter-spacing:.05em">Available Programs</h3>
            <?php foreach ($programs as $p): ?>
            <div class="program-option">
                <div class="comm"><?= $p['commission_type'] === 'percentage' ? h($p['commission_value']) . '%' : '$' . number_format((float)$p['commission_value'], 2) ?></div>
                <div>
                    <div class="name"><?= h($p['name']) ?></div>
                    <div class="desc"><?= h(substr($p['description'] ?? '', 0, 120)) ?><?= strlen($p['description'] ?? '') > 120 ? '...' : '' ?> · <?= (int)$p['cookie_days'] ?>-day cookie · $<?= number_format((float)$p['min_payout'], 2) ?> min payout</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="form-card">
            <h2>📝 Apply Now</h2>
            <form method="POST" action="/affiliate/register">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?= h($_POST['name'] ?? '') ?>" required placeholder="Your full name">
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <label>Affiliate Program *</label>
                    <select name="program_id" required>
                        <option value="">Select a program...</option>
                        <?php foreach ($programs as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ((int)($_POST['program_id'] ?? 0)) === (int)$p['id'] ? 'selected' : '' ?>><?= h($p['name']) ?> — <?= $p['commission_type'] === 'percentage' ? h($p['commission_value']) . '%' : '$' . number_format((float)$p['commission_value'], 2) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Website / Social Media</label>
                    <input type="url" name="website" value="<?= h($_POST['website'] ?? '') ?>" placeholder="https://yoursite.com">
                    <div class="hint">Where will you promote our products?</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Preferred Payment Method</label>
                        <select name="payment_method">
                            <option value="paypal" <?= ($_POST['payment_method']??'')==='paypal'?'selected':'' ?>>PayPal</option>
                            <option value="bank_transfer" <?= ($_POST['payment_method']??'')==='bank_transfer'?'selected':'' ?>>Bank Transfer</option>
                            <option value="crypto" <?= ($_POST['payment_method']??'')==='crypto'?'selected':'' ?>>Cryptocurrency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Details</label>
                        <input type="text" name="payment_details" value="<?= h($_POST['payment_details'] ?? '') ?>" placeholder="PayPal email or wallet address">
                    </div>
                </div>
                <button type="submit" class="btn-submit">🚀 Submit Application</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
