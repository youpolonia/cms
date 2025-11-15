<?php
?><div class="home-page">
    <?php if (!empty($title)): ?>
<h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
    <?php endif; ?>
    <?php if (!empty($processed_content)): ?>
<div class="markdown-content">
            <?= $processed_content ?>
        </div>
    <?php elseif (!empty($content)): ?>
<div class="fallback-content">
            <?= nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) ?>
        </div>
    <?php else: ?>
<div class="no-content">
            <p>No content available for this page</p>
        </div>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/layout.php';
