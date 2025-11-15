# Recommendation System Documentation

## Architecture Overview

The recommendation system consists of:
1. **Recommendation Analytics Model** (`includes/models/RecommendationAnalytics.php`)
   - Tracks impressions, clicks, dismissals, and feedback
   - Generates performance reports and detects anomalies
2. **API Endpoints** (`includes/routing/api/recommendations.php`)
   - GET `/api/recommendations` - List recommendations
   - GET `/api/recommendations/{id}` - Get single recommendation
   - POST `/api/recommendations/feedback` - Submit feedback

## API Reference

### GET /api/recommendations
Returns a list of recommendations based on user preferences and content relationships.

**Response Example:**
```json
{
  "recommendations": [
    {
      "id": "rec_123",
      "content_id": "cont_456",
      "title": "Recommended Content",
      "score": 0.85,
      "type": "similar_content"
    }
  ]
}
```

### GET /api/recommendations/{id}
Returns details for a specific recommendation.

**Parameters:**
- `id`: Recommendation ID

### POST /api/recommendations/feedback
Submit feedback about a recommendation.

**Request Body:**
```json
{
  "recommendation_id": "rec_123",
  "rating": 5,
  "feedback": "Great recommendation!"
}
```

## Analytics Setup

The `RecommendationAnalytics` model provides these key methods:

1. `recordImpression($recommendationId, $contentId, $userId, $sessionId, $ip, $userAgent)`
   - Records when a recommendation is shown to a user

2. `recordClick($recommendationId)`
   - Records when a user clicks on a recommendation

3. `recordDismissal($recommendationId)`
   - Records when a user dismisses a recommendation

4. `recordFeedback($recommendationId, $rating)`
   - Records user feedback (1-5 scale)

5. `generateReport($days = 7)`
   - Returns aggregated metrics including:
     - Total impressions, clicks, dismissals
     - Average rating
     - Daily metrics
     - Performance anomalies

## Usage Examples

### Tracking a Recommendation Impression
```php
$analytics = new RecommendationAnalytics();
$analytics->recordImpression(
    'rec_123', 
    'cont_456',
    $userId,
    $sessionId,
    $_SERVER['REMOTE_ADDR'],
    $_SERVER['HTTP_USER_AGENT']
);
```

### Generating a Weekly Report
```php
$analytics = new RecommendationAnalytics();
$report = $analytics->generateReport(7);

// Returns:
// [
//    'time_period' => "7 days",
//    'total_impressions' => 1250,
//    'total_clicks' => 320,
//    'total_dismissals' => 80,
//    'average_rating' => 4.2,
//    'daily_metrics' => [...],
//    'anomalies' => [...]
// ]
```

## Version History
- v1.0 (2025-04-01): Initial implementation
- v1.1 (2025-04-15): Added anomaly detection
- v1.2 (2025-04-28): Enhanced feedback collection