<?php
namespace JessieMembership;

if (!defined('CMS_ROOT')) define('CMS_ROOT', realpath(__DIR__ . '/../..'));

class JessieMembershipPlugin implements \EnhancedPluginInterface
{
    private $hookManager;
    public function __construct(\CMS\Plugins\HookManager $hookManager) { $this->hookManager = $hookManager; }
    public function getMetadata(): array { return ['name' => 'Jessie Membership', 'version' => '1.0.0', 'author' => 'Jessie CMS', 'description' => 'Membership plans, gated content, subscriptions']; }
    public function getDependencies(): array { return []; }
    public function getVersionCompatibility(): array { return ['1.0.0', '99.0.0']; }
    public function init(): void {}
    public function registerHooks(): void {}
    public function install(): void { require_once __DIR__ . '/install.php'; }
    public function activate(): void { $this->install(); }
    public function deactivate(): void {}
    public function uninstall(): void
    {
        $pdo = db();
        foreach (['membership_content_rules','membership_transactions','membership_members','membership_plans'] as $t) {
            $pdo->exec("DROP TABLE IF EXISTS {$t}");
        }
    }
}
