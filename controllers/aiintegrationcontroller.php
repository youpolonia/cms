<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class AIIntegrationController {
    private static $config;
    private static $cache;
    private static $cacheEnabled = true;
    private static $cacheTtl = 3600; // 1 hour
    
    public static function init() {
        self::$config = require_once __DIR__ . '/../config/ai.php';
        self::initCache();
    }
    
    private static function initCache() {
        if (class_exists('Cache')) {
            self::$cache = new Cache();
        } else {
            self::$cacheEnabled = false;
        }
    }
    
    /**
     * Get all available templates
     */
    public static function getTemplates(): array {
        $templates = self::$config['templates']['system_templates'] ?? [];
        $tenantId = self::getCurrentTenantId();
        
        if ($tenantId && isset(self::$config['templates']['tenant_overrides'][$tenantId])) {
            $templates = array_merge(
                $templates,
                self::$config['templates']['tenant_overrides'][$tenantId]
            );
        }
        
        return $templates;
    }
    
    /**
     * Render template with variables
     */
    public static function renderTemplate(string $templateName, array $variables): string {
        $cacheKey = self::generateCacheKey($templateName, $variables);
        
        if (self::$cacheEnabled) {
            $cached = self::$cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }
        
        $templates = self::getTemplates();
        
        if (!isset($templates[$templateName])) {
            throw new \InvalidArgumentException("Template '$templateName' not found");
        }
        
        $template = $templates[$templateName]['content'];
        $result = self::parseTemplate($template, $variables);
        
        if (self::$cacheEnabled) {
            self::$cache->set($cacheKey, $result, self::$cacheTtl);
        }
        
        return $result;
    }
    
    /**
     * Parse template with variables
     */
    private static function parseTemplate(string $template, array $variables): string {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{{{$key}}}}", $value, $template);
        }
        return $template;
    }
    
    /**
     * Get AI parameters for template
     */
    public static function getTemplateAIParameters(string $templateName): array {
        $templates = self::getTemplates();
        
        if (!isset($templates[$templateName])) {
            throw new \InvalidArgumentException("Template '$templateName' not found");
        }
        
        return $templates[$templateName]['ai_parameters'] ?? [];
    }
    
    /**
     * Check if template requires AI processing
     */
    public static function templateRequiresAI(string $templateName): bool {
        $templates = self::getTemplates();
        return $templates[$templateName]['requires_ai'] ?? false;
    }
    
    private static function getCurrentTenantId(): ?int {
        // Implementation depends on your tenant identification system
        return null;
    }
    
    private static function generateCacheKey(string $templateName, array $variables): string {
        $tenantId = self::getCurrentTenantId() ?? 'global';
        $varsHash = md5(json_encode($variables));
        return "ai_template_{$tenantId}_{$templateName}_{$varsHash}";
    }
    
    public static function clearTemplateCache(string $templateName): void {
        if (!self::$cacheEnabled) return;
        
        $tenantId = self::getCurrentTenantId() ?? 'global';
        $prefix = "ai_template_{$tenantId}_{$templateName}_";
        self::$cache->deleteByPrefix($prefix);
    }
}

AIIntegrationController::init();
