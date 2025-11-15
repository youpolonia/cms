<?php
// Gallery template file
// Assumes $images array is passed from controller
?><section class="gallery">
    <h1>Image Gallery</h1>

    <div class="gallery-grid">
        <?php foreach ($images as $image): ?>
<div class="gallery-item">
                <img src="<?= htmlspecialchars($image['src']) ?>" alt="<?= htmlspecialchars($image['title']) ?>">
                <p><?= htmlspecialchars($image['title']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

<a href="/" class="back-home">Back to Home</a>
</section>
