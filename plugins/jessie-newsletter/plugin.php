<?php
namespace JessieNewsletter;

if (!defined('CMS_ROOT')) define('CMS_ROOT', realpath(__DIR__ . '/../..'));

class JessieNewsletterPlugin implements \EnhancedPluginInterface
{
    private $hookManager;

    public function __construct(\CMS\Plugins\HookManager $hookManager) { $this->hookManager = $hookManager; }
    public function getMetadata(): array { return ['name' => 'Jessie Newsletter+', 'version' => '1.0.0', 'author' => 'Jessie CMS', 'description' => 'Email marketing with AI content generation']; }
    public function getDependencies(): array { return []; }
    public function getVersionCompatibility(): array { return ['1.0.0', '99.0.0']; }
    public function init(): void { $this->registerHooks(); }
    public function registerHooks(): void { $this->hookManager->addAction('admin_menu', [$this, 'registerAdminMenu']); }
    public function registerAdminMenu(): void {}
    public function install(): void { require_once __DIR__ . '/install.php'; }
    public function activate(): void { $this->install(); }
    public function deactivate(): void {}
    public function uninstall(): void
    {
        $pdo = db();
        foreach (['newsletter_events', 'newsletter_automations', 'newsletter_campaigns', 'newsletter_subscribers', 'newsletter_templates', 'newsletter_lists'] as $t) {
            $pdo->exec("DROP TABLE IF EXISTS {$t}");
        }
    }
}
