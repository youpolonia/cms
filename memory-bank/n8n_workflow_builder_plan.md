# n8n Workflow Builder Module – Technical Blueprint

## Context

This CMS is a pure-PHP, FTP-only content management system designed for schools and small businesses. It runs standalone without any framework dependencies (no Laravel, Symfony, etc.) and requires no CLI tooling or build steps.

**n8n Integration Vision**: This module will allow CMS administrators to connect to and control their own n8n instance directly from within the CMS admin panel. Rather than requiring administrators to learn n8n's interface separately, they can manage workflows for common tasks (teacher material generation, lead capture, automated publishing, etc.) from the familiar CMS environment.

**Key Principle**: n8n remains the workflow execution engine and source of truth for workflow definitions. The CMS acts as a friendly administrative interface and integration point, using n8n's REST API to:
- Configure connection settings
- List and filter workflows
- Trigger workflows manually
- (Future) Build workflows using guided, template-based wizards

This design allows non-technical users to leverage powerful automation without leaving their content management environment.

---

## High-Level Architecture

### Module Overview

The **n8n Integration** module consists of three main components:

1. **Core Helper Library** (`core/n8n_client.php`)
   - Pure PHP HTTP client for n8n REST API
   - Configuration loader/saver for connection settings
   - Error handling and response normalization
   - Zero database dependencies

2. **Configuration File** (`config/n8n_settings.json`)
   - File-based storage for connection details (base URL, API token)
   - Allows easy backup and version control
   - Kept separate from root `config.php` for modularity
   - Never committed with real credentials

3. **Admin Pages**
   - **n8n Settings** (`admin/n8n-settings.php`): Configure connection, test connectivity
   - **n8n Workflows** (`admin/n8n-workflows.php`): Browse, filter, view, trigger workflows
   - **Workflow Builder** (future): Template-based guided wizards for common use cases

### Responsibilities Split

**CMS Responsibilities:**
- Store connection settings to n8n instance (base URL, API token, timeout, SSL verification)
- Provide secure, admin-only UI for listing workflows
- Provide UI for triggering workflows manually
- Expose webhook endpoints that n8n can call to interact with CMS data (e.g., create content, query users)
- Handle authentication, CSRF protection, session management for all n8n-related admin pages
- Provide guided wizards (future) that generate workflow JSON and push to n8n

**n8n Responsibilities:**
- Execute all workflow logic
- Store workflow definitions, credentials for external services
- Connect to third-party APIs (Google Workspace, email providers, databases, etc.)
- Maintain execution history and logs
- Provide REST API for workflow management

**Data Flow:**
```
CMS Admin UI → core/n8n_client.php → HTTP(S) → n8n REST API
                                                    ↓
                                            n8n executes workflow
                                                    ↓
                                            (optionally) n8n calls CMS webhook
```

---

## Configuration & Security Model

### Configuration File Structure

**File:** `config/n8n_settings.json`

**Example:**
```json
{
  "base_url": "https://n8n.example.com",
  "api_token": "n8n_api_XXXXXXXXXXXXXXXXXXXX",
  "verify_ssl": true,
  "timeout": 30,
  "last_updated": "2025-01-20T14:30:00Z"
}
```

**Fields:**
- `base_url` (string): Full base URL of n8n instance (e.g., `https://n8n.company.com` or `http://localhost:5678`)
- `api_token` (string): Personal access token or API key for n8n REST API authentication
- `verify_ssl` (boolean): Whether to verify SSL certificates (should be `true` in production)
- `timeout` (integer): HTTP request timeout in seconds (recommended: 10-30)
- `last_updated` (string, optional): ISO 8601 timestamp of last configuration change

**Default Values:**
- `base_url`: `""`
- `api_token`: `""`
- `verify_ssl`: `true`
- `timeout`: `15`

### Security Rules

**Critical Security Requirements:**

1. **Token Storage:**
   - API token MUST be stored ONLY in `config/n8n_settings.json`
   - NEVER store in `root/config.php` (avoids mixing with database credentials)
   - NEVER store in database
   - File permissions: 0600 (read/write owner only) where possible

2. **Token Handling in UI:**
   - Token NEVER rendered in HTML (even masked)
   - Settings form uses blank `api_token_new` field
   - Token only updated if `api_token_new` is non-empty
   - After save, redirect to prevent token exposure in POST replay

3. **Authentication & Authorization:**
   - All admin pages require `cms_session_start('admin')`
   - All admin pages require `cms_require_admin_role()` or equivalent permission check
   - Use dedicated permission: `n8n_manage` (checked via `cms_check_permission()`)

4. **CSRF Protection:**
   - All pages call `csrf_boot('admin')` during bootstrap
   - All forms include `<?php csrf_field(); ?>`
   - All POST handlers call `csrf_validate_or_403()` before processing

5. **DEV_MODE Gating:**
   - Initial release: All n8n admin pages check `if (!defined('DEV_MODE') || DEV_MODE !== true)` → 403
   - After testing, move to production with permission-based access

6. **Logging:**
   - API errors logged via `error_log()` WITHOUT including tokens
   - Log format: `"n8n API error: {method} {path} - {safe_error_message}"`
   - Never log request/response headers containing Authorization

