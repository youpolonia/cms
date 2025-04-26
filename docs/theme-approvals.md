# Theme Approval Workflows

## Overview
The theme approval system provides a structured workflow for reviewing and approving theme versions before they can be published. Key components include:

- **Workflows**: Define the approval process steps and requirements
- **Steps**: Individual approval stages with specific criteria
- **Approvals**: Records of approval decisions for each theme version

## Models

### ThemeApprovalWorkflow
- Defines the overall approval process
- Contains metadata like name, description, activation status
- Has many ThemeApprovalSteps

### ThemeApprovalStep
- Represents a single step in the approval workflow
- Contains logic fields for conditional progression
- Specifies required approvals count
- Belongs to a ThemeApprovalWorkflow

### ThemeVersionApproval
- Tracks approval decisions for specific theme versions
- Records approver, decision, comments, timestamp
- Belongs to a ThemeVersion and ThemeApprovalStep

## Database Schema
Key tables:
- `theme_approval_workflows`
- `theme_approval_steps` 
- `theme_version_approvals`

## Workflow Process
1. Theme version submitted for approval
2. System applies appropriate workflow based on version metadata
3. Progresses through each step sequentially
4. Requires all step conditions to be met before advancing
5. Final approval publishes the theme version

## API Endpoints
- `/api/approvals/workflows` - Manage workflows
- `/api/approvals/steps` - Manage workflow steps
- `/api/approvals/decisions` - Submit approval decisions

## Views
- `workflows.blade.php` - List all workflows
- `steps.blade.php` - View steps for a workflow
- `show.blade.php` - Approval details view
