# Widget System Test Cases

## Test Scenarios:
1. **Basic Text Widget**
   - Verify renders plain text content
   - Test HTML escaping
   - Test empty content case

2. **Missing Widget Fallback**
   - Request non-existent widget
   - Verify error_fallback.php renders
   - Check _widget_name is passed correctly

3. **Tenant Override**
   - Create tenant-specific widget
   - Verify overrides default template
   - Test fallback when tenant template missing

## Expected Results:
- All widgets should render without PHP errors
- Missing widgets should use error_fallback.php
- Tenant-specific templates should take precedence