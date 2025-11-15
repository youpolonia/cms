<?php

class LocaleDetector {
    private $languageManager;
    private $sessionKey = 'user_language';

    public function __construct() {
        $this->languageManager = LanguageManager::getInstance();
    }

    public function detectFromRequest(): string {
        // Check session/cookie first if available
        if (isset($_SESSION[$this->sessionKey])) {
            $lang = $_SESSION[$this->sessionKey];
            if ($this->languageManager->isLanguageAvailable($lang)) {
                return $lang;
            }
        }

        // Parse Accept-Language header
        $httpLang = $this->parseHttpAcceptLanguage();
        if ($httpLang && $this->languageManager->isLanguageAvailable($httpLang)) {
            return $httpLang;
        }

        return $this->languageManager->getDefaultLanguage();
    }

    public function setUserLanguage(string $language): bool {
        if (!$this->languageManager->isLanguageAvailable($language)) {
            return false;
        }

        $_SESSION[$this->sessionKey] = $language;
        return true;
    }

    private function parseHttpAcceptLanguage(): ?string {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $languages = explode(',', $acceptLang);
        
        foreach ($languages as $lang) {
            $lang = explode(';', $lang)[0]; // Remove quality values
            $lang = trim($lang);
            
            // Check full language code (e.g. en-US)
            if ($this->languageManager->isLanguageAvailable($lang)) {
                return $lang;
            }
            
            // Check base language (e.g. en)
            $baseLang = explode('-', $lang)[0];
            if ($this->languageManager->isLanguageAvailable($baseLang)) {
                return $baseLang;
            }
        }

        return null;
    }
}
