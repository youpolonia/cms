<?php
/**
 * CMS Template System
 * 
 * @package CMS
 * @subpackage Theme
 */

namespace Includes\Theme;

class Template
{
    protected Theme $theme;
    protected string $template;
    protected array $data = [];
    protected array $sections = [];
    protected ?string $currentSection = null;

    public function __construct(Theme $theme, string $template)
    {
        $this->theme = $theme;
        $this->template = $template;
    }

    public function render(array $data = []): string
    {
        $this->data = array_merge($this->data, $data);
        $templatePath = $this->theme->getThemePath() . '/templates/' . $this->template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$templatePath}");
        }

        ob_start();
        extract($this->data);
        require_once $templatePath;
        return ob_get_clean();
    }

    public function extend(string $parentTemplate): void
    {
        $this->parentTemplate = $parentTemplate;
    }

    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection(): void
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    public function yield(string $sectionName): string
    {
        return $this->sections[$sectionName] ?? '';
    }
}