7. **HTTPS:**
   - Production deployments STRONGLY RECOMMENDED to use HTTPS between CMS and n8n
   - `verify_ssl` should be `true` in production
   - Setting `verify_ssl: false` should trigger admin warning on settings page

---

## Core Helper Design (n8n Client)

**File:** `core/n8n_client.php`

This file provides a pure PHP interface to n8n's REST API. It has zero dependencies beyond PHP's `curl` extension and existing CMS patterns.

### Function Specifications

#### `n8n_config_path(): string`
Returns absolute path to n8n configuration file.

```php
function n8n_config_path(): string {
    return dirname(__DIR__) . '/config/n8n_settings.json';
}
```

#### `n8n_config_load(): array`
Loads and parses n8n configuration with safe defaults.

**Returns:**
```php
[
    'base_url' => string,
    'api_token' => string,
    'verify_ssl' => bool,
    'timeout' => int,
    'last_updated' => ?string
]
```

**Behavior:**
- If file doesn't exist, returns defaults (empty strings, sensible values)
- If JSON parse fails, logs error and returns defaults
- Normalizes types (ensure `verify_ssl` is boolean, `timeout` is integer)
- Trims whitespace from `base_url` and `api_token`

#### `n8n_config_save(array $data): bool`
Validates and persists configuration to JSON file.

**Parameters:**
- `$data`: Array with keys `base_url`, `api_token`, `verify_ssl`, `timeout`

**Validation:**
- `base_url`: Must be empty or valid URL (http/https)
- `api_token`: String (can be empty to clear)
- `verify_ssl`: Cast to boolean
- `timeout`: Integer, min 5, max 120 seconds
- Sets `last_updated` to current ISO 8601 timestamp

**Behavior:**
- Ensures `config/` directory exists
- Writes with `LOCK_EX` for atomicity
- Returns `true` on success, `false` on failure
- Logs errors via `error_log()` on failure
- File ends with exactly one newline
- UTF-8 encoding without BOM

#### `n8n_http_request(string $method, string $path, array $options = []): array`
Performs HTTP request to n8n REST API.

**Parameters:**
- `$method`: HTTP verb (GET, POST, PUT, DELETE)
- `$path`: API path relative to base_url (e.g., `/api/v1/workflows`)
- `$options`: Optional array:
  - `json_body` (array): Data to JSON-encode and send (for POST/PUT)
  - `query` (array): Query parameters to append to URL

**Returns:**
```php
[
    'ok' => bool,           // true if HTTP 2xx, false otherwise
    'status' => ?int,       // HTTP status code (null on network error)
    'body' => ?string,      // Raw response body (null on network error)
    'json' => mixed,        // Decoded JSON (null if not JSON or decode failed)
    'error' => ?string      // Error message (null on success)
]
```

**Behavior:**
1. Load config via `n8n_config_load()`
2. If `base_url` or `api_token` is empty, return error immediately
3. Build full URL: `rtrim($config['base_url'], '/') . '/' . ltrim($path, '/')`
4. Append query parameters if provided
5. Initialize cURL with:
   - `CURLOPT_RETURNTRANSFER`: true
   - `CURLOPT_TIMEOUT`: from config
   - `CURLOPT_SSL_VERIFYPEER` and `CURLOPT_SSL_VERIFYHOST`: based on `verify_ssl`
   - `CURLOPT_HTTPHEADER`: Include `Authorization: Bearer {api_token}` and `Content-Type: application/json`
6. For POST/PUT: Set `CURLOPT_POSTFIELDS` with JSON-encoded body
7. Execute request, capture HTTP status and response
8. Attempt to decode JSON response (suppress errors)
9. Close cURL handle
10. Return normalized array

**Error Handling:**
- Network errors (connection timeout, DNS failure): `ok=false`, `status=null`, `error="Network error: {message}"`
- HTTP 4xx/5xx: `ok=false`, `status={code}`, `error="HTTP {code}: {status text}"`
- All errors logged via `error_log()` without exposing token
- Safe error messages returned to caller (no sensitive data)

#### `n8n_format_error(array $response): string`
Helper to format user-friendly error message from response array.

**Example:**
```php
if (!$response['ok']) {
    echo '<div class="alert alert-danger">' . esc(n8n_format_error($response)) . '</div>';
}
```

---

## Admin Screens (Detailed)

### 5.1) n8n Settings Page

**File:** `admin/n8n-settings.php`

**Purpose:** Configure connection to n8n instance and test connectivity.

#### Bootstrap Sequence
```php
// 1. Define CMS_ROOT constant
define('CMS_ROOT', dirname(__DIR__));

// 2. Load core dependencies
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/n8n_client.php';

// 3. Initialize session and security
cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();

// 4. DEV_MODE gate (initial release only)
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('n8n integration is not enabled in production mode');
}

// 5. Permission check
if (!cms_check_permission('n8n_manage')) {
    http_response_code(403);
    exit('You do not have permission to manage n8n integration');
}
```

#### UI Elements

**Read-Only Information Section:**
- Config file path (display only)
- Config file exists: Yes/No
- Last updated timestamp (if available)

