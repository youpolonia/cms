# Version Branching/Merging System Design

## Database Schema

```mermaid
erDiagram
    CONTENTS ||--o{ CONTENT_VERSIONS : has
    CONTENTS ||--o{ BRANCHES : has
    CONTENT_VERSIONS ||--o{ BRANCH_VERSIONS : maps
    BRANCHES ||--o{ BRANCH_VERSIONS : has
    BRANCHES ||--o{ MERGE_REQUESTS : has
    MERGE_REQUESTS ||--o{ MERGE_CONFLICTS : has

    CONTENTS {
        bigint id PK
        string title
        text content
        string slug
        string content_type
        json ai_metadata
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    CONTENT_VERSIONS {
        bigint id PK
        bigint content_id FK
        text content
        json changes
        string version_hash
        boolean is_autosave
        timestamp created_at
    }

    BRANCHES {
        bigint id PK
        bigint content_id FK
        string name
        string description
        bigint base_version_id FK
        bigint head_version_id FK
        string status
        timestamp created_at
        timestamp updated_at
    }

    BRANCH_VERSIONS {
        bigint id PK
        bigint branch_id FK
        bigint version_id FK
        timestamp created_at
    }

    MERGE_REQUESTS {
        bigint id PK
        bigint source_branch_id FK
        bigint target_branch_id FK
        string status
        json merge_commit
        bigint merged_by
        timestamp created_at
        timestamp merged_at
    }

    MERGE_CONFLICTS {
        bigint id PK
        bigint merge_request_id FK
        string conflict_type
        json conflict_data
        string resolution
        bigint resolved_by
        timestamp created_at
        timestamp resolved_at
    }
```

## API Endpoints

### Branch Management
- `POST /api/contents/{id}/branches` - Create new branch
- `GET /api/contents/{id}/branches` - List branches
- `GET /api/branches/{id}` - Get branch details
- `DELETE /api/branches/{id}` - Delete branch

### Version Operations
- `POST /api/branches/{id}/versions` - Create version in branch
- `GET /api/branches/{id}/versions` - List branch versions

### Merge Operations
- `POST /api/merge-requests` - Create merge request
- `GET /api/merge-requests/{id}/conflicts` - List merge conflicts
- `POST /api/merge-requests/{id}/resolve` - Resolve conflict
- `POST /api/merge-requests/{id}/complete` - Complete merge

### Comparison
- `GET /api/branches/{id}/compare/{otherBranchId}` - Compare branches
- `GET /api/versions/{id}/diff/{otherVersionId}` - Get version diff

## Background Processing

### Merge Job Queue
- Processes merge requests asynchronously
- Detects and records conflicts
- Sends notifications

### Conflict Detection Service
- Content diffing
- Structural conflict detection
- Metadata conflict detection

### Notification System
- Merge request status updates
- Conflict alerts
- Merge completions

## Frontend Components

### Branch Visualization
- Interactive branch graph
- Version timeline
- Diff viewer

### Merge Interface
- Side-by-side comparison
- Conflict resolution tools
- Merge preview

### Branch Management
- Create/edit branches
- Version history
- Branch permissions

## Security Model

### Branch Permissions
- Role-based access control
- Branch-level permissions
- Ownership model

### Merge Approvals
- Required approvers
- Approval workflows
- Audit trail

### Audit Logging
- Branch operations
- Merge activities
- Conflict resolutions