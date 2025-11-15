# Theme Management Testing Procedures

## Overview
This document outlines the testing procedures for the Theme Management system, including both automated and manual testing approaches.

## Test Types

### 1. Unit Tests
- Location: `tests/ThemeManagerTest.php`
- Purpose: Verify individual methods of ThemeManager class
- Run with: `phpunit tests/ThemeManagerTest.php`

### 2. Web Test Runner
- Location: `tests/theme_test_runner.php`
- Purpose: Browser-based testing of theme functionality
- Access via: `http://yourdomain.com/tests/theme_test_runner.php`

## Test Cases

### Theme Switching
1. Switch to default theme
2. Switch to child theme  
3. Attempt invalid theme switch
4. Verify theme persistence

### Theme Detection
1. Detect active theme
2. Verify theme exists
3. Validate theme configuration

### Theme Rendering
1. Render full template
2. Render partial template
3. Verify template inheritance

## Test Data Requirements
- Minimum 2 test themes (default + child)
- Each theme must contain:
  - `theme.json` config
  - `templates/` directory
  - `assets/` directory

## Test Environment Setup
1. Ensure test themes exist in `themes/` directory
2. Verify write permissions for `cache/` directory
3. Clear theme cache before testing

## Expected Results
All tests should:
- Pass with valid input
- Fail gracefully with invalid input
- Maintain consistent state between requests

## Troubleshooting
- **Theme not switching**: Check file permissions and cache
- **Missing templates**: Verify theme directory structure
- **Configuration errors**: Validate `theme.json` files