# Conditional Publishing Guide

## Creating Rules

1. Navigate to Content → Conditional Publishing
2. Click "Create New Rule"
3. Fill in basic details:
   - Rule Name (required)
   - Description (optional)

### Adding Conditions
Click "Add Condition" to define when the rule should execute:

```yaml
conditions:
  - field: status
    operator: equals
    value: draft
  - field: category
    operator: contains
    value: news
```

Supported condition fields:
- `status`: Content workflow status
- `category`: Content category
- `tags`: Content tags

### Defining Actions
Click "Add Action" to specify what happens when conditions are met:

```yaml
actions:
  - type: publish
    value: "immediately"
  - type: notify
    value: "editor@example.com"
```

Supported action types:
- `publish`: Change content status
- `notify`: Send email notification
- `move`: Move to different category

## Common Use Cases

### Scheduled Publishing
```yaml
conditions:
  - field: status
    operator: equals
    value: ready_for_review
actions:
  - type: publish
    value: "2025-05-01T08:00:00Z"
```

### Category-Based Approval
```yaml
conditions:
  - field: category
    operator: contains
    value: legal
actions:
  - type: notify
    value: "legal-team@example.com"
```

## Best Practices

1. **Keep rules simple** - Break complex logic into multiple rules
2. **Use descriptive names** - "Auto-publish News after Legal Review"
3. **Test with sample content** - Verify conditions match expected content
4. **Monitor execution** - Check logs for evaluation results
5. **Document rules** - Add comments explaining business logic

## Troubleshooting

**Rule not executing?**
- Verify content matches all conditions
- Check rule is active
- Review evaluation logs

**Unexpected actions?**
- Validate condition logic
- Check for conflicting rules
- Review action parameters