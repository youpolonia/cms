<?php
$themes = ThemeRegistry::getAll();
$activeTheme = ThemeRegistry::getActive();
?><div class="admin-container">
    <h1>Theme Management</h1>
    
    <div class="theme-actions">
        <a href="/admin/themes/import" class="button">Import Theme</a>
    </div>

    <div class="theme-grid">
        <?php foreach ($themes as $name => $config): ?>
            <div class="theme-card <?= $name === $activeTheme ? 'active' : '' ?>">
                <div class="theme-preview" data-bg-color="<?= htmlspecialchars($config['colors']['primary'] ?? '#ffffff') ?>">
                    <?php if ($config['meta']['screenshot'] ?? false): ?>
                        <img src="/assets/images/themes/<?= basename($config['meta']['screenshot']) ?>" alt="<?= htmlspecialchars($name) ?> Preview">
                    <?php endif; ?>
                </div>
                
                <div class="theme-info">
                    <h3><?= $name ?></h3>
                    <p>Version: <?= $config['meta']['version'] ?? '1.0' ?></p>
                    <div class="theme-actions">
                        <?php if ($name !== $activeTheme): ?>
                            <a href="/admin/themes/toggle/<?= $name ?>/1" class="button">Activate</a>
                        <?php else: ?>
                            <a href="/admin/themes/toggle/<?= $name ?>/0" class="button danger">Deactivate</a>
                        <?php endif; ?>
                        <a href="/admin/themes/preview/<?= $name ?>" class="button">Preview</a>
                        <a href="/admin/themes/edit/<?= $name ?>" class="button">Edit</a>
                        <a href="/admin/themes/export/<?= $name ?>" class="button">Export</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="/assets/js/themes.js"></script>
