# AIContentEngine Module Documentation

## Overview
The AIContentEngine module provides AI-powered content generation capabilities for the CMS. It interfaces with the AIClient service to generate structured content (titles, summaries, body text, and tags) and handles saving the generated content to disk.

## Public Methods

### `generateContent(string $topic): array`
Generates structured content about a given topic using the AIClient service.

**Parameters:**
- `$topic` (string): The topic to generate content about

**Returns:**
- Associative array containing:
  - `title` (string)
  - `summary` (string)
  - `body` (string) - HTML formatted content
  - `tags` (array of strings)

**Throws:**
- `Exception` if content generation fails or response is invalid

### `saveContent(array $data): string`
Saves generated content to disk as a JSON file.

**Parameters:**
- `$data` (array): Content data (must include title, summary, body, tags)

**Returns:**
- Filename of saved content (string)

**Throws:**
- `Exception` if validation fails or file cannot be written

## Input and Output Format

**Input Formats:**
- For generation: Plain text topic string
- For saving: Structured array with required fields

**Output Formats:**
- Generated content: Associative array
- Saved content: JSON file with same structure as input array

## Integration Points
- Used by content generation UI components
- Integrated with content publishing system
- Called by automated content workflows

## Example Usage

```php
// Generate content
$content = AIContentEngine::generateContent("Renewable energy trends");

// Save content
$filename = AIContentEngine::saveContent($content);

// Publish content (using another module)
ContentPublisher::publish($filename);