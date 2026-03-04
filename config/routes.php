<?php
declare(strict_types=1);

/**
 * Application Routes
 * Format: 'METHOD /path' => ['ControllerClass', 'method', ['options']]
 */

return [
    // Auth routes (no auth required)
    'GET /admin/login' => ['Admin\\AuthController', 'showLogin'],
    'POST /admin/login' => ['Admin\\AuthController', 'login', ['csrf' => true]],
    'GET /admin/logout' => ['Admin\\AuthController', 'logout'],
    'GET /admin/forgot-password' => ['Admin\\AuthController', 'showForgotPassword'],
    'POST /admin/forgot-password' => ['Admin\\AuthController', 'forgotPassword', ['csrf' => true]],
    'GET /admin/reset-password' => ['Admin\\AuthController', 'showResetPassword'],
    'POST /admin/reset-password' => ['Admin\\AuthController', 'resetPassword', ['csrf' => true]],

    // Admin routes (auth required)
    'GET /admin' => ['Admin\\DashboardController', 'index', ['auth' => true]],
    'GET /admin/dashboard' => ['Admin\\DashboardController', 'index', ['auth' => true]],

    // Pages CRUD
    'GET /admin/pages' => ['Admin\\PagesController', 'index', ['auth' => true]],
    'GET /admin/pages/create' => ['Admin\\PagesController', 'create', ['auth' => true]],
    'POST /admin/pages' => ['Admin\\PagesController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/pages/{id}/edit' => ['Admin\\PagesController', 'edit', ['auth' => true]],
    'POST /admin/pages/{id}' => ['Admin\\PagesController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/pages/{id}/delete' => ['Admin\\PagesController', 'destroy', ['auth' => true, 'csrf' => true]],

    // Articles CRUD
    'GET /admin/articles' => ['Admin\\ArticlesController', 'index', ['auth' => true]],
    'GET /admin/articles/create' => ['Admin\\ArticlesController', 'create', ['auth' => true]],
    'POST /admin/articles' => ['Admin\\ArticlesController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/articles/{id}/edit' => ['Admin\\ArticlesController', 'edit', ['auth' => true]],
    'POST /admin/articles/{id}' => ['Admin\\ArticlesController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/articles/{id}/delete' => ['Admin\\ArticlesController', 'destroy', ['auth' => true, 'csrf' => true]],
    'POST /admin/articles/preview' => ['Admin\ArticlesController', 'preview', ['auth' => true, 'csrf' => true]],

    // Contact Submissions
    'GET /admin/contact-submissions' => ['Admin\\ContactSubmissionsController', 'index', ['auth' => true]],
    'GET /admin/contact-submissions/{id}' => ['Admin\\ContactSubmissionsController', 'show', ['auth' => true]],
    'POST /admin/contact-submissions/{id}/status' => ['Admin\\ContactSubmissionsController', 'updateStatus', ['auth' => true, 'csrf' => true]],
    'POST /admin/contact-submissions/{id}/delete' => ['Admin\\ContactSubmissionsController', 'destroy', ['auth' => true, 'csrf' => true]],
    'POST /admin/contact-submissions/bulk' => ['Admin\\ContactSubmissionsController', 'bulkAction', ['auth' => true, 'csrf' => true]],

    // Categories CRUD
    'GET /admin/categories' => ['Admin\\CategoriesController', 'index', ['auth' => true]],
    'GET /admin/categories/create' => ['Admin\\CategoriesController', 'create', ['auth' => true]],
    'POST /admin/categories' => ['Admin\\CategoriesController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/categories/{id}/edit' => ['Admin\\CategoriesController', 'edit', ['auth' => true]],
    'POST /admin/categories/{id}' => ['Admin\\CategoriesController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/categories/{id}/delete' => ['Admin\\CategoriesController', 'destroy', ['auth' => true, 'csrf' => true]],

    // Users CRUD
    'GET /admin/users' => ['Admin\\UsersController', 'index', ['auth' => true]],
    'GET /admin/users/create' => ['Admin\\UsersController', 'create', ['auth' => true]],
    'POST /admin/users' => ['Admin\\UsersController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/users/{id}/edit' => ['Admin\\UsersController', 'edit', ['auth' => true]],
    'POST /admin/users/{id}' => ['Admin\\UsersController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/users/{id}/delete' => ['Admin\\UsersController', 'destroy', ['auth' => true, 'csrf' => true]],

    // Comments moderation
    'GET /admin/comments' => ['Admin\\CommentsController', 'index', ['auth' => true]],
    'POST /admin/comments/{id}/approve' => ['Admin\\CommentsController', 'approve', ['auth' => true, 'csrf' => true]],
    'POST /admin/comments/{id}/spam' => ['Admin\\CommentsController', 'spam', ['auth' => true, 'csrf' => true]],
    'POST /admin/comments/{id}/trash' => ['Admin\\CommentsController', 'trash', ['auth' => true, 'csrf' => true]],
    'POST /admin/comments/{id}/restore' => ['Admin\\CommentsController', 'restore', ['auth' => true, 'csrf' => true]],
    'POST /admin/comments/{id}/delete' => ['Admin\\CommentsController', 'destroy', ['auth' => true, 'csrf' => true]],
    'POST /admin/comments/bulk' => ['Admin\\CommentsController', 'bulkAction', ['auth' => true, 'csrf' => true]],

    // Menus CRUD
    'GET /admin/menus' => ['Admin\\MenusController', 'index', ['auth' => true]],
    'GET /admin/menus/create' => ['Admin\\MenusController', 'create', ['auth' => true]],
    'POST /admin/menus' => ['Admin\\MenusController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/menus/{id}/edit' => ['Admin\\MenusController', 'edit', ['auth' => true]],
    'POST /admin/menus/{id}' => ['Admin\\MenusController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/menus/{id}/delete' => ['Admin\\MenusController', 'destroy', ['auth' => true, 'csrf' => true]],
    'GET /admin/menus/{id}/items' => ['Admin\\MenusController', 'items', ['auth' => true]],
    'POST /admin/menus/{id}/items' => ['Admin\\MenusController', 'addItem', ['auth' => true, 'csrf' => true]],
    'POST /admin/menus/{id}/items/{itemId}/delete' => ['Admin\\MenusController', 'deleteItem', ['auth' => true, 'csrf' => true]],
    'GET /admin/menus/{id}/items/{itemId}/edit' => ['Admin\\MenusController', 'editItem', ['auth' => true]],
    'POST /admin/menus/{id}/items/{itemId}' => ['Admin\\MenusController', 'updateItem', ['auth' => true, 'csrf' => true]],
    'POST /admin/menus/{id}/items/reorder' => ['Admin\\MenusController', 'reorderItems', ['auth' => true, 'csrf' => true]],

    // Menu extended actions
    'POST /admin/menus/{id}/toggle' => ['Admin\\MenusController', 'toggleActive', ['auth' => true, 'csrf' => true]],
    'POST /admin/menus/{id}/duplicate' => ['Admin\\MenusController', 'duplicate', ['auth' => true, 'csrf' => true]],
    'POST /admin/menus/{id}/items/{itemId}/clone' => ['Admin\\MenusController', 'cloneItem', ['auth' => true, 'csrf' => true]],
    'POST /admin/menus/{id}/items/{itemId}/toggle' => ['Admin\\MenusController', 'toggleItem', ['auth' => true, 'csrf' => true]],
    'POST /admin/menus/{id}/items/bulk-delete' => ['Admin\\MenusController', 'bulkDeleteItems', ['auth' => true, 'csrf' => true]],
    'GET /admin/menus/{id}/preview' => ['Admin\\MenusController', 'preview', ['auth' => true]],
    'GET /admin/menus/{id}/export' => ['Admin\\MenusController', 'exportMenu', ['auth' => true]],
    'POST /admin/menus/import' => ['Admin\\MenusController', 'importMenu', ['auth' => true, 'csrf' => true]],

    // Widgets CRUD
    'GET /admin/widgets' => ['Admin\\WidgetsController', 'index', ['auth' => true]],
    'GET /admin/widgets/create' => ['Admin\\WidgetsController', 'create', ['auth' => true]],
    'POST /admin/widgets' => ['Admin\\WidgetsController', 'store', ['auth' => true, 'csrf' => true]],
    'POST /admin/widgets/' => ['Admin\\WidgetsController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/widgets/{id}/edit' => ['Admin\\WidgetsController', 'edit', ['auth' => true]],
    'POST /admin/widgets/{id}' => ['Admin\\WidgetsController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/widgets/{id}/toggle' => ['Admin\\WidgetsController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/widgets/{id}/delete' => ['Admin\\WidgetsController', 'destroy', ['auth' => true, 'csrf' => true]],

    // Widgets extended actions
    'POST /admin/widgets/{id}/duplicate' => ['Admin\\WidgetsController', 'duplicate', ['auth' => true, 'csrf' => true]],
    'POST /admin/widgets/bulk-delete' => ['Admin\\WidgetsController', 'bulkDelete', ['auth' => true, 'csrf' => true]],
    'GET /admin/widgets/{id}/preview' => ['Admin\\WidgetsController', 'preview', ['auth' => true]],
    'GET /admin/widgets/export' => ['Admin\\WidgetsController', 'export', ['auth' => true]],
    'POST /admin/widgets/import' => ['Admin\\WidgetsController', 'import', ['auth' => true, 'csrf' => true]],
    'POST /admin/widgets/reorder' => ['Admin\\WidgetsController', 'reorder', ['auth' => true, 'csrf' => true]],

    // Galleries CRUD
    'GET /admin/galleries' => ['Admin\\GalleriesController', 'index', ['auth' => true]],
    'GET /admin/galleries/create' => ['Admin\\GalleriesController', 'create', ['auth' => true]],
    'POST /admin/galleries' => ['Admin\\GalleriesController', 'store', ['auth' => true, 'csrf' => true]],
    'POST /admin/galleries/' => ['Admin\\GalleriesController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/galleries/{id}/edit' => ['Admin\\GalleriesController', 'edit', ['auth' => true]],
    'POST /admin/galleries/{id}' => ['Admin\\GalleriesController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/galleries/{id}/delete' => ['Admin\\GalleriesController', 'destroy', ['auth' => true, 'csrf' => true]],
    'GET /admin/galleries/{id}/images' => ['Admin\\GalleriesController', 'images', ['auth' => true]],
    'POST /admin/galleries/{id}/images/{imageId}/delete' => ['Admin\\GalleriesController', 'deleteImage', ['auth' => true, 'csrf' => true]],
    'POST /admin/galleries/{id}/upload' => ['Admin\\GalleriesController', 'upload', ['auth' => true, 'csrf' => true]],
    'POST /admin/galleries/{id}/reorder' => ['Admin\\GalleriesController', 'reorder', ['auth' => true, 'csrf' => true]],
    'POST /admin/galleries/{id}/images/{imageId}/title' => ['Admin\\GalleriesController', 'updateImageTitle', ['auth' => true, 'csrf' => true]],

    // Logs viewer
    'GET /admin/logs' => ['Admin\\LogsController', 'index', ['auth' => true]],
    'POST /admin/logs/clear' => ['Admin\\LogsController', 'clear', ['auth' => true, 'csrf' => true]],
    'GET /admin/logs/files' => ['Admin\\LogsController', 'files', ['auth' => true]],
    'GET /admin/logs/view' => ['Admin\\LogsController', 'viewFile', ['auth' => true]],

    // URL Redirects
    'GET /admin/urls' => ['Admin\\UrlsController', 'index', ['auth' => true]],
    'GET /admin/urls/create' => ['Admin\\UrlsController', 'create', ['auth' => true]],
    'POST /admin/urls' => ['Admin\\UrlsController', 'store', ['auth' => true, 'csrf' => true]],
    'POST /admin/urls/' => ['Admin\\UrlsController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/urls/{id}/edit' => ['Admin\\UrlsController', 'edit', ['auth' => true]],
    'POST /admin/urls/{id}' => ['Admin\\UrlsController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/urls/{id}/toggle' => ['Admin\\UrlsController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/urls/{id}/delete' => ['Admin\\UrlsController', 'destroy', ['auth' => true, 'csrf' => true]],

    // Search
    'GET /admin/search' => ['Admin\\SearchController', 'index', ['auth' => true]],
    'GET /admin/search/analytics' => ['Admin\\SearchController', 'analytics', ['auth' => true]],
    'POST /admin/search/clear' => ['Admin\\SearchController', 'clearLogs', ['auth' => true, 'csrf' => true]],

    // Backup
    'GET /admin/backup' => ['Admin\\BackupController', 'index', ['auth' => true]],
    'POST /admin/backup' => ['Admin\\BackupController', 'create', ['auth' => true, 'csrf' => true]],
    'POST /admin/backup/' => ['Admin\\BackupController', 'create', ['auth' => true, 'csrf' => true]],
    'GET /admin/backup/{id}/download' => ['Admin\\BackupController', 'download', ['auth' => true]],
    'POST /admin/backup/{id}/delete' => ['Admin\\BackupController', 'destroy', ['auth' => true, 'csrf' => true]],
    'POST /admin/backup/cleanup' => ['Admin\\BackupController', 'cleanup', ['auth' => true, 'csrf' => true]],

    // Content Blocks CRUD
    'GET /admin/content' => ['Admin\\ContentController', 'index', ['auth' => true]],
    'GET /admin/content/create' => ['Admin\\ContentController', 'create', ['auth' => true]],
    'POST /admin/content' => ['Admin\\ContentController', 'store', ['auth' => true, 'csrf' => true]],
    'POST /admin/content/' => ['Admin\\ContentController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/content/{id}/edit' => ['Admin\\ContentController', 'edit', ['auth' => true]],
    'POST /admin/content/{id}' => ['Admin\\ContentController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/content/{id}/toggle' => ['Admin\\ContentController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/content/{id}/delete' => ['Admin\\ContentController', 'destroy', ['auth' => true, 'csrf' => true]],

    // Content Blocks extended actions
    'POST /admin/content/{id}/duplicate' => ['Admin\\ContentController', 'duplicate', ['auth' => true, 'csrf' => true]],
    'POST /admin/content/bulk-delete' => ['Admin\\ContentController', 'bulkDelete', ['auth' => true, 'csrf' => true]],
    'GET /admin/content/{id}/preview' => ['Admin\\ContentController', 'preview', ['auth' => true]],
    'GET /admin/content/export' => ['Admin\\ContentController', 'export', ['auth' => true]],
    'POST /admin/content/import' => ['Admin\\ContentController', 'import', ['auth' => true, 'csrf' => true]],

    // Extensions
    'GET /admin/extensions' => ['Admin\\ExtensionsController', 'index', ['auth' => true]],
    'POST /admin/extensions/install' => ['Admin\\ExtensionsController', 'install', ['auth' => true, 'csrf' => true]],
    'POST /admin/extensions/install/' => ['Admin\\ExtensionsController', 'install', ['auth' => true, 'csrf' => true]],
    'POST /admin/extensions/{id}/toggle' => ['Admin\\ExtensionsController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/extensions/{id}/uninstall' => ['Admin\\ExtensionsController', 'uninstall', ['auth' => true, 'csrf' => true]],
    'GET /admin/extensions/{id}/settings' => ['Admin\\ExtensionsController', 'settings', ['auth' => true]],
    'POST /admin/extensions/{id}/settings' => ['Admin\\ExtensionsController', 'saveSettings', ['auth' => true, 'csrf' => true]],

    // Migrations
    'GET /admin/migrations' => ['Admin\\MigrationsController', 'index', ['auth' => true]],
    'POST /admin/migrations/run' => ['Admin\\MigrationsController', 'run', ['auth' => true, 'csrf' => true]],
    'POST /admin/migrations/run-single' => ['Admin\\MigrationsController', 'runSingle', ['auth' => true, 'csrf' => true]],
    'POST /admin/migrations/rollback' => ['Admin\\MigrationsController', 'rollback', ['auth' => true, 'csrf' => true]],
    'POST /admin/migrations/create' => ['Admin\\MigrationsController', 'create', ['auth' => true, 'csrf' => true]],

    // Maintenance
    'GET /admin/maintenance' => ['Admin\\MaintenanceController', 'index', ['auth' => true]],
    'POST /admin/maintenance/toggle' => ['Admin\\MaintenanceController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/maintenance/update' => ['Admin\\MaintenanceController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/maintenance/add-ip' => ['Admin\\MaintenanceController', 'addIp', ['auth' => true, 'csrf' => true]],
    'POST /admin/maintenance/remove-ip' => ['Admin\\MaintenanceController', 'removeIp', ['auth' => true, 'csrf' => true]],

    // Plugins
    'GET /admin/plugins' => ['Admin\\PluginsController', 'index', ['auth' => true]],
    'GET /admin/plugins-marketplace' => ['Admin\\PluginsController', 'index', ['auth' => true]],
    'POST /admin/plugins/install' => ['Admin\\PluginsController', 'install', ['auth' => true, 'csrf' => true]],
    'POST /admin/plugins/{id}/toggle' => ['Admin\\PluginsController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/plugins/{id}/uninstall' => ['Admin\\PluginsController', 'uninstall', ['auth' => true, 'csrf' => true]],
    'GET /admin/plugins/{slug}/settings' => ['Admin\\PluginsController', 'settings', ['auth' => true]],
    'POST /admin/plugins/{slug}/settings' => ['Admin\\PluginsController', 'saveSettings', ['auth' => true, 'csrf' => true]],

    // Modules
    'GET /admin/modules' => ['Admin\\ModulesController', 'index', ['auth' => true]],
    'GET /admin/modules/{slug}' => ['Admin\\ModulesController', 'show', ['auth' => true]],
    'POST /admin/modules/{slug}/toggle' => ['Admin\\ModulesController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/modules/refresh' => ['Admin\\ModulesController', 'refresh', ['auth' => true, 'csrf' => true]],
    'POST /admin/modules/bulk' => ['Admin\\ModulesController', 'bulkAction', ['auth' => true, 'csrf' => true]],

    // Email Campaigns (AI)
    'GET /admin/email-campaigns' => ['Admin\\EmailCampaignsController', 'index', ['auth' => true]],
    'POST /admin/email-campaigns' => ['Admin\\EmailCampaignsController', 'index', ['auth' => true, 'csrf' => true]],

    // Email Settings
    'GET /admin/email-settings' => ['Admin\\EmailSettingsController', 'index', ['auth' => true]],
    'POST /admin/email-settings' => ['Admin\\EmailSettingsController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/email-settings/test' => ['Admin\\EmailSettingsController', 'testEmail', ['auth' => true, 'csrf' => true]],

    // Email Queue
    'GET /admin/email-queue' => ['Admin\\EmailQueueController', 'index', ['auth' => true]],
    'GET /admin/email-queue/compose' => ['Admin\\EmailQueueController', 'compose', ['auth' => true]],
    'POST /admin/email-queue/send' => ['Admin\\EmailQueueController', 'send', ['auth' => true, 'csrf' => true]],
    'GET /admin/email-queue/{id}' => ['Admin\\EmailQueueController', 'view', ['auth' => true]],
    'POST /admin/email-queue/{id}/retry' => ['Admin\\EmailQueueController', 'retry', ['auth' => true, 'csrf' => true]],
    'POST /admin/email-queue/{id}/delete' => ['Admin\\EmailQueueController', 'destroy', ['auth' => true, 'csrf' => true]],
    'POST /admin/email-queue/bulk' => ['Admin\\EmailQueueController', 'bulkAction', ['auth' => true, 'csrf' => true]],
    'POST /admin/email-queue/clear' => ['Admin\\EmailQueueController', 'clearOld', ['auth' => true, 'csrf' => true]],

    // Notifications
    'GET /admin/notifications' => ['Admin\\NotificationsController', 'index', ['auth' => true]],
    'POST /admin/notifications/mark-all-read' => ['Admin\\NotificationsController', 'markAllRead', ['auth' => true, 'csrf' => true]],
    'POST /admin/notifications/delete' => ['Admin\\NotificationsController', 'delete', ['auth' => true, 'csrf' => true]],
    'POST /admin/notifications/clear-all' => ['Admin\\NotificationsController', 'clearAll', ['auth' => true, 'csrf' => true]],

    // Analytics (MVC)
    'GET /admin/analytics' => ['Admin\\AnalyticsController', 'index', ['auth' => true]],
    'GET /admin/analytics/realtime' => ['Admin\\AnalyticsController', 'realtime', ['auth' => true]],
    'GET /admin/analytics/export' => ['Admin\\AnalyticsController', 'export', ['auth' => true]],

    // Media Library
    'GET /admin/media' => ['Admin\\MediaController', 'index', ['auth' => true]],
    'GET /admin/media/upload' => ['Admin\\MediaController', 'upload', ['auth' => true]],
    'POST /admin/media' => ['Admin\\MediaController', 'store', ['auth' => true, 'csrf' => true]],
    'POST /admin/media/' => ['Admin\\MediaController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/media/{id}/edit' => ['Admin\\MediaController', 'edit', ['auth' => true]],
    'POST /admin/media/{id}' => ['Admin\\MediaController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/media/{id}/delete' => ['Admin\\MediaController', 'destroy', ['auth' => true, 'csrf' => true]],
    'POST /admin/media/bulk-delete' => ['Admin\\MediaController', 'bulkDelete', ['auth' => true, 'csrf' => true]],
    'GET /api/media/browse' => ['Admin\MediaController', 'apiBrowse', ['auth' => true]],
    'GET /admin/media/stock-search' => ['Admin\\MediaController', 'stockSearch', ['auth' => true]],
    'POST /admin/media/ai-generate' => ['Admin\\MediaController', 'aiGenerate', ['auth' => true, 'csrf' => true]],

    // Themes
    'GET /admin/themes' => ['Admin\\ThemesController', 'index', ['auth' => true]],
    'GET /admin/themes/' => ['Admin\\ThemesController', 'index', ['auth' => true]],
    'POST /admin/themes/activate' => ['Admin\\ThemesController', 'activate', ['auth' => true, 'csrf' => true]],
    'POST /admin/themes/delete' => ['Admin\\ThemesController', 'delete', ['auth' => true, 'csrf' => true]],
    'POST /admin/themes/upload' => ['Admin\\ThemesController', 'upload', ['auth' => true, 'csrf' => true]],
    'GET /admin/themes/{slug}/customize' => ['Admin\\ThemesController', 'customize', ['auth' => true]],
    'POST /admin/themes/{slug}/customize' => ['Admin\\ThemesController', 'saveCustomize', ['auth' => true, 'csrf' => true]],
    'POST /admin/themes/install-demo' => ['Admin\\ThemesController', 'installDemo', ['auth' => true, 'csrf' => true]],
    'POST /admin/themes/remove-demo' => ['Admin\\ThemesController', 'removeDemo', ['auth' => true, 'csrf' => true]],

    // Theme Editor — REMOVED 2026-02-10 (merged into Theme Studio)

    // Legacy TB3/TB4/AI Theme Builder/AI Designer routes — REMOVED 2026-02-08
    // JTB (Jessie Theme Builder) routes are in /api/jtb/* and handled by index.php
    // Legacy AI Designer routes — REMOVED 2026-02-08

    // Layout Library
    // Legacy layout-library routes — REMOVED 2026-02-08 (used TB3 tb_layout_library table)

    // n8n Integration Settings (MVC)
    'GET /admin/n8n-settings' => ['Admin\\N8nSettingsController', 'index', ['auth' => true]],
    'POST /admin/n8n-settings' => ['Admin\\N8nSettingsController', 'save', ['auth' => true, 'csrf' => true]],
    'POST /admin/n8n-settings/health' => ['Admin\\N8nSettingsController', 'healthCheck', ['auth' => true, 'csrf' => true]],
    'GET /admin/n8n-settings/workflows' => ['Admin\\N8nSettingsController', 'listWorkflows', ['auth' => true]],
    'POST /admin/n8n-settings/webhook' => ['Admin\\N8nSettingsController', 'testWebhook', ['auth' => true, 'csrf' => true]],
    'POST /admin/n8n-settings/clear-log' => ['Admin\\N8nSettingsController', 'clearLog', ['auth' => true, 'csrf' => true]],

    // n8n Event Bindings (MVC)
    'GET /admin/n8n-bindings' => ['Admin\\N8nBindingsController', 'index', ['auth' => true]],
    'POST /admin/n8n-bindings' => ['Admin\\N8nBindingsController', 'index', ['auth' => true, 'csrf' => true]],

    // Automations (MVC)
    'GET /admin/automations' => ['Admin\\AutomationsController', 'index', ['auth' => true]],
    'POST /admin/automations' => ['Admin\\AutomationsController', 'index', ['auth' => true, 'csrf' => true]],

    // Automation Rules (MVC)
    'GET /admin/automation-rules' => ['Admin\\AutomationRulesController', 'index', ['auth' => true]],
    'POST /admin/automation-rules' => ['Admin\\AutomationRulesController', 'index', ['auth' => true, 'csrf' => true]],

    // Scheduler (MVC)
    'GET /admin/scheduler' => ['Admin\\SchedulerController', 'index', ['auth' => true]],
    'GET /admin/scheduler/create' => ['Admin\\SchedulerController', 'create', ['auth' => true]],
    'POST /admin/scheduler' => ['Admin\\SchedulerController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/scheduler/{id}/edit' => ['Admin\\SchedulerController', 'edit', ['auth' => true]],
    'POST /admin/scheduler/{id}' => ['Admin\\SchedulerController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/scheduler/{id}/toggle' => ['Admin\\SchedulerController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/scheduler/{id}/run' => ['Admin\\SchedulerController', 'run', ['auth' => true, 'csrf' => true]],
    'POST /admin/scheduler/{id}/delete' => ['Admin\\SchedulerController', 'destroy', ['auth' => true, 'csrf' => true]],

    // Security Dashboard (MVC)
    'GET /admin/security' => ['Admin\\SecurityDashboardController', 'index', ['auth' => true]],
    'GET /admin/security-dashboard' => ['Admin\\SecurityDashboardController', 'index', ['auth' => true]],
    'POST /admin/security/scan' => ['Admin\\SecurityDashboardController', 'scan', ['auth' => true, 'csrf' => true]],

    // GDPR Tools (MVC)
    'GET /admin/gdpr' => ['Admin\\GdprController', 'index', ['auth' => true]],
    'GET /admin/gdpr-tools' => ['Admin\\GdprController', 'index', ['auth' => true]],
    'POST /admin/gdpr/export' => ['Admin\\GdprController', 'export', ['auth' => true, 'csrf' => true]],
    'GET /admin/gdpr/download' => ['Admin\\GdprController', 'download', ['auth' => true]],
    'POST /admin/gdpr/anonymize' => ['Admin\\GdprController', 'anonymize', ['auth' => true, 'csrf' => true]],
    'POST /admin/gdpr/delete' => ['Admin\\GdprController', 'deleteData', ['auth' => true, 'csrf' => true]],

    // Version Control (MVC)
    'GET /admin/version-control' => ['Admin\\VersionControlController', 'index', ['auth' => true]],
    'GET /admin/version-control/view' => ['Admin\\VersionControlController', 'view', ['auth' => true]],
    'GET /admin/version-control/compare' => ['Admin\\VersionControlController', 'compare', ['auth' => true]],
    'POST /admin/version-control/restore' => ['Admin\\VersionControlController', 'restore', ['auth' => true, 'csrf' => true]],
    'POST /admin/version-control/delete' => ['Admin\\VersionControlController', 'delete', ['auth' => true, 'csrf' => true]],
    'POST /admin/version-control/purge' => ['Admin\\VersionControlController', 'purge', ['auth' => true, 'csrf' => true]],

    // Profile (MVC)
    'GET /admin/profile' => ['Admin\\ProfileController', 'index', ['auth' => true]],
    'POST /admin/profile/update' => ['Admin\\ProfileController', 'update', ['auth' => true, 'csrf' => true]],
    'GET /admin/profile/password' => ['Admin\\ProfileController', 'password', ['auth' => true]],
    'POST /admin/profile/password/update' => ['Admin\\ProfileController', 'updatePassword', ['auth' => true, 'csrf' => true]],

    // Jessie Theme Builder Plugin (JTB)
    // Theme Studio
    'GET /admin/theme-studio' => ['Admin\\ThemeStudioController', 'index', ['auth' => true]],
    'GET /admin/theme-studio/preview' => ['Admin\\ThemeStudioController', 'preview', ['auth' => true]],
    'GET /api/theme-studio/schema' => ['Admin\\ThemeStudioController', 'apiSchema', ['auth' => true]],
    'GET /api/theme-studio/values' => ['Admin\\ThemeStudioController', 'apiValues', ['auth' => true]],
    'POST /api/theme-studio/save' => ['Admin\\ThemeStudioController', 'apiSave', ['auth' => true, 'csrf' => true]],
    'POST /api/theme-studio/reset' => ['Admin\\ThemeStudioController', 'apiReset', ['auth' => true, 'csrf' => true]],
    'GET /api/theme-studio/history' => ['Admin\\ThemeStudioController', 'apiHistory', ['auth' => true]],
    'POST /api/theme-studio/restore' => ['Admin\\ThemeStudioController', 'apiRestore', ['auth' => true, 'csrf' => true]],
    'POST /api/theme-studio/upload' => ['Admin\\ThemeStudioController', 'apiUpload', ['auth' => true, 'csrf' => true]],
    'POST /api/theme-studio/ai/customize' => ['Admin\\ThemeStudioController', 'aiCustomize', ['auth' => true, 'csrf' => true]],
    'POST /api/theme-studio/ai/generate-content' => ['Admin\\ThemeStudioController', 'aiGenerateContent', ['auth' => true, 'csrf' => true]],
    'POST /api/theme-studio/ai/suggest-images' => ['Admin\\ThemeStudioController', 'aiSuggestImages', ['auth' => true, 'csrf' => true]],
    'POST /api/theme-studio/ai/color-palette' => ['Admin\\ThemeStudioController', 'aiColorPalette', ['auth' => true, 'csrf' => true]],
    'GET /api/theme-studio/sections' => ['Admin\\ThemeStudioController', 'apiSections', ['auth' => true]],
    'POST /api/theme-studio/sections/save' => ['Admin\\ThemeStudioController', 'apiSectionsSave', ['auth' => true, 'csrf' => true]],
    'GET /api/theme-studio/ai/models' => ['Admin\\ThemeStudioController', 'aiModels', ['auth' => true]],

    // Theme Studio — Menu Management
    'GET /api/theme-studio/menus' => ['Admin\\ThemeStudioController', 'apiMenus', ['auth' => true]],
    'POST /api/theme-studio/menus/item' => ['Admin\\ThemeStudioController', 'apiMenuAddItem', ['auth' => true, 'csrf' => true]],
    'POST /api/theme-studio/menus/item/update' => ['Admin\\ThemeStudioController', 'apiMenuUpdateItem', ['auth' => true, 'csrf' => true]],
    'POST /api/theme-studio/menus/item/delete' => ['Admin\\ThemeStudioController', 'apiMenuDeleteItem', ['auth' => true, 'csrf' => true]],
    'POST /api/theme-studio/menus/reorder' => ['Admin\\ThemeStudioController', 'apiMenuReorder', ['auth' => true, 'csrf' => true]],

    // Visual Editor AI
    'POST /api/visual-editor/ai' => ['Admin\\ThemeStudioController', 'veAi', ['auth' => true, 'csrf' => true]],
    'POST /api/visual-editor/save-page' => ['Admin\\ThemeStudioController', 'vePageSave', ['auth' => true, 'csrf' => true]],

    // AI Theme Builder
    'GET /admin/ai-theme-builder' => ['Admin\\AiThemeBuilderController', 'index', ['auth' => true]],
    'GET /admin/ai-theme-builder/wizard' => ['Admin\\AiThemeBuilderController', 'wizard', ['auth' => true]],
    'POST /api/ai-theme-builder/generate' => ['Admin\\AiThemeBuilderController', 'generate', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/apply' => ['Admin\\AiThemeBuilderController', 'apply', ['auth' => true, 'csrf' => true]],
    'GET /admin/ai-theme-builder/preview' => ['Admin\\AiThemeBuilderController', 'preview', ['auth' => true]],
    'GET /admin/docs' => ['Admin\\DocsController', 'index', ['auth' => true]],
    'GET /api/docs/search' => ['Admin\\DocsController', 'search', ['auth' => true]],
    'POST /api/ai-theme-builder/delete' => ['Admin\\AiThemeBuilderController', 'delete', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/generate-stream' => ['Admin\\AiThemeBuilderController', 'generateStream', ['auth' => true, 'csrf' => true]],
    'GET /api/ai-theme-builder/export' => ['Admin\\AiThemeBuilderController', 'export', ['auth' => true]],
    'POST /api/ai-theme-builder/refine' => ['Admin\\AiThemeBuilderController', 'refine', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/regenerate-css' => ['Admin\\AiThemeBuilderController', 'regenerateCss', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/update-brief' => ['Admin\\AiThemeBuilderController', 'updateBrief', ['auth' => true, 'csrf' => true]],

    // AI Theme Builder — Wizard API (multi-step)
    'GET /api/ai-theme-builder/check-providers' => ['Admin\\AiThemeBuilderController', 'checkProviders', ['auth' => true]],
    'POST /api/ai-theme-builder/wizard/brief' => ['Admin\\AiThemeBuilderController', 'wizardBrief', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/layout' => ['Admin\\AiThemeBuilderController', 'wizardLayout', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/layout-stream' => ['Admin\\AiThemeBuilderController', 'wizardLayoutStream', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/page' => ['Admin\\AiThemeBuilderController', 'wizardPage', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/finalize' => ['Admin\\AiThemeBuilderController', 'wizardFinalize', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/upload-images' => ['Admin\\AiThemeBuilderController', 'wizardUploadImages', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/search-images' => ['Admin\\AiThemeBuilderController', 'searchImages', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/analyze-inspiration' => ['Admin\\AiThemeBuilderController', 'analyzeInspiration', ['auth' => true, 'csrf' => true]],

    // AI Theme Builder — Content-first wizard endpoints
    'POST /api/ai-theme-builder/wizard/content-plan' => ['Admin\\AiThemeBuilderController', 'wizardContentPlan', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/generate-content' => ['Admin\\AiThemeBuilderController', 'wizardGenerateContent', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/rewrite-content' => ['Admin\\AiThemeBuilderController', 'wizardRewriteContent', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/seo-check' => ['Admin\\AiThemeBuilderController', 'wizardSeoCheck', ['auth' => true, 'csrf' => true]],
    'POST /api/ai-theme-builder/wizard/regenerate-section' => ['Admin\\AiThemeBuilderController', 'regenerateSection', ['auth' => true, 'csrf' => true]],

    'GET /admin/jessie-theme-builder' => ['Admin\\JtbController', 'index', ['auth' => true]],
    'GET /admin/jessie-theme-builder/edit/{id}' => ['Admin\\JtbController', 'edit', ['auth' => true]],

    // JTB API Routes
    'GET /api/jtb/modules' => ['Admin\\JtbApiController', 'modules', ['auth' => true]],
    'GET /api/jtb/load/{id}' => ['Admin\\JtbApiController', 'load', ['auth' => true]],
    'POST /api/jtb/save' => ['Admin\\JtbApiController', 'save', ['auth' => true, 'csrf' => true]],
    'POST /api/jtb/render' => ['Admin\\JtbApiController', 'render', ['auth' => true]],
    'POST /api/jtb/upload' => ['Admin\\JtbApiController', 'upload', ['auth' => true, 'csrf' => true]],

    // JTB AI Routes (delegated to plugin router)






    // AI Chat Assistant
    'GET /admin/ai-chat' => ['Admin\\AiChatController', 'index', ['auth' => true]],
    'POST /api/ai-chat/send' => ['Admin\\AiChatController', 'send', ['auth' => true]],
    'POST /api/ai-chat/clear' => ['Admin\\AiChatController', 'clear', ['auth' => true]],
    
    // AI Tutor
    'GET /admin/ai-tutor' => ['Admin\\AiTutorController', 'index', ['auth' => true]],
    'POST /api/ai-tutor/ask' => ['Admin\\AiTutorController', 'ask', ['auth' => true]],
    'POST /api/ai-tutor/clear' => ['Admin\\AiTutorController', 'clear', ['auth' => true]],

    // White Label
    'GET /admin/white-label' => ['Admin\\WhiteLabelController', 'index', ['auth' => true]],
    'POST /admin/white-label/save' => ['Admin\\WhiteLabelController', 'save', ['auth' => true, 'csrf' => true]],

    // Languages (i18n)
    'GET /admin/languages' => ['Admin\\LanguagesController', 'index', ['auth' => true]],
    'POST /admin/languages/toggle/{id}' => ['Admin\\LanguagesController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/languages/default/{id}' => ['Admin\\LanguagesController', 'setDefault', ['auth' => true, 'csrf' => true]],
    'POST /admin/languages/add' => ['Admin\\LanguagesController', 'add', ['auth' => true, 'csrf' => true]],
    'POST /api/languages/set' => ['Admin\\LanguagesController', 'setLocale', ['auth' => true]],

    // API Keys management
    'GET /admin/api-keys' => ['Admin\\ApiKeysController', 'index', ['auth' => true]],
    'POST /admin/api-keys/create' => ['Admin\\ApiKeysController', 'create', ['auth' => true, 'csrf' => true]],
    'POST /admin/api-keys/{id}/toggle' => ['Admin\\ApiKeysController', 'toggle', ['auth' => true, 'csrf' => true]],
    'POST /admin/api-keys/{id}/delete' => ['Admin\\ApiKeysController', 'delete', ['auth' => true, 'csrf' => true]],

    // === PUBLIC REST API v1 ===
    // Read-only public API, no auth required, CORS enabled
    
    // Site information
    'GET /api/v1/site' => ['Api\\SiteApiController', 'index', ['cors' => true]],
    'OPTIONS /api/v1/site' => ['Api\\SiteApiController', 'index', ['cors' => true]],
    
    // Articles
    'GET /api/v1/articles' => ['Api\\ArticlesApiController', 'index', ['cors' => true]],
    'GET /api/v1/articles/{slug}' => ['Api\\ArticlesApiController', 'show', ['cors' => true]],
    'OPTIONS /api/v1/articles' => ['Api\\ArticlesApiController', 'index', ['cors' => true]],
    'OPTIONS /api/v1/articles/{slug}' => ['Api\\ArticlesApiController', 'show', ['cors' => true]],
    
    // Pages
    'GET /api/v1/pages' => ['Api\\PagesApiController', 'index', ['cors' => true]],
    'GET /api/v1/pages/{slug}' => ['Api\\PagesApiController', 'show', ['cors' => true]],
    'OPTIONS /api/v1/pages' => ['Api\\PagesApiController', 'index', ['cors' => true]],
    'OPTIONS /api/v1/pages/{slug}' => ['Api\\PagesApiController', 'show', ['cors' => true]],
    
    // Menus
    'GET /api/v1/menus' => ['Api\\MenusApiController', 'index', ['cors' => true]],
    'GET /api/v1/menus/{location}' => ['Api\\MenusApiController', 'show', ['cors' => true]],
    'OPTIONS /api/v1/menus' => ['Api\\MenusApiController', 'index', ['cors' => true]],
    'OPTIONS /api/v1/menus/{location}' => ['Api\\MenusApiController', 'show', ['cors' => true]],

    // === REST API v1 — WRITE ENDPOINTS (Bearer token auth) ===

    // Articles — write
    'POST /api/v1/articles' => ['Api\\ArticlesApiController', 'store', ['cors' => true]],
    'PUT /api/v1/articles/{slug}' => ['Api\\ArticlesApiController', 'update', ['cors' => true]],
    'DELETE /api/v1/articles/{slug}' => ['Api\\ArticlesApiController', 'destroy', ['cors' => true]],

    // Pages — write
    'POST /api/v1/pages' => ['Api\\PagesApiController', 'store', ['cors' => true]],
    'PUT /api/v1/pages/{slug}' => ['Api\\PagesApiController', 'update', ['cors' => true]],
    'DELETE /api/v1/pages/{slug}' => ['Api\\PagesApiController', 'destroy', ['cors' => true]],

    // FRONT-END ROUTES (Public)

    // Home page
    'GET /' => ['Front\\HomeController', 'index'],
    
    // Static pages
    'GET /page/{slug}' => ['Front\\PageController', 'show'],
    
    // Blog/Articles
    'GET /articles' => ['Front\\ArticlesController', 'index'],
    'GET /article/{slug}' => ['Front\\ArticleController', 'show'],
    'GET /blog' => ['Front\\ArticlesController', 'index'],
    
    // Gallery
    'GET /gallery' => ['Front\\GalleryController', 'index'],
    
    // Features page — rendered through active theme via PageController
    'GET /features' => ['Front\\PageController', 'show'],

    // Contact form submission (AJAX)
    'POST /api/contact' => ['Front\\ContactController', 'submit', ['csrf' => true]],

    // Frontend user auth
    'GET /register' => ['Front\\UserController', 'showRegister'],
    'POST /register' => ['Front\\UserController', 'register', ['csrf' => true]],
    'GET /login' => ['Front\\UserController', 'showLogin'],
    'POST /login' => ['Front\\UserController', 'login', ['csrf' => true]],
    'GET /logout' => ['Front\\UserController', 'logout'],
    'GET /forgot-password' => ['Front\\UserController', 'showForgotPassword'],
    'POST /forgot-password' => ['Front\\UserController', 'forgotPassword', ['csrf' => true]],
    'GET /reset-password' => ['Front\\UserController', 'showResetPassword'],
    'POST /reset-password' => ['Front\\UserController', 'resetPassword', ['csrf' => true]],
    'GET /verify-email' => ['Front\\UserController', 'verifyEmail'],
    'POST /resend-verification' => ['Front\\UserController', 'resendVerification', ['csrf' => true]],
    'GET /account/delete' => ['Front\\UserController', 'showDeleteAccount'],
    'POST /account/delete' => ['Front\\UserController', 'deleteAccount', ['csrf' => true]],
    'GET /account/export' => ['Front\\UserController', 'exportData'],
    'POST /comment' => ['Front\\CommentController', 'store', ['csrf' => true]],
    'GET /api/comments' => ['Front\\CommentController', 'list'],
    'POST /newsletter/subscribe' => ['Front\\NewsletterController', 'subscribe', ['csrf' => true]],
    'GET /account' => ['Front\\UserController', 'account'],
    'POST /account/update' => ['Front\\UserController', 'updateProfile', ['csrf' => true]],
    'POST /account/password' => ['Front\\UserController', 'changePassword', ['csrf' => true]],

    // Frontend search
    'GET /search' => ['Front\\SearchController', 'index'],

    // RSS feed
    'GET /feed' => ['Front\\FeedController', 'rss'],
    'GET /rss' => ['Front\\FeedController', 'rss'],
    'GET /feed.xml' => ['Front\\FeedController', 'rss'],

    // robots.txt + favicon (dynamic)
    'GET /robots.txt' => ['Front\\SeoController', 'robots'],
    'GET /favicon.ico' => ['Front\\SeoController', 'favicon'],
    
    // A/B Testing
    'GET /admin/ab-testing' => ['Admin\\AbTestController', 'index', ['auth' => true, 'role' => 'admin']],
    'GET /admin/ab-testing/create' => ['Admin\\AbTestController', 'create', ['auth' => true, 'role' => 'admin']],
    'POST /admin/ab-testing/store' => ['Admin\\AbTestController', 'store', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/ab-testing/{id}/edit' => ['Admin\\AbTestController', 'edit', ['auth' => true, 'role' => 'admin']],
    'POST /admin/ab-testing/{id}/update' => ['Admin\\AbTestController', 'update', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/ab-testing/{id}/toggle' => ['Admin\\AbTestController', 'toggle', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/ab-testing/{id}/complete' => ['Admin\\AbTestController', 'complete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/ab-testing/{id}/delete' => ['Admin\\AbTestController', 'delete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/ab-testing/{id}/results' => ['Admin\\AbTestController', 'results', ['auth' => true, 'role' => 'admin']],
    'GET /api/ab-tests' => ['Admin\\AbTestController', 'apiList'],
    'POST /api/ab-track' => ['Admin\\AbTestController', 'apiTrack'],

    // Content Calendar
    'GET /admin/content-calendar' => ['Admin\\ContentCalendarController', 'index', ['auth' => true, 'role' => 'editor']],

    // Shop frontend
    'GET /shop' => ['Front\\ShopController', 'index'],
    'GET /shop/category/{slug}' => ['Front\\ShopController', 'category'],
    'POST /shop/review/submit' => ['Front\\ShopController', 'submitReview', ['csrf' => true]],
    'POST /shop/review/{id}/helpful' => ['Front\\ShopController', 'reviewHelpful', ['csrf' => true]],
    // Digital downloads
    'GET /shop/download/{token}' => ['Front\\ShopController', 'download'],
    // Wishlist
    'GET /shop/wishlist' => ['Front\\ShopController', 'wishlist'],
    'POST /shop/wishlist/toggle' => ['Front\\ShopController', 'wishlistToggle', ['csrf' => true]],
    'POST /shop/wishlist/remove' => ['Front\\ShopController', 'wishlistRemove', ['csrf' => true]],

    'GET /shop/{slug}' => ['Front\\ShopController', 'product'],
    'GET /cart' => ['Front\\ShopController', 'cart'],
    'POST /cart/add' => ['Front\\ShopController', 'addToCart', ['csrf' => true]],
    'POST /cart/update' => ['Front\\ShopController', 'updateCart', ['csrf' => true]],
    'POST /cart/remove' => ['Front\\ShopController', 'removeFromCart', ['csrf' => true]],
    'GET /checkout' => ['Front\\ShopController', 'checkout'],
    'POST /checkout' => ['Front\\ShopController', 'processCheckout', ['csrf' => true]],
    'GET /order/thank-you/{number}' => ['Front\\ShopController', 'thankYou'],

    // Shop admin
    'GET /admin/shop' => ['Admin\\ShopController', 'dashboard', ['auth' => true, 'role' => 'admin']],
    'GET /admin/shop/products' => ['Admin\\ShopController', 'products', ['auth' => true, 'role' => 'admin']],
    'GET /admin/shop/products/create' => ['Admin\\ShopController', 'productCreate', ['auth' => true, 'role' => 'admin']],
    'GET /admin/shop/products/{id}/edit' => ['Admin\\ShopController', 'productEdit', ['auth' => true, 'role' => 'admin']],
    'POST /admin/shop/products/store' => ['Admin\\ShopController', 'productStore', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/products/{id}/update' => ['Admin\\ShopController', 'productUpdate', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/products/{id}/delete' => ['Admin\\ShopController', 'productDelete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/shop/categories' => ['Admin\\ShopController', 'categories', ['auth' => true, 'role' => 'admin']],
    'POST /admin/shop/categories/store' => ['Admin\\ShopController', 'categoryStore', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/categories/{id}/update' => ['Admin\\ShopController', 'categoryUpdate', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/categories/{id}/delete' => ['Admin\\ShopController', 'categoryDelete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/shop/orders' => ['Admin\\ShopController', 'orders', ['auth' => true, 'role' => 'admin']],
    'GET /admin/shop/orders/{id}' => ['Admin\\ShopController', 'orderView', ['auth' => true, 'role' => 'admin']],
    'POST /admin/shop/orders/{id}/status' => ['Admin\\ShopController', 'orderUpdateStatus', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/orders/{id}/tracking' => ['Admin\\ShopController', 'orderUpdateTracking', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/shop/orders/{id}/invoice' => ['Admin\\ShopController', 'orderInvoice', ['auth' => true, 'role' => 'admin']],
    'POST /admin/shop/digital-upload' => ['Admin\\ShopController', 'digitalUpload', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/shop/settings' => ['Admin\\ShopController', 'settings', ['auth' => true, 'role' => 'admin']],
    'POST /admin/shop/settings' => ['Admin\\ShopController', 'settingsSave', ['auth' => true, 'csrf' => true, 'role' => 'admin']],

    // Product Reviews
    'GET /admin/shop/reviews' => ['Admin\\ShopController', 'reviews', ['auth' => true, 'role' => 'admin']],
    'POST /admin/shop/reviews/{id}/approve' => ['Admin\\ShopController', 'reviewApprove', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/reviews/{id}/reject' => ['Admin\\ShopController', 'reviewReject', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/reviews/{id}/delete' => ['Admin\\ShopController', 'reviewDelete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/reviews/{id}/reply' => ['Admin\\ShopController', 'reviewReply', ['auth' => true, 'csrf' => true, 'role' => 'admin']],

    // Shop AI
    'POST /api/shop/ai/generate' => ['Admin\\ShopController', 'aiGenerate', ['auth' => true]],
    'POST /api/shop/ai/seo' => ['Admin\\ShopController', 'aiSeo', ['auth' => true]],
    'POST /api/shop/ai/price' => ['Admin\\ShopController', 'aiPrice', ['auth' => true]],
    'POST /api/shop/ai/review-summary' => ['Admin\\ShopController', 'aiReviewSummary', ['auth' => true]],
    'POST /api/shop/ai/seo-analyze' => ['Admin\\ShopController', 'aiSeoAnalyze', ['auth' => true]],
    'POST /api/shop/ai/keywords' => ['Admin\\ShopController', 'aiKeywords', ['auth' => true]],
    'POST /api/shop/ai/rewrite' => ['Admin\\ShopController', 'aiRewrite', ['auth' => true]],
    'POST /api/shop/ai/category-description' => ['Admin\\ShopController', 'aiCategoryDescription', ['auth' => true]],
    'POST /api/shop/ai/bulk-seo-scan' => ['Admin\\ShopController', 'aiBulkSeoScan', ['auth' => true]],
    'POST /api/shop/ai/bulk-generate-seo' => ['Admin\\ShopController', 'aiBulkGenerateSeo', ['auth' => true]],
    'POST /api/shop/ai/bulk-rewrite' => ['Admin\\ShopController', 'aiBulkRewrite', ['auth' => true]],

    // Shop AI Image Processing
    'POST /api/shop/ai/remove-bg' => ['Admin\\ShopController', 'aiRemoveBg', ['auth' => true]],
    'POST /api/shop/ai/alt-text' => ['Admin\\ShopController', 'aiAltText', ['auth' => true]],
    'POST /api/shop/ai/enhance-image' => ['Admin\\ShopController', 'aiEnhanceImage', ['auth' => true]],
    'POST /api/shop/ai/generate-image' => ['Admin\\ShopController', 'aiGenerateImage', ['auth' => true]],
    'POST /api/shop/ai/process-images' => ['Admin\\ShopController', 'aiProcessImages', ['auth' => true]],

    // Shop SEO Dashboard
    'GET /admin/shop/seo' => ['Admin\\ShopController', 'seo', ['auth' => true, 'role' => 'admin']],

    // ─── DROPSHIPPING ───
    'GET /admin/dropshipping' => ['Admin\\DropshippingController', 'dashboard', ['auth' => true, 'role' => 'admin']],
    'GET /admin/dropshipping/suppliers' => ['Admin\\DropshippingController', 'suppliers', ['auth' => true, 'role' => 'admin']],
    'GET /admin/dropshipping/suppliers/create' => ['Admin\\DropshippingController', 'supplierCreate', ['auth' => true, 'role' => 'admin']],
    'GET /admin/dropshipping/suppliers/{id}/edit' => ['Admin\\DropshippingController', 'supplierEdit', ['auth' => true, 'role' => 'admin']],
    'POST /admin/dropshipping/suppliers/store' => ['Admin\\DropshippingController', 'supplierStore', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/dropshipping/suppliers/{id}/update' => ['Admin\\DropshippingController', 'supplierUpdate', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/dropshipping/suppliers/{id}/delete' => ['Admin\\DropshippingController', 'supplierDelete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/dropshipping/products' => ['Admin\\DropshippingController', 'products', ['auth' => true, 'role' => 'admin']],
    'GET /admin/dropshipping/import' => ['Admin\\DropshippingController', 'import', ['auth' => true, 'role' => 'admin']],
    'GET /admin/dropshipping/price-rules' => ['Admin\\DropshippingController', 'priceRules', ['auth' => true, 'role' => 'admin']],
    'POST /admin/dropshipping/price-rules/store' => ['Admin\\DropshippingController', 'priceRuleStore', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/dropshipping/price-rules/{id}/update' => ['Admin\\DropshippingController', 'priceRuleUpdate', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/dropshipping/price-rules/{id}/delete' => ['Admin\\DropshippingController', 'priceRuleDelete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/dropshipping/orders' => ['Admin\\DropshippingController', 'orders', ['auth' => true, 'role' => 'admin']],

    // Dropshipping API
    'POST /api/dropshipping/import-url' => ['Admin\\DropshippingController', 'apiImportUrl', ['auth' => true]],
    'POST /api/dropshipping/import-batch' => ['Admin\\DropshippingController', 'apiImportBatch', ['auth' => true]],
    'POST /api/dropshipping/import-csv' => ['Admin\\DropshippingController', 'apiImportCsv', ['auth' => true]],
    'POST /api/dropshipping/link-product' => ['Admin\\DropshippingController', 'apiLinkProduct', ['auth' => true]],
    'POST /api/dropshipping/unlink-product' => ['Admin\\DropshippingController', 'apiUnlinkProduct', ['auth' => true]],
    'POST /api/dropshipping/calculate-price' => ['Admin\\DropshippingController', 'apiCalculatePrice', ['auth' => true]],

    // Dropshipping Faza 2: Orders & Sync
    'GET /admin/dropshipping/orders/{id}' => ['Admin\\DropshippingController', 'orderDetail', ['auth' => true, 'role' => 'admin']],
    'POST /admin/dropshipping/orders/{id}/update' => ['Admin\\DropshippingController', 'orderUpdateStatus', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /api/dropshipping/forward-order' => ['Admin\\DropshippingController', 'apiForwardOrder', ['auth' => true]],
    'POST /api/dropshipping/sync-all' => ['Admin\\DropshippingController', 'apiSyncAll', ['auth' => true]],
    'POST /api/dropshipping/sync-product' => ['Admin\\DropshippingController', 'apiSyncProduct', ['auth' => true]],
    'POST /api/dropshipping/sync-tracking' => ['Admin\\DropshippingController', 'apiSyncTracking', ['auth' => true]],

    // Dropshipping Faza 3: AI Research
    'GET /admin/dropshipping/research' => ['Admin\\DropshippingController', 'research', ['auth' => true, 'role' => 'admin']],
    'POST /api/dropshipping/ai-scout' => ['Admin\\DropshippingController', 'apiAiScout', ['auth' => true]],
    'POST /api/dropshipping/ai-niches' => ['Admin\\DropshippingController', 'apiAiNiches', ['auth' => true]],
    'POST /api/dropshipping/ai-competition' => ['Admin\\DropshippingController', 'apiAiCompetition', ['auth' => true]],
    'POST /api/dropshipping/profit-calc' => ['Admin\\DropshippingController', 'apiProfitCalc', ['auth' => true]],
    'POST /api/dropshipping/ai-trends' => ['Admin\\DropshippingController', 'apiAiTrends', ['auth' => true]],
    'POST /api/dropshipping/optimize-listing' => ['Admin\\DropshippingController', 'apiOptimizeListing', ['auth' => true]],

    // Dropshipping Faza 4: Settings
    'GET /admin/dropshipping/settings' => ['Admin\\DropshippingController', 'settings', ['auth' => true, 'role' => 'admin']],
    'POST /admin/dropshipping/settings/save' => ['Admin\\DropshippingController', 'settingsSave', ['auth' => true, 'csrf' => true, 'role' => 'admin']],

    // CRM
    'GET /admin/crm' => ['Admin\\CrmController', 'dashboard', ['auth' => true, 'role' => 'admin']],
    'GET /admin/crm/contacts' => ['Admin\\CrmController', 'contacts', ['auth' => true, 'role' => 'admin']],
    'GET /admin/crm/contacts/create' => ['Admin\\CrmController', 'contactCreate', ['auth' => true, 'role' => 'admin']],
    'POST /admin/crm/contacts/store' => ['Admin\\CrmController', 'contactStore', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/crm/contacts/{id}' => ['Admin\\CrmController', 'contactView', ['auth' => true, 'role' => 'admin']],
    'GET /admin/crm/contacts/{id}/edit' => ['Admin\\CrmController', 'contactEdit', ['auth' => true, 'role' => 'admin']],
    'POST /admin/crm/contacts/{id}/update' => ['Admin\\CrmController', 'contactUpdate', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/crm/contacts/{id}/delete' => ['Admin\\CrmController', 'contactDelete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/crm/activities/add' => ['Admin\\CrmController', 'activityAdd', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/crm/activities/{id}/complete' => ['Admin\\CrmController', 'activityComplete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/crm/pipeline' => ['Admin\\CrmController', 'pipeline', ['auth' => true, 'role' => 'admin']],
    'POST /admin/crm/deals/create' => ['Admin\\CrmController', 'dealCreate', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/crm/deals/{id}/update' => ['Admin\\CrmController', 'dealUpdate', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/crm/deals/{id}/delete' => ['Admin\\CrmController', 'dealDelete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/crm/import' => ['Admin\\CrmController', 'importPage', ['auth' => true, 'role' => 'admin']],
    'POST /admin/crm/import' => ['Admin\\CrmController', 'importFromSubmissions', ['auth' => true, 'csrf' => true, 'role' => 'admin']],

    // Social Media Manager
    'GET /admin/social-media' => ['Admin\\SocialMediaController', 'dashboard', ['auth' => true, 'role' => 'admin']],
    'GET /admin/social-media/accounts' => ['Admin\\SocialMediaController', 'accounts', ['auth' => true, 'role' => 'admin']],
    'GET /admin/social-media/connect/{platform}' => ['Admin\\SocialMediaController', 'connect', ['auth' => true, 'role' => 'admin']],
    'GET /admin/social-media/callback/{platform}' => ['Admin\\SocialMediaController', 'callback', ['auth' => true, 'role' => 'admin']],
    'GET /admin/social-media/calendar' => ['Admin\\SocialMediaController', 'calendar', ['auth' => true, 'role' => 'admin']],
    'POST /admin/social-media/generate' => ['Admin\\SocialMediaController', 'generate', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/social-media/schedule' => ['Admin\\SocialMediaController', 'schedule', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/social-media/publish/{id}' => ['Admin\\SocialMediaController', 'publish', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/social-media/delete/{id}' => ['Admin\\SocialMediaController', 'delete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/social-media/settings' => ['Admin\\SocialMediaController', 'settings', ['auth' => true, 'csrf' => true, 'role' => 'admin']],

    // AI Chatbot
    'POST /api/chat' => ['Front\\ChatController', 'message'],
    'GET /api/chat/config' => ['Front\\ChatController', 'config'],
    'GET /admin/chat-settings' => ['Admin\\ChatSettingsController', 'index', ['auth' => true, 'role' => 'admin']],
    'POST /admin/chat-settings' => ['Admin\\ChatSettingsController', 'save', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/chat-settings/sessions' => ['Admin\\ChatSettingsController', 'sessions', ['auth' => true, 'role' => 'admin']],

    // System updates
    'GET /admin/updates' => ['Admin\\UpdateController', 'index', ['auth' => true, 'role' => 'admin']],
    'POST /admin/updates/check' => ['Admin\\UpdateController', 'check', ['auth' => true, 'csrf' => true, 'role' => 'admin']],

    // Form Builder
    'POST /form/{slug}' => ['Front\\FormController', 'submit', ['csrf' => true]],
    'GET /admin/form-builder' => ['Admin\\FormBuilderController', 'index', ['auth' => true, 'role' => 'admin']],
    'GET /admin/form-builder/create' => ['Admin\\FormBuilderController', 'create', ['auth' => true, 'role' => 'admin']],
    'GET /admin/form-builder/edit/{id}' => ['Admin\\FormBuilderController', 'edit', ['auth' => true, 'role' => 'admin']],
    'POST /admin/form-builder/store' => ['Admin\\FormBuilderController', 'store', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/form-builder/update/{id}' => ['Admin\\FormBuilderController', 'update', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/form-builder/delete/{id}' => ['Admin\\FormBuilderController', 'delete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/form-builder/submissions/{id}' => ['Admin\\FormBuilderController', 'submissions', ['auth' => true, 'role' => 'admin']],
    'GET /admin/form-builder/export/{id}' => ['Admin\\FormBuilderController', 'exportCsv', ['auth' => true, 'role' => 'admin']],
    'POST /admin/form-builder/mark-read/{id}' => ['Admin\\FormBuilderController', 'markRead', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /form-embed/{slug}.js' => ['Front\\FormController', 'embed'],

    // Pop-ups
    'GET /admin/popups' => ['Admin\\PopupsController', 'index', ['auth' => true, 'role' => 'admin']],
    'GET /admin/popups/create' => ['Admin\\PopupsController', 'create', ['auth' => true, 'role' => 'admin']],
    'GET /admin/popups/{id}/edit' => ['Admin\\PopupsController', 'edit', ['auth' => true, 'role' => 'admin']],
    'POST /admin/popups/store' => ['Admin\\PopupsController', 'store', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/popups/{id}/update' => ['Admin\\PopupsController', 'update', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/popups/{id}/delete' => ['Admin\\PopupsController', 'delete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/popups/{id}/toggle' => ['Admin\\PopupsController', 'toggle', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'GET /admin/popups/{id}/submissions' => ['Admin\\PopupsController', 'submissions', ['auth' => true, 'role' => 'admin']],
    'GET /admin/popups/{id}/export' => ['Admin\\PopupsController', 'exportCsv', ['auth' => true, 'role' => 'admin']],
    'GET /api/popups' => ['Admin\\PopupsController', 'apiList'],
    'POST /api/popup-submit' => ['Admin\\PopupsController', 'apiSubmit'],
    'POST /api/popup-track' => ['Admin\\PopupsController', 'apiTrack'],

    // ─── Shop Coupons ───
    'GET /admin/shop/coupons' => ['Admin\\ShopController', 'coupons', ['auth' => true, 'role' => 'admin']],
    'GET /admin/shop/coupons/create' => ['Admin\\ShopController', 'couponCreate', ['auth' => true, 'role' => 'admin']],
    'GET /admin/shop/coupons/{id}/edit' => ['Admin\\ShopController', 'couponEdit', ['auth' => true, 'role' => 'admin']],
    'POST /admin/shop/coupons/store' => ['Admin\\ShopController', 'couponStore', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/coupons/{id}/update' => ['Admin\\ShopController', 'couponUpdate', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/coupons/{id}/delete' => ['Admin\\ShopController', 'couponDelete', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /admin/shop/coupons/{id}/toggle' => ['Admin\\ShopController', 'couponToggle', ['auth' => true, 'csrf' => true, 'role' => 'admin']],
    'POST /cart/coupon/apply' => ['Front\\ShopController', 'applyCoupon', ['csrf' => true]],
    'POST /cart/coupon/remove' => ['Front\\ShopController', 'removeCoupon', ['csrf' => true]],

    // ─── Shop Abandoned Carts ───
    'GET /admin/shop/abandoned-carts' => ['Admin\\ShopController', 'abandonedCarts', ['auth' => true, 'role' => 'admin']],
    'POST /admin/shop/abandoned-carts/send-reminders' => ['Admin\\ShopController', 'abandonedCartsSendReminders', ['auth' => true, 'csrf' => true, 'role' => 'admin']],

    // ─── Shop Analytics ───
    'GET /admin/shop/analytics' => ['Admin\\ShopController', 'analytics', ['auth' => true, 'role' => 'admin']],

    // Page slug catch-all handled by PageController via router notFound handler
];
