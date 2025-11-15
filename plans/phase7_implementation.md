# Phase 7 Implementation Plan

## 1. Security Audit Logging
```mermaid
flowchart TD
    A[RBAC Permission Check] --> B{Allowed?}
    B -->|Yes| C[Log Success]
    B -->|No| D[Log Failure]
    C & D --> E[Write to security_log]
```

Implementation:
- Add logging to RBAC::checkPermission()
- Log format: 
  ```json
  {
    "timestamp": "ISO8601",
    "user_id": "int",
    "action": "string",
    "resource": "string",
    "allowed": "bool"
  }
  ```

## 2. Query Logging
- New DatabaseLogger class
- Log slow queries (>500ms)
- Capture:
  - SQL with sanitized parameters
  - Execution time
  - Call stack

## 3. Performance Monitoring
- Extend Heartbeat system:
  - Track memory usage
  - Log metrics hourly
  - Alert thresholds:
    - Memory > 80%
    - Response time > 2s