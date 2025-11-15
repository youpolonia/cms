#!/bin/bash

# Initialize logging
LOG_FILE=".roo/git.log"
mkdir -p "$(dirname "$LOG_FILE")"
exec >> "$LOG_FILE" 2>&1

# Get timestamp for branch name
TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
TASK_SUMMARY=$(echo "$1" | tr '[:upper:]' '[:lower:]' | tr -s ' ' '-' | tr -cd '[:alnum:]-')

# Verify clean working directory
if [ -n "$(git status --porcelain)" ]; then
    echo "[$(date)] Error: Working directory not clean. Stash or commit changes before creating new branch."
    exit 1
fi

# Create new branch
BRANCH_NAME="roo/$TIMESTAMP-$TASK_SUMMARY"
if ! git checkout -b "$BRANCH_NAME" 2>/dev/null; then
    echo "[$(date)] Error: Failed to create branch $BRANCH_NAME"
    exit 1
fi

echo "[$(date)] Success: Created branch $BRANCH_NAME"
exit 0