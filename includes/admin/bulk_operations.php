<?php
/**
 * Bulk Version Operations
 * 
 * Provides UI for bulk operations on content versions:
 * - Bulk deletion
 * - Bulk restoration 
 * - Bulk metadata updates
 * 
 * @package CMS
 * @subpackage Admin
 */

require_once __DIR__ . '/version_diff.php';
require_once __DIR__ . '/../models/ContentVersionModel.php';

function renderBulkOperationsPanel(int $contentId): string {
    $model = new ContentVersionModel();
    $versions = $model->getByPageId($contentId);
    $latestVersion = $model->getLatestVersion($contentId);
    
    ob_start();
    ?><!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Bulk Version Operations</title>
        <style>
            .bulk-container {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 1200px;
                margin: 2rem auto;
                padding: 1rem;
                background: #fff;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .bulk-header {
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #eee;
            }
            
            .version-selector {
                margin-bottom: 2rem;
            }
            
            .version-list {
                max-height: 300px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            
            .version-item {
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #eee;
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            
            .version-item:hover {
                background: #f5f5f5;
            }
            
            .version-meta {
                font-size: 0.85rem;
                color: #666;
                margin-top: 0.25rem;
            }
            
            .bulk-actions {
                display: flex;
                gap: 1rem;
                margin: 2rem 0;
                padding: 1rem;
                background: #f5f5f5;
                border-radius: 4px;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                border-radius: 4px;
                cursor: pointer;
                border: 1px solid transparent;
                font-weight: 500;
            }
            
            .btn-primary {
                background: #1890ff;
                color: white;
            }
            
            .btn-danger {
                background: #ff4d4f;
                color: white;
            }
            
            .btn-secondary {
                background: #f5f5f5;
                border-color: #d9d9d9;
            }
            
            .hidden {
                display: none;
            }
            
            .confirmation-dialog {
                background: #fff8e6;
                padding: 1rem;
                border-radius: 4px;
                border-left: 3px solid #faad14;
                margin: 1rem 0;
            }
        </style>
    </head>
    <body>
        <div class="bulk-container">
            <div class="bulk-header">
                <h1>Bulk Version Operations</h1>
                <p>Select versions to perform bulk actions</p>
            </div>
            
            <div class="version-selector">
                <h3>Available Versions</h3>
                <div class="version-list" id="version-list">
                    <?php foreach ($versions as $version): ?>
                        <div class="version-item">
                            <input type="checkbox" 
                                   class="version-checkbox" 
                                   data-version-id="<?= $version['id'] ?>"
                                   <?= $version['id'] === $latestVersion['id'] ? 'disabled' : '' ?>>
                            <div>
                                <div>
                                    <strong>Version <?= $version['version_number'] ?></strong>
                                    <?php if ($version['id'] === $latestVersion['id']): ?>
                                        <span>(Current Version)</span>
                                    <?php endif; ?>
                                </div>
                                <div class="version-meta">
                                    <?= date('M j, Y g:i a', strtotime($version['created_at'])) ?>
                                    • <?= htmlspecialchars($version['author_name']) ?>
                                    <?php if ($version['is_autosave']): ?>
                                        • Autosave
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="bulk-actions">
                <button id="bulk-delete" class="btn btn-danger">Delete Selected</button>
                <button id="bulk-restore" class="btn btn-primary">Restore Selected</button>
                <button id="bulk-update" class="btn btn-secondary">Update Metadata</button>
            </div>
            
            <div id="confirmation-dialog" class="confirmation-dialog hidden">
                <h3 id="confirmation-title">⚠️ Confirm Action</h3>
                <p id="confirmation-message"></p>
                <div class="action-buttons">
                    <button id="confirm-action" class="btn btn-danger">Confirm</button>
                    <button id="cancel-action" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var contentId = <?php echo json_encode((int)$contentId); ?>;
            const bulkDeleteBtn = document.getElementById('bulk-delete');
            const bulkRestoreBtn = document.getElementById('bulk-restore');
            const bulkUpdateBtn = document.getElementById('bulk-update');
            const confirmationDialog = document.getElementById('confirmation-dialog');
            const confirmActionBtn = document.getElementById('confirm-action');
            const cancelActionBtn = document.getElementById('cancel-action');
            
            let currentAction = null;
            let selectedVersions = [];
            
            // Update selected versions array when checkboxes change
            document.querySelectorAll('.version-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        selectedVersions.push(this.dataset.versionId);
                    } else {
                        selectedVersions = selectedVersions.filter(id => id !== this.dataset.versionId);
                    }
                });
            });
            
            // Bulk delete handler
            bulkDeleteBtn.addEventListener('click', function() {
                if (selectedVersions.length === 0) {
                    alert('Please select at least one version');
                    return;
                }
                
                currentAction = 'delete';
                document.getElementById('confirmation-title').textContent = '⚠️ Confirm Deletion';
                document.getElementById('confirmation-message').textContent = 
                    `You are about to permanently delete ${selectedVersions.length} version(s). This cannot be undone.`;
                confirmationDialog.classList.remove('hidden');
            });
            
            // Bulk restore handler
            bulkRestoreBtn.addEventListener('click', function() {
                if (selectedVersions.length === 0) {
                    alert('Please select at least one version');
                    return;
                }
                
                currentAction = 'restore';
                document.getElementById('confirmation-title').textContent = '⚠️ Confirm Restoration';
                document.getElementById('confirmation-message').textContent = 
                    `You are about to restore ${selectedVersions.length} version(s). This will overwrite current content.`;
                confirmationDialog.classList.remove('hidden');
            });
            
            // Bulk update handler
            bulkUpdateBtn.addEventListener('click', function() {
                if (selectedVersions.length === 0) {
                    alert('Please select at least one version');
                    return;
                }
                
                // TODO: Implement metadata update UI
                alert('Metadata update functionality coming soon');
            });
            
            // Confirm action handler
            confirmActionBtn.addEventListener('click', function() {
                if (!currentAction || selectedVersions.length === 0) return;
                
                const endpoint = currentAction === 'delete' 
                    ? '/api/version/bulk-delete' 
                    : '/api/content/bulk-restore';
                
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        content_id: contentId,
                        version_ids: selectedVersions
                    })
                })
                .then(response => {
                    if (response.ok) {
                        alert(`${selectedVersions.length} version(s) ${currentAction}d successfully!`);
                        window.location.reload();
                    } else {
                        throw new Error(`${currentAction} failed`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(`Failed to ${currentAction} versions. Please try again.`);
                });
            });
            
            // Cancel action handler
            cancelActionBtn.addEventListener('click', function() {
                confirmationDialog.classList.add('hidden');
            });
        });
        </script>
    </body>
    </html>
    <?php return ob_get_clean();
}
