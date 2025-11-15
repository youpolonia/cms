<?php $this->extend('layout') ?>
<?php $this->section('content') ?>
<article>
        <h2>Welcome to Our CMS</h2>
        <p>This is a sample home page using the theme system.</p>
        
        <?php if (!empty($featuredContent)): ?>
<div class="featured">
                <?= $featuredContent ?>
            </div>
        <?php endif; ?>
    </article>
<?php $this->endSection();
