<?php
/**
 * Home page template
 * Variables available: $featuredContent
 */
?>
<article>
    <h2>Welcome to Our CMS</h2>
    <p>This is a sample home page using the theme system.</p>

    <?php if (!empty($featuredContent)): ?>
    <div class="featured">
        <?= $featuredContent ?>
    </div>
    <?php endif; ?>
</article>
