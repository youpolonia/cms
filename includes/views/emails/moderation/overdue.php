<?php
/**
 * Moderation Overdue Email Template
 */
?><!DOCTYPE html>
<html>
<body>
    <h1>Moderation Item Overdue (24 Hours)</h1>
    
    <p>The following content item has been in the moderation queue for over 24 hours:</p>
    
    <p><strong>Content ID:</strong> <?= htmlspecialchars($item->content_id) ?><br>
    <strong>Received At:</strong> <?= htmlspecialchars($item->received_at->format('Y-m-d H:i')) ?><br>
    <strong>Hours Overdue:</strong> <?= htmlspecialchars($hoursOverdue) ?></p>
    <p>Please review and process this item as soon as possible.</p>
    
    <a href="<?= $moderationUrl ?>" style="display: inline-block; padding: 10px 20px; background-color: #3490dc; color: white; text-decoration: none; border-radius: 4px;">
        View Moderation Item
    </a>
    
    <p>Thanks,<br>
    <?= htmlspecialchars($appName) ?></p>
</body>
</html>