**Configuration Form:**
```html
<form method="POST" action="n8n-settings.php">
    <?php csrf_field(); ?>

    <label>n8n Base URL:</label>
    <input type="url" name="base_url" value="<?= esc($config['base_url']) ?>"
           placeholder="https://n8n.example.com" required>
    <small>Full URL of your n8n instance (no trailing slash)</small>

    <label>Verify SSL Certificates:</label>
    <input type="checkbox" name="verify_ssl" value="1"
           <?= $config['verify_ssl'] ? 'checked' : '' ?>>
    <small>Uncheck only for development/testing (not recommended for production)</small>

    <label>Request Timeout (seconds):</label>
    <input type="number" name="timeout" value="<?= (int)$config['timeout'] ?>"
           min="5" max="120" required>
    <small>How long to wait for n8n API responses (recommended: 15-30)</small>

    <label>API Token (leave blank to keep existing):</label>
    <input type="password" name="api_token_new" value=""
           placeholder="Enter new token or leave blank">
    <small>Your n8n API token or personal access token. Only updated if filled.</small>

    <button type="submit" name="action" value="save">Save Settings</button>
    <button type="submit" name="action" value="test">Test Connection</button>
</form>
```

**Warning Messages:**
- If `verify_ssl` is false, show prominent warning: "SSL verification is disabled. This is insecure for production use."
- If `base_url` is `localhost` or `127.0.0.1`, show info: "Detected local development n8n instance."

#### POST Handler Logic

**Action: Save Settings**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'save') {
    csrf_validate_or_403();

    // Load existing config
    $config = n8n_config_load();

    // Update fields
    $config['base_url'] = trim($_POST['base_url']);
    $config['verify_ssl'] = isset($_POST['verify_ssl']);
    $config['timeout'] = max(5, min(120, (int)$_POST['timeout']));

    // Update token only if new one provided
    $new_token = trim($_POST['api_token_new'] ?? '');
    if ($new_token !== '') {
        $config['api_token'] = $new_token;
    }

    // Save
    if (n8n_config_save($config)) {
        $_SESSION['flash_success'] = 'n8n settings saved successfully.';
    } else {
        $_SESSION['flash_error'] = 'Failed to save n8n settings. Check file permissions.';
    }

    // Redirect to prevent form resubmission
    header('Location: n8n-settings.php');
    exit;
}
```

**Action: Test Connection**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'test') {
    csrf_validate_or_403();

    // Perform a lightweight API request
    $response = n8n_http_request('GET', '/api/v1/workflows', ['query' => ['limit' => 1]]);

    if ($response['ok']) {
        $count = is_array($response['json']['data']) ? count($response['json']['data']) : 0;
        $_SESSION['flash_success'] = "Connected to n8n successfully! Found workflows.";
    } else {
        $_SESSION['flash_error'] = 'Connection failed: ' . n8n_format_error($response);
    }

    header('Location: n8n-settings.php');
    exit;
}
```

#### Security Notes
- API token never echoed in HTML (input always blank)
- Token only saved when `api_token_new` is non-empty
- Test connection doesn't expose full API response to UI (only success/failure)
- All errors logged server-side, safe messages shown to user

---

### 5.2) n8n Workflows Page

**File:** `admin/n8n-workflows.php`

**Purpose:** List, filter, view, and manually trigger n8n workflows.

#### Bootstrap Sequence
Same as settings page, plus:
```php
// Additional check: verify n8n is configured
$config = n8n_config_load();
if (empty($config['base_url']) || empty($config['api_token'])) {
    $_SESSION['flash_error'] = 'n8n is not configured. Please configure connection first.';
    header('Location: n8n-settings.php');
    exit;
}
```

#### Features

**1. Workflow List Table**

Fetch workflows from n8n API:
```php
// GET /api/v1/workflows
$response = n8n_http_request('GET', '/api/v1/workflows');

if (!$response['ok']) {
    // Show error, don't expose raw response
    echo '<div class="alert alert-danger">Failed to load workflows: ' .
         esc(n8n_format_error($response)) . '</div>';
    exit;
}

$workflows = $response['json']['data'] ?? [];
```

**Table Columns:**
- **ID**: n8n workflow ID (e.g., `123`)
- **Name**: Workflow name (e.g., "Teacher Material Generator")
- **Active**: Badge (green "Active" or gray "Inactive")
- **Updated**: Formatted date (e.g., "2025-01-20 14:30")
- **Actions**:
  - "View Details" button/link
  - "Trigger Now" button (POST form with CSRF)

**Filters:**
- **Search by name** (client-side JavaScript filter or server-side query param)
- **Filter by active status** (All / Active / Inactive)

**Example Row:**
```html
<tr>
    <td>42</td>
    <td>Blog Auto-Publisher</td>
    <td><span class="badge badge-success">Active</span></td>
    <td>2025-01-20 10:15</td>
    <td>
        <a href="n8n-workflows.php?action=view&id=42" class="btn btn-sm btn-info">View Details</a>
        <form method="POST" style="display:inline;">
            <?php csrf_field(); ?>
            <input type="hidden" name="workflow_id" value="42">
            <button type="submit" name="action" value="trigger" class="btn btn-sm btn-primary">
                Trigger Now
            </button>
        </form>
    </td>
</tr>
```

**2. View Details**

When `?action=view&id={id}` is accessed:
```php
$workflow_id = (int)$_GET['id'];
$response = n8n_http_request('GET', "/api/v1/workflows/{$workflow_id}");

if (!$response['ok']) {
    echo '<div class="alert alert-danger">Failed to load workflow: ' .
         esc(n8n_format_error($response)) . '</div>';
    exit;
}

$workflow = $response['json']['data'] ?? null;
```

