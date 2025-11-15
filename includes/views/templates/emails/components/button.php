<?php
/**
 * Email Button Component
 * @param string $url The button URL
 * @param string $text The button text
 */
?><a href="<?php echo htmlspecialchars($url); ?>" class="button" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0;">
    <?php echo htmlspecialchars($text);  ?>
</a>
