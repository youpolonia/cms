# Concurrent Approval Test Cases

## Race Condition Tests
1. **Concurrent Approval Attempts**
   - Setup: Create version in pending state
   - Action: Simulate 5 concurrent approval requests
   - Verify: Only one approval succeeds, others fail gracefully
   - Check: Approval log contains exactly one entry

2. **Version Numbering Under Load**
   - Setup: Empty content branch
   - Action: Simulate 10 concurrent version creations
   - Verify: All versions get unique sequential numbers
   - Check: No duplicate version numbers exist

## Locking Mechanism Tests
3. **Row Locking Verification**
   - Setup: Create pending version
   - Action: Start approval transaction with SELECT FOR UPDATE
   - Verify: Concurrent select is blocked until commit
   - Check: Second transaction times out after 5s

4. **Optimistic Concurrency Control**
   - Setup: Create version with initial hash
   - Action: Modify version content before approval
   - Verify: Approval fails with concurrency error
   - Check: Status remains pending after failed approval

## Transaction Isolation Tests  
5. **Dirty Read Prevention**
   - Setup: Start uncommitted approval
   - Action: Attempt to read version status in separate transaction
   - Verify: Reads show pre-approval state
   - Check: Changes not visible until commit

6. **Non-repeatable Reads**
   - Setup: Read version status
   - Action: Approve version in separate transaction
   - Verify: Repeat read shows same initial status
   - Check: Changes only visible in new transaction

7. **Phantom Reads**
   - Setup: Count pending versions
   - Action: Create new version in separate transaction
   - Verify: Repeat count shows same initial number
   - Check: New version only visible in new transaction

## Test Execution Notes
- Run tests with isolation levels: READ COMMITTED, REPEATABLE READ
- Measure performance impact of locking
- Verify rollback cleans up all intermediate states