Display safe subset of workflow data:
- Name, ID, active status
- Created/updated timestamps
- Number of nodes (count of workflow steps)
- Tags (if any)
- Description (sanitized, HTML-escaped)

**Do NOT display:**
- Full node definitions (may contain credentials references)
- Connection details
- Webhook URLs with tokens

**3. Trigger Workflow**

When POST `action=trigger`:
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'trigger') {
    csrf_validate_or_403();

    $workflow_id = (int)$_POST['workflow_id'];

    // Note: Exact endpoint depends on n8n version and configuration
    // Common options:
    // - POST /api/v1/workflows/{id}/execute
    // - POST to workflow webhook URL

    $response = n8n_http_request('POST', "/api/v1/workflows/{$workflow_id}/execute", [
        'json_body' => []  // Can add input data here if needed
    ]);

    if ($response['ok']) {
        $_SESSION['flash_success'] = "Workflow triggered successfully!";
    } else {
        $_SESSION['flash_error'] = 'Failed to trigger workflow: ' . n8n_format_error($response);
    }

    header('Location: n8n-workflows.php');
    exit;
}
```

**Important Note on Endpoints:**
The exact n8n REST API endpoints for triggering workflows may vary based on:
- n8n version (older vs. newer API versions)
- Authentication method (API key vs. JWT)
- Whether workflow uses webhook trigger vs. manual trigger

The implementation phase MUST:
1. Consult current n8n API documentation
2. Make endpoint paths configurable (e.g., in `n8n_settings.json`: `"api_version": "v1"`)
3. Provide fallback logic for different trigger methods
4. Validate against a test n8n instance before deployment

#### Security & UX Notes
- Triggering is manual only (no automatic triggers from CMS for now)
- Each trigger is a separate CSRF-protected POST
- Consider adding confirmation dialog for destructive workflows
- Log all trigger attempts to CMS audit log (future enhancement)

---

### 5.3) Workflow Builder (Future)

**File:** `admin/n8n-workflow-builder.php` (NOT implemented in Phase 1 or 2)

**Purpose:** Provide template-based guided wizards for creating workflows without requiring users to understand n8n's visual editor.

#### Design Philosophy

**NOT a visual workflow editor clone.** Building a full drag-and-drop workflow editor in pure PHP is impractical and duplicates n8n's native UI.

**Instead: Opinionated Template Wizards** for common CMS use cases.

#### Template Examples

**Template 1: Teacher Material Generator**

Wizard steps:
1. **Choose AI Provider**: Select from configured providers (OpenAI, HuggingFace, Ollama)
2. **Configure Inputs**:
   - Subject (dropdown: Math, Science, English, etc.)
   - Grade level (1-12)
   - Material type (Worksheet, Quiz, Lesson Plan)
3. **Set Output**:
   - Save to CMS content type (e.g., "Teaching Materials")
   - Email to teacher (optional)
4. **Review & Create**:
   - Show summary
   - Click "Create Workflow" → CMS generates n8n workflow JSON:
     ```json
     {
       "name": "Teacher Material Generator - Math - Grade 5",
       "nodes": [
         {
           "type": "n8n-nodes-base.httpRequest",
           "name": "Fetch Prompt Template",
           "parameters": {
             "url": "https://cms.example.com/api/prompts/teaching-material",
             "method": "GET"
           }
         },
         {
           "type": "n8n-nodes-base.openAi",
           "name": "Generate Content",
           "parameters": {
             "prompt": "={{$node['Fetch Prompt Template'].json.prompt}}"
           }
         },
         {
           "type": "n8n-nodes-base.httpRequest",
           "name": "Save to CMS",
           "parameters": {
             "url": "https://cms.example.com/api/content",
             "method": "POST",
             "bodyParameters": {
               "content": "={{$node['Generate Content'].json.content}}"
             }
           }
         }
       ],
       "connections": { /* ... */ }
     }
     ```
   - POST this JSON to n8n: `POST /api/v1/workflows`

**Template 2: Blog Auto-Publisher**

Wizard steps:
1. **Choose Trigger**:
   - Time-based (daily at 9 AM)
   - Webhook (external system calls)
2. **Content Source**:
   - CMS content drafts with tag "auto-publish"
   - RSS feed URL
3. **Publishing Rules**:
   - Publish immediately
   - Schedule for specific time
4. **Create Workflow** → generates n8n workflow JSON

**Template 3: Lead Capture to CRM**

Wizard steps:
1. **Form Webhook**: Auto-generates webhook URL in n8n
2. **Field Mapping**: Map form fields to CRM fields
3. **CRM Selection**: Choose integration (HubSpot, Salesforce, Google Sheets)
4. **Create Workflow** → generates and activates

#### Technical Implementation Notes

**Workflow JSON Generation:**
- CMS provides PHP classes for each template (e.g., `TeacherMaterialTemplate`)
- Each class has `generateWorkflowJson()` method
- Methods return associative arrays that are JSON-encoded
- Use n8n's node type registry (hardcode common node types initially, make extensible later)

**Pushing to n8n:**
```php
$workflow_json = $template->generateWorkflowJson($_POST);
$response = n8n_http_request('POST', '/api/v1/workflows', [
    'json_body' => $workflow_json
]);

