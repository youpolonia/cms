<?php
// Admin Routes
$router->get('/blog', 'BlogController@index');
$router->get('/blog/post', 'BlogController@post');

// Admin Dashboard Routes
$router->get('/admin/approval_dashboard', 'ApprovalDashboardController@index');
$router->get('/admin/logs/audit', 'AuditLogController@index');

// Company Management
$router->get('/admin/company', 'CompanyController@index');
$router->get('/admin/company/create', 'CompanyController@create');
$router->post('/admin/company/create', 'CompanyController@create');
$router->get('/admin/company/edit/{id}', 'CompanyController@edit');
$router->post('/admin/company/edit/{id}', 'CompanyController@edit');

// Plugin Management
$router->get('/admin/plugins', 'PluginController@index');
$router->get('/admin/plugins/marketplace', 'MarketplaceController@index');
$router->post('/admin/plugins/marketplace', 'MarketplaceController@index');

// Theme Management
$router->get('/admin/themes', 'ThemeController@index');
$router->post('/admin/themes/switch', 'ThemeController@switch');
$router->get('/admin/themes/lock-status', 'ThemeController@lockStatus');
$router->post('/admin/themes/create', 'ThemeController@create');
$router->get('/admin/themes/variables', 'ThemeController@variables');
$router->post('/admin/themes/variables', 'ThemeController@variables');
$router->get('/admin/themes/settings', 'ThemeController@settings');
$router->post('/admin/themes/settings', 'ThemeController@settings');

// Version Control
$router->get('/admin/preview-version', 'VersionController@preview');
$router->post('/admin/restore-version', 'VersionController@restore');

// Notification System
$router->get('/admin/notifications', 'NotificationController@index');
$router->get('/admin/notifications/view/{id}', 'NotificationController@view');
$router->post('/admin/notifications/schedule', 'NotificationController@schedule');
$router->post('/admin/notifications/save_preferences', 'NotificationController@savePreferences');
$router->get('/admin/notifications/rules', 'NotificationController@rules');
$router->get('/admin/notifications/rule_edit', 'NotificationController@ruleEdit');
$router->post('/admin/notifications/rule_edit', 'NotificationController@ruleEdit');
$router->post('/admin/notifications/rule_save', 'NotificationController@ruleSave');

// Analytics
$router->get('/admin/analytics', 'AnalyticsController@index');
$router->get('/admin/analytics/tenant', 'AnalyticsController@tenant');
$router->get('/admin/analytics/version_metrics', 'AnalyticsController@versionMetrics');

// User Management
$router->get('/admin/users/roles', 'UserController@roles');
$router->post('/admin/users/roles', 'UserController@roles');
$router->post('/admin/users/store', 'UserController@store');
$router->get('/admin/users/edit/{id}', 'UserController@edit');
$router->post('/admin/users/edit/{id}', 'UserController@edit');

// Widget System
$router->get('/admin/widgets', 'WidgetController@index');
$router->post('/admin/widgets/toggle', 'WidgetController@toggle');
$router->get('/admin/widgets/regions', 'WidgetController@regions');
$router->post('/admin/widgets/regions', 'WidgetController@regions');
$router->get('/admin/widgets/layout', 'WidgetController@layout');

// System Tools
$router->get('/admin/system/status', 'SystemController@status');
$router->post('/admin/system/status', 'SystemController@status');
$router->get('/admin/system/tools', 'SystemController@tools');
$router->get('/admin/system/phpinfo', 'SystemController@phpinfo');
$router->get('/admin/system/log-rotation', 'SystemController@logRotation');

// AI Settings
$router->get('/admin/ai-settings', 'AIController@settings');
$router->post('/admin/ai-settings', 'AIController@settings');

// Emergency Mode
$router->post('/admin/emergency', 'EmergencyController@handle');

// Content Management
$router->get('/admin/content_approval', 'ContentApprovalController@index');
$router->post('/admin/content_approval', 'ContentApprovalController@index');

// GDPR Tools
$router->get('/admin/gdpr-tools', 'GdprController@index');
$router->post('/admin/gdpr-tools', 'GdprController@index');

// Cache Management
$router->get('/admin/cache', 'CacheController@index');
$router->post('/admin/cache', 'CacheController@index');

// Module System
$router->get('/admin/modules', 'ModuleController@index');
$router->post('/admin/modules', 'ModuleController@index');

// Alert System
$router->get('/admin/alerts', 'AlertController@index');
$router->post('/admin/alerts', 'AlertController@index');
$router->post('/admin/alerts/resolve', 'AlertController@resolve');

// Tenant Branding
$router->get('/admin/tenant/branding', 'TenantController@branding');
$router->post('/admin/tenant/branding', 'TenantController@branding');