<?php
namespace Admin\Components;

use Admin\Core\Services\LanguageService;

class LanguageSwitcher {
    private $languageService;

    public function __construct() {
        $this->languageService = LanguageService::getInstance();
    }

    /**
     * Render language switcher dropdown
     */
    public function render(): string {
        $currentLanguage = $this->languageService->detectLanguage();
        $languages = $this->languageService->getAvailableLanguages();

        $html = '<div class="language-switcher">';
        $html .= '<select onchange="window.location.href=this.value">';
        
        foreach ($languages as $code) {
            $selected = $code === $currentLanguage ? ' selected' : '';
            $url = $this->getLanguageUrl($code);
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                htmlspecialchars($url),
                $selected,
                htmlspecialchars($this->getLanguageName($code))
            );
        }

        $html .= '</select>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Get URL with language parameter
     */
    private function getLanguageUrl(string $code): string {
        $currentUrl = $_SERVER['REQUEST_URI'];
        $query = $_GET;
        $query['lang'] = $code;
        
        return strtok($currentUrl, '?') . '?' . http_build_query($query);
    }

    /**
     * Get display name for language code
     */
    private function getLanguageName(string $code): string {
        $names = [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano'
        ];
        
        return $names[$code] ?? strtoupper($code);
    }
}
