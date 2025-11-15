<?php
/**
 * Recent Content Widget Template
 * 
 * @var array $items Array of recent content items
 * @var string $title Widget title
 * @var string $empty_message Message to show when no items
 */
?><div class="widget recent-content-widget">
    <?php if ($title): ?>
        <h3 class="widget-title"><?= htmlspecialchars($title) ?></h3>
    <?php endif;  ?>
    <?php if (!empty($items)): ?>
        <ul class="recent-content-list">
            <?php foreach ($items as $item): ?>
                <li>
                    <a href="<?= htmlspecialchars($item['url']) ?>">
                        <?= htmlspecialchars($item['title'])  ?>
                    </a>
                    <span class="date"><?= $item['date'] ?></span>
                </li>
            <?php endforeach;  ?>
        </ul>
    <?php else: ?>
        <p class="empty-message"><?= htmlspecialchars($empty_message) ?></p>
    <?php endif;  ?>
</div>
