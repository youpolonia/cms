# Workflow Template Deployment

## Configuration Changes
1. Added WORKFLOW_TEMPLATE_PATH constant (config.php)
2. Updated template endpoint reference (api/workflow/templates.php)

## Environment Variables
```bash
# Optional override for template location
CMS_WORKFLOW_TEMPLATE_PATH=/custom/path/to/templates
```

## Verification Steps
1. Confirm directory creation:
```php
<?php 
require_once 'config.php';
var_dump(is_dir(WORKFLOW_TEMPLATE_PATH)); // Should return true
```

2. Test endpoint access:
```bash
curl -X GET http://localhost/api/workflow/templates