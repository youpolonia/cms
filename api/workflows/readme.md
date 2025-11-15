# Workflow Automation API

## Endpoints

### Workflow Management
- `GET /api/workflows` - List all workflows
- `POST /api/workflows` - Create new workflow
- `GET /api/workflows/{id}` - Get workflow details
- `PUT /api/workflows/{id}` - Update workflow
- `DELETE /api/workflows/{id}` - Delete workflow

### Trigger Management
- `GET /api/triggers` - List all triggers
- `POST /api/triggers` - Create new trigger
- `GET /api/triggers/{id}` - Get trigger details
- `PUT /api/triggers/{id}` - Update trigger
- `DELETE /api/triggers/{id}` - Delete trigger

### Instance Tracking
- `GET /api/instances` - List active instances
- `GET /api/instances/{id}` - Get instance details
- `POST /api/instances/{id}/approve` - Approve step
- `POST /api/instances/{id}/reject` - Reject step

### Status Monitoring
- `GET /api/history` - Get approval history
- `GET /api/history/{id}` - Get history entry
- `GET /api/status` - Get system status