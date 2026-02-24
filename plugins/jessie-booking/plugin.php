<?php
namespace JessieBooking;

if (!defined('CMS_ROOT')) define('CMS_ROOT', realpath(__DIR__ . '/../..'));

class JessieBookingPlugin implements \EnhancedPluginInterface
{
    private $hookManager;

    public function __construct(\CMS\Plugins\HookManager $hookManager) { $this->hookManager = $hookManager; }
    public function getMetadata(): array { return ['name' => 'Jessie Booking', 'version' => '1.0.0', 'author' => 'Jessie CMS', 'description' => 'Appointment booking & scheduling system with AI']; }
    public function getDependencies(): array { return []; }
    public function getVersionCompatibility(): array { return ['1.0.0', '99.0.0']; }

    public function init(): void { $this->registerHooks(); }

    public function registerHooks(): void
    {
        $this->hookManager->addAction('admin_menu', [$this, 'registerAdminMenu']);
    }

    public function registerAdminMenu(): void
    {
        $menuItems = [
            ['label' => '📅 Dashboard', 'url' => '/admin/booking'],
            ['label' => '📋 Services', 'url' => '/admin/booking/services'],
            ['label' => '👤 Staff', 'url' => '/admin/booking/staff'],
            ['label' => '📅 Calendar', 'url' => '/admin/booking/calendar'],
            ['label' => '📋 Appointments', 'url' => '/admin/booking/appointments'],
            ['label' => '⚙️ Settings', 'url' => '/admin/booking/settings'],
        ];
        $this->hookManager->doAction('admin_menu_register', [$menuItems]);
    }

    public function install(): void
    {
        require_once __DIR__ . '/install.php';
    }

    public function activate(): void { $this->install(); }
    public function deactivate(): void {}
    public function uninstall(): void
    {
        $pdo = db();
        foreach (['booking_appointments', 'booking_staff', 'booking_services', 'booking_settings'] as $t) {
            $pdo->exec("DROP TABLE IF EXISTS {$t}");
        }
    }
}
