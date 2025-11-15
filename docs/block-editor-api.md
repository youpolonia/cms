# Block Editor Features and API

## Overview
The CMS features a modular block editor supporting:
- Drag-and-drop content assembly
- Real-time preview
- Version-aware editing
- AI-assisted block generation

## Block Types

### Core Blocks
| Type | Description | Schema |
|------|-------------|--------|
| Text | Rich text content | `{type: 'text', content: string}` |
| Image | Media with caption | `{type: 'image', src: string, alt: string}` |
| Video | Embedded media | `{type: 'video', url: string, autoplay: boolean}` |
| Columns | Multi-column layout | `{type: 'columns', children: Block[]}` |

### Custom Block Development
```javascript
// Register a custom block
CMS.registerBlockType('custom/cta', {
    title: 'Call to Action',
    icon: 'megaphone',
    category: 'marketing',
    attributes: {
        heading: {type: 'string'},
        buttonText: {type: 'string'},
        buttonUrl: {type: 'string'}
    },
    edit: ({attributes, setAttributes}) => (
        <div className="cta-block">
            <TextControl
                label="Heading"
                value={attributes.heading}
                onChange={(heading) => setAttributes({heading})}
            />
            <TextControl
                label="Button Text"
                value={attributes.buttonText}
                onChange={(buttonText) => setAttributes({buttonText})}
            />
            <URLInput
                label="Button URL"
                value={attributes.buttonUrl}
                onChange={(buttonUrl) => setAttributes({buttonUrl})}
            />
        </div>
    ),
    save: ({attributes}) => (
        <div className="cta-block">
            <h3>{attributes.heading}</h3>
            <a href={attributes.buttonUrl} className="button">
                {attributes.buttonText}
            </a>
        </div>
    )
});
```

## Editor API

### Core Methods
```javascript
// Get current content
const content = editor.getContent();

// Replace content
editor.setContent(newContent);

// Subscribe to changes
editor.subscribe((event) => {
    if (event.type === 'BLOCK_ADDED') {
        console.log('Block added:', event.block);
    }
});

// Execute command
editor.executeCommand('undo');
```

### AI Integration
```javascript
// Generate block with AI
editor.generateBlock({
    type: 'text',
    prompt: 'Write an engaging introduction paragraph',
    options: {
        tone: 'professional',
        length: 'medium'
    }
}).then((block) => {
    editor.insertBlock(block);
});
```

## Storage Format
Blocks are stored as JSON with version metadata:
```json
{
    "version": "1.0.0",
    "blocks": [
        {
            "id": "a1b2c3",
            "type": "text",
            "content": "Welcome to our site",
            "version": 5
        }
    ]
}
```

## Performance Considerations
- Block rendering: ≤50ms per block
- Undo/redo stack: 100 operations max
- Memory usage: ≤5MB per editor instance

## Configuration
Set in `config/cms.php`:
```php
'blocks' => [
    'default_types' => ['text', 'image', 'video'],
    'max_blocks' => 100,
    'history_size' => 100,
    'ai_enabled' => true
]