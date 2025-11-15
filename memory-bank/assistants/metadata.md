# AI Metadata Generator Assistant

## Overview
Generates structured metadata for content using AI models. Supports:
- SEO metadata (title, description, keywords)
- Schema.org markup
- Open Graph tags
- Twitter Card metadata

## Usage
```php
$metadata = MetadataGenerator::generateMetadata(
    'model-id', 
    'metadata-type',
    ['content' => '...'],
    'optional-prompt'
);
```

## Metadata Types
| Type | Description |
|------|-------------|
| seo_metadata | Standard SEO metadata |
| schema_org | Structured data markup |
| open_graph | Facebook/Open Graph tags |
| twitter_card | Twitter Card metadata |

## Examples
```php
// Generate SEO metadata
$seoData = MetadataGenerator::generateMetadata(
    'gemini-pro',
    'seo_metadata',
    ['title' => 'Product Page', 'content' => '...']
);

// Get Schema.org markup
$schemaData = MetadataGenerator::generateMetadata(
    'claude-3',
    'schema_org',
    ['@type' => 'Product', 'name' => '...']
);
```

## Requirements
- CMS Core >= 2.3.0
- AI Module >= 1.2.0