<?php
/**
 * Example: Content Publication Workflow
 * 
 * Demonstrates triggering n8n when content is published
 */

require_once __DIR__.'/../../includes/core/workflows.php';

$workflows = new Workflows();

// Register webhook for content publication events
$workflows->registerWebhook(
    'content_published',
    'https://your-n8n-instance/webhook/content-published',
    ['content.published']
);

// Subscribe to content publication events
$workflows->subscribeToEvent('content.published', function(array $data) {
    // Example data transformation
    return [
        'content_id' => $data['id'],
        'title' => $data['title'],
        'author' => $data['author'],
        'published_at' => date('c')
    ];
});

/**
 * n8n Workflow Setup:
 * 1. Create a webhook trigger node in n8n
 * 2. Set the URL to match the registered webhook
 * 3. Add processing nodes as needed (email, Slack, etc.)
 */
