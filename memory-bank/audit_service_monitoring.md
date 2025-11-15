# AuditService Post-Deployment Monitoring Plan

## 1. Performance Metrics Tracking
- **Response Times**: Monitor API endpoints via `/api/audit/log` and `/api/audit/query`
  - Thresholds: <500ms (P95), <1s (P99)
  - Alert if: >1s sustained for 5 minutes
- **Throughput**: Track requests per minute (RPM)
  - Baseline: 1000 RPM
  - Alert if: <500 RPM or >1500 RPM sustained
- **Resource Usage**:
  - Memory: Alert if >80% of allocated
  - CPU: Alert if >70% sustained for 10 minutes

## 2. Error Logging Thresholds
- **Error Levels**:
  - WARN: Non-critical issues (logged to `audit_warnings.log`)
  - ERROR: Service-impacting (logged to `audit_errors.log`)
  - FATAL: Service failure (immediate alert)
- **Thresholds**:
  - >5 WARN/min for 15 minutes → Notify
  - >1 ERROR/min → Page on-call
  - Any FATAL → Immediate escalation

## 3. Tenant Isolation Checks
- **Verification Tests**:
  1. Inject test audit entries across tenants
  2. Verify queries return only tenant-specific data
  3. Check cross-tenant ID collisions
- **Schedule**: Run hourly via `/api/audit/test/isolate`
- **Alert**: If any cross-tenant data leakage detected

## 4. Scheduled Verification Tests
- **Daily**:
  - Full audit trail integrity check
  - Verify retention policy enforcement
  - Test rollback procedures
- **Weekly**:
  - Load test with 2x expected traffic
  - Verify backup/restore procedures
  - Test failover scenarios

## Monitoring Implementation
- **Tools**:
  - Built-in PHP monitoring via `admin/monitoring/audit.php`
  - Cron jobs for scheduled tests
  - Error tracking via `admin/error-logging/`
- **Documentation**: Updated in `memory-bank/audit_docs.md`