<?php
/**
 * Jessie Directory — Claim Listing Form
 * URL: /directory/{slug}/claim
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-listing.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$slug = $directorySlug ?? '';
$listing = DirectoryListing::getBySlug($slug);

if (!$listing || $listing['status'] !== 'active') {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Not Found</title></head><body style="background:#0f172a;color:#e2e8f0;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh"><div style="text-align:center"><h1>404</h1><p>Listing not found.</p><a href="/directory" style="color:#6366f1">← Back</a></div></body></html>';
    exit;
}

$submitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $proof = trim($_POST['proof'] ?? '');

    if (!$name) $errors[] = 'Your name is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!$proof) $errors[] = 'Please explain how you can verify ownership.';

    if (empty($errors)) {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO directory_claims (listing_id, email, name, proof, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([(int)$listing['id'], $email, $name, $proof]);
        $submitted = true;
        if (function_exists('cms_event')) {
            cms_event('directory.claim.submitted', ['listing_id' => $listing['id'], 'title' => $listing['title'], 'email' => $email]);
        }
    }
}

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: ''; } catch (\Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim: <?= h($listing['title']) ?> — <?= h($siteTitle ?: 'Directory') ?></title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}

        .claim-wrap{max-width:600px;margin:0 auto;padding:40px 20px}
        .breadcrumb{font-size:.78rem;color:var(--muted);margin-bottom:16px}
        .breadcrumb a{color:var(--muted)}
        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px}
        .card h2{font-size:1.3rem;font-weight:700;margin-bottom:6px}
        .card h3{font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)}
        .listing-preview{display:flex;align-items:center;gap:14px;padding:14px;background:rgba(99,102,241,.05);border-radius:8px;margin-bottom:16px}
        .listing-preview h4{font-size:.95rem;margin-bottom:2px}
        .listing-preview .desc{font-size:.78rem;color:var(--muted)}
        .form-group{margin-bottom:14px}
        .form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text);margin-bottom:5px}
        .form-group input,.form-group textarea{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
        .form-group textarea{min-height:100px;resize:vertical}
        .form-group .hint{font-size:.72rem;color:var(--muted);margin-top:4px}
        .btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px 24px;border-radius:10px;font-weight:600;cursor:pointer;font-size:.9rem}
        .btn-secondary{background:var(--bg-card);color:var(--text);padding:12px 20px;border-radius:10px;font-size:.85rem;font-weight:600;border:1px solid var(--border);text-decoration:none}
        .errors{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:14px;border-radius:8px;margin-bottom:16px;font-size:.85rem}
        .errors li{margin-left:16px}
        .success-box{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);border-radius:12px;padding:40px 24px;text-align:center}
        .success-box h2{font-size:1.3rem;margin-bottom:10px;color:#34d399}
        .success-box p{color:var(--muted);font-size:.9rem;margin-bottom:16px}
    </style>
</head>
<body>
    <div class="claim-wrap">
        <div class="breadcrumb"><a href="/directory">Directory</a> › <a href="/directory/<?= h($listing['slug']) ?>"><?= h($listing['title']) ?></a> › Claim</div>

        <?php if ($submitted): ?>
            <div class="success-box">
                <h2>✅ Claim Submitted!</h2>
                <p>We've received your claim for <strong><?= h($listing['title']) ?></strong>. Our team will review it and contact you at the provided email.</p>
                <a href="/directory/<?= h($listing['slug']) ?>" class="btn-secondary">← Back to Listing</a>
            </div>
        <?php else: ?>

        <div class="card">
            <h2>🏢 Claim This Business</h2>
            <p style="color:var(--muted);font-size:.85rem;margin-bottom:16px">Verify that you own or manage this business to gain control of the listing.</p>

            <div class="listing-preview">
                <div>
                    <h4><?= h($listing['title']) ?></h4>
                    <div class="desc"><?= h($listing['city'] ? $listing['city'] . ($listing['state'] ? ', ' . $listing['state'] : '') : 'No location') ?></div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="errors"><ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <form method="post">
                <div class="card" style="padding:0;border:none;background:none;margin-bottom:0">
                    <h3>👤 Your Information</h3>
                    <div class="form-group"><label>Your Name *</label><input type="text" name="name" value="<?= h($_POST['name'] ?? '') ?>" required></div>
                    <div class="form-group"><label>Email Address *</label><input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required><div class="hint">We'll use this to verify your claim and for listing management</div></div>
                    <div class="form-group"><label>Proof of Ownership *</label><textarea name="proof" required placeholder="Explain how we can verify you own/manage this business. For example:&#10;- I'm the owner, my email matches the business website&#10;- I can provide a business registration number&#10;- I can verify via a phone call to the listed number"><?= h($_POST['proof'] ?? '') ?></textarea></div>
                </div>
                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:16px">
                    <a href="/directory/<?= h($listing['slug']) ?>" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">📤 Submit Claim</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
