<?php
/**
 * Company Model - Handles company data and operations
 */
class CompanyModel extends BaseModel {
    protected static string $table = 'companies';
    protected static array $columns = [
        'id', 'name', 'address', 'city', 'state',
        'postal_code', 'country', 'phone', 'email',
        'website', 'description', 'created_at', 'updated_at'
    ];
    /**
     * @var bool Whether to enhance descriptions with AI
     */
    public static bool $enhanceDescriptions = false;

    /**
     * Initialize AI services if enabled
     */
    public static function initAiServices(): void
    {
        if (self::$enhanceDescriptions && file_exists(__DIR__.'/../config/ai_services.php')) {
            require_once __DIR__.'/../Services/AiDescriptionService.php';
            $aiConfig = require_once __DIR__.'/../config/ai_services.php';
            AiDescriptionService::init($aiConfig['description_service']);
        }
    }
    /**
     * @var int|null Company ID
     */
    public ?int $id = null;

    /**
     * @var string Company name
     */
    public string $name = '';

    /**
     * @var string Company address
     */
    public string $address = '';

    /**
     * @var string Company city
     */
    public string $city = '';

    /**
     * @var string Company state/province
     */
    public string $state = '';

    /**
     * @var string Company postal code
     */
    public string $postal_code = '';

    /**
     * @var string Company country
     */
    public string $country = '';

    /**
     * @var string Company phone number
     */
    public string $phone = '';

    /**
     * @var string Company email
     */
    public string $email = '';

    /**
     * @var string Company website
     */
    public string $website = '';

    /**
     * @var string Company description
     */
    public string $description = '';

    /**
     * @var string Date created (ISO 8601 format)
     */
    public string $created_at = '';

    /**
     * @var string Date updated (ISO 8601 format)
     */
    public string $updated_at = '';

    /**
     * Saves company data
     * @return bool True on success
     */
    public function save(): bool {
        if (self::$enhanceDescriptions && !empty($this->description)) {
            $this->description = AiDescriptionService::enhanceDescription(
                $this->description,
                $this->name
            );
        }
        
        return parent::save();
    }

    /**
     * Loads company data by ID
     * @param int $id Company ID
     * @return bool True if found and loaded
     */
    public function load(int $id): bool {
        return parent::load($id);
    }
}
