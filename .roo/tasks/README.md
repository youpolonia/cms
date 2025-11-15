# Task Management System

## File Format Requirements

All task files must include these required fields:
- `title`: Short descriptive title of the task
- `description`: Detailed explanation of the task requirements
- `agents`: Comma-separated list of responsible agents/modes

### Example Template
```yaml
title: "Implement version comparison UI"
description: |
  Create a Vue component that shows side-by-side comparison of content versions.
  Should include:
  - Diff highlighting
  - Change navigation
  - Approval controls
agents: code,db-support
status: pending
```

## Status Tracking Conventions
- `pending`: Task not yet started
- `in-progress`: Actively being worked on
- `blocked`: Waiting on external dependency
- `completed`: Finished implementation
- `verified`: Completed and validated