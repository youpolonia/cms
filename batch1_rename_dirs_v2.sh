#!/bin/bash
# Batch-1: Rename includes/* directories to lowercase
# Handles case-sensitive filesystem safely

set +e  # Continue on errors

CMS_ROOT="/var/www/html/cms/includes"
LOG_FILE="/var/www/html/cms/batch1_rename_dirs.log"

echo "=== Batch-1 Directory Rename - $(date) ===" | tee "$LOG_FILE"

# Function to safely rename a directory
safe_rename_dir() {
    local parent="$1"
    local oldname="$2"
    local newname="$3"

    local src="$parent/$oldname"
    local dst="$parent/$newname"

    # Skip if source doesn't exist
    if [ ! -d "$src" ]; then
        return 0
    fi

    # Skip if already lowercase
    if [ "$oldname" == "$newname" ]; then
        echo "SKIP: $src (already lowercase)" | tee -a "$LOG_FILE"
        return 0
    fi

    # Check if destination already exists
    if [ -d "$dst" ] && [ "$src" != "$dst" ]; then
        echo "MERGE: $src → $dst (destination exists)" | tee -a "$LOG_FILE"
        # Move all contents from src to dst
        cp -r "$src"/* "$dst"/ 2>/dev/null || true
        # Remove source after merging
        rm -rf "$src"
        echo "MERGED: $oldname → $newname" | tee -a "$LOG_FILE"
    else
        # Simple rename via temp
        local temp="${src}_temp_$$"
        mv "$src" "$temp" 2>/dev/null
        mv "$temp" "$dst" 2>/dev/null
        echo "RENAMED: $oldname → $newname" | tee -a "$LOG_FILE"
    fi
}

# List of directories to rename (top-level in includes/)
declare -A DIRS=(
    ["API"]="api"
    ["Analytics"]="analytics"
    ["Audit"]="audit"
    ["Auth"]="auth"
    ["BatchProcessing"]="batchprocessing"
    ["CDN"]="cdn"
    ["Cache"]="cache"
    ["Compliance"]="compliance"
    ["Content"]="content"
    ["Controllers"]="controllers"
    ["Cron"]="cron"
    ["Database"]="database"
    ["Debug"]="debug"
    ["Deployment"]="deployment"
    ["Developer"]="developer"
    ["Editor"]="editor"
    ["Exceptions"]="exceptions"
    ["Federation"]="federation"
    ["Http"]="http"
    ["Media"]="media"
    ["Metrics"]="metrics"
    ["Middleware"]="middleware"
    ["Models"]="models"
    ["Monitoring"]="monitoring"
    ["Notifications"]="notifications"
    ["PageBuilder"]="pagebuilder"
    ["PatternReader"]="patternreader"
    ["Permission"]="permission"
    ["Personalization"]="personalization"
    ["Phase4"]="phase4"
    ["Plugins"]="plugins"
    ["Privacy"]="privacy"
    ["Providers"]="providers"
    ["Realtime"]="realtime"
    ["Renderers"]="renderers"
    ["Report"]="report"
    ["Reports"]="reports"
    ["Repositories"]="repositories"
    ["Routing"]="routing"
    ["RoutingV2"]="routingv2"
    ["Scaling"]="scaling"
    ["Security"]="security"
    ["Storage"]="storage"
    ["Tasks"]="tasks"
    ["Tenant"]="tenant"
    ["Testing"]="testing"
    ["Theme"]="theme"
    ["Themes"]="themes"
    ["UI"]="ui"
    ["User"]="user"
    ["Utilities"]="utilities"
    ["Utils"]="utils"
    ["Validation"]="validation"
    ["Version"]="version"
    ["VersionControl"]="versioncontrol"
    ["Versioning"]="versioning"
    ["Widgets"]="widgets"
    ["Worker"]="worker"
    ["Workflow"]="workflow"
    ["Api"]="api"
    ["Admin"]="admin"
)

# Process each directory
for oldname in "${!DIRS[@]}"; do
    newname="${DIRS[$oldname]}"
    safe_rename_dir "$CMS_ROOT" "$oldname" "$newname"
done

# Handle subdirectories
echo "=== Processing subdirectories ===" | tee -a "$LOG_FILE"

# API subdirectories
safe_rename_dir "$CMS_ROOT/api" "Middleware" "middleware"
safe_rename_dir "$CMS_ROOT/api" "Webhooks" "webhooks"

# Controllers subdirectories
safe_rename_dir "$CMS_ROOT/controllers" "Admin" "admin"
safe_rename_dir "$CMS_ROOT/controllers" "Auth" "auth"
safe_rename_dir "$CMS_ROOT/controllers" "Api" "api"

# Core subdirectories
safe_rename_dir "$CMS_ROOT/core" "Core" "core"
safe_rename_dir "$CMS_ROOT/core/core" "Builder" "builder"
safe_rename_dir "$CMS_ROOT/core/core" "Controllers" "controllers"
safe_rename_dir "$CMS_ROOT/core/core" "Middleware" "middleware"

# Database subdirectories
safe_rename_dir "$CMS_ROOT/database" "Middleware" "middleware"
safe_rename_dir "$CMS_ROOT/database" "Migrations" "migrations"

# Routing V2 subdirectories
safe_rename_dir "$CMS_ROOT/routing_v2" "Middleware" "middleware"

# Workflow subdirectories
safe_rename_dir "$CMS_ROOT/workflow" "Triggers" "triggers"

# AI subdirectories
safe_rename_dir "$CMS_ROOT/ai" "AI" "ai"
safe_rename_dir "$CMS_ROOT/ai/ai" "MediaGallery" "mediagallery"
safe_rename_dir "$CMS_ROOT/ai/ai" "SEO" "seo"

# Services subdirectories
safe_rename_dir "$CMS_ROOT/services" "Services" "services"
safe_rename_dir "$CMS_ROOT/services/services" "Calendar" "calendar"

# Http subdirectories
safe_rename_dir "$CMS_ROOT/http" "Psr" "psr"
safe_rename_dir "$CMS_ROOT/http/psr" "Http" "http"
safe_rename_dir "$CMS_ROOT/http/psr/http" "Message" "message"

# Notifications subdirectories
safe_rename_dir "$CMS_ROOT/notifications" "Templates" "templates"

echo "=== Directory rename complete ===" | tee -a "$LOG_FILE"
