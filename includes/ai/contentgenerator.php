<?php

class ContentGenerator {
    private AIManager $aiManager;
    private array $promptTemplates = [];
    private array $config;

    public function __construct(AIManager $aiManager, array $config) {
        $this->aiManager = $aiManager;
        $this->config = $config;
        $this->loadPromptTemplates();
    }

    private function loadPromptTemplates(): void {
        $this->promptTemplates = [
            'blog_post' => [
                'template' => "Write a blog post about {{topic}} with the following sections:\n"
                    . "- Introduction\n"
                    . "- Main Content (3-5 paragraphs)\n"
                    . "- Conclusion\n\n"
                    . "Use a {{tone}} tone and target {{audience}} audience.",
                'variables' => ['topic', 'tone', 'audience']
            ],
            'product_description' => [
                'template' => "Write a product description for {{product_name}} with these features:\n"
                    . "- {{feature1}}\n"
                    . "- {{feature2}}\n"
                    . "- {{feature3}}\n\n"
                    . "Highlight the benefits and use a {{style}} writing style.",
                'variables' => ['product_name', 'feature1', 'feature2', 'feature3', 'style']
            ],
            'seo_meta' => [
                'template' => "Generate SEO meta title and description for a page about {{topic}}.\n"
                    . "Target keywords: {{keywords}}\n"
                    . "Character limits: Title (60), Description (160)",
                'variables' => ['topic', 'keywords']
            ]
        ];
    }

    public function generateTextContent(
        string $templateType,
        array $variables,
        ?string $provider = null,
        array $options = []
    ): string {
        if (!isset($this->promptTemplates[$templateType])) {
            throw new InvalidArgumentException("Template type {$templateType} not found");
        }

        $template = $this->promptTemplates[$templateType]['template'];
        return $this->aiManager->generateContent($template, $variables, $options, $provider);
    }

    public function generateImage(
        string $prompt,
        ?string $provider = null,
        array $options = []
    ): string {
        $options['image_generation'] = true;
        return $this->aiManager->generateContent($prompt, [], $options, $provider);
    }

    public function getAvailableTemplates(): array {
        return array_keys($this->promptTemplates);
    }

    public function addCustomTemplate(
        string $name,
        string $template,
        array $variables
    ): void {
        $this->promptTemplates[$name] = [
            'template' => $template,
            'variables' => $variables
        ];
    }
}
