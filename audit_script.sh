#!/bin/bash

# Set output file
OUTPUT_FILE="audit_report.csv"
echo "path,line,type,snippet" > $OUTPUT_FILE

# Define excluded directories for find command
EXCLUDE_ARGS=(
    -path "./logs" -o \
    -path "./vendor" -o \
    -path "./node_modules" -o \
    -path "./memory-bank" -o \
    -path "./.git" -o \
    -path "./.idea" -o \
    -path "./.vscode" -o \
    -path "./.clinerules" -o \
    -path "./.clinerules-architect" -o \
    -path "./.clinerules-ask" -o \
    -path "./.clinerules-code" -o \
    -path "./.clinerules-debug" -o \
    -path "./.clinerules-documents" -o \
    -path "./.clinerules-orchestrator" -o \
    -path "./.clinerules-read" -o \
    -path "./.clinerules-test" -o \
    -path "./.claude" -o \
    -path "./.phpunit.cache" -o \
    -path "./.roo" -o \
    -path "./.venv"
)

# Find all PHP files, excluding specified directories
find . -type d \( "${EXCLUDE_ARGS[@]}" \) -prune -o -type f -name "*.php" -print0 | while IFS= read -r -d $'\0' file; do
    # A) include / include_once (argument is a quoted literal)
    grep -nE "^\s*include\s+['\"][^'\"]*['\"]" "$file" | while read -r line; do
        SNIPPET=$(echo "$line" | cut -d':' -f2- | sed 's/"/""/g' | cut -c1-120)
        LINE_NUM=$(echo "$line" | cut -d':' -f1)
        echo "\"$file\",$LINE_NUM,include,\"$SNIPPET\"" >> $OUTPUT_FILE
    done
    grep -nE "^\s*include_once\s+['\"][^'\"]*['\"]" "$file" | while read -r line; do
        SNIPPET=$(echo "$line" | cut -d':' -f2- | sed 's/"/""/g' | cut -c1-120)
        LINE_NUM=$(echo "$line" | cut -d':' -f1)
        echo "\"$file\",$LINE_NUM,include_once,\"$SNIPPET\"" >> $OUTPUT_FILE
    done

    # B) Dynamic include/require (argument not a quoted literal)
    grep -nE "^\s*(include|require|include_once|require_once)\s*\(?[^'\"]*\$" "$file" | while read -r line; do
        SNIPPET=$(echo "$line" | cut -d':' -f2- | sed 's/"/""/g' | cut -c1-120)
        LINE_NUM=$(echo "$line" | cut -d':' -f1)
        echo "\"$file\",$LINE_NUM,dynamic_include,\"$SNIPPET\"" >> $OUTPUT_FILE
    done

    # C) Trailing closing tag “?>” at end of file
    LAST_LINE=$(tail -n 1 "$file" | sed -e 's/^[ \t]*//' -e 's/[ \t]*$//')
    if [[ "$LAST_LINE" == "?>" ]]; then
        lines=$(wc -l < "$file" | tr -d ' ')
        echo "\"$file\",$lines,closing_tag,\"?&gt;\"" >> $OUTPUT_FILE
    fi

    # D) Forbidden exec family
    grep -nE "(system|exec|shell_exec|passthru|popen|proc_open|php://stdin)\s*\(" "$file" | while read -r line; do
        SNIPPET=$(echo "$line" | cut -d':' -f2- | sed 's/"/""/g' | cut -c1-120)
        LINE_NUM=$(echo "$line" | cut -d':' -f1)
        echo "\"$file\",$LINE_NUM,exec_family,\"$SNIPPET\"" >> $OUTPUT_FILE
    done

    # E) Autoloaders
    grep -nE "spl_autoload_register|__autoload" "$file" | while read -r line; do
        SNIPPET=$(echo "$line" | cut -d':' -f2- | sed 's/"/""/g' | cut -c1-120)
        LINE_NUM=$(echo "$line" | cut -d':' -f1)
        echo "\"$file\",$LINE_NUM,autoloader,\"$SNIPPET\"" >> $OUTPUT_FILE
    done

    # F) DB anti-patterns
    if [ "$file" != "./core/database.php" ]; then
        grep -n "new PDO" "$file" | while read -r line; do
            SNIPPET=$(echo "$line" | cut -d':' -f2- | sed 's/"/""/g' | cut -c1-120)
            LINE_NUM=$(echo "$line" | cut -d':' -f1)
            echo "\"$file\",$LINE_NUM,db_new_pdo,\"$SNIPPET\"" >> $OUTPUT_FILE
        done
    fi
    grep -n "Database::getConnection" "$file" | while read -r line; do
        SNIPPET=$(echo "$line" | cut -d':' -f2- | sed 's/"/""/g' | cut -c1-120)
        LINE_NUM=$(echo "$line" | cut -d':' -f1)
        echo "\"$file\",$LINE_NUM,db_getConnection,\"$SNIPPET\"" >> $OUTPUT_FILE
    done
    grep -nE '["''''](mysql|pgsql|sqlite|oci):host=' "$file" | while read -r line; do
        SNIPPET=$(echo "$line" | cut -d':' -f2- | sed 's/"/""/g' | cut -c1-120)
        LINE_NUM=$(echo "$line" | cut -d':' -f1)
        echo "\"$file\",$LINE_NUM,db_dsn,\"$SNIPPET\"" >> $OUTPUT_FILE
    done

    # G) Public test/debug endpoints lacking DEV_MODE gate
    if [[ "$file" == ./public/* || "$file" == ./admin/test* || "$file" == ./developer-tools/* || "$file" == ./endpoints/* || "$file" == ./debug*.php ]]; then
        if ! grep -q "DEV_MODE" "$file"; then
            echo "\"$file\",1,dev_gate_missing,\"File does not check DEV_MODE\"" >> $OUTPUT_FILE
        fi
    fi
done

# Sort the output file
sort -t, -k1,1 -k2,2n -u "$OUTPUT_FILE" -o "$OUTPUT_FILE"

# PART 1 is the CSV itself. Now for PART 2.
echo ""
echo "--- Hotspots ---"
# Top 20 per type
for type in include include_once dynamic_include closing_tag exec_family autoloader db_new_pdo db_getConnection db_dsn dev_gate_missing; do
    COUNT=$(grep -c ",$type," "$OUTPUT_FILE")
    if [ "$COUNT" -gt 0 ]; then
        echo ""
        echo "Top 20 for type: $type"
        awk -F, -v type="$type" '$3 == type {print $1}' "$OUTPUT_FILE" | sort | uniq -c | sort -nr | head -n 20 | awk -v type="$type" '{print type ", " $2 ", " $1}'
    fi
done

# Top 20 overall
echo ""
echo "Top 20 Overall"
awk -F, 'NR>1 {print $1}' "$OUTPUT_FILE" | sort | uniq -c | sort -nr | head -n 20 | awk '{print $2 ", " $1}'


# PART 3: Result
VIOLATIONS=$(($(wc -l < "$OUTPUT_FILE") - 1))

echo ""
echo "--- Result ---"
if [ "$VIOLATIONS" -eq 0 ]; then
    echo "COMPLIANCE: PASS (0 violations)"
else
    echo "COMPLIANCE: FAIL ($VIOLATIONS violations)"
    echo ""
    echo "--- Remediation Plan ---"
    # (1) include/include_once -> require_once
    awk -F, '$3 == "include" || $3 == "include_once" {print $1}' "$OUTPUT_FILE" | sort -u | head -n 4 | while read -r file;
        do
        echo "In $file, change include/include_once to require_once."
    done
    # (2) Remove trailing ?>
    awk -F, '$3 == "closing_tag" {print $1}' "$OUTPUT_FILE" | sort -u | head -n 4 | while read -r file;
        do
        echo "In $file, remove trailing '?>'."
    done
    # (3) Add DEV_MODE gate
    awk -F, '$3 == "dev_gate_missing" {print $1}' "$OUTPUT_FILE" | sort -u | head -n 4 | while read -r file;
        do
        echo "In $file, add DEV_MODE gate at the top."
    done
fi

# Summary
echo ""
echo "--- Summary ---"
awk -F, 'NR>1 {print $3}' "$OUTPUT_FILE" | sort | uniq -c | sort -nr | awk '{print "Total " $2 ": " $1}'
AFFECTED_FILES=$(awk -F, 'NR>1 {print $1}' "$OUTPUT_FILE" | sort -u | wc -l)
echo "Affected files: $AFFECTED_FILES"
if [ "$VIOLATIONS" -gt 0 ]; then
    echo "BATCH CONTINUATION NEEDED: YES"
else
    echo "BATCH CONTINUATION NEEDED: NO"
fi

# Display the final CSV
cat $OUTPUT_FILE