# Workflow Builder Design Documentation

## Component Structure

### WorkflowBuilder.vue
- Main container component
- Manages workflow state and version control
- Contains trigger/action panels and canvas

### TriggerPanel.vue
- Displays available triggers
- Emits events when triggers are selected

### ActionPanel.vue
- Displays available actions
- Emits events when actions are selected

### WorkflowNode.vue
- Visual representation of workflow steps
- Handles individual node configuration

## API Endpoints

### GET /api/workflows/versions
- Returns list of saved workflow versions

### POST /api/workflows/versions
- Saves new workflow version
- Accepts JSON payload with nodes array

## Trigger Condition Syntax
- Uses simple JSON structure:
```json
{
  "type": "content_published",
  "params": {
    "content_type": "post"
  }
}
```

## Step Configuration Architecture
- Each node maintains its own config
- Config panel rendered based on node type
- Saved as part of workflow version