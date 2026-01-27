<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();

require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

require_once CMS_ROOT . '/core/email.php';

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';

$sent = false;
$success = null;
$message = '';
$to = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $to = trim($_POST['to'] ?? '');

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $sent = true;
        $success = false;
        $message = 'Invalid email address.';
    } else {
        $subject = 'CMS Email Test';
        $body = 'This is a test email sent on ' . date('c');

        $ok = email_send([
            'to'      => $to,
            'subject' => $subject,
            'body'    => $body,
        ]);

        $sent = true;
        $success = (bool)$ok;
        $message = $ok
            ? 'Test email sent successfully.'
            : 'Failed to send test email. Check SMTP/Email settings and logs/email.log.';
    }
}
?>

<h1>Email Test</h1>

<?php if ($sent): ?>
    <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>" style="padding:12px;margin:12px 0;border:1px solid;border-radius:4px;">
        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<form method="post" action="email-test.php" style="max-width:600px;">
    <?php csrf_field(); ?>

    <div style="margin-bottom:16px;">
        <label for="to" style="display:block;margin-bottom:4px;font-weight:bold;">
            Recipient email
        </label>
        <input
            type="email"
            id="to"
            name="to"
            required
            style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;"
            value="<?php echo ($sent && !$success && $to) ? htmlspecialchars($to, ENT_QUOTES, 'UTF-8') : ''; ?>"
        >
        <small style="display:block;margin-top:4px;color:#666;">
            The email will be sent using the current Email Settings configuration.
        </small>
    </div>

    <button type="submit" style="padding:8px 16px;background:#007bff;color:white;border:none;border-radius:4px;cursor:pointer;">
        Send Test Email
    </button>
</form>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
