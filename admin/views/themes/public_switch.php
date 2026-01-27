<?php
// Admin Theme Switcher View
require_once __DIR__.'/../../includes/admin_header.php';

?><div class="admin-content">
    <h1>Public Theme Switcher</h1>

    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-<?= $_SESSION['admin_message']['type'] ?>">
            <?= htmlspecialchars($_SESSION['admin_message']['text']) ?>
        </div>
        <?php unset($_SESSION['admin_message']); ?>    <?php endif; ?>
    <div class="theme-grid">
        <?php foreach ($themes as $theme): ?>
            <div class="theme-card <?= $theme === $activeTheme ? 'active' : '' ?>">
                <div class="theme-preview">
                    <img src="/themes/<?= htmlspecialchars($theme) ?>/preview.jpg" 
                         alt="<?= htmlspecialchars($theme) ?> Preview"
                         onerror="this.src='/admin/assets/images/default-theme-preview.jpg'">
                </div>
                <h3><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $theme))) ?></h3>
                <form method="POST" class="theme-form">
                    <input type="hidden" name="theme" value="<?= htmlspecialchars($theme) ?>">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <?php if ($theme === $activeTheme): ?>
                        <button type="button" class="btn btn-success" disabled>Active</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary">Activate</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.theme-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 30px;
}
.theme-card {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    text-align: center;
}
.theme-card.active {
    border-color: #4CAF50;
    background-color: #f8fff8;
}
.theme-preview img {
    max-width: 100%;
    height: 200px;
    object-fit: cover;
    border: 1px solid #eee;
}
</style>

<?php
require_once __DIR__.'/../../includes/admin_footer.php';
