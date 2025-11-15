<div class="demo-text-block" style="text-align: <?= htmlspecialchars($data['alignment'] ?? 'left') ?>">
    <textarea 
        class="demo-text-content"
        name="content"
        placeholder="Enter your text here"
    ><?= htmlspecialchars($data['content'] ?? '') ?></textarea>
</div>
