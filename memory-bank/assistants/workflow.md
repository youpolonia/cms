# AI Assistant Workflow Integration

## Supported Assistants
- Copywriter
- Metadata Generator
- Translator
- UI Assistant

## Step Types
Each assistant provides specialized workflow step types:

### Copywriter (`copywriter_step`)
- Generates marketing copy based on input prompts
- Configuration options:
  - `tone`: Professional, Casual, Friendly
  - `length`: Short, Medium, Long
  - `style`: Blog, Social, Email

### Metadata Generator (`metadata_step`)
- Creates SEO-optimized metadata
- Configuration options:
  - `keywords`: List of target keywords
  - `character_limit`: Max length for title/description

### Translator (`translator_step`)
- Translates content between languages
- Configuration options:
  - `source_lang`: Source language code
  - `target_lang`: Target language code
  - `formality`: Formal, Informal

### UI Assistant (`ui_step`)
- Generates UI component descriptions
- Configuration options:
  - `component_type`: Button, Form, Card
  - `style_guide`: Reference to style guide

## Implementation Notes
- All assistants use the core AI service
- Responses are stored in workflow variables
- Error handling follows standard workflow patterns