# Custom Post Type Builder Plan

## Module Structure
```
modules/CPT/
├── CPTRegistry.php
├── CPTEditor.php
├── CPTRenderer.php
├── schemas/
└── templates/
```

## JSON Schema Example
```json
{
  "name": "event",
  "label": "Events",
  "fields": [
    {
      "name": "title",
      "type": "text",
      "required": true
    },
    {
      "name": "date",
      "type": "date"
    }
  ]
}
```

## Integration Points
- PageBuilder Core: Field rendering
- Builder Engine: Layout templates
- Plugin System: Hooks

## Roadmap
| Phase | Features |
|-------|----------|
| MVP | Basic fields + Admin UI |
| Core | Advanced fields + Templates |
| Optional | REST API + AI features |

## Risks
1. Schema versioning
2. Legacy content migration
3. Performance optimization