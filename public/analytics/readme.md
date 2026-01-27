# Analytics Dashboard Components

## Overview
The analytics dashboard provides real-time monitoring of system performance metrics, token usage, and cache statistics. It consists of:

- `index.html`: Main dashboard interface
- `dashboard.js`: Core functionality and data visualization
- `styles.css`: Responsive styling
- `test.html`: Endpoint testing interface

## Integration

### AnalyticsService API
The dashboard integrates with the following endpoints:

- `GET /api/v2/analytics/performance`
  - Returns: 
    ```json
    {
      "labels": ["timestamp1", "timestamp2"],
      "responseTimes": [ms1, ms2],
      "queryTimes": [ms1, ms2]
    }
    ```

- `GET /api/v2/analytics/token-usage`
  - Returns:
    ```json
    {
      "labels": ["timestamp1", "timestamp2"],
      "usage": [percentage1, percentage2]
    }
    ```

- `GET /api/v2/analytics/cache-stats`
  - Returns:
    ```json
    {
      "hits": number,
      "misses": number,
      "hitRate": percentage,
      "size": kilobytes
    }
    ```

## Testing
Run tests using `test.html` which provides:
- Individual endpoint testing
- Response visualization
- Error handling

## Responsive Design
The dashboard adapts to:
- Desktop (grid layout)
- Tablet (stacked widgets)
- Mobile (single column)

## Error Handling
The dashboard includes:
- Visual error indicators
- Automatic retry mechanism
- Manual refresh option