# Builder v2.2 Technical Components

## Core Components

| Module Name | Purpose | Dependencies | Classification | Complexity |
|-------------|---------|--------------|----------------|------------|
| Theme Presets System | Manage reusable theme configurations | ThemeManager.php, WebSocketService | Core | Medium |
| Visual Block Editor | Drag-and-drop interface for content creation | BlockManager.php, PropertyPanel.js | Core | High |
| Enhanced AI Endpoint | Content/layout suggestions with context awareness | AI Service API, ContentModel | Core | Medium |
| AI Metadata Extensions | Track AI-generated content at block level | BlockRegistry.php, HistoryService | Core | Low |
| Hook System | Plugin extensibility framework | EventDispatcher.php | Core | Medium |
| Plugin API Endpoints | Manage plugin registration/hook subscriptions | AuthService, API Gateway | Core | Medium |
| Legacy Conversion Engine | Convert HTML to block-based layouts | ParserService, StyleMapper | Core | High |
| Migration UI | Manage legacy content conversion | BatchProcessor.php, DiffEngine | Core | Medium |

## Optional Components

| Module Name | Purpose | Dependencies | Classification | Complexity |
|-------------|---------|--------------|----------------|------------|
| PDF Generation | Export content as PDF documents | Headless Chrome API, TemplateSystem | Optional | Medium |
| AMP Output | Generate AMP-compatible content | AMP Validator, ComponentRegistry | Optional | Medium |
| n8n Webhooks | Trigger workflows from builder events | WebhookService, PayloadBuilder | Optional | Low |
| n8n Action Nodes | Content/layout operations via n8n | API Gateway, AuthService | Optional | Medium |
| Scheduled Operations | Automated content/theme updates | CronService, VersionControl | Optional | High |
| Conditional Logic | Audience targeting/A-B testing | AnalyticsService, RuleEngine | Optional | High |

## Modified v2.1 Components

| Module Name | Changes | Dependencies | Classification | Complexity |
|------------|---------|--------------|----------------|------------|
| BlockManager.php | Extended for visual editor integration | VisualEditor.js | Core | Medium |
| ThemeManager.php | Updated for preset support | PresetStorageService | Core | Low |
| AI Service Client | Enhanced for advisory features | ContextService | Core | Medium |
| API Gateway | New endpoints for plugins/hooks | AuthService, RateLimiter | Core | Low |