<?php
declare(strict_types=1);
/** 
 * Widget Preview Template
 * Allows administrators to preview widget placement in theme regions
 */
?><div class="widget-preview-container">
    <div class="preview-controls">
        <select class="region-selector">
            <option value="">Select Region</option>
            <?php foreach ($regions as $region): ?>                <option value="<?= htmlspecialchars($region['id']) ?>">
                    <?= htmlspecialchars($region['name'])  ?>
                </option>
            <?php endforeach;  ?>
        </select>
        <button class="preview-toggle">Toggle Preview</button>
    </div>
    
    <div class="preview-area">
        <!-- Widget preview will be rendered here -->
    </div>
</div>
