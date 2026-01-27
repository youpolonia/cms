<?php
/**
 * TB 4.0 Module System Initialization
 *
 * Loads and registers all TB4 modules
 */

namespace Core\TB4;

require_once __DIR__ . '/modules/module.php';
require_once __DIR__ . '/modules/sectionmodule.php';
require_once __DIR__ . '/modules/rowmodule.php';
require_once __DIR__ . '/modules/columnmodule.php';
require_once __DIR__ . '/modules/textmodule.php';
require_once __DIR__ . '/modules/imagemodule.php';
require_once __DIR__ . '/modules/buttonmodule.php';
require_once __DIR__ . '/modules/dividermodule.php';
require_once __DIR__ . '/modules/blurbmodule.php';
require_once __DIR__ . '/modules/heromodule.php';
require_once __DIR__ . '/modules/ctamodule.php';
require_once __DIR__ . '/modules/testimonialmodule.php';
require_once __DIR__ . '/modules/media/gallerymodule.php';
require_once __DIR__ . '/modules/media/videomodule.php';
require_once __DIR__ . '/modules/media/audiomodule.php';
require_once __DIR__ . '/modules/media/videoslidermodule.php';
require_once __DIR__ . '/modules/content/codemodule.php';
require_once __DIR__ . '/modules/content/teammodule.php';
require_once __DIR__ . '/modules/content/iconmodule.php';
require_once __DIR__ . '/modules/content/socialmodule.php';
require_once __DIR__ . '/modules/content/searchmodule.php';
require_once __DIR__ . '/modules/content/loginmodule.php';
require_once __DIR__ . '/modules/content/signupmodule.php';
require_once __DIR__ . '/modules/content/numbermodule.php';
require_once __DIR__ . '/modules/content/circlemodule.php';
require_once __DIR__ . '/modules/content/countdownmodule.php';
require_once __DIR__ . '/modules/content/progressmodule.php';
require_once __DIR__ . '/modules/content/barcountermodule.php';
require_once __DIR__ . '/modules/content/pricingmodule.php';
require_once __DIR__ . '/modules/content/pricingitemmodule.php';
require_once __DIR__ . '/modules/content/shopmodule.php';
require_once __DIR__ . '/modules/content/blogmodule.php';
require_once __DIR__ . '/modules/content/portfoliomodule.php';
require_once __DIR__ . '/modules/content/filterportfoliomodule.php';
require_once __DIR__ . '/modules/content/postslidermodule.php';
require_once __DIR__ . '/modules/content/posttitlemodule.php';
require_once __DIR__ . '/modules/content/postcontentmodule.php';
require_once __DIR__ . '/modules/content/commentsmodule.php';
require_once __DIR__ . '/modules/content/postnavmodule.php';
require_once __DIR__ . '/modules/interactive/togglemodule.php';
require_once __DIR__ . '/modules/interactive/accordionmodule.php';
require_once __DIR__ . '/modules/interactive/accordionitemmodule.php';
require_once __DIR__ . '/modules/interactive/tabsmodule.php';
require_once __DIR__ . '/modules/interactive/tabsitemmodule.php';
require_once __DIR__ . '/modules/interactive/slidermodule.php';
require_once __DIR__ . '/modules/interactive/slideritemmodule.php';
require_once __DIR__ . '/modules/interactive/contactmodule.php';

// Fullwidth modules
require_once __DIR__ . '/modules/fullwidth/fwheadermodule.php';
require_once __DIR__ . '/modules/fullwidth/fwimagemodule.php';
require_once __DIR__ . '/modules/fullwidth/fwslidermodule.php';
require_once __DIR__ . '/modules/fullwidth/fwmapmodule.php';
require_once __DIR__ . '/modules/fullwidth/fwmenumodule.php';
require_once __DIR__ . '/modules/fullwidth/fwportfoliomodule.php';
require_once __DIR__ . '/modules/fullwidth/fwpostslidermodule.php';
require_once __DIR__ . '/modules/fullwidth/fwcodemodule.php';

/**
 * Module Registry
 */
class ModuleRegistry {

    private static ?ModuleRegistry $instance = null;
    private array $modules = [];

    private function __construct() {
    }

    public static function getInstance(): ModuleRegistry {
        if (self::$instance === null) {
            self::$instance = new ModuleRegistry();
        }
        return self::$instance;
    }

    /**
     * Register a module class
     */
    public function register(string $moduleClass): void {
        if (!class_exists($moduleClass)) {
            return;
        }

        $module = new $moduleClass();
        $slug = $module->getSlug();
        $this->modules[$slug] = $module;
    }

    /**
     * Get a registered module by slug
     */
    public function getModule(string $slug): ?\Core\TB4\Modules\Module {
        return $this->modules[$slug] ?? null;
    }

    /**
     * Get all registered modules
     */
    public function getAllModules(): array {
        return $this->modules;
    }

    /**
     * Get modules by category
     */
    public function getModulesByCategory(string $category): array {
        return array_filter($this->modules, function($module) use ($category) {
            return $module->getCategory() === $category;
        });
    }