if ($response['ok']) {
    $workflow_id = $response['json']['data']['id'];
    $_SESSION['flash_success'] = "Workflow created successfully! ID: {$workflow_id}";
    header("Location: n8n-workflows.php?action=view&id={$workflow_id}");
} else {
    $_SESSION['flash_error'] = 'Failed to create workflow: ' . n8n_format_error($response);
}
```

**User Experience:**
- Clear step-by-step wizard UI (similar to CMS page builder)
- Preview of what the workflow will do (plain English summary)
- After creation, redirect to workflow details page
- Link to edit in native n8n UI for advanced users

**Limitations:**
- Only supports predefined templates (no arbitrary workflow creation)
- Complex logic requires editing in n8n's UI
- Templates maintained by CMS developers (not user-extensible initially)

**Phase 3 Scope:**
Implement 1-2 templates as proof of concept. Gather user feedback before expanding template library.

---

## Example Use-Cases Within the CMS

### Use Case 1: Teacher Material Generator

**User Story:** A teacher admin wants to generate a worksheet for Grade 5 Math without leaving the CMS.

**Flow:**
1. Teacher navigates to **n8n Workflows** page in CMS admin
2. Finds workflow "Teacher Material Generator" in list
3. Clicks "Trigger Now"
4. (In future with builder): Fills in wizard (Subject: Math, Grade: 5, Type: Worksheet)
5. CMS calls n8n API to trigger workflow
6. n8n workflow:
   - Fetches prompt template from CMS API (`/api/prompts/teaching-material?subject=math&grade=5`)
   - Sends prompt to OpenAI/Ollama
   - Receives generated content
   - POSTs content back to CMS (`/api/content` with type "teaching_materials")
   - (Optional) Sends email to teacher with link
7. Teacher sees flash message "Workflow triggered successfully!"
8. Teacher navigates to **Teaching Materials** section in CMS and finds newly created draft

**Data Flow:**
```
CMS Admin UI → n8n_http_request() → n8n API (trigger workflow)
                                        ↓
                                    n8n executes:
                                        ↓
                                    GET https://cms.example.com/api/prompts/teaching-material
                                        ↓
                                    POST to OpenAI API
                                        ↓
                                    POST https://cms.example.com/api/content
```

**CMS API Requirements:**
- `GET /api/prompts/teaching-material`: Returns JSON prompt template
- `POST /api/content`: Creates draft content (requires authentication)

---

### Use Case 2: Auto-Publish AI-Generated Blog Posts

**User Story:** Marketing admin wants CMS to automatically generate and publish blog posts daily at 9 AM.

**Flow:**
1. Admin uses **Workflow Builder** (future) to create "Blog Auto-Publisher" workflow
2. Wizard asks:
   - Topic source: CMS editorial calendar API
   - AI provider: OpenAI GPT-4
   - Publishing rule: Publish immediately to "Blog" section
   - Schedule: Daily at 9 AM
3. CMS generates n8n workflow JSON and creates workflow via API
4. n8n workflow runs daily:
   - GET next topic from `https://cms.example.com/api/editorial-calendar/next`
   - Generate blog post using OpenAI
   - POST to `https://cms.example.com/api/content` with `status=published`
5. Admin receives daily email summary (optional)

**Data Flow:**
```
CMS Workflow Builder → generate JSON → POST to n8n API
                                            ↓
                                        n8n workflow created
                                            ↓
                                        (daily at 9 AM)
                                            ↓
                                        GET /api/editorial-calendar/next
                                            ↓
                                        OpenAI generation
                                            ↓
                                        POST /api/content (published)
```

**CMS API Requirements:**
- `GET /api/editorial-calendar/next`: Returns next scheduled topic
- `POST /api/content`: Creates published content

---

### Use Case 3: Backup & Notification Orchestrator

**User Story:** IT admin wants n8n to monitor CMS backups and send alerts if backup fails or is missing.

**Flow:**
1. Admin creates workflow "Backup Monitor" in n8n (manually or via CMS builder)
2. Workflow runs every 4 hours:
   - Checks `https://cms.example.com/api/system/backup-status`
   - If last backup > 24 hours old: Send alert email to admin
   - If backup failed: Create system alert in CMS
3. Admin can trigger backup manually from CMS by clicking "Run Backup Now" in n8n Workflows page
4. n8n workflow executes:
   - POST to `https://cms.example.com/api/system/backup/trigger`
   - Waits for completion
   - Sends email with results

**Data Flow:**
```
n8n scheduled trigger (every 4 hours)
    ↓
GET https://cms.example.com/api/system/backup-status
    ↓
(if alert condition met)
    ↓
POST https://cms.example.com/api/alerts (create system alert)
    ↓
Send email via n8n email node
```

**CMS API Requirements:**
- `GET /api/system/backup-status`: Returns last backup time and status
- `POST /api/system/backup/trigger`: Initiates backup (requires admin auth)
- `POST /api/alerts`: Creates system alert

---

### Use Case 4: Lead Capture Form to Google Sheets

**User Story:** School admin wants contact form submissions automatically added to Google Sheets without manual copying.

