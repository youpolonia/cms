<div class="demo-text-block-preview" style="text-align: <?= htmlspecialchars($data['alignment'] ?? 'left') ?>">
    <div class="demo-text-content">
        <?= nl2br(htmlspecialchars($data['content'] ?? '')) ?>
    </div>
</div>
