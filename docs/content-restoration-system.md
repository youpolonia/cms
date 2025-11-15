# Automated Content Restoration System Design

## Workflow Diagrams
```mermaid
graph TD
    A[Content Version Created] --> B[Store in Version History]
    B --> C[Mark as Auto-save?]
    C -->|Yes| D[Auto-save Flow]
    C -->|No| E[Manual Version Flow]
    D --> F[Periodic Cleanup]
    E --> G[Available for Restoration]
    G --> H[User Requests Restoration]
    H --> I[Validation Checks]
    I --> J[Approval Required?]
    J -->|Yes| K[Send for Approval]
    J -->|No| L[Execute Restoration]
    K --> M[Approved?]
    M -->|Yes| L
    M -->|No| N[Notify Rejection]
    L --> O[Update Content]
    O --> P[Log Restoration]
    P --> Q[Notify Stakeholders]
```

## Content Validation Requirements
- Schema validation against content_type  
- SEO metadata integrity check  
- Reference integrity (categories, media)  
- AI-generated content watermark verification  
- Change impact analysis  

## Approval System Design
```mermaid
stateDiagram-v2
    [*] --> Pending
    Pending --> Approved: Content Admin
    Pending --> Rejected: Content Admin
    Pending --> NeedsRevision: Content Admin
    NeedsRevision --> Pending: Editor Updates
```

## Notification Requirements
- Email notifications for:
  - Restoration requests
  - Approval decisions  
  - Failed validations
- In-app notifications for:
  - Successful restorations
  - Pending approvals
  - Version comparisons

## Implementation Roadmap
1. **Phase 1: Core Restoration (2 weeks)**
   - Database hooks
   - Version comparison API
   - Basic restoration endpoint

2. **Phase 2: Validation System (1 week)**
   - Content validation service
   - Impact analysis

3. **Phase 3: Approval Workflow (1 week)**
   - Approval queue
   - Decision tracking

4. **Phase 4: Notification System (3 days)**
   - Email templates
   - Webhook integrations

## Restoration Logging System

The RestorationLog model tracks all completed content restorations with:

- **Version Relationships**: Links to both the restored version and original version
- **Audit Trail**: Who performed the restoration and when
- **Metadata**:
  - Restoration method (auto/manual)
  - Number of changes applied
  - Custom notes
- **Soft Deletes**: Preserves history even if log entries are removed

```mermaid
classDiagram
    RestorationLog --> ContentVersion : content_version_id
    RestorationLog --> ContentVersion : original_version_id
    RestorationLog --> User : restored_by
    class RestorationLog {
        +content_version_id
        +original_version_id
        +restored_by
        +restoration_notes
        +metadata
        +completed_at
        +deleted_at
    }
```

## Export Features

The system provides CSV export of restoration logs with:

- **Full version metadata**: Includes all restoration details
- **Filter capabilities**: Date ranges, content types, users
- **Streaming response**: Efficient for large datasets
- **Authentication required**: Only authorized users can export

```mermaid
sequenceDiagram
    User->>+API: GET /api/restoration/logs/export/csv
    API->>+Database: Query logs with relationships
    Database-->>-API: Log data
    API->>+Stream: Generate CSV
    Stream-->>-User: Download file
```

## API Endpoints
```
POST /api/restoration/validate
GET /api/restoration/approvals
POST /api/restoration/approve/{id}
POST /api/restoration/reject/{id}
GET /api/restoration/history
POST /api/restoration/execute
GET /api/restoration/logs/export/csv
GET /api/restoration/logs/export/pdf