**Flow:**
1. Admin creates workflow "Contact Form to Sheets" using CMS builder
2. Wizard:
   - Trigger: Webhook (CMS generates unique URL in n8n)
   - Field mapping: name → Column A, email → Column B, message → Column C
   - Destination: Google Sheets (admin authenticates Google account in n8n)
3. CMS updates contact form handler to POST submissions to n8n webhook URL
4. When form submitted:
   - CMS validates and saves submission to database
   - CMS POSTs to n8n webhook: `POST https://n8n.example.com/webhook/abc123`
   - n8n appends row to Google Sheets
5. Admin sees new leads in both CMS and Google Sheets

**Data Flow:**
```
User submits form → CMS form handler
                        ↓
                    Save to CMS database
                        ↓
                    POST to n8n webhook
                        ↓
                    n8n Google Sheets node appends row
```

**Integration Note:**
This workflow is triggered BY the CMS, not manually. Implementation requires:
- Storing webhook URLs in CMS (per-form configuration)
- Secure webhook authentication (HMAC signature or API key)
- Error handling if n8n is unreachable (queue retries)

---

## Security & Deployment Considerations

### Transport Security

**HTTPS Required in Production:**
- All communication between CMS and n8n MUST use HTTPS in production
- Self-signed certificates acceptable for development (with `verify_ssl: false`)
- Certificate validation enabled by default (`verify_ssl: true`)

**API Token Security:**
- Use n8n's API key or personal access token (not owner account password)
- Rotate tokens periodically (e.g., every 90 days)
- Revoke tokens immediately if compromise suspected
- Never commit `n8n_settings.json` with real tokens to version control

### CMS API Security (When n8n Calls CMS)

When n8n workflows call CMS endpoints:

**Authentication Options:**
1. **API Key Header**:
   - CMS generates API key for n8n service account
   - n8n sends `X-API-Key: {key}` header
   - CMS validates before processing
2. **JWT Bearer Token**:
   - Use existing `WorkerAuthenticate` system
   - Generate long-lived token for n8n
   - Auto-refresh if needed
3. **IP Allowlist**:
   - Restrict sensitive endpoints to n8n server IP
   - Configure in `.htaccess` or nginx config

**Recommended Pattern:**
```php
// In CMS API endpoint (e.g., /api/content/create.php)
require_once __DIR__ . '/../../core/api_auth.php';

// Validate API key or JWT
if (!validate_api_request()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Process request
// ...
```

### Rate Limiting

**CMS → n8n:**
- CMS should implement basic rate limiting to prevent abuse
- Example: Max 10 workflow triggers per minute per admin user
- Use existing `rate_limits` table pattern

**n8n → CMS:**
- Protect CMS API endpoints with rate limiting
- Allow higher limits for n8n service account
- Log all n8n API calls for audit

### Firewall Rules

**Production Deployment:**
- n8n server and CMS server should be on same private network OR
- Use VPN tunnel between servers OR
- Allowlist IP addresses in firewall

**Webhook Endpoints:**
- If n8n sends webhooks TO CMS, expose only specific paths (e.g., `/api/webhooks/n8n/*`)
- Use HMAC signature verification for webhook payloads
- Consider using secret token in webhook URL (e.g., `/webhooks/n8n/{secret_token}/trigger`)

### DEV_MODE Strategy

**Phase 1 (Initial Development):**
- All n8n admin pages gated with `DEV_MODE` check
- Allows testing without exposing to production users

**Phase 2 (Controlled Rollout):**
- Remove `DEV_MODE` check
- Add permission check: `cms_check_permission('n8n_manage')`
- Grant permission only to trusted admins

**Phase 3 (General Availability):**
- Document production hardening checklist
- Require HTTPS + valid SSL
- Audit all API endpoints

### Logging & Monitoring

**CMS-Side Logging:**
- Log all n8n API calls with: timestamp, user, endpoint, success/failure
- Do NOT log API tokens or sensitive parameters
- Store in `logs/n8n_api.log` (JSONL format)

**Example Log Entry:**
```json
{
  "timestamp": "2025-01-20T14:30:45Z",
  "user_id": 123,
  "action": "trigger_workflow",
  "workflow_id": 42,
  "success": true,
  "duration_ms": 250
}
```

**n8n-Side Logging:**
- n8n maintains its own execution logs
- CMS should provide link to n8n execution logs (if admin has n8n access)

### Error Handling Strategy

**Network Failures:**
- If CMS cannot reach n8n (network down, n8n server offline):
  - Show user-friendly error: "Cannot connect to workflow server. Please try again later."
  - Log error server-side with details
  - Do NOT expose n8n base URL or technical details to non-admin users

**API Errors:**
- Parse n8n error responses and translate to user-friendly messages
- Example: n8n returns 404 → "Workflow not found. It may have been deleted."
- Example: n8n returns 401 → "Authentication failed. Please check API token in settings."

**Workflow Execution Failures:**
- If workflow trigger succeeds but execution fails (e.g., API quota exceeded):
  - n8n handles execution errors internally
  - CMS can optionally poll for execution status via API
  - Display execution status on workflows page (future enhancement)

---

## Roadmap & Milestones

### Phase 1: Foundation (Configuration & Client)

**Goal:** Establish secure, working connection between CMS and n8n.

