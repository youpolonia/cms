#!/bin/bash

# Initialize logging
LOG_FILE=".roo/git.log"
mkdir -p "$(dirname "$LOG_FILE")"
exec >> "$LOG_FILE" 2>&1

# Verify we're on a roo/ branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [[ ! "$CURRENT_BRANCH" =~ ^roo/ ]]; then
    echo "[$(date)] Error: Not on a roo/ branch (current branch: $CURRENT_BRANCH)"
    exit 1
fi

# Commit all changes
TASK_SUMMARY=$(echo "$1" | tr '[:upper:]' '[:lower:]' | tr -s ' ' '-' | tr -cd '[:alnum:]-')
if ! git add . && git commit -m "Roo: $TASK_SUMMARY"; then
    echo "[$(date)] Error: Failed to commit changes"
    exit 1
fi

# Optional push to remote
if [ "$REMOTE_PUSH" = "true" ]; then
    if ! git push -u origin "$CURRENT_BRANCH"; then
        echo "[$(date)] Error: Failed to push branch $CURRENT_BRANCH"
        exit 1
    fi
    echo "[$(date)] Success: Pushed branch $CURRENT_BRANCH to remote"
fi

echo "[$(date)] Success: Committed changes to branch $CURRENT_BRANCH"
exit 0