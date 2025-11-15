# AI Copywriter Assistant Documentation

## Overview
The AI Copywriter Assistant plugin provides AI-powered content generation capabilities within the CMS editor. It integrates with the core AI services to help users quickly generate high-quality content.

## Installation
1. Place the plugin folder in `/plugins/ai-copywriter`
2. The plugin will auto-register with the CMS
3. No additional configuration required for basic functionality

## Features
- Content generation from prompts
- Tone/style adjustments
- Multiple output length options
- Integration with CMS editor tools

## Usage
1. Open any content editor
2. Click the "AI Copywriter" tool in the editor toolbar
3. Enter your prompt and desired settings
4. Click "Generate" to create content
5. Insert the generated content into your editor

## Configuration
Advanced configuration options in `plugin.json`:
```json
{
  "ai_service": "gemini|openai",
  "default_tone": "professional",
  "max_length": 500
}
```

## API Methods
```php
// Generate content
$content = AssistantMain::generateContent($prompt);

// Add to editor tools
add_filter('content_editor_tools', [AssistantMain::class, 'addEditorTool']);
```

## Troubleshooting
- Ensure core AI services are properly configured
- Check API key permissions if using external AI services
- Verify plugin is loading by checking admin panel