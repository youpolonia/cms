# Documentation Overhaul Implementation Plan

## 1. Architecture Decision Records (ADRs)
```mermaid
graph TD
    A[Existing Technical Specs] --> B{ADR Conversion}
    B --> C[Format Standardization]
    C --> D[Version History]
    D --> E[Cross-linking]
```

### Conversion Process:
1. Create `docs/architecture/decisions/` directory
2. Convert each section from `technical-specs-v2.md` using template:
```markdown
# ADR-{num}: {Title}

## Status
✅ Approved | ⌛ Proposed | ❌ Deprecated

## Context
{Problem description}

## Decision
{Chosen solution}

## Consequences
- Positive impacts
- Trade-offs
- Migration requirements
```

## 2. User Guides Structure
```mermaid
graph LR
    A[Core Components] --> B[Roles System]
    A --> C[Analytics Dashboard]
    A --> D[MCP Integration]
    
    B --> B1[Permission Models]
    C --> C1[Custom Reports]
    D --> D1[Server Setup]
```

### Implementation Sources:
- Role management: `assign_role.php`, `config/auth.php`
- Analytics: `database/migrations/2025_04_25_183000_create_content_comparison_analytics_table.php`
- MCP: `mcp-knowledge/server.js`

## 3. API Documentation Pipeline
```mermaid
graph LR
    A[Swagger Annotations] --> B[CI Process]
    B --> C[OpenAPI Spec]
    C --> D[MkDocs Integration]
    D --> E[Versioned Docs]
```

### Automation Steps:
1. Configure `config/l5-swagger.php` for spec consolidation
2. Add CI step to generate docs on merge
3. Integrate with mkdocs-material using:
```yaml
plugins:
  - redoc:
      spec: 'api/combined.openapi.yaml'
```

## 4. Roadmap Integration
```mermaid
gantt
    title Documentation Timeline
    dateFormat  YYYY-MM-DD
    section ADRs
    Conversion       :active, doc1, 2025-04-27, 3d
    Review           :doc2, after doc1, 2d
    
    section User Guides
    Core Components  :2025-04-28, 4d
    Advanced Features:2025-05-02, 3d
    
    section API
    Spec Consolidation :2025-04-29, 2d
    UI Integration   :2025-05-01, 2d
```

## 5. Deployment Runbooks
```mermaid
graph TB
    A[Deployment] --> B[Pre-flight Checks]
    B --> C[Database Migrations]
    C --> D[Cache Warmup]
    D --> E[Health Verification]
```

### Key Procedures:
- Zero-downtime deployment using `cache_cms_files.sh`
- Rollback process from `ContentRestorationsTable` schema
- Monitoring integration based on `config/analytics.php`

## Cross-linking Strategy
````markdown
![Documentation Relationships](./diagrams/doc-relationships.mmd)

```mermaid
graph LR
    ADR-->|references|APIDocs
    UserGuide-->|links to|Runbook
    Roadmap-->|version|ADR
```