    /**
     * Get modules as JSON-serializable array
     */
    public function getModulesForJson(): array {
        $result = [];
        foreach ($this->modules as $slug => $module) {
            $result[$slug] = [
                'name' => $module->getName(),
                'slug' => $module->getSlug(),
                'icon' => $module->getIcon(),
                'category' => $module->getCategory(),
                'type' => $module->getType(),
                'child_slug' => $module->getChildSlug(),
                'parent_slug' => $module->getParentSlug(),
                'child_title_var' => $module->getChildTitleVar(),
                'fields' => $module->get_content_fields(),
                'design' => $module->get_design_fields(),
                'advanced' => $module->get_advanced_tab_fields()
            ];
        }
        return $result;
    }
}

// Initialize the registry
$registry = ModuleRegistry::getInstance();

// Register structure modules (containers)
$registry->register(\Core\TB4\Modules\SectionModule::class);
$registry->register(\Core\TB4\Modules\RowModule::class);
$registry->register(\Core\TB4\Modules\ColumnModule::class);

// Register content modules
$registry->register(\Core\TB4\Modules\TextModule::class);
$registry->register(\Core\TB4\Modules\ImageModule::class);
$registry->register(\Core\TB4\Modules\ButtonModule::class);
$registry->register(\Core\TB4\Modules\DividerModule::class);
$registry->register(\Core\TB4\Modules\BlurbModule::class);
$registry->register(\Core\TB4\Modules\HeroModule::class);
$registry->register(\Core\TB4\Modules\CtaModule::class);
$registry->register(\Core\TB4\Modules\TestimonialModule::class);

// Register media modules
$registry->register(\Core\TB4\Modules\Media\GalleryModule::class);
$registry->register(\Core\TB4\Modules\Media\VideoModule::class);
$registry->register(\Core\TB4\Modules\Media\AudioModule::class);
$registry->register(\Core\TB4\Modules\Media\VideoSliderModule::class);

// Register content modules (subfolder)
$registry->register(\Core\TB4\Modules\Content\CodeModule::class);
$registry->register(\Core\TB4\Modules\Content\TeamModule::class);
$registry->register(\Core\TB4\Modules\Content\IconModule::class);
$registry->register(\Core\TB4\Modules\Content\SocialModule::class);
$registry->register(\Core\TB4\Modules\Content\SearchModule::class);
$registry->register(\Core\TB4\Modules\Content\LoginModule::class);
$registry->register(\Core\TB4\Modules\Content\SignupModule::class);
$registry->register(\Core\TB4\Modules\Content\NumberModule::class);
$registry->register(\Core\TB4\Modules\Content\CircleModule::class);
$registry->register(\Core\TB4\Modules\Content\CountdownModule::class);
$registry->register(\Core\TB4\Modules\Content\ProgressModule::class);
$registry->register(\Core\TB4\Modules\Content\BarCounterModule::class);
$registry->register(\Core\TB4\Modules\Content\PricingModule::class);
$registry->register(\Core\TB4\Modules\Content\PricingItemModule::class);
$registry->register(\Core\TB4\Modules\Content\ShopModule::class);
$registry->register(\Core\TB4\Modules\Content\BlogModule::class);
$registry->register(\Core\TB4\Modules\Content\PortfolioModule::class);
$registry->register(\Core\TB4\Modules\Content\FilterPortfolioModule::class);
$registry->register(\Core\TB4\Modules\Content\PostSliderModule::class);
$registry->register(\Core\TB4\Modules\Content\PostTitleModule::class);
$registry->register(\Core\TB4\Modules\Content\PostContentModule::class);
$registry->register(\Core\TB4\Modules\Content\CommentsModule::class);
$registry->register(\Core\TB4\Modules\Content\PostNavModule::class);

// Register interactive modules
$registry->register(\Core\TB4\Modules\Interactive\ToggleModule::class);
$registry->register(\Core\TB4\Modules\Interactive\AccordionModule::class);
$registry->register(\Core\TB4\Modules\Interactive\AccordionItemModule::class);
$registry->register(\Core\TB4\Modules\Interactive\TabsModule::class);
$registry->register(\Core\TB4\Modules\Interactive\TabsItemModule::class);
$registry->register(\Core\TB4\Modules\Interactive\SliderModule::class);
$registry->register(\Core\TB4\Modules\Interactive\SliderItemModule::class);
$registry->register(\Core\TB4\Modules\Interactive\ContactModule::class);

// Register fullwidth modules
$registry->register(\Core\TB4\Modules\Fullwidth\FwHeaderModule::class);
$registry->register(\Core\TB4\Modules\Fullwidth\FwImageModule::class);
$registry->register(\Core\TB4\Modules\Fullwidth\FwSliderModule::class);
$registry->register(\Core\TB4\Modules\Fullwidth\FwMapModule::class);
$registry->register(\Core\TB4\Modules\Fullwidth\FwMenuModule::class);
$registry->register(\Core\TB4\Modules\Fullwidth\FwPortfolioModule::class);
$registry->register(\Core\TB4\Modules\Fullwidth\FwPostSliderModule::class);
$registry->register(\Core\TB4\Modules\Fullwidth\FwCodeModule::class);
