#!/bin/bash

echo "=== UPPERCASE FILES (anywhere under /includes/**) ==="

# Find all files with uppercase letters in basename under includes
find includes -type f -name "*[A-Z]*" ! -path "./logs/*" ! -path "./memory-bank/*" ! -path "./node_modules/*" ! -path "./vendor/*" ! -path "./tcpdf/*" ! -path "./.*" | while read file; do
    basename=$(basename "$file")
    dir=$(dirname "$file")
    
    # Check if basename contains uppercase letters
    if [[ "$basename" =~ [A-Z] ]]; then
        size=$(stat -c%s "$file" 2>/dev/null || echo "0")
        sha=$(sha1sum "$file" 2>/dev/null | cut -d' ' -f1 || echo "N/A")
        mtime=$(stat -c%Y "$file" 2>/dev/null | xargs -I{} date -d@{} +%Y-%m-%dT%H:%M:%S || echo "N/A")
        
        echo "UPPERFILE: $file|$size|$sha|$mtime"
        
        # Compute lowercase candidate
        candidate_basename=$(echo "$basename" | tr '[:upper:]' '[:lower:]')
        candidate_path="$dir/$candidate_basename"
        
        if [ -f "$candidate_path" ]; then
            echo "CANDIDATE: $file -> $candidate_path|exists:true"
        else
            echo "CANDIDATE: $file -> $candidate_path|exists:false"
        fi
        
        # REFS by BASENAME (case-sensitive across *.php)
        echo "REFS: $basename"
        matches=$(find . -name "*.php" -type f ! -path "./logs/*" ! -path "./memory-bank/*" ! -path "./node_modules/*" ! -path "./vendor/*" ! -path "./tcpdf/*" ! -path "./.*" -exec grep -n "$basename" {} \; 2>/dev/null | head -5)
        if [ -n "$matches" ]; then
            echo "$matches" | while IFS=: read -r ref_file line content; do
                trimmed=$(echo "$content" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
                echo "$ref_file:$line:$trimmed"
            done
        else
            echo "NONE"
        fi
        
        # PLAN
        refs_count=$(find . -name "*.php" -type f ! -path "./logs/*" ! -path "./memory-bank/*" ! -path "./node_modules/*" ! -path "./vendor/*" ! -path "./tcpdf/*" ! -path "./.*" -exec grep -n "$basename" {} \; 2>/dev/null | wc -l)
        candidate_exists=$([ -f "$candidate_path" ] && echo "true" || echo "false")
        echo "PLAN: $file -> $candidate_path | refs_upper:$refs_count | candidate_exists:$candidate_exists"
        echo ""
    fi
done
