# Worker Monitoring System Test Cases

## Overview

This document outlines comprehensive test cases for the CMS worker monitoring system. The worker monitoring system tracks the health and status of distributed worker processes through heartbeat signals, health metrics, and failure detection.

## System Components

1. **WorkerController**: Handles API endpoints for worker registration, heartbeats, and status
2. **WorkerSupervisor**: Processes heartbeats, calculates health scores, and initiates recovery
3. **Database**: Stores worker information (worker_id, health_score, failure_count, last_seen)

## Test Scenarios

### 1. Normal Heartbeat Operations

#### 1.1 Successful Heartbeat Processing

**Description**: Test that a worker can successfully send a heartbeat with valid metrics.

**Test Steps**:
1. Send a POST request to `/worker/{workerId}/heartbeat` with valid metrics
2. Verify the response contains success status and health score
3. Check the database to confirm the worker's last_seen time was updated

**Expected Behavior**:
- Response: `{"success": true, "data": {"health_score": 100, "status": "healthy"}}`
- Database: Worker record updated with current timestamp and health score

**Sample Payload**:
```json
{
  "metrics": {
    "memory_usage": 0.45,
    "cpu_usage": 0.30
  }
}
```

#### 1.2 Multiple Consecutive Heartbeats

**Description**: Test that a worker can send multiple heartbeats over time.

**Test Steps**:
1. Send a heartbeat for worker "worker-1" with good metrics
2. Wait 30 seconds
3. Send another heartbeat for the same worker with slightly different metrics
4. Verify both heartbeats were processed correctly

**Expected Behavior**:
- Both heartbeats are processed successfully
- The worker's last_seen time is updated with each heartbeat
- Health score reflects the most recent metrics

#### 1.3 Heartbeat with Missing Metrics

**Description**: Test system handling of heartbeats with missing metrics.

**Test Steps**:
1. Send a POST request to `/worker/{workerId}/heartbeat` with empty metrics
2. Verify the response contains an error message

**Expected Behavior**:
- Response: `{"success": false, "error": "Missing metrics data"}`
- Database: Worker record remains unchanged

**Sample Payload**:
```json
{
  "metrics": {}
}
```

### 2. Failure Detection Thresholds

#### 2.1 Health Score Calculation

**Description**: Test that health scores are calculated correctly based on metrics.

**Test Steps**:
1. Send heartbeats with various combinations of memory_usage and cpu_usage
2. Verify the health scores match the expected calculations

**Test Cases**:
- Low resource usage: memory_usage=0.2, cpu_usage=0.3 → health_score=100
- High memory usage: memory_usage=0.85, cpu_usage=0.3 → health_score=80
- High CPU usage: memory_usage=0.2, cpu_usage=0.95 → health_score=70
- High memory and CPU: memory_usage=0.85, cpu_usage=0.95 → health_score=50

#### 2.2 Warning Threshold Detection

**Description**: Test that workers crossing the warning threshold are properly flagged.

**Test Steps**:
1. Send a heartbeat with metrics that result in a health score just above HEALTH_WARNING (70)
2. Verify the worker is marked as healthy
3. Send a heartbeat with metrics that result in a health score just below HEALTH_WARNING
4. Verify the worker is marked as warning

**Expected Behavior**:
- Health score 71: status="healthy", failure_count=0
- Health score 69: status="warning", failure_count incremented

#### 2.3 Critical Threshold Detection

**Description**: Test that workers crossing the critical threshold trigger recovery.

**Test Steps**:
1. Send heartbeats with progressively worse metrics until health score falls below HEALTH_CRITICAL (30)
2. Verify the worker is marked for recovery

**Expected Behavior**:
- When health_score < 30, the worker should be flagged for recovery
- The alertService should be notified (if configured)

#### 2.4 Failure Count Threshold

**Description**: Test that workers exceeding MAX_FAILURES (3) trigger recovery.

**Test Steps**:
1. Send multiple heartbeats with health scores below warning threshold
2. Verify failure_count increments correctly
3. When failure_count reaches MAX_FAILURES, verify recovery is initiated

**Expected Behavior**:
- failure_count increments with each unhealthy heartbeat
- When failure_count >= 3, recovery is initiated

### 3. Recovery Procedures

#### 3.1 Automatic Recovery Initiation

**Description**: Test that recovery is automatically initiated for unhealthy workers.

**Test Steps**:
1. Configure a mock alertService
2. Force a worker into an unhealthy state (either by health score or failure count)
3. Call the checkWorkers() method
4. Verify the alertService was notified

