<?php
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../../includes/scheduleservice.php';

csrf_boot('admin');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        // Validate required fields
        $required = ['title', 'message', 'user_id', 'schedule_time'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        // Parse schedule time
        $scheduleTime = new DateTime($_POST['schedule_time']);
        
        // Get service
        $scheduleService = new ScheduleService();

        // Schedule the notification using public method
        $scheduleService->scheduleNotification(
            $_POST['title'],
            $_POST['message'],
            $_POST['user_id'],
            $scheduleTime,
            $_SESSION['user_id']
        );

        // Redirect to notifications list with success message
        $_SESSION['flash_message'] = 'Notification scheduled successfully';
        header('Location: /admin/notifications');
        exit;
        
    } catch (Exception $e) {
        // Store error and redirect back to form
        $_SESSION['flash_error'] = $e->getMessage();
        header('Location: /admin/notifications/schedule');
        exit;
    }
}

// If not POST, redirect to form
header('Location: /admin/notifications/schedule');
exit;
