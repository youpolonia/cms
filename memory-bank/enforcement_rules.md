# MEMORY BANK ENFORCEMENT SYSTEM

## HARD REQUIREMENTS:
1. System will scan for these files before ANY task:
   - memory-bank/db_migration_rules.md
   - memory-bank/phase3_plan.md
   - memory-bank/decisionLog.md
   - memory-bank/progress.md

2. Task execution blocked until:
   - Files are confirmed read via checksum verification
   - Compliance documented in decisionLog.md
   - Phase alignment confirmed with phase3_plan.md

3. Automatic routing:
   - All database migrations → db-support ONLY
   - Any Laravel patterns → immediate rejection
   - CLI commands → permanent block

## VERIFICATION PROCESS:
1. Pre-task checksum validation of memory bank files
2. Mandatory documentation of approach
3. System lock until requirements met
4. Real-time monitoring of compliance