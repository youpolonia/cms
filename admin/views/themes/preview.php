<?php
$theme = ThemeRegistry::get($themeName);
$activeTheme = ThemeRegistry::getActive();
$themeBuilder = new ThemeBuilder($themeName);
?><div class="admin-container">
    <h1>Theme Builder: <?= $theme['meta']['name'] ?></h1>
    <div class="theme-preview-container">
        <div class="theme-info">
            <p><strong>Version:</strong> <?= $theme['meta']['version'] ?? '1.0' ?></p>
            <p><strong>Author:</strong> <?= $theme['meta']['author'] ?? 'Unknown' ?></p>
            <p><strong>Description:</strong> <?= $theme['meta']['description'] ?? '' ?></p>
            <div class="theme-actions">
                <?php if ($themeName !== $activeTheme): ?>
                    <a href="/admin/themes/toggle/<?= $themeName ?>/1" class="button">Activate This Theme</a>
                <?php endif;  ?>
                <a href="/admin/themes" class="button">Back to Themes</a>
            </div>
        </div>
        
        <div class="preview-frame">
            <?= $themeBuilder->renderPreview() 
?>        </div>
    </div>
</div>
<?php ThemeBuilder::injectBuilderScripts();
