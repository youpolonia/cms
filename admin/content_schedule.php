<?php
require_once __DIR__.'/../core/auth.php';
require_once __DIR__.'/../core/contentscheduler.php';
require_once __DIR__ . '/../core/csrf.php';

$upcoming = ContentScheduler::getUpcomingSchedules(50);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $result = ContentScheduler::handlePublishRequest($_POST);
    if ($result['success']) {
        header('Location: content_schedule.php?success=1');
        exit;
    }
    $errors[] = $result['error'] ?? 'Failed to create schedule';
}

?><!DOCTYPE html>
<html>
<head>
    <title>Content Scheduler</title>
    <style>
        .schedule-list { margin: 20px 0; }
        .schedule-item { padding: 10px; border-bottom: 1px solid #eee; }
        .form-group { margin-bottom: 15px; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Content Scheduler</h1>

    <?php if (!empty($_GET['success'])): ?>
        <div class="success">Schedule created successfully</div>
    <?php endif; ?> 

    <?php foreach ($errors as $error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
    <form method="POST">
        <div class="form-group">
            <label>Content ID:</label>
            <input type="number" name="content_id" required>        </div>
        <div class="form-group">
            <label>Publish Date/Time:</label>
            <input type="datetime-local" name="publish_at" required>        </div>
        <button type="submit">Schedule Content</button>
    </form>

    <div class="schedule-list">
        <h2>Upcoming Schedules</h2>
        <?php foreach ($upcoming as $schedule): ?>
            <div class="schedule-item">
                Content #<?= $schedule['content_id'] ?> -
                <?= date('Y-m-d H:i', strtotime($schedule['publish_at'])) ?>                (<?= $schedule['status'] ?>)
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
