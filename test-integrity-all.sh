#!/bin/bash

# This script tests the integrity of all extensions in a pure PHP CMS.
# It operates in an environment with only standard shell tools.
#
# Usage:
#   ./test-integrity-all.sh
#   ./test-integrity-all.sh --details
#   ./test-integrity-all.sh --fail-on-error
#   ./test-integrity-all.sh --details --fail-on-error

# --- Configuration ---
BASE_URL="http://localhost:8001"
EXTENSIONS_PAGE_URL="${BASE_URL}/admin/extensions/index.php"
VERIFY_URL="${BASE_URL}/admin/extensions/verify.php"
COOKIE_JAR="/tmp/integrity_cookies.txt"
SLUG_SOURCE_HTML="/tmp/slug_source.html"
EXT_HTML="/tmp/ext.html"
BUILD_HTML="/tmp/build.html"
VERIFY_HTML="/tmp/verify.html"

# --- Flag Handling ---
DETAILS_FLAG=false
FAIL_ON_ERROR_FLAG=false
for arg in "$@"
do
    case $arg in
        --details)
        DETAILS_FLAG=true
        ;;
        --fail-on-error)
        FAIL_ON_ERROR_FLAG=true
        ;;
    esac
done

# --- State Variable ---
FAILURE_DETECTED=false

# --- Cleanup ---
rm -f "$COOKIE_JAR" "$SLUG_SOURCE_HTML" "$EXT_HTML" "$BUILD_HTML" "$VERIFY_HTML"

# --- Main Logic ---
curl -s -L -c "$COOKIE_JAR" "$EXTENSIONS_PAGE_URL" > "$SLUG_SOURCE_HTML"
if [ ! -s "$SLUG_SOURCE_HTML" ]; then
    echo "FATAL: Could not fetch $EXTENSIONS_PAGE_URL to get slugs. Is the server running?" >&2
    exit 1
fi

SLUGS=$(perl -0777 -ne 'while(/<tr>.*?<td.*?>(.*?)<\/td>/gs) { print "$1\n" }' "$SLUG_SOURCE_HTML" | sed 's/^[ \t]*//;s/[ \t]*$//' | grep .)
if [ -z "$SLUGS" ]; then
    echo "FATAL: No extension slugs found on $EXTENSIONS_PAGE_URL" >&2
    exit 1
fi

echo "$SLUGS" | while read -r slug; do
    echo "SLUG: $slug"

    # --- Build Baseline ---
    curl -s -L -b "$COOKIE_JAR" -c "$COOKIE_JAR" -e "$EXTENSIONS_PAGE_URL" "$EXTENSIONS_PAGE_URL" > "$EXT_HTML"
    CSRF_TOKEN=$(grep 'name="csrf_token"' "$EXT_HTML" | sed -n 's/.*value="\([^"]*\)".*/\1/p' | head -n 1)

    if [ -z "$CSRF_TOKEN" ]; then
        echo "BASELINE: error"
    else
        curl -s -L \
             -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
             -e "$EXTENSIONS_PAGE_URL" \
             --data-urlencode "csrf_token=$CSRF_TOKEN" \
             --data-urlencode "slug=$slug" \
             --data-urlencode "action=build" \
             "$VERIFY_URL" > "$BUILD_HTML"

        if grep -q '<div class="flash ok">' "$BUILD_HTML"; then
            echo "BASELINE: ok"
        else
            echo "BASELINE: error"
        fi
    fi

    # --- Verify Baseline ---
    curl -s -L -b "$COOKIE_JAR" -c "$COOKIE_JAR" -e "$VERIFY_URL" "$EXTENSIONS_PAGE_URL" > "$EXT_HTML"
    CSRF_TOKEN=$(grep 'name="csrf_token"' "$EXT_HTML" | sed -n 's/.*value="\([^"]*\)".*/\1/p' | head -n 1)

    if [ -z "$CSRF_TOKEN" ]; then
        echo "VERIFY: error"
        FAILURE_DETECTED=true
    else
        curl -s -L \
             -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
             -e "$EXTENSIONS_PAGE_URL" \
             --data-urlencode "csrf_token=$CSRF_TOKEN" \
             --data-urlencode "slug=$slug" \
             --data-urlencode "action=check" \
             "$VERIFY_URL" > "$VERIFY_HTML"

        if grep -q '<div class="flash ok">' "$VERIFY_HTML"; then
            echo "VERIFY: ok"
        else
            echo "VERIFY: error"
            FAILURE_DETECTED=true
            if [ "$DETAILS_FLAG" = true ]; then
                MISMATCHES=$(sed -n '/<div class="flash err">/,/<\/div>/p' "$VERIFY_HTML" | \
                             grep '<li>' | \
                             sed -e 's/^[ \t]*<li>//' -e 's/<\/li>//' -e 's/^[ \t]*//' -e 's/[ \t]*$//')

                if [ -n "$MISMATCHES" ]; then
                    echo "$MISMATCHES" | while read -r line; do
                        echo "  - $line"
                    done
                fi
            fi
        fi
    fi
done

# --- Final Cleanup ---
rm -f "$COOKIE_JAR" "$SLUG_SOURCE_HTML" "$EXT_HTML" "$BUILD_HTML" "$VERIFY_HTML"

# --- Final Exit Status ---
if [ "$FAIL_ON_ERROR_FLAG" = true ] && [ "$FAILURE_DETECTED" = true ]; then
    exit 1
fi

exit 0