# Workflow API Test Cases

## Version Control Integration
1. Verify new version created on approval
2. Test version comparison endpoint (/api/versions/{id}/content)
3. Validate JSON diff structure in version_history

## Concurrent Approvals
1. Simulate parallel approval requests
2. Verify transaction isolation
3. Check version numbering consistency

## Rejected Submissions
1. Test rollback creates correct version
2. Verify workflow history maintained
3. Check rejected state transitions

## Permission Validation
1. Test user role access to versions
2. Verify approval permissions
3. Check version history visibility rules