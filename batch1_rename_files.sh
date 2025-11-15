#!/bin/bash
# Batch-1: Rename all PHP files in includes/ to lowercase

set +e  # Continue on errors

LOG_FILE="/var/www/html/cms/batch1_rename_files.log"
echo "=== Batch-1 File Rename - $(date) ===" | tee "$LOG_FILE"

COUNT=0

# Find all PHP files in includes/ that have uppercase letters
find /var/www/html/cms/includes -name "*.php" -type f | while read filepath; do
    dir=$(dirname "$filepath")
    filename=$(basename "$filepath")
    lowercase_filename=$(echo "$filename" | tr '[:upper:]' '[:lower:]')

    # Skip if already lowercase
    if [ "$filename" == "$lowercase_filename" ]; then
        continue
    fi

    # Check if lowercase version already exists
    if [ -f "$dir/$lowercase_filename" ]; then
        echo "CONFLICT: $filepath → $dir/$lowercase_filename (destination exists, skipping)" | tee -a "$LOG_FILE"
        continue
    fi

    # Rename via temp to handle case-insensitive filesystems
    temp="$filepath.temp_$$"
    mv "$filepath" "$temp" 2>/dev/null
    mv "$temp" "$dir/$lowercase_filename" 2>/dev/null

    if [ $? -eq 0 ]; then
        echo "RENAMED: $filename → $lowercase_filename (in $dir)" | tee -a "$LOG_FILE"
        ((COUNT++))
    else
        echo "ERROR: Failed to rename $filepath" | tee -a "$LOG_FILE"
        # Try to restore from temp
        mv "$temp" "$filepath" 2>/dev/null
    fi
done

echo "=== Complete: Renamed $COUNT files ===" | tee -a "$LOG_FILE"
