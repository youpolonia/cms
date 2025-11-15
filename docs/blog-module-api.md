# Blog Module API Documentation

## Overview
The Blog module provides content management for blog posts with:
- CRUD operations
- AI-assisted content generation
- Version history tracking
- SEO optimization

## Class: BlogPost

### Public Properties
```php
public string $slug;        // URL-friendly post identifier
public string $title;       // Post title
public string $body;        // HTML content
public array $tags;         // Categorization tags
public DateTime $createdAt; // Publication date
public bool $isPublished;   // Visibility status
public string $author;      // Creator identifier
```

## Class: BlogManager

### Public Methods

#### `getPost(string $slug): ?BlogPost`
Retrieves a single post by its slug. Returns null if not found.

#### `savePost(BlogPost $post): bool`
Persists post changes. Returns true on success.

#### `deletePost(string $slug): bool`
Removes a post. Returns true on success.

#### `listPosts(int $limit = 10, int $offset = 0): array`
Returns paginated array of published posts.

#### `getVersionHistory(string $slug): array`
Returns version snapshots for a post.

### File Structure Expectations
- Posts stored in `/data/blog/{slug}.json`
- Versions in `/data/blog/versions/{slug}_{timestamp}.json`

## AI Integration Points
1. Content analysis via `AIContentEngine::analyzeContent()`
2. Automatic rewriting via `AIContentEngine::rewriteContent()`
3. SEO suggestions via hidden form POST to `/admin/blog-seo-analyze.php`

## Versioning Mechanism
1. Automatic snapshots on save
2. Manual version creation via UI
3. Storage in JSON format with timestamp
4. Retrieval via `getVersionHistory()`

## Example Usage
```php
// Create new post
$post = new BlogPost();
$post->title = "New Post";
$post->body = "Content here";

// Save post
$manager = new BlogManager();
if ($manager->savePost($post)) {
    echo "Post saved as: " . $post->slug;
}

// Get AI rewrite
$aiEngine = new AIContentEngine();
$rewritten = $aiEngine->rewriteContent([
    'title' => $post->title,
    'content' => $post->body
]);