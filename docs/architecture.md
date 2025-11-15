# System Architecture

## Overview
The CMS is built as a custom PHP application with a modular architecture and clear separation between:
- Presentation layer (Views/Components)
- Application layer (Controllers/Services)
- Domain layer (Models/Repositories)
- Infrastructure layer (Database/External Services)

## Core Components

```mermaid
graph TD
    A[Client] --> B[Web Server]
    B --> C[Application]
    C --> D[Database]
    C --> E[Redis Cache]
    C --> F[External APIs]
    F --> G[AI Services]
    F --> H[Analytics]
    C --> I[Personalization Engine]
    C --> J[Search Enhancement]
    J --> K[Vector Database]
    C --> L[TenantManager]
    L --> M[Tenant Storage]
    L --> N[Quota Enforcement]
```

## Data Flow

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant Backend
    participant Database
    participant AI
    
    User->>Frontend: Interacts with UI
    Frontend->>Backend: API Requests
    Backend->>Database: CRUD Operations
    Backend->>AI: Content Generation
    AI-->>Backend: Generated Content
    Backend-->>Frontend: API Responses
    Frontend-->>User: Updates UI
```

## Key Technologies
- **Backend**: PHP 8.2, Custom Framework
- **Frontend**: Vanilla JS, Custom Components
- **Database**: MySQL 8, Redis
- **Search**: Custom Search Implementation
- **AI Integration**: OpenAI API
- **Analytics**: Custom Analytics System
- **Personalization**: MCP Personalization Engine
- **Search Enhancement**: MCP Search Service

## Multi-Tenant Architecture

The system implements comprehensive tenant isolation through:

- **TenantManager**: Central service for tenant configuration
  - Schema-per-tenant database isolation
  - Automated tenant initialization
- **Isolated Storage**: Dedicated storage paths per tenant
  - `/storage/tenants/{tenant_id}/` structure
- **Quota Enforcement**: Resource limits per tenant
  - Storage quota monitoring
  - Request rate limiting

```mermaid
flowchart TD
    subgraph Tenant Initialization
    A[Create Tenant] --> B[Setup Database Schema]
    B --> C[Create Storage Structure]
    C --> D[Apply Configuration]
    end
    
    subgraph Runtime Operation
    Tenant -->|Requests| TenantManager
    TenantManager -->|Validate| Quotas
    TenantManager -->|Apply| Config
    TenantManager -->|Route| AppCore
    AppCore -->|Isolated Access| Storage
    AppCore -->|Tenant-aware| Database
    end
```

## Deployment
The system is deployed via FTP with:
- Web servers (Apache/Nginx)
- Database servers
- Scheduled tasks via cron