**Deliverables:**
1. `config/n8n_settings.json` structure defined
2. `core/n8n_client.php` implemented:
   - `n8n_config_path()`
   - `n8n_config_load()`
   - `n8n_config_save()`
   - `n8n_http_request()`
   - `n8n_format_error()`
3. `admin/n8n-settings.php` page:
   - Configuration form
   - Save settings handler
   - Test connection button
4. Security complete:
   - CSRF protection
   - Token masking
   - DEV_MODE gating
   - Permission checks

**Testing:**
- Manual testing with local n8n instance (Docker)
- Verify token storage and retrieval
- Test error handling (invalid URL, wrong token, network timeout)
- Confirm no token exposure in HTML or logs

**Success Criteria:**
- Admin can save n8n connection settings
- "Test Connection" successfully calls n8n API
- Token never visible in UI or logs
- All POST actions CSRF-protected

**Estimated Complexity:** Small (1-2 days implementation + testing)

---

### Phase 2: Workflow Management (List, View, Trigger)

**Goal:** Allow admins to interact with existing n8n workflows from CMS.

**Deliverables:**
1. `admin/n8n-workflows.php` page:
   - List all workflows (table view)
   - Filter by name and active status
   - View workflow details (safe subset)
   - Trigger workflow manually (POST with CSRF)
2. Enhanced `core/n8n_client.php`:
   - Helper functions for common API calls (list workflows, get workflow, trigger workflow)
3. Admin navigation updates:
   - Add "n8n Integration" section to admin menu
   - Links to Settings and Workflows pages

**Testing:**
- Create test workflows in n8n (at least 3: simple, medium complexity, inactive)
- Verify list page displays all workflows correctly
- Test filtering (active/inactive)
- Test view details page
- Test manual trigger (verify execution in n8n logs)
- Test error handling (deleted workflow, network error)

**Success Criteria:**
- Admin can see all workflows from n8n
- Admin can trigger workflows manually
- All actions properly authenticated and CSRF-protected
- Errors handled gracefully

**Estimated Complexity:** Medium (2-3 days implementation + testing)

---

### Phase 3: Template-Based Builder (1-2 Wizards)

**Goal:** Demonstrate guided workflow creation for common use cases.

**Deliverables:**
1. `admin/n8n-workflow-builder.php` page:
   - Landing page with template selection
   - Template: "Teacher Material Generator"
     - Multi-step wizard
     - JSON generation
     - Workflow creation via API
   - (Optional) Template: "Blog Auto-Publisher"
2. New classes in `core/n8n_templates/`:
   - `AbstractWorkflowTemplate.php` (base class)
   - `TeacherMaterialTemplate.php`
   - (Optional) `BlogPublisherTemplate.php`
3. Helper functions in `core/n8n_client.php`:
   - `n8n_create_workflow(array $workflow_json): array`
   - `n8n_update_workflow(int $id, array $workflow_json): array`
4. Documentation:
   - Template developer guide
   - How to add new templates (for future developers)

**Testing:**
- Complete wizard flow for Teacher Material template
- Verify generated workflow JSON is valid
- Create workflow in n8n and verify structure
- Test workflow execution (end-to-end)
- Verify error handling (invalid inputs, API failures)

**Success Criteria:**
- Admin can create at least 1 working workflow via wizard
- Generated workflow executes successfully in n8n
- Workflow interacts with CMS API correctly
- Code is extensible for future templates

**Estimated Complexity:** Large (4-5 days implementation + testing)

---

### Future Enhancements (Beyond Phase 3)

**Workflow Execution Monitoring:**
- Poll n8n for execution status
- Display recent executions on workflows page
- Show success/failure/duration
- Link to full execution log in n8n UI

**Advanced Templates:**
- Lead capture to CRM (Google Sheets, HubSpot)
- Social media auto-posting
- Student assessment generator
- Backup orchestrator

**Webhook Management:**
- UI for managing webhook endpoints
- Generate HMAC signatures for webhook security
- Test webhook delivery

**Multi-Instance Support:**
- Connect to multiple n8n instances (e.g., production + staging)
- Instance switcher in admin UI
- Per-instance configuration

**Integration with CMS Events:**
- Trigger n8n workflows automatically on CMS events
- Example: On content published → trigger "social media share" workflow
- Use EventBus system to dispatch to n8n

**Workflow Import/Export:**
- Export workflows as JSON from n8n
- Import workflows from JSON files
- Share workflow templates between CMS instances

---

## Implementation Notes

### Code Style & Patterns

**Follow Existing CMS Conventions:**
- Pure PHP, no frameworks
- Explicit `require_once` for all dependencies (no autoloading)
- UTF-8 encoding without BOM
- No closing `?>` tags in PHP-only files
- Exactly one trailing newline at EOF
- Database access ONLY via `\core\Database::connection()` (though n8n module should not touch DB)

**Naming Conventions:**
- Functions: `snake_case` (e.g., `n8n_config_load()`)
- Classes: `PascalCase` (e.g., `TeacherMaterialTemplate`)
- Constants: `UPPER_SNAKE_CASE` (e.g., `N8N_API_VERSION`)
- Files: `kebab-case.php` (e.g., `n8n-settings.php` for pages, `n8n_client.php` for libraries)

**Security Patterns:**
- Always use `esc()` helper for HTML output
- Always use `csrf_field()` and `csrf_validate_or_403()` for forms
- Always use `cms_session_start('admin')` and `cms_require_admin_role()` for admin pages
- Never expose sensitive data in logs or error messages

