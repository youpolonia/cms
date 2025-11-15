<?php

class TemplateSystem {
    private array $templates = [];
    private array $tenantTemplates = [];

    public function __construct(array $config) {
        $this->loadSystemTemplates($config['system_templates'] ?? []);
    }

    public function loadTenantTemplates(int $tenantId, array $templates): void {
        $this->tenantTemplates[$tenantId] = $templates;
    }

    public function getTemplate(string $name, ?int $tenantId = null): ?array {
        // Check tenant-specific templates first
        if ($tenantId && isset($this->tenantTemplates[$tenantId][$name])) {
            return $this->tenantTemplates[$tenantId][$name];
        }

        // Fall back to system templates
        return $this->templates[$name] ?? null;
    }

    public function applyTemplate(
        string $templateName,
        array $variables,
        ?int $tenantId = null
    ): string {
        $template = $this->getTemplate($templateName, $tenantId);
        if (!$template) {
            throw new InvalidArgumentException("Template {$templateName} not found");
        }

        $content = $template['content'];
        foreach ($variables as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }

    public function generateFromTemplate(
        string $templateName,
        array $variables,
        ?int $tenantId = null,
        ?AIProviderInterface $aiProvider = null
    ): string {
        $template = $this->getTemplate($templateName, $tenantId);
        if (!$template) {
            throw new InvalidArgumentException("Template {$templateName} not found");
        }

        $content = $this->applyTemplate($templateName, $variables, $tenantId);

        if ($template['requires_ai'] && $aiProvider) {
            $content = $aiProvider->generateContent(
                $content,
                $template['ai_parameters'] ?? [],
                $tenantId
            );
        }

        return $content;
    }

    private function loadSystemTemplates(array $templates): void {
        foreach ($templates as $name => $config) {
            $this->templates[$name] = [
                'content' => $config['content'] ?? '',
                'requires_ai' => $config['requires_ai'] ?? false,
                'ai_parameters' => $config['ai_parameters'] ?? []
            ];
        }
    }
}
