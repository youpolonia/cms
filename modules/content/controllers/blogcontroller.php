<?php
namespace modules\content\controllers;

use services\ContentService;
use services\TemplateRenderer;
use RuntimeException;

class BlogController {
    private ContentService $contentService;
    private TemplateRenderer $templateRenderer;

    public function __construct(ContentService $contentService, TemplateRenderer $templateRenderer) {
        $this->contentService = $contentService;
        $this->templateRenderer = $templateRenderer;
    }

    public function show(string $slug): void {
        try {
            $content = $this->contentService->getContentBySlug($slug);
            
            if (!$content) {
                http_response_code(404);
                echo "Blog post not found";
                return;
            }

            if (!$content['published']) {
                http_response_code(403);
                echo "This blog post is not published";
                return;
            }

            // Set template variables
            $this->templateRenderer->title = $content['title'];
            $this->templateRenderer->content = $content['content'];
            
            // Render template
            $this->templateRenderer->extend('blog/show.php');
        } catch (RuntimeException $e) {
            http_response_code(500);
            echo "An error occurred while rendering the blog post";
            error_log($e->getMessage());
        }
    }
}
