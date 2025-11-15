<?php

class BlogPostTemplate extends ContentTemplate {
    protected string $name = 'Blog Post';
    protected string $description = 'Template for generating blog post content';
    protected array $variables = [
        'title' => '',
        'keywords' => '',
        'tone' => 'professional',
        'length' => 'medium'
    ];

    public function getSystemPrompt(): string {
        return <<<PROMPT
You are a professional blog writer. Create a blog post with the following requirements:

Title: {{title}}
Keywords: {{keywords}}
Tone: {{tone}}
Length: {{length}}

The blog post should be well-structured with an introduction, body paragraphs, and conclusion.
Use markdown formatting for headings and lists.
PROMPT;
    }
}