**Error Handling:**
- Use `error_log()` for server-side logging
- Return structured error information from functions
- Display user-friendly error messages in UI
- Never expose stack traces or internal details to users (unless DEV_MODE)

### Testing Strategy

**Manual Testing Checklist:**
- [ ] Configuration save/load (valid and invalid inputs)
- [ ] API token masking (verify never rendered)
- [ ] Connection test (success and failure scenarios)
- [ ] Workflow list (empty, single, multiple workflows)
- [ ] Workflow trigger (success and failure)
- [ ] CSRF protection (verify all POST forms protected)
- [ ] Permission checks (non-admin cannot access)
- [ ] Network errors (simulate timeout, unreachable host)
- [ ] n8n errors (wrong token, deleted workflow, quota exceeded)
- [ ] DEV_MODE gating (verify 403 when DEV_MODE=false)

**Integration Testing:**
1. Set up local n8n instance (Docker recommended)
2. Create test workflows in n8n
3. Configure CMS to connect to local n8n
4. Test all admin pages end-to-end
5. Verify CMS API endpoints work when called by n8n

**Security Testing:**
- Attempt to access admin pages without authentication
- Attempt to submit forms without CSRF token
- Inspect HTML source for exposed tokens
- Review logs for sensitive data leakage
- Test with invalid SSL certificates
- Test with malicious input (XSS, SQL injection attempts)

### Documentation Requirements

**User-Facing Documentation:**
- Admin guide: How to configure n8n integration
- Tutorial: Creating your first workflow
- Template library: Available workflow templates
- Troubleshooting: Common errors and solutions

**Developer Documentation:**
- API reference for `core/n8n_client.php`
- How to add new workflow templates
- Security best practices
- n8n API endpoint mapping

---

## Appendix: n8n API Reference

**Note:** This section provides a preliminary API reference based on n8n's public API documentation. Exact endpoints and request/response formats MUST be verified against the specific n8n version during implementation.

### Common Endpoints

**List Workflows:**
```
GET /api/v1/workflows
Query Params:
  - limit (int, optional): Max results per page
  - offset (int, optional): Pagination offset
Response:
  {
    "data": [
      {
        "id": 1,
        "name": "Example Workflow",
        "active": true,
        "createdAt": "2025-01-01T00:00:00.000Z",
        "updatedAt": "2025-01-20T14:30:00.000Z",
        "nodes": [...],
        "connections": {...}
      }
    ],
    "nextCursor": null
  }
```

**Get Workflow:**
```
GET /api/v1/workflows/{id}
Response:
  {
    "data": {
      "id": 1,
      "name": "Example Workflow",
      "active": true,
      "nodes": [...],
      "connections": {...},
      ...
    }
  }
```

**Create Workflow:**
```
POST /api/v1/workflows
Body:
  {
    "name": "New Workflow",
    "nodes": [...],
    "connections": {...},
    "active": false
  }
Response:
  {
    "data": {
      "id": 42,
      "name": "New Workflow",
      ...
    }
  }
```

**Update Workflow:**
```
PUT /api/v1/workflows/{id}
Body: (same as create)
Response: (same as create)
```

**Delete Workflow:**
```
DELETE /api/v1/workflows/{id}
Response: 204 No Content
```

**Execute Workflow:**
```
POST /api/v1/workflows/{id}/execute
Body:
  {
    "data": {
      // Optional input data for workflow
    }
  }
Response:
  {
    "data": {
      "executionId": "abc123",
      "finished": true,
      "data": {
        // Execution results
      }
    }
  }
```

**Note:** Webhook-triggered workflows use a different pattern:
```
POST /webhook/{webhook-path}
(Configured per workflow, not generic API endpoint)
```

### Authentication

n8n supports multiple authentication methods:
1. **API Key** (recommended for CMS integration):
   ```
   X-N8N-API-KEY: your_api_key_here
   ```
2. **Bearer Token**:
   ```
   Authorization: Bearer your_jwt_token_here
   ```

The CMS implementation should detect auth method from n8n version and allow configuration.

---

## Summary

This blueprint defines a complete, production-ready n8n integration module for the CMS that:

1. **Respects all CMS constraints**: Pure PHP, FTP-only, no frameworks, no CLI tools
2. **Maintains security**: Token masking, CSRF protection, admin authentication, HTTPS
3. **Provides clear user value**: Workflow management from familiar CMS admin panel
4. **Supports future growth**: Template system for guided workflow creation
5. **Minimizes risk**: File-based config (no DB changes), n8n remains source of truth

**Key Files:**
- `config/n8n_settings.json` - Connection configuration
- `core/n8n_client.php` - HTTP client and helpers
- `admin/n8n-settings.php` - Configuration UI
- `admin/n8n-workflows.php` - Workflow browser and trigger UI
- `admin/n8n-workflow-builder.php` - Template-based wizards (future)

**Implementation Phases:**
- Phase 1: Configuration and connectivity (1-2 days)
- Phase 2: Workflow listing and triggering (2-3 days)
- Phase 3: Template-based builder (4-5 days)

This module will enable non-technical administrators to leverage powerful automation workflows without leaving their CMS environment, dramatically improving productivity for schools and small businesses using this system.
