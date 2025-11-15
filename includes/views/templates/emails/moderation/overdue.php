<?php
/**
 * Moderation Overdue Email Template
 */
?><!DOCTYPE html>
<html>
<head>
    <title>Moderation Item Overdue</title>
</head>
<body>
    <h1>Moderation Item Overdue (24 Hours)</h1>
    
    <p>The following content item has been in the moderation queue for over 24 hours:</p>
    
    <p><strong>Content ID:</strong> <?php echo htmlspecialchars($item->content_id); ?></p>
    <p><strong>Received At:</strong> <?php echo htmlspecialchars($item->received_at->format('Y-m-d H:i')); ?></p>
    <p><strong>Hours Overdue:</strong> <?php echo htmlspecialchars($hoursOverdue); ?></p>
    <p>Please review and process this item as soon as possible.</p>
    
    <a href="<?php echo $moderationUrl; ?>" style="display: inline-block; padding: 10px 20px; background: #3490dc; color: white; text-decoration: none;">
        View Moderation Item
    </a>
    
    <p>Thanks,<br>
    <?php echo htmlspecialchars($appName); ?></p>
</body>
</html>
