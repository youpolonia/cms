<?php require_once __DIR__.'/../includes/views/templates/base.php'; 
?><div class="user-preferences-container">
    <h1>User Preferences</h1>
    
    <div class="preferences-layout">
        <!-- Preferences Form -->
        <div class="preferences-form-section">
            <form id="preferences-form" method="post">
                <div class="form-group">
                    <label for="theme">Theme:</label>
                    <select id="theme" name="theme" class="preference-input">
                        <option value="light" <?= ($preferences['theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Light</option>
                        <option value="dark" <?= ($preferences['theme'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Dark</option>
                        <option value="system" <?= ($preferences['theme'] ?? 'light') === 'system' ? 'selected' : '' ?>>System Default</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="font_size">Font Size:</label>
                    <input type="range" id="font_size" name="font_size" class="preference-input" 
                           min="12" max="24" step="1" value="<?= $preferences['font_size'] ?? 16 ?>">
                    <span class="value-display"><?= $preferences['font_size'] ?? 16 ?>px</span>
                </div>

                <div class="form-group">
                    <label for="notifications">Email Notifications:</label>
                    <select id="notifications" name="notifications" class="preference-input">
                        <option value="all" <?= ($preferences['notifications'] ?? 'all') === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="important" <?= ($preferences['notifications'] ?? 'all') === 'important' ? 'selected' : '' ?>>Important Only</option>
                        <option value="none" <?= ($preferences['notifications'] ?? 'all') === 'none' ? 'selected' : '' ?>>None</option>
                    </select>
                </div>

                <button type="button" id="save-preferences" class="btn-primary">Save Preferences</button>
                <div id="status-message" class="status-message"></div>
            </form>
        </div>

        <!-- Live Preview Section -->
        <div class="preferences-preview-section">
            <h2>Live Preview</h2>
            <div id="preferences-preview" class="preview-content">
                <p><strong>Theme:</strong> <span data-pref="theme"><?= $preferences['theme'] ?? 'light' ?></span></p>
                <p><strong>Font Size:</strong> <span data-pref="font_size"><?= $preferences['font_size'] ?? 16 ?></span>px</p>
                <p><strong>Notifications:</strong> <span data-pref="notifications"><?= $preferences['notifications'] ?? 'all' ?></span></p>
            </div>
        </div>
    </div>
</div>

<script src="/js/user/preferences.js"></script>
