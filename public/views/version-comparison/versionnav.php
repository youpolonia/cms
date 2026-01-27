<?php
class VersionNav {
    public static function render($currentVersion, $availableVersions) {
?>        <div class="version-nav">
            <div class="nav-buttons">
                <button class="nav-btn prev-btn" <?= $currentVersion['has_prev'] ? '' : 'disabled' ?>>&larr; Previous</button>
                <select class="version-select">
                    <?php foreach ($availableVersions as $version): ?>                        <option value="<?= $version['id'] ?>" <?= $version['id'] == $currentVersion['id'] ? 'selected' : '' ?>>
                            Version <?= $version['number'] ?> (<?= date('Y-m-d', $version['timestamp']) ?>)
                        </option>
                    <?php endforeach;  ?>
                </select>
                <button class="nav-btn next-btn" <?= $currentVersion['has_next'] ? '' : 'disabled' ?>>Next &rarr;</button>
            </div>
            <div class="current-version">
                Viewing: Version <?= $currentVersion['number'] ?> (<?= date('Y-m-d H:i', $currentVersion['timestamp']) ?>)
            </div>
        </div>
        <?php
    }
}
