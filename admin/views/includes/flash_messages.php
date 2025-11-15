<?php if (!empty($flash_messages)): ?>
<div class="flash-messages">
    <?php foreach ($flash_messages as $message): ?>
<div class="flash-message flash-<?= htmlspecialchars($message['type']) ?>">
        <div class="flash-content">
            <?= htmlspecialchars($message['message']) ?>            <?php if (!empty($message['data'])): ?>
<div class="flash-data">
                    <?php foreach ($message['data'] as $key => $value): ?>
<span class="flash-data-item">
                            <strong><?= htmlspecialchars($key) ?>:</strong>
                            <?= htmlspecialchars(is_array($value) ? json_encode($value) : $value) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif;
