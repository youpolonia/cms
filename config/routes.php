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
    'POST /admin/articles/preview' => ['Admin\\ArticlesController', 'preview', ['auth' => true]],

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
    'POST /admin/galleries/{id}/reorder' => ['Admin\\GalleriesController', 'reorder', ['auth' => true]],
    'POST /admin/galleries/{id}/images/{imageId}/title' => ['Admin\\GalleriesController', 'updateImageTitle', ['auth' => true]],

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

    // Theme Editor (Visual Customizer)
    'GET /admin/theme-editor/{name}' => ['Admin\\ThemeEditorController', 'edit', ['auth' => true]],
    'POST /admin/theme-editor/{name}/save' => ['Admin\\ThemeEditorController', 'save', ['auth' => true]],

    // Legacy TB3/TB4/AI Theme Builder/AI Designer routes — REMOVED 2026-02-08
    // JTB (Jessie Theme Builder) routes are in /api/jtb/* and handled by index.php
    // Legacy AI Designer routes — REMOVED 2026-02-08

    // Layout Library
    // Legacy layout-library routes — REMOVED 2026-02-08 (used TB3 tb_layout_library table)

    // n8n Integration Settings (MVC)
    'GET /admin/n8n-settings' => ['Admin\\N8nSettingsController', 'index', ['auth' => true]],
    'POST /admin/n8n-settings' => ['Admin\\N8nSettingsController', 'save', ['auth' => true, 'csrf' => true]],
    'POST /admin/n8n-settings/health' => ['Admin\\N8nSettingsController', 'healthCheck', ['auth' => true]],
    'GET /admin/n8n-settings/workflows' => ['Admin\\N8nSettingsController', 'listWorkflows', ['auth' => true]],
    'POST /admin/n8n-settings/webhook' => ['Admin\\N8nSettingsController', 'testWebhook', ['auth' => true]],
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
    'GET /admin/jessie-theme-builder' => ['Admin\\JtbController', 'index', ['auth' => true]],
    'GET /admin/jessie-theme-builder/edit/{id}' => ['Admin\\JtbController', 'edit', ['auth' => true]],

    // JTB API Routes
    'GET /api/jtb/modules' => ['Admin\\JtbApiController', 'modules', ['auth' => true]],
    'GET /api/jtb/load/{id}' => ['Admin\\JtbApiController', 'load', ['auth' => true]],
    'POST /api/jtb/save' => ['Admin\\JtbApiController', 'save', ['auth' => true, 'csrf' => true]],
    'POST /api/jtb/render' => ['Admin\\JtbApiController', 'render', ['auth' => true]],
    'POST /api/jtb/upload' => ['Admin\\JtbApiController', 'upload', ['auth' => true, 'csrf' => true]],

    // JTB AI Routes (delegated to plugin router)

    // FRONT-END ROUTES (Public)

    // Home page
    'GET /' => ['Front\\HomeController', 'index'],
    
    // Static pages
    'GET /page/{slug}' => ['Front\\PageController', 'show'],
    
    // Blog/Articles
    'GET /articles' => ['Front\\ArticlesController', 'index'],
    'GET /article/{slug}' => ['Front\\ArticleController', 'show'],
    'GET /blog' => ['Front\\ArticlesController', 'index'],
    
    // Features page
    'GET /features' => ['Front\\FeaturesController', 'index'],
    
    // Theme Builder preview
    // Legacy TB preview routes — REMOVED 2026-02-08
];
