# Builder v2.2 Feature Roadmap

## Core Features (Must-Have)

### [P0] UX/Visual Editing Improvements
- **Theme Presets System** (extends v2.1 architecture)
  - Preset management UI in admin panel
  - Live preview integration with WebSocket
  - Storage: `data/theme-presets/` (JSON)
- **Visual Block Editor**
  - Drag-and-drop interface
  - Real-time property panel
  - Integration with BlockManager.php

### [P1] AI Advisory Features
- **Enhanced AI Endpoint** (`/api/ai/generate`)
  - Content suggestions with context awareness
  - Layout recommendations with compatibility checks
  - SEO optimization advisor
- **AI Metadata Extensions**
  - Block-level AI capabilities registry
  - Prompt history tracking

### [P2] Plugin/Extensibility Support
- **Hook System**
  - Core hooks for builder events
  - Filter system for content modification
  - Storage: `modules/plugin-system/`
- **API Endpoints**
  - Plugin registration endpoint
  - Hook subscription management

### [P2] Legacy Layout Conversion
- **Conversion Engine**
  - HTML to Block converter
  - Style mapping system
  - Output validation
- **Migration UI**
  - Batch processing
  - Preview/diff system
  - Undo capability

## Optional Features

### Export Options (PDF, AMP)
- **PDF Generation**
  - Headless Chrome integration via API
  - Template system for PDF layouts
- **AMP Output**
  - AMP validator integration
  - Component compatibility checks

### n8n Workflow Integration
- **Webhook Endpoints**
  - Builder events as triggers
  - Payload customization
- **Action Nodes**
  - Content generation
  - Layout operations
  - AI service calls

### Advanced Automation Features
- **Scheduled Operations**
  - Content versioning
  - Theme rotations
  - Block updates
- **Conditional Logic**
  - Audience targeting
  - A/B testing framework