**Expected Behavior**:
- alertService receives notification: "Worker {workerId} requires recovery"

#### 3.2 Recovery Success

**Description**: Test successful worker recovery.

**Test Steps**:
1. Force a worker into an unhealthy state
2. Simulate recovery by sending a healthy heartbeat
3. Verify the worker's health score improves and failure_count resets

**Expected Behavior**:
- After recovery, health_score > 70
- failure_count resets to 0
- last_failure_time is set to NULL

#### 3.3 Recovery Failure

**Description**: Test handling of failed recovery attempts.

**Test Steps**:
1. Force a worker into an unhealthy state
2. Simulate multiple failed recovery attempts
3. Verify the system handles persistent failures appropriately

**Expected Behavior**:
- System continues to attempt recovery
- Alerts are sent for each failed recovery attempt

### 4. Edge Cases

#### 4.1 Network Latency Simulation

**Description**: Test system behavior under network latency conditions.

**Test Steps**:
1. Configure a test environment with simulated network delays
2. Send heartbeats with varying delays
3. Verify the system correctly handles delayed heartbeats

**Expected Behavior**:
- Delayed heartbeats are still processed correctly
- System doesn't mark workers as unhealthy due solely to network delays

#### 4.2 Duplicate Heartbeats

**Description**: Test handling of duplicate or rapid-succession heartbeats.

**Test Steps**:
1. Send multiple identical heartbeats in rapid succession
2. Verify the system handles duplicates appropriately

**Expected Behavior**:
- All heartbeats are processed
- No errors or unexpected behavior occurs
- Database reflects the most recent heartbeat

#### 4.3 Worker Resurrection

**Description**: Test behavior when a previously "dead" worker sends a heartbeat.

**Test Steps**:
1. Create a worker that hasn't sent a heartbeat for over 1 hour
2. Send a new heartbeat from this worker
3. Verify the worker is properly "resurrected" in the system

**Expected Behavior**:
- Worker record is updated with new heartbeat data
- Worker appears in active worker lists

#### 4.4 System Under Load

**Description**: Test behavior when many workers send heartbeats simultaneously.

**Test Steps**:
1. Simulate 100+ workers sending heartbeats within a short time window
2. Monitor system performance and response times
3. Verify all heartbeats are processed correctly

**Expected Behavior**:
- All heartbeats are processed without errors
- System maintains acceptable performance

## Success/Failure Criteria

### Success Criteria

1. **Heartbeat Processing**: All valid heartbeats are processed and stored correctly
2. **Health Monitoring**: Health scores accurately reflect worker conditions
3. **Failure Detection**: Unhealthy workers are identified within one heartbeat cycle
4. **Recovery**: Recovery procedures are initiated for workers meeting failure criteria
5. **Scalability**: System handles the expected number of workers without performance degradation
6. **Resilience**: System recovers from temporary network or worker issues

### Failure Criteria

1. **False Positives**: Healthy workers incorrectly marked as unhealthy
2. **False Negatives**: Unhealthy workers not detected
3. **Data Loss**: Heartbeat data not properly recorded
4. **Performance Issues**: System unable to process heartbeats in a timely manner
5. **Recovery Failures**: System unable to recover unhealthy workers

## Verification Steps

For each test case:

1. Set up the test environment with known initial state
2. Execute the test steps
3. Verify the actual results match expected behavior
4. Document any discrepancies
5. Reset the environment for the next test

## Sample Payloads

### Heartbeat Request

```json
{
  "metrics": {
    "memory_usage": 0.45,
    "cpu_usage": 0.30,
    "queue_depth": 12,
    "active_tasks": 3
  }
}
```

### Status Response

```json
{
  "success": true,
  "data": {
    "workers": [
      {
        "worker_id": "worker-1",
        "health_score": 95,
        "failure_count": 0,
        "last_seen": "2025-05-18 11:05:23"
      },
      {
        "worker_id": "worker-2",
        "health_score": 65,
        "failure_count": 1,
        "last_seen": "2025-05-18 11:04:17"
      }
    ],
    "timestamp": 1716026723
  }
}
```

## Known Issues and Limitations

1. Recovery logic is currently a placeholder (marked as TODO in the code)
2. There's a discrepancy between routes defined in api/v1/routes/worker.php and the actual controller implementation
3. No tests currently exist for WorkerController or WorkerSupervisor
4. The workers table schema is not documented in migration files