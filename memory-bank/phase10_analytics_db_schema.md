# Phase 10 Database Schema - Multi-Tenant Analytics

## Tables to be Created

### tenant_analytics_events
- Stores all analytics events with tenant isolation
- Fields:
  - id: SERIAL PRIMARY KEY
  - tenant_id: VARCHAR(50) NOT NULL
  - event_type: VARCHAR(100) NOT NULL
  - event_data: JSON NOT NULL
  - timestamp: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  - user_id: VARCHAR(50) NULL
  - session_id: VARCHAR(100) NULL
  - ip_address: VARCHAR(45) NULL
  - user_agent: VARCHAR(255) NULL
  - Indexes: 
    - (tenant_id, timestamp) - Composite index for efficient tenant-specific queries
    - (event_type) - For filtering by event type
    - (user_id) - For user-specific analytics

### analytics_reports
- Stores generated analytics reports
- Fields:
  - report_id: VARCHAR(50) PRIMARY KEY
  - tenant_id: VARCHAR(50) NOT NULL
  - report_type: VARCHAR(100) NOT NULL
  - parameters: JSON NOT NULL
  - result_data: JSON NULL
  - generated_at: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  - expires_at: TIMESTAMP NULL
  - status: ENUM('pending', 'completed', 'failed') DEFAULT 'pending'
  - Indexes:
    - (tenant_id, report_type) - For tenant-specific report retrieval
    - (expires_at) - For cleanup of expired reports

### analytics_aggregates
- Stores pre-calculated aggregate metrics for performance
- Fields:
  - id: SERIAL PRIMARY KEY
  - tenant_id: VARCHAR(50) NOT NULL
  - metric_key: VARCHAR(100) NOT NULL
  - metric_value: DECIMAL(15,4) NOT NULL
  - dimension: VARCHAR(100) NULL
  - dimension_value: VARCHAR(100) NULL
  - time_period: VARCHAR(20) NOT NULL
  - start_date: DATE NOT NULL
  - end_date: DATE NOT NULL
  - updated_at: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  - Indexes:
    - (tenant_id, metric_key, time_period) - For efficient dashboard queries
    - (start_date, end_date) - For time-based filtering

## Relationships
- All tables include tenant_id for tenant isolation
- No foreign key constraints between analytics tables to optimize write performance
- Soft references to user_id for user tracking without strict constraints

## Data Partitioning Strategy
- Monthly partitioning for tenant_analytics_events table
- Tenant-based sharding for high-volume tenants

## Security Considerations
- Row-level security policies for tenant isolation
- Encryption for sensitive event data
- Audit logging for all data access

## Test Endpoints
- `/api/test/analytics-events.php` - Returns first 10 records from tenant_analytics_events table
- `/api/test/analytics-reports.php` - Returns first 10 records from analytics_reports table