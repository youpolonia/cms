# CMS Route Candidates

## Admin Routes

| HTTP Method | Path | Controller | Method | Source File |
|-------------|------|------------|--------|-------------|
| GET | /blog | BlogController | index | blog/index.php |
| GET | /blog/post | BlogController | post | blog/post.php |

## Admin Routes
| HTTP Method | Path | Controller | Method | Source File |
|-------------|------|------------|--------|-------------|
| GET | /admin/approval_dashboard | ApprovalDashboardController | index | admin/approval_dashboard.php |
| GET | /admin/logs/audit | AuditLogController | index | admin/logs/audit.php |
| GET,POST | /admin/company/create | CompanyController | create | admin/company/create.php |
| GET,POST | /admin/company/edit/{id} | CompanyController | edit | admin/company/edit.php |
| GET | /admin/company | CompanyController | index | admin/company/index.php |
| GET,POST | /admin/plugins/marketplace | MarketplaceController | index | admin/plugins/marketplace-ui.php |
| GET,POST | /admin/blog-admin | BlogAdminController | manage | admin/blog-admin-view.php |
| GET,POST | /admin/themes | ThemeController | index | admin/themes/index.php |
| POST | /admin/themes/switch | ThemeController | switch | admin/themes/public_switch.php |
| GET,POST | /admin/themes/variables | ThemeController | variables | admin/themes/variables.php |
| GET,POST | /admin/themes/settings | ThemeController | settings | admin/themes/settings.php |
| GET | /admin/plugins | PluginController | index | admin/plugins/index.php |
| GET | /admin/migrations | MigrationController | index | admin/migrations/index.php |
| POST | /admin/themes/create | ThemeController | create | admin/themes/create.php |
| GET | /admin/themes/lock-status | ThemeController | lockStatus | admin/themes/lock-status.php |
| GET | /admin/preview-version | VersionController | preview | admin/preview-version.php |
| POST | /admin/restore-version | VersionController | restore | admin/restore-version.php |
| GET,POST | /admin/notifications | NotificationController | index | admin/notifications/index.php |
| GET | /admin/notifications/view/{id} | NotificationController | view | admin/notifications/view.php |
| POST | /admin/notifications/schedule | NotificationController | schedule | admin/notifications/schedule.php |
| POST | /admin/notifications/save_preferences | NotificationController | savePreferences | admin/notifications/save_preferences.php |
| GET | /admin/notifications/rules | NotificationController | rules | admin/notifications/rules_listing.php |
| GET,POST | /admin/notifications/rule_edit | NotificationController | ruleEdit | admin/notifications/rule_edit.php |
| POST | /admin/notifications/rule_save | NotificationController | ruleSave | admin/notifications/rule_save.php |
| GET | /admin/analytics | AnalyticsController | index | admin/analytics/dashboard.php |
| GET | /admin/analytics/tenant | AnalyticsController | tenant | admin/analytics/tenant.php |
| GET | /admin/analytics/version_metrics | AnalyticsController | versionMetrics | admin/analytics/version_metrics.php |
| GET,POST | /admin/users/roles | UserController | roles | admin/users/roles.php |
| POST | /admin/users/store | UserController | store | admin/users/store.php |
| GET,POST | /admin/users/edit/{id} | UserController | edit | admin/users/edit.php |
| GET,POST | /admin/widgets | WidgetController | index | admin/widgets/index.php |
| POST | /admin/widgets/toggle | WidgetController | toggle | admin/widgets/toggle.php |
| GET,POST | /admin/widgets/regions | WidgetController | regions | admin/widgets/regions.php |
| GET | /admin/widgets/layout | WidgetController | layout | admin/widgets/layout.php |
| GET,POST | /admin/system/status | SystemController | status | admin/system/status.php |
| GET | /admin/system/tools | SystemController | tools | admin/system/tools.php |
| GET | /admin/system/phpinfo | SystemController | phpinfo | admin/system/phpinfo.php |
| GET | /admin/system/log-rotation | SystemController | logRotation | admin/system/log-rotation-api.php |
| GET,POST | /admin/ai-settings | AIController | settings | admin/ai-settings.php |
| POST | /admin/emergency | EmergencyController | handle | admin/emergency.php |
| GET,POST | /admin/content_approval | ContentApprovalController | index | admin/content_approval.php |
| GET,POST | /admin/gdpr-tools | GdprController | index | admin/gdpr-tools.php |
| GET,POST | /admin/cache | CacheController | index | admin/cache/index.php |
| GET,POST | /admin/modules | ModuleController | index | admin/modules.php |
| GET,POST | /admin/alerts | AlertController | index | admin/alerts/index.php |
| POST | /admin/alerts/resolve | AlertController | resolve | admin/alerts/resolve.php |
| GET,POST | /admin/tenant/branding | TenantController | branding | admin/tenant/branding.php |

Notes:
1. All controllers should be created in `admin/controllers/` directory
2. Paths with {id} indicate dynamic parameters
3. Some endpoints may need additional security middleware
4. API endpoints (returning JSON) should be moved to `/api/` namespace