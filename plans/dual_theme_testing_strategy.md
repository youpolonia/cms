# Dual Theme System Testing Strategy

## Unit Tests
1. ThemeManager modifications:
   - Test context-based theme selection (public vs admin)
   - Verify theme metadata loading
   - Test theme validation rules

## Integration Tests
1. Theme switching:
   - Verify admin switcher updates database
   - Test session fallback when DB unavailable
   - Check public/admin themes operate independently

## UI Tests
1. Admin interface:
   - Verify theme preview functionality
   - Test activation workflow
   - Check theme cards display correctly

## Cross-Browser Testing
1. Chrome, Firefox, Safari
2. Mobile responsiveness

## Performance Tests
1. Theme loading times
2. Concurrent theme switching

## Security Tests
1. XSS protection in theme metadata
2. Path traversal prevention
3. Session fixation checks

## Test Data
```mermaid
graph LR
    A[Test Themes] --> B[Valid Theme]
    A --> C[Invalid Theme]
    A --> D[Missing Metadata]
    A --> E[Broken Templates]