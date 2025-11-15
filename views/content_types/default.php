<?php
// Default content type template
// Available variables:
// $content - The content data array
// $fields - Field definitions array
// $context - Additional context data
// $userId - Current user ID (defaults to 0/guest if not provided)

// Initialize user ID with guest default if not provided
$userId = $userId ?? 0;

// Basic output structure
require_once __DIR__ . '/../../core/rolemanager.php';
$roleManager = RoleManager::getInstance();

// Filter content based on access level
$content = array_filter($content, function($value, $key) use ($roleManager, $userId) {
    if ($key === 'access_level') {
        return $roleManager->hasPermission($userId, 'view_' . $value);
    }
    return true;
}, ARRAY_FILTER_USE_BOTH);
?><div class="content-type-default">
    <?php if (!empty($content['title'])): ?>
        <h2><?= htmlspecialchars($content['title']) ?></h2>
    <?php endif;  ?>
    <?php if (!empty($content['body'])): ?>
        <div class="content-body">
            <?= $content['body']  ?>
        </div>
    <?php endif;  ?>
    <?php if (!empty($fields)): ?>
        <div class="content-fields">
            <?php foreach ($fields as $field): ?>
                <div class="field field-<?= $field['type'] ?>">
                    <label><?= htmlspecialchars($field['label']) ?></label>
                    <?php if (isset($content[$field['name']])): ?>
                        <div class="field-value">
                            <?= is_array($content[$field['name']])  ?>                                ? implode(', ',
 $content[$field['name']])
                                : $content[$field['name']] 
?>                        </div>
                    <?php endif;  ?>
                </div>
            <?php endforeach;  ?>
        </div>
    <?php endif;  ?>
</div>
