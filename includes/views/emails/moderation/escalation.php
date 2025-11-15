/**
 * Moderation Escalation Email Template
 */
?><!DOCTYPE html>
<html>
<body>
    <h1>Moderation Item Escalation (48 Hours)</h1>
    
    <p>The following content item has been in the moderation queue for over 48 hours:</p>
    
    <p><strong>Content ID:</strong> <?= htmlspecialchars($item->content_id) ?><br>
    <strong>Assigned To:</strong> <?= htmlspecialchars($item->assigned_to->name) ?><br>
    <strong>Received At:</strong> <?= htmlspecialchars($item->received_at->format('Y-m-d H:i')) ?><br>
    <strong>Hours Overdue:</strong> <?= htmlspecialchars($hoursOverdue) ?></p>
    <p>This item
 requires immediate attention as it has exceeded the maximum allowed processing time.</p>
    
?>    <a href="<?= $moderationUrl ?>" style="display: inline-block; padding: 10px 20px; background-color: #3490dc; color: white; text-decoration: none; border-radius: 4px;">
        View Moderation Item
    </a>
    
    <p>Thanks,<br>
    <?= htmlspecialchars($appName) ?></p>
</body>
</html>
