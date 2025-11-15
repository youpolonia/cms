# Workflow Template Library System

## Overview
The Workflow Template Library provides pre-configured workflow templates that can be imported into the WorkflowBuilder interface. Templates follow a standardized JSON format and include all necessary steps, conditions, and actions.

## Template Format Specification
```json
{
  "name": "Template Name",
  "version": "1.0",
  "description": "Template purpose",
  "steps": [
    {
      "id": "step1",
      "type": "action|condition|approval",
      "config": {
        // Step-specific configuration
      }
    }
  ],
  "variables": {
    // Template-scoped variables
  },
  "security": {
    "required_roles": ["editor", "admin"],
    "content_types": ["page", "post"]
  }
}
```

## Admin UI Usage
1. Navigate to Admin → Workflows → Templates
2. Use the template importer (top-right) to add new templates
3. Existing templates can be:
   - Previewed
   - Edited (creates a copy)
   - Exported
   - Deleted (if unused)

## WorkflowBuilder Integration
Templates integrate with [`admin/workflow/WorkflowBuilder.vue`](admin/workflow/WorkflowBuilder.vue) through:
- Template selection dropdown
- One-click import button
- Template validation before import

## Security Considerations
1. All templates are validated against schema
2. Role requirements are enforced during import
3. Template actions are sandboxed
4. Content type restrictions are applied

## Example Use Cases
1. **Content Approval Workflow**
   - Draft → Editor Review → Publish
   - Automatic notifications at each stage

2. **Multi-language Translation**
   - Source content → Translation assignment → Review → Publish

3. **Scheduled Content Update**
   - Draft → Schedule → Automatic publish at target date