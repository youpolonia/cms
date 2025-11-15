<?php

namespace Modules\Content\Services;

class TemplateResolutionException extends \RuntimeException
{
    public function __construct(
        string $templatePath,
        array $attemptedPaths,
        string $requestedTheme,
        string $fallbackTheme
    ) {
        $message = sprintf(
            "Template resolution failed for '%s' (requested theme: %s, fallback: %s).\nAttempted paths:\n%s",
            $templatePath,
            $requestedTheme,
            $fallbackTheme,
            implode("\n", array_map(fn($p) => "- $p", $attemptedPaths))
        );
        
        parent::__construct($message, 0, null);
    }
}

/**
 * Template renderer with theme fallback support
 */
class TemplateRenderer
{
    private string $basePath;
    private string $fallbackTheme;
    private const DEFAULT_THEME = '_default';

    /**
     * @param string $basePath Base directory for templates
     * @param string $fallbackTheme Fallback theme name
     */
    public function __construct(string $basePath, string $fallbackTheme = 'default')
    {
        $this->basePath = rtrim($basePath, '/');
        $this->fallbackTheme = $fallbackTheme;
    }

    /**
     * Render a template with variables
     * @param string $templatePath Template path relative to theme
     * @param array $variables Variables to expose in template
     * @param string|null $theme Theme name (optional)
     * @return string Rendered content
     * @throws \RuntimeException If template not found
     */
    public function render(string $templatePath, array $variables = [], ?string $theme = null): string
    {
        $templateFile = $this->resolveTemplatePath($templatePath, $theme);
        
        if (!file_exists($templateFile)) {
            throw new \RuntimeException("Template not found: {$templateFile}");
        }

        extract($variables, EXTR_SKIP);
        ob_start();
        require_once $templateFile;
        return ob_get_clean();
    }

    /**
     * Resolve template path with theme fallback
     * @throws TemplateResolutionException
     */
    private function resolveTemplatePath(string $templatePath, ?string $theme): string
    {
        $attempts = [];
        $pathsToCheck = [];

        // Build paths to check in order of precedence
        if ($theme !== null) {
            // 1. Exact path in requested theme
            $pathsToCheck[] = "{$this->basePath}/{$theme}/{$templatePath}";
            
            // 2. Parent directories in requested theme (file-level fallback)
            $parts = explode('/', $templatePath);
            while (count($parts) > 1) {
                array_pop($parts);
                $pathsToCheck[] = "{$this->basePath}/{$theme}/" . implode('/', $parts) . '/' . basename($templatePath);
            }

            // 3. Default theme templates (shared across all themes)
            $pathsToCheck[] = "{$this->basePath}/" . self::DEFAULT_THEME . "/{$templatePath}";
        }

        // 4. Exact path in fallback theme
        $pathsToCheck[] = "{$this->basePath}/{$this->fallbackTheme}/{$templatePath}";
        
        // 5. Parent directories in fallback theme (file-level fallback)
        $parts = explode('/', $templatePath);
        while (count($parts) > 1) {
            array_pop($parts);
            $pathsToCheck[] = "{$this->basePath}/{$this->fallbackTheme}/" . implode('/', $parts) . '/' . basename($templatePath);
        }

        // 6. Default theme templates (shared across all themes)
        $pathsToCheck[] = "{$this->basePath}/" . self::DEFAULT_THEME . "/{$templatePath}";

        // Check each path
        foreach ($pathsToCheck as $path) {
            $attempts[] = $path;
            if (file_exists($path)) {
                $this->logResolution($templatePath, $path, $theme);
                return $path;
            }
        }

        throw new TemplateResolutionException(
            $templatePath,
            $attempts,
            $theme ?? 'none',
            $this->fallbackTheme
        );
    }
    /**
     * Log successful template resolution
     */
    private function logResolution(string $templatePath, string $resolvedPath, ?string $theme): void
    {
        $logEntry = sprintf(
            "[%s] Resolved template '%s' to '%s' (theme: %s)",
            date('Y-m-d H:i:s'),
            $templatePath,
            $resolvedPath,
            $theme ?? 'fallback'
        );
        
        file_put_contents(
            __DIR__ . '/../../../memory-bank/template_resolution.log',
            $logEntry . PHP_EOL,
            FILE_APPEND
        );
    }
}
