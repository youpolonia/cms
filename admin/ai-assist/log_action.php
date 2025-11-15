<?php
require_once __DIR__.'/../../includes/admin_header.php';
require_once __DIR__.'/../../includes/core/aifeedbacklogger.php';
require_once __DIR__ . '/../../core/csrf.php';

$sessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $data = [
        'type' => $_POST['type'],
        'session_id' => $sessionId,
        'user_id' => $userId,
        'interaction_id' => uniqid(),
        'data' => json_decode($_POST['data'], true)
    ];

    AIFeedbackLogger::logFeedback($data);
}

header('Location: /admin/ai-assist/index.php');
exit;
