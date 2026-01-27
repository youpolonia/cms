<?php

require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../includes/contentrepository.php';
require_once __DIR__ . '/../../core/database.php';

$contentId = $id ?? 0;
$contentRepository = new ContentRepository();

try {
    $contentItem = $contentRepository->getContentById($contentId);
    if (!$contentItem) {
        throw new Exception("Content not found");
    }

    $versions = $contentRepository->getContentVersions($contentId);
    $contentItem['version_info'] = [
        'current_version' => $contentItem['current_version'],
        'total_versions' => $contentItem['total_versions']
    ];
    $contentItem['possible_states'] = $contentRepository->getPossibleStates($contentItem['lifecycle_state']);
} catch (\Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage());
    exit;
}


$pageTitle = "Edit Content: " . htmlspecialchars($contentItem['title']);

ob_start();

?><h1>Edit Content: <?php echo htmlspecialchars($contentItem['title']); ?></h1>
<div id="error-display" style="display: none; color: red; margin-bottom: 1rem; padding: 0.5rem; border: 1px solid red;">
    <h3>Validation Errors:</h3>
    <ul id="error-list"></ul>
</div>

<?php if (!empty($errors)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const errorDisplay = document.getElementById('error-display');
        const errorList = document.getElementById('error-list');
        
        errorDisplay.style.display = 'block';
        <?php foreach ($errors as $field => $message): ?>
            const li = document.createElement('li');
            li.textContent = '<?= htmlspecialchars($field) ?>: <?= htmlspecialchars($message) ?>';
            errorList.appendChild(li);
            
            // Highlight invalid field
            const fieldElement = document.querySelector('[name="<?= $field ?>"]');
            if (fieldElement) {
                fieldElement.style.border = '1px solid red';
                fieldElement.addEventListener('input', function() {
                    this.style.border = '';
                });
            }
        <?php endforeach; ?>    });
    </script>
<?php endif; ?>
<div id="lock-indicator-container"></div>

<form action="/admin/content/update/<?php echo $contentItem['id']; ?>" method="POST" id="content-form">
    <?= csrf_field(); 
?>    <input type="hidden" name="_method" value="PUT"> <!-- Optional: for true RESTful, but POST is fine -->
    
    <div style="margin-bottom: 1rem;">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($contentItem['title']); ?>" required style="width: 100%; padding: 0.5rem;">    </div>

    <div style="margin-bottom: 1rem;">
        <label for="content">Content:</label><br>
        <textarea id="content" name="content" rows="10" required style="width: 100%; padding: 0.5rem;"><?php echo htmlspecialchars($contentItem['content']); ?></textarea>
    </div>

    <div style="margin-bottom: 1rem;">
        <label for="content_type">Content Type:</label><br>
        <select id="content_type" name="content_type" required style="width: 100%; padding: 0.5rem;">
            <option value="page" <?php echo ($contentItem['content_type'] === 'page') ? 'selected' : ''; ?>>Page</option>
            <option value="post" <?php echo ($contentItem['content_type'] === 'post') ? 'selected' : ''; ?>>Post</option>
        </select>
    </div>

    <div style="margin-bottom: 1rem;">
        <label for="status">Status:</label><br>
        <select id="status" name="status" required style="width: 100%; padding: 0.5rem;">
            <option value="draft" <?php echo ($contentItem['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
            <option value="published" <?php echo ($contentItem['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
        </select>
    </div>

    <fieldset style="margin-bottom: 1rem; border: 1px solid #ccc; padding: 1rem;">
        <legend>Lifecycle Management</legend>

        <div style="margin-bottom: 1rem;">
            <label for="lifecycle_state">Content State:</label><br>
            <select id="lifecycle_state" name="lifecycle_state" style="width: 100%; padding: 0.5rem;">
                <option value="draft" <?php echo ($contentItem['lifecycle_state'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                <option value="pending_review" <?php echo ($contentItem['lifecycle_state'] === 'pending_review') ? 'selected' : ''; ?>>Pending Review</option>
                <option value="published" <?php echo ($contentItem['lifecycle_state'] === 'published') ? 'selected' : ''; ?>>Published</option>
                <option value="scheduled" <?php echo ($contentItem['lifecycle_state'] === 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                <option value="archived" <?php echo ($contentItem['lifecycle_state'] === 'archived') ? 'selected' : ''; ?>>Archived</option>
            </select>
            <small>Current state will be managed here. AJAX save on change.</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="schedule_datetime">Schedule Publication/Unpublication:</label><br>
            <input type="datetime-local" id="schedule_datetime" name="schedule_datetime" value="<?php echo htmlspecialchars($contentItem['scheduled_at'] ?? ''); ?>" style="width: 100%; padding: 0.5rem;">
            <small>Select a date and time to schedule this content.</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <h4>GDPR Status:</h4>
            <p id="gdpr_status_indicator">Loading GDPR status...</p>
            <!-- GDPR status will be loaded here via ContentLifecycleManager -->
        </div>

        <div style="margin-bottom: 1rem;">
            <h4>Version Management:</h4>
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <button type="button" id="retire_old_versions_btn" style="padding: 0.5rem 1rem;">Retire Old Versions</button>
                <button type="button" id="compare_versions_btn" style="padding: 0.5rem 1rem;">Compare Versions</button>
            </div>
            
            <!-- Version Comparison Modal -->
            <div id="versionComparisonModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; padding: 2rem;">
                <div style="background: white; padding: 2rem; max-width: 90%; max-height: 90%; overflow: auto;">
                    <h2>Version Comparison</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label for="version1_select">Version 1:</label>
                            <select id="version1_select" style="width: 100%; padding: 0.5rem;"></select>
                        </div>
                        <div>
                            <label for="version2_select">Version 2:</label>
                            <select id="version2_select" style="width: 100%; padding: 0.5rem;"></select>
                        </div>
                    </div>
                    <div id="diff_results" style="border: 1px solid #ddd; padding: 1rem; min-height: 300px;"></div>
                    <div style="margin-top: 1rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                        <button type="button" id="close_modal_btn" style="padding: 0.5rem 1rem;">Close</button>
                        <button type="button" id="save_comparison_btn" style="padding: 0.5rem 1rem; background: #2c3e50; color: white;">Save Comparison</button>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>

    <div>
        <button type="submit" style="padding: 0.75rem 1.5rem; background-color: #2c3e50; color: white; border: none; cursor: pointer;">Update Content</button>
        <a href="/admin/content" style="padding: 0.75rem 1.5rem; background-color: #7f8c8d; color: white; border: none; cursor: pointer; text-decoration: none; margin-left: 0.5rem;">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentId = <?php echo json_encode($contentItem['id']); ?>;
    const currentUserId = <?php echo json_encode($_SESSION['user_id'] ?? '0'); ?>;
    const contentForm = document.getElementById('content-form');
    const lockIndicatorContainer = document.getElementById('lock-indicator-container');
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    const scheduleDatetimeInput = document.getElementById('schedule_datetime');

?></script>

<!-- Import LockIndicator component -->
<script type="module">
import LockIndicator from '/resources/js/components/content/LockIndicator.vue';
</script>

<?php
$content = ob_get_clean(); // Use $content for consistency if layout expects it

// Adjust path to layout.php if necessary.
// Path from /var/www/html/cms/admin/content/edit.php to /var/www/html/cms/admin/views/layout.php is ../views/layout.php
require_once __DIR__ . '/../views/layout.php';
