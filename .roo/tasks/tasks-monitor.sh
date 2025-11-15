#!/bin/bash

TASKS_DIR=".roo/tasks"
LOG_FILE=".roo/tasks.log"

# Ensure log file exists
touch "$LOG_FILE"

# Validate task file format
validate_task() {
  local file="$1"
  local errors=0
  
  # Check required fields
  if ! grep -q "^title:" "$file"; then
    echo "ERROR: Missing 'title' field in $file" >> "$LOG_FILE"
    errors=$((errors+1))
  fi
  
  if ! grep -q "^description:" "$file"; then
    echo "ERROR: Missing 'description' field in $file" >> "$LOG_FILE"
    errors=$((errors+1))
  fi
  
  if ! grep -q "^agents:" "$file"; then
    echo "ERROR: Missing 'agents' field in $file" >> "$LOG_FILE"
    errors=$((errors+1))
  fi

  if [ $errors -eq 0 ]; then
    echo "VALID: $file passed validation" >> "$LOG_FILE"
  fi
  
  return $errors
}

# Watch for file changes
inotifywait -m -r -e create -e modify "$TASKS_DIR" | while read path action file; do
  if [[ "$file" =~ \.yaml$|\.yml$ ]]; then
    timestamp=$(date +"%Y-%m-%d %T")
    echo "[$timestamp] Detected $action on $file" >> "$LOG_FILE"
    validate_task "$path$file"
    
    # Git hook integration
    if [ -f ".git/hooks/post-commit" ]; then
      .git/hooks/post-commit
    fi
    
    # Insights logging
    if [ -f "scripts/log_insight.sh" ]; then
      scripts/log_insight.sh "task_modified" "$file"
    fi
  fi
done