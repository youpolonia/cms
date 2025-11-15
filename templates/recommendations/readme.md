# Recommendation Components

## Components
1. **RecommendationList** - Main container component that fetches and displays recommendations
2. **RecommendationCard** - Individual recommendation item display
3. **FeedbackWidget** - Handles user ratings/feedback

## Usage
Include the main template in your page:
```html
<?php include_once('templates/recommendations/main.html'); ?>
```

## API Integration
The components expect the following API response format:
```json
[
    {
        "id": 1,
        "title": "Recommended Item",
        "description": "Item description",
        "image": "optional-image-url.jpg"
    }
]
```

Feedback submissions are sent to:
```
POST /api/recommendations/feedback
{
    "recommendationId": 1,
    "rating": 5
}
```

## Styling
Customize the appearance by modifying:
`/public/js/components/recommendations/recommendations.css`

## Features
- Responsive design
- Loading states
- Error handling
- Accessible markup
- User feedback collection