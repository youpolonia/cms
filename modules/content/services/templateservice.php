<?php
declare(strict_types=1);

namespace Modules\Content\Services;

use Modules\Content\Exceptions\TemplateNotFoundException;
use Modules\Content\Exceptions\InvalidThemeException;

class TemplateService {
    private const CORE_TEMPLATES_PATH = 'core/templates';
    private const DEFAULT_THEME = 'default';

    /**
     * Resolves template path using theme fallback hierarchy
     * @throws TemplateNotFoundException
     * @throws InvalidThemeException
     */
    public function resolveTemplatePath(string $template, string $theme): string {
        $this->validateTheme($theme);

        $pathsToCheck = [
            "themes/{$theme}/{$template}",
            "themes/" . self::DEFAULT_THEME . "/{$template}",
            self::CORE_TEMPLATES_PATH . "/{$template}"
        ];

        foreach ($pathsToCheck as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new TemplateNotFoundException("Template '{$template}' not found in theme hierarchy");
    }

    private function validateTheme(string $theme): void {
        if (!is_dir("themes/{$theme}")) {
            throw new InvalidThemeException("Theme '{$theme}' does not exist");
        }
    }
}
