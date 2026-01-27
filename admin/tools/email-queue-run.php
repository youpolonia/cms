<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();
require_once CMS_ROOT . '/core/email.php';
require_once CMS_ROOT . '/core/database.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = \core\Database::connection();

$stmt = $pdo->prepare("SELECT * FROM email_queue WHERE status='queued' ORDER BY id ASC LIMIT 10");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$processed = 0;
$sent = 0;
$failed = 0;

foreach ($rows as $row) {
    $processed++;

    $params = [
        'to' => $row['to'] ?? $row['recipient_email'] ?? $row['recipient'] ?? '',
        'subject' => $row['subject'] ?? '',
        'body' => $row['body'] ?? $row['message'] ?? ''
    ];

    $ok = email_send($params);

    if ($ok) {
        $updateStmt = $pdo->prepare("UPDATE email_queue SET status='sent', sent_at=NOW() WHERE id=?");
        $updateStmt->execute([$row['id']]);
        $sent++;
    } else {
        $errorMsg = 'Email send failed';
        $updateStmt = $pdo->prepare("UPDATE email_queue SET status='failed', error_message=?, updated_at=NOW() WHERE id=?");
        $updateStmt->execute([$errorMsg, $row['id']]);
        $failed++;
    }
}

echo "processed: $processed\n";
echo "sent: $sent\n";
echo "failed: $failed\n";
