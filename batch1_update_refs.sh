#!/bin/bash
# Batch-1: Update all require_once paths to lowercase
# Updates directory names and file names in includes/ paths

set +e  # Don't exit on errors, continue processing

LOG_FILE="/var/www/html/cms/batch1_update_refs.log"
echo "=== Batch-1 require_once Path Updates - $(date) ===" | tee "$LOG_FILE"

# Get list of files that need updating
FILES=$(grep -rl "require_once.*includes/[A-Z]" --include="*.php" 2>/dev/null | grep -v "\.log" | grep -v "\.bak" | sort -u)

# Counter
COUNT=0

# Process each file
for FILE in $FILES; do
    if [ ! -f "$FILE" ]; then
        continue
    fi

    echo "Processing: $FILE" | tee -a "$LOG_FILE"

    # Create backup
    cp "$FILE" "$FILE.bak_batch1"

    # Apply transformations - convert includes/ paths to lowercase
    # This handles both directory names and file names

    # Directory transformations
    sed -i \
        -e 's|includes/API/|includes/api/|g' \
        -e 's|includes/Analytics/|includes/analytics/|g' \
        -e 's|includes/Audit/|includes/audit/|g' \
        -e 's|includes/Auth/|includes/auth/|g' \
        -e 's|includes/CDN/|includes/cdn/|g' \
        -e 's|includes/Cache/|includes/cache/|g' \
        -e 's|includes/Content/|includes/content/|g' \
        -e 's|includes/Controllers/|includes/controllers/|g' \
        -e 's|includes/Cron/|includes/cron/|g' \
        -e 's|includes/Database/|includes/database/|g' \
        -e 's|includes/Deployment/|includes/deployment/|g' \
        -e 's|includes/Developer/|includes/developer/|g' \
        -e 's|includes/Editor/|includes/editor/|g' \
        -e 's|includes/Export/|includes/export/|g' \
        -e 's|includes/Federation/|includes/federation/|g' \
        -e 's|includes/Http/|includes/http/|g' \
        -e 's|includes/Media/|includes/media/|g' \
        -e 's|includes/Middleware/|includes/middleware/|g' \
        -e 's|includes/Models/|includes/models/|g' \
        -e 's|includes/Monitoring/|includes/monitoring/|g' \
        -e 's|includes/Notifications/|includes/notifications/|g' \
        -e 's|includes/Permission/|includes/permission/|g' \
        -e 's|includes/Personalization/|includes/personalization/|g' \
        -e 's|includes/Plugins/|includes/plugins/|g' \
        -e 's|includes/Privacy/|includes/privacy/|g' \
        -e 's|includes/Providers/|includes/providers/|g' \
        -e 's|includes/Realtime/|includes/realtime/|g' \
        -e 's|includes/Renderers/|includes/renderers/|g' \
        -e 's|includes/Reports/|includes/reports/|g' \
        -e 's|includes/Routing/|includes/routing/|g' \
        -e 's|includes/Security/|includes/security/|g' \
        -e 's|includes/Session/|includes/session/|g' \
        -e 's|includes/Tenant/|includes/tenant/|g' \
        -e 's|includes/Template/|includes/template/|g' \
        -e 's|includes/User/|includes/user/|g' \
        -e 's|includes/Utils/|includes/utils/|g' \
        -e 's|includes/Utilities/|includes/utilities/|g' \
        -e 's|includes/Validation/|includes/validation/|g' \
        -e 's|includes/Workflow/|includes/workflow/|g' \
        -e 's|includes/Admin/|includes/admin/|g' \
        -e 's|includes/Core/|includes/core/|g' \
        "$FILE"

    # File name transformations within includes/
    # Convert CamelCase.php to lowercase.php
    sed -i \
        -e 's|/AdminAuthController\.php|/adminauthcontroller.php|g' \
        -e 's|/AdminFooter\.php|/adminfooter.php|g' \
        -e 's|/AdminHeader\.php|/adminheader.php|g' \
        -e 's|/AIFeedbackLogger\.php|/aifeedbacklogger.php|g' \
        -e 's|/AIIntegrationProvider\.php|/aiintegrationprovider.php|g' \
        -e 's|/APIResponse\.php|/apiresponse.php|g' \
        -e 's|/ApiAuth\.php|/apiauth.php|g' \
        -e 's|/ApprovalLogger\.php|/approvallogger.php|g' \
        -e 's|/ArchiveSystem\.php|/archivesystem.php|g' \
        -e 's|/Auth\.php"|/auth.php"|g' \
        -e 's|/AuthTestController\.php|/authtestcontroller.php|g' \
        -e 's|/CacheInterface\.php|/cacheinterface.php|g' \
        -e 's|/CacheManager\.php|/cachemanager.php|g' \
        -e 's|/CachePurger\.php|/cachepurger.php|g' \
        -e 's|/Collector\.php|/collector.php|g' \
        -e 's|/ConflictDetector\.php|/conflictdetector.php|g' \
        -e 's|/ConflictResolutionService\.php|/conflictresolutionservice.php|g' \
        -e 's|/ConflictResolver\.php|/conflictresolver.php|g' \
        -e 's|/ContentApproval\.php|/contentapproval.php|g' \
        -e 's|/ContentManager\.php|/contentmanager.php|g' \
        -e 's|/ContentRepository\.php|/contentrepository.php|g' \
        -e 's|/ContentScorer\.php|/contentscorer.php|g' \
        -e 's|/ContentStateController\.php|/contentstatecontroller.php|g' \
        -e 's|/ContentStateHistory\.php|/contentstatehistory.php|g' \
        -e 's|/ContentStateManager\.php|/contentstatemanager.php|g' \
        -e 's|/ContentVersionManager\.php|/contentversionmanager.php|g' \
        -e 's|/CoreLoader\.php|/coreloader.php|g' \
        -e 's|/CsvExporter\.php|/csvexporter.php|g' \
        -e 's|/DB\.php|/db.php|g' \
        -e 's|/FTPManager\.php|/ftpmanager.php|g' \
        -e 's|/FileCache\.php|/filecache.php|g' \
        -e 's|/FileUtils\.php|/fileutils.php|g' \
        -e 's|/GDPRLog\.php|/gdprlog.php|g' \
        -e 's|/GeminiProvider\.php|/geminiprovider.php|g' \
        -e 's|/HttpClient\.php|/httpclient.php|g' \
        -e 's|/LockManager\.php|/lockmanager.php|g' \
        -e 's|/MCPAlert\.php|/mcpalert.php|g' \
        -e 's|/Migration\.php|/migration.php|g' \
        -e 's|/ModerationController\.php|/moderationcontroller.php|g' \
        -e 's|/OpenAIProvider\.php|/openaiprovider.php|g' \
        -e 's|/PermissionManager\.php|/permissionmanager.php|g' \
        -e 's|/PersonalizationEngine\.php|/personalizationengine.php|g' \
        -e 's|/RBAC\.php|/rbac.php|g' \
        -e 's|/RateLimiter\.php|/ratelimiter.php|g' \
        -e 's|/ReportDataGenerator\.php|/reportdatagenerator.php|g' \
        -e 's|/Response\.php|/response.php|g' \
        -e 's|/Router\.php|/router.php|g' \
        -e 's|/Sanitizer\.php|/sanitizer.php|g' \
        -e 's|/SearchIndex\.php|/searchindex.php|g' \
        -e 's|/SecureLogger\.php|/securelogger.php|g' \
        -e 's|/SecurityAuditorController\.php|/securityauditorcontroller.php|g' \
        -e 's|/SecurityLog\.php|/securitylog.php|g' \
        -e 's|/SessionManager\.php|/sessionmanager.php|g' \
        -e 's|/TemplateSystem\.php|/templatesystem.php|g' \
        -e 's|/TenantAIConfig\.php|/tenantaiconfig.php|g' \
        -e 's|/TenantRepository\.php|/tenantrepository.php|g' \
        -e 's|/Tracing\.php|/tracing.php|g' \
        -e 's|/UserManager\.php|/usermanager.php|g' \
        -e 's|/UserProfileAnalyzer\.php|/userprofileanalyzer.php|g' \
        -e 's|/Utilities\.php|/utilities.php|g' \
        -e 's|/ValidationHelper\.php|/validationhelper.php|g' \
        -e 's|/Validator\.php|/validator.php|g' \
        -e 's|/VersionController\.php|/versioncontroller.php|g' \
        -e 's|/VersionManager\.php|/versionmanager.php|g' \
        -e 's|/View\.php|/view.php|g' \
        -e 's|/ViewRenderer\.php|/viewrenderer.php|g' \
        -e 's|/Visualizer\.php|/visualizer.php|g' \
        -e 's|/WorkflowManager\.php|/workflowmanager.php|g' \
        -e 's|/Workflows\.php|/workflows.php|g' \
        -e 's|/environmentmanager\.php|/environmentmanager.php|g' \
        "$FILE"

    ((COUNT++))
done

echo "=== Complete: Updated $COUNT files ===" | tee -a "$LOG_FILE"
echo "=== Backups saved with .bak_batch1 extension ===" | tee -a "$LOG_FILE"
