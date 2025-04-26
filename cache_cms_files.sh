#!/bin/bash
# Script to cache all core CMS files via MCP

FILES=(
  "app/Services/ContentDiffService.php"
  "routes/content_versions.php"
  "config/cms.php"
  "config/database.php"
  "config/auth.php"
  "config/app.php"
  "resources/js/app.js"
  "resources/js/version-comparison-analytics.js"
)

for file in "${FILES[@]}"; do
  echo "Caching $file"
  node /home/krala/Cline/MCP/cms-knowledge-server/build/index.js cache_file --path "$file"
done

echo "All core CMS files cached successfully"