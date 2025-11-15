# DailyAggregator API Documentation

## Overview
The DailyAggregator processes Redis metrics into MySQL for long-term storage and analysis.

## Endpoints

### `processDailyMetrics`
- **Method**: GET/POST
- **Path**: `/api/aggregate/metrics`
- **Parameters**:
  - `tenantId` (optional): Filter metrics by tenant
- **Response**:
  ```json
  {
    "processed": 1234,
    "errors": 2,
    "retries": 5
  }
  ```

## Error Handling
- Retries failed batches up to 3 times with exponential backoff
- Logs errors to system log
- Returns error count in response

## Rate Limits
- Maximum 1 concurrent aggregation per tenant
- Minimum 5 minutes between runs

## Data Flow
1. Scans Redis for metric keys
2. Processes in batches of 1000
3. Inserts into MySQL with transaction safety
4. Returns statistics