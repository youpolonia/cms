<?php
namespace Admin\Core\Services;

class LanguageService {
    private static $instance = null;
    private $db;
    private $defaultLanguage = 'en';
    
    private function __construct() {
        require_once __DIR__ . '/../../../core/database.php';
        $this->db = \core\Database::connection();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get all available languages
     * @return array List of language codes
     */
    public function getAvailableLanguages(): array {
        $stmt = $this->db->prepare("SELECT language_code FROM languages WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get default language code
     * @return string Default language code
     */
    public function getDefaultLanguage(): string {
        return $this->defaultLanguage;
    }

    /**
     * Get translations for specific content entry
     * @param int $entryId Content entry ID
     * @param string $languageCode Target language code
     * @return array|null Translation data or null if not found
     */
    public function getContentTranslations(int $entryId, string $languageCode): ?array {
        // First try requested language
        $stmt = $this->db->prepare("
            SELECT * FROM content_translations 
            WHERE entry_id = ? AND language_code = ?
        ");
        $stmt->execute([$entryId, $languageCode]);
        $translation = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($translation) {
            return $translation;
        }

        // Fallback to default language if enabled
        return $this->fallbackToDefault($entryId);
    }

    /**
     * Fallback to default language translation
     * @param int $entryId Content entry ID
     * @return array|null Default language translation or null
     */
    private function fallbackToDefault(int $entryId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM content_translations 
            WHERE entry_id = ? AND language_code = ?
        ");
        $stmt->execute([$entryId, $this->defaultLanguage]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Detect language from URL/session
     * @return string Detected language code
     */
    public function detectLanguage(): string {
        // Check URL first (e.g. /en/page)
        if (isset($_GET['lang']) && in_array($_GET['lang'], $this->getAvailableLanguages())) {
            $_SESSION['language'] = $_GET['lang'];
            return $_GET['lang'];
        }

        // Check session
        if (isset($_SESSION['language'])) {
            return $_SESSION['language'];
        }

        // Fallback to default
        return $this->defaultLanguage;
    }
}
