#!/bin/bash
# Batch-1: Normalize includes/* directories to lowercase
# Uses two-step rename to avoid case-sensitive filesystem conflicts

set -e

CMS_ROOT="/var/www/html/cms/includes"
LOG_FILE="/var/www/html/cms/batch1_rename.log"

echo "=== Batch-1 Directory Rename - $(date) ===" | tee -a "$LOG_FILE"

# Function to safely rename directory (two-step process)
rename_dir() {
    local src="$1"
    local dst="$2"

    if [ ! -d "$src" ]; then
        echo "SKIP: $src does not exist" | tee -a "$LOG_FILE"
        return 0
    fi

    # Check if source and destination are the same (case-insensitive)
    if [ "$(basename "$src" | tr '[:upper:]' '[:lower:]')" == "$(basename "$dst" | tr '[:upper:]' '[:lower:]')" ] && [ "$src" == "$dst" ]; then
        echo "SKIP: $src already lowercase" | tee -a "$LOG_FILE"
        return 0
    fi

    # Step 1: Rename to temporary name
    local temp="${src}_temp_$$"
    echo "Step 1: $src → $temp" | tee -a "$LOG_FILE"
    mv "$src" "$temp"

    # Step 2: Rename to final lowercase name
    echo "Step 2: $temp → $dst" | tee -a "$LOG_FILE"

    # If destination already exists, need to merge or handle conflict
    if [ -d "$dst" ]; then
        echo "WARNING: $dst already exists. Merging contents from $temp" | tee -a "$LOG_FILE"
        # Move contents from temp to existing dst
        cp -r "$temp"/* "$dst"/ 2>/dev/null || true
        rm -rf "$temp"
    else
        mv "$temp" "$dst"
    fi

    echo "DONE: $src → $dst" | tee -a "$LOG_FILE"
}

# Top-level directory renames (alphabetical order)
cd "$CMS_ROOT"

# Primary renames from patch
rename_dir "$CMS_ROOT/API" "$CMS_ROOT/api_old"
rename_dir "$CMS_ROOT/Analytics" "$CMS_ROOT/analytics_caps"
rename_dir "$CMS_ROOT/Audit" "$CMS_ROOT/audit_caps"
rename_dir "$CMS_ROOT/Auth" "$CMS_ROOT/auth_caps"
rename_dir "$CMS_ROOT/BatchProcessing" "$CMS_ROOT/batchprocessing"
rename_dir "$CMS_ROOT/CDN" "$CMS_ROOT/cdn_caps"
rename_dir "$CMS_ROOT/Cache" "$CMS_ROOT/cache_caps"
rename_dir "$CMS_ROOT/Compliance" "$CMS_ROOT/compliance"
rename_dir "$CMS_ROOT/Content" "$CMS_ROOT/content_caps"
rename_dir "$CMS_ROOT/Controllers" "$CMS_ROOT/controllers_caps"
rename_dir "$CMS_ROOT/Cron" "$CMS_ROOT/cron_caps"
rename_dir "$CMS_ROOT/Database" "$CMS_ROOT/database_caps"
rename_dir "$CMS_ROOT/Debug" "$CMS_ROOT/debug"
rename_dir "$CMS_ROOT/Deployment" "$CMS_ROOT/deployment"
rename_dir "$CMS_ROOT/Developer" "$CMS_ROOT/developer"
rename_dir "$CMS_ROOT/Editor" "$CMS_ROOT/editor_caps"
rename_dir "$CMS_ROOT/Exceptions" "$CMS_ROOT/exceptions"
rename_dir "$CMS_ROOT/Federation" "$CMS_ROOT/federation_caps"
rename_dir "$CMS_ROOT/Http" "$CMS_ROOT/http"
rename_dir "$CMS_ROOT/Media" "$CMS_ROOT/media_caps"
rename_dir "$CMS_ROOT/Metrics" "$CMS_ROOT/metrics"
rename_dir "$CMS_ROOT/Middleware" "$CMS_ROOT/middleware_caps"
rename_dir "$CMS_ROOT/Models" "$CMS_ROOT/models_caps"
rename_dir "$CMS_ROOT/Monitoring" "$CMS_ROOT/monitoring"
rename_dir "$CMS_ROOT/Notifications" "$CMS_ROOT/notifications_caps"
rename_dir "$CMS_ROOT/PageBuilder" "$CMS_ROOT/pagebuilder"
rename_dir "$CMS_ROOT/PatternReader" "$CMS_ROOT/patternreader"
rename_dir "$CMS_ROOT/Permission" "$CMS_ROOT/permission_caps"
rename_dir "$CMS_ROOT/Personalization" "$CMS_ROOT/personalization"
rename_dir "$CMS_ROOT/Phase4" "$CMS_ROOT/phase4"
rename_dir "$CMS_ROOT/Plugins" "$CMS_ROOT/plugins_caps"
rename_dir "$CMS_ROOT/Privacy" "$CMS_ROOT/privacy_caps"
rename_dir "$CMS_ROOT/Providers" "$CMS_ROOT/providers_caps"
rename_dir "$CMS_ROOT/Realtime" "$CMS_ROOT/realtime_caps"
rename_dir "$CMS_ROOT/Renderers" "$CMS_ROOT/renderers_caps"
rename_dir "$CMS_ROOT/Report" "$CMS_ROOT/report"
rename_dir "$CMS_ROOT/Reports" "$CMS_ROOT/reports"
rename_dir "$CMS_ROOT/Repositories" "$CMS_ROOT/repositories"
rename_dir "$CMS_ROOT/Routing" "$CMS_ROOT/routing_caps"
rename_dir "$CMS_ROOT/RoutingV2" "$CMS_ROOT/routingv2"
rename_dir "$CMS_ROOT/Scaling" "$CMS_ROOT/scaling"
rename_dir "$CMS_ROOT/Security" "$CMS_ROOT/security_caps"
rename_dir "$CMS_ROOT/Storage" "$CMS_ROOT/storage"
rename_dir "$CMS_ROOT/Tasks" "$CMS_ROOT/tasks"
rename_dir "$CMS_ROOT/Tenant" "$CMS_ROOT/tenant_caps"
rename_dir "$CMS_ROOT/Testing" "$CMS_ROOT/testing"
rename_dir "$CMS_ROOT/Theme" "$CMS_ROOT/theme_sing"
rename_dir "$CMS_ROOT/Themes" "$CMS_ROOT/themes"
rename_dir "$CMS_ROOT/UI" "$CMS_ROOT/ui"
rename_dir "$CMS_ROOT/User" "$CMS_ROOT/user"
rename_dir "$CMS_ROOT/Utilities" "$CMS_ROOT/utilities_caps"
rename_dir "$CMS_ROOT/Utils" "$CMS_ROOT/utils_caps"
rename_dir "$CMS_ROOT/Validation" "$CMS_ROOT/validation"
rename_dir "$CMS_ROOT/Version" "$CMS_ROOT/version_sing"
rename_dir "$CMS_ROOT/VersionControl" "$CMS_ROOT/versioncontrol"
rename_dir "$CMS_ROOT/Versioning" "$CMS_ROOT/versioning_caps"
rename_dir "$CMS_ROOT/Widgets" "$CMS_ROOT/widgets"
rename_dir "$CMS_ROOT/Worker" "$CMS_ROOT/worker"
rename_dir "$CMS_ROOT/Workflow" "$CMS_ROOT/workflow_caps"

# Handle Api separately (conflicts with api)
rename_dir "$CMS_ROOT/Api" "$CMS_ROOT/api_mixed"

echo "=== Phase 1 Complete - All directories renamed with suffixes ===" | tee -a "$LOG_FILE"
echo "=== Review log at $LOG_FILE ===" | tee -a "$LOG_FILE"
