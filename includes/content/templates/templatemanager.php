<?php

class TemplateManager {
    private static array $templates = [];
    private static array $tenantTemplates = [];

    public static function registerTemplate(string $key, string $templateClass): void {
        self::$templates[$key] = $templateClass;
    }

    public static function getTemplate(string $key, ?int $tenantId = null): ContentTemplate {
        if (!isset(self::$templates[$key])) {
            throw new InvalidArgumentException("Template {$key} not registered");
        }

        $templateClass = self::$templates[$key];
        $template = new $templateClass($tenantId);

        // Apply tenant-specific overrides if available
        if ($tenantId && isset(self::$tenantTemplates[$tenantId][$key])) {
            $template->addTenantOverride($tenantId, self::$tenantTemplates[$tenantId][$key]);
        }

        return $template;
    }

    public static function registerTenantTemplate(int $tenantId, string $key, array $overrides): void {
        if (!isset(self::$tenantTemplates[$tenantId])) {
            self::$tenantTemplates[$tenantId] = [];
        }
        self::$tenantTemplates[$tenantId][$key] = $overrides;
    }

    public static function getAvailableTemplates(?int $tenantId = null): array {
        $result = [];
        foreach (self::$templates as $key => $class) {
            $template = new $class($tenantId);
            $result[$key] = [
                'name' => $template->getName(),
                'description' => $template->getDescription()
            ];
        }
        return $result;
    }
}
