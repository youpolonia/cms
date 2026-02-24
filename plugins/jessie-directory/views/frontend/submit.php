<?php
/**
 * Jessie Directory — Public Submit Listing Form
 * URL: /directory/submit
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-listing.php';
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-category.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$categories = DirectoryCategory::getAll('active');
$submitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $email = trim($_POST['owner_email'] ?? '');
    $name = trim($_POST['owner_name'] ?? '');

    if (!$title) $errors[] = 'Business name is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!$name) $errors[] = 'Your name is required.';

    if (empty($errors)) {
        $data = $_POST;
        unset($data['csrf_token']);
        $data['status'] = 'pending';
        $id = DirectoryListing::create($data);
        $submitted = true;
        if (function_exists('cms_event')) {
            cms_event('directory.listing.submitted', ['listing_id' => $id, 'title' => $title, 'email' => $email]);
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
    <title>Submit Listing — <?= h($siteTitle ?: 'Directory') ?></title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}

        .submit-header{background:linear-gradient(135deg,rgba(99,102,241,.12) 0%,rgba(139,92,246,.08) 100%);border-bottom:1px solid var(--border);padding:32px 20px;text-align:center}
        .submit-header h1{font-size:1.6rem;font-weight:800;margin-bottom:6px}
        .submit-header p{color:var(--muted);font-size:.9rem}
        .breadcrumb{font-size:.78rem;color:var(--muted);margin-bottom:12px}
        .breadcrumb a{color:var(--muted)}

        .form-wrap{max-width:700px;margin:0 auto;padding:24px 20px}
        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px}
        .card h3{font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid var(--border)}
        .form-group{margin-bottom:14px}
        .form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text);margin-bottom:5px}
        .form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
        .form-group textarea{min-height:100px;resize:vertical}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
        @media(max-width:600px){.form-row,.form-row3{grid-template-columns:1fr}}
        .form-group .hint{font-size:.72rem;color:var(--muted);margin-top:4px}

        .btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px 28px;border-radius:10px;font-weight:600;cursor:pointer;font-size:.9rem}
        .btn-secondary{background:var(--bg-card);color:var(--text);padding:12px 20px;border-radius:10px;font-size:.85rem;font-weight:600;border:1px solid var(--border);text-decoration:none}

        .errors{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:14px;border-radius:8px;margin-bottom:16px;font-size:.85rem}
        .errors li{margin-left:16px}
        .success-box{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);border-radius:12px;padding:40px 24px;text-align:center}
        .success-box h2{font-size:1.3rem;margin-bottom:10px;color:#34d399}
        .success-box p{color:var(--muted);font-size:.9rem;margin-bottom:16px}
    </style>
</head>
<body>
    <div class="submit-header">
        <div class="breadcrumb"><a href="/directory">← Back to Directory</a></div>
        <h1>📝 Submit Your Business</h1>
        <p>Add your business to our directory. Listings are reviewed before publishing.</p>
    </div>

    <div class="form-wrap">
        <?php if ($submitted): ?>
            <div class="success-box">
                <h2>✅ Listing Submitted!</h2>
                <p>Thank you! Your listing has been submitted and will be reviewed shortly. We'll notify you once it's approved.</p>
                <a href="/directory" class="btn-secondary">← Back to Directory</a>
            </div>
        <?php else: ?>

        <?php if (!empty($errors)): ?>
            <div class="errors"><ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="post" action="/directory/submit">
            <div class="card">
                <h3>🏢 Business Information</h3>
                <div class="form-group"><label>Business Name *</label><input type="text" name="title" value="<?= h($_POST['title'] ?? '') ?>" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Category</label><select name="category_id"><option value="">— Select Category —</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= (($_POST['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= h(($c['icon'] ? $c['icon'] . ' ' : '') . $c['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label>Price Range</label><select name="price_range"><option value="">—</option><option value="$">$ Budget</option><option value="$$">$$ Moderate</option><option value="$$$">$$$ Premium</option><option value="$$$$">$$$$ Luxury</option></select></div>
                </div>
                <div class="form-group"><label>Short Description</label><input type="text" name="short_description" value="<?= h($_POST['short_description'] ?? '') ?>" maxlength="500" placeholder="One sentence about your business"><div class="hint">Max 500 characters</div></div>
                <div class="form-group"><label>Full Description</label><textarea name="description" placeholder="Tell people about your business, services, and what makes you special..."><?= h($_POST['description'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Tags</label><input type="text" name="tags" value="<?= h($_POST['tags'] ?? '') ?>" placeholder="restaurant, italian, pizza, downtown"><div class="hint">Comma-separated keywords to help people find you</div></div>
            </div>

            <div class="card">
                <h3>📍 Location & Contact</h3>
                <div class="form-group"><label>Address</label><input type="text" name="address" value="<?= h($_POST['address'] ?? '') ?>"></div>
                <div class="form-row3">
                    <div class="form-group"><label>City</label><input type="text" name="city" value="<?= h($_POST['city'] ?? '') ?>"></div>
                    <div class="form-group"><label>State/Province</label><input type="text" name="state" value="<?= h($_POST['state'] ?? '') ?>"></div>
                    <div class="form-group"><label>ZIP/Postal Code</label><input type="text" name="zip" value="<?= h($_POST['zip'] ?? '') ?>"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?= h($_POST['phone'] ?? '') ?>"></div>
                    <div class="form-group"><label>Website</label><input type="url" name="website" value="<?= h($_POST['website'] ?? '') ?>" placeholder="https://..."></div>
                </div>
            </div>

            <div class="card">
                <h3>👤 Your Information</h3>
                <div class="form-row">
                    <div class="form-group"><label>Your Name *</label><input type="text" name="owner_name" value="<?= h($_POST['owner_name'] ?? '') ?>" required></div>
                    <div class="form-group"><label>Your Email *</label><input type="email" name="owner_email" value="<?= h($_POST['owner_email'] ?? '') ?>" required><div class="hint">We'll use this for listing management and notifications</div></div>
                </div>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end">
                <a href="/directory" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">📤 Submit Listing</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
