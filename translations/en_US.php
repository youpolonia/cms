<?php

return [
    // Module operations
    'module.csrf_error' => 'Invalid CSRF token',
    'module.action_success' => 'Module action processed',

    // AI Settings
    'ai.settings_saved' => 'Settings saved successfully',
    'ai.connection_success' => 'Connection successful: :response',
    'ai.connection_failed' => 'Connection failed: :error',
    'ai.generation_success' => 'Generation successful',
    'ai.generation_failed' => 'Generation failed: :error',

    // Scheduling
    'scheduling.conflict' => 'This worker already has a scheduled shift during this time period',
    'scheduling.create_failed' => 'Failed to create shift',
    'scheduling.permission_denied' => 'You do not have permission to change status to :status',
    'scheduling.update_failed' => 'Failed to update schedule: :error',
    'scheduling.delete_approved' => 'Cannot delete an approved schedule. Please contact an administrator.',
    'scheduling.delete_failed' => 'Failed to delete schedule: :error',

    // Version Management
    'version.restore_success' => 'Version restored successfully',
    'version.purge_success' => 'Purged :count versions',

    // Content Management
    'content.delete_success' => 'Content deleted successfully',
    'content.create_success' => 'Content created successfully',
    'content.update_success' => 'Content updated successfully',
    'content.error' => 'Error: :error',

    // Worker Profile
    'profile.update_success' => 'Profile updated successfully',
    'profile.password_success' => 'Password changed successfully',
    'profile.password_current_error' => 'Current password is incorrect',
    'profile.password_mismatch' => 'New passwords do not match',
    'profile.picture_success' => 'Profile picture updated',

    // Migrations
    'migration.success' => 'Migrations completed successfully',
    'migration.rollback_success' => 'Rollback completed successfully',
    'migration.invalid_action' => 'Invalid action',
    'migration.error' => 'Error: :error',

    // Content Lifecycle
    'lifecycle.state_change' => 'State changed successfully',

    // Error Logs
    'error_log.test_added' => 'Test error log added successfully',
    'error_log.deleted' => 'Error log deleted successfully',
    'error_log.cleared' => 'All test error logs cleared successfully',
    
    // Client deletion strings
    'clients.delete.title' => 'Delete Client',
    'clients.delete.heading' => 'Delete Client',
    'clients.delete.confirm_text' => 'Are you sure you want to delete client: %s?',
    'clients.delete.warning' => 'This action cannot be undone.',
    'clients.delete.confirm_button' => 'Confirm Delete',
    'clients.delete.cancel_button' => 'Cancel'
];
