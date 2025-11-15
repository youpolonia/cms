# Personalization Engine API

## Overview
The enhanced personalization engine provides advanced content targeting capabilities through:

- User segmentation
- Behavioral analytics
- Machine learning recommendations
- Performance optimization

## Key Features

### User Segmentation
Users are automatically classified into segments based on:

- **Demographics**: Age groups, location
- **Behavior**: Engagement level, content preferences
- **Interests**: Multiple interest categories

Example segments:
- `under_18`, `18_24`, `25_34`, `35_44`, `45_plus`
- `new_user`, `casual_user`, `active_user`, `power_user`
- `multi_interest`

### Content Recommendations
Recommendations are generated using a weighted combination of:

1. **Segment-based** (40% weight)
   - Content optimized for user's demographic and behavioral segments
2. **History-based** (30% weight)
   - Content similar to previously viewed items
3. **Collaborative** (20% weight)
   - Content popular with similar users
4. **Trending** (10% weight)
   - Currently popular content

### Analytics Endpoints

#### Get User Segments
`GET /api/analytics/personalization/user/{user_id}/segments`

Returns:
```json
{
  "data": {
    "segments": ["25_34", "active_user", "multi_interest"]
  }
}
```

#### Get Segment Recommendations
`GET /api/analytics/personalization/segment/{segment_id}/recommendations`

Returns:
```json
{
  "data": {
    "segment": "25_34",
    "recommendations": [
      {
        "id": 123,
        "title": "Example Content",
        "score": 0.85
      }
    ]
  }
}
```

#### Get Content Performance
`GET /api/analytics/personalization/content/{content_id}`

Returns engagement metrics for specific content across segments.

## Implementation Details

### Caching Strategy
All personalization data is cached with 1-hour TTL to optimize performance. Cache keys include:

- `user:{id}:segments`
- `user:{id}:personalized_content`
- `segment:{id}:recommendations`

### Refresh Mechanism
Call `POST /api/personalization/refresh/{user_id}` to force recalculation of recommendations.

## Migration Guide
Existing implementations will continue working with these changes:

1. The `getPersonalizedContent()` method maintains backward compatibility
2. New segment data is automatically collected
3. Recommendation weights have been adjusted for better results