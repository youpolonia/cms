<?php
declare(strict_types=1);

class AuthService {
    private static ?AuthService $instance = null;
    private array $sessions = [];
    private array $permissions = [];

    private function __construct() {
        // Initialize with default permissions
        $this->loadDefaultPermissions();
    }

    public static function getInstance(): AuthService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadDefaultPermissions(): void {
        // TODO: Load default permissions from configuration
    }

    public function authenticate(string $username, string $password): bool {
        // TODO: Implement actual authentication
        $this->sessions[$username] = [
            'logged_in' => true,
            'last_active' => time()
        ];
        return true;
    }

    public function checkPermission(string $username, string $permission): bool {
        return $this->permissions[$username][$permission] ?? false;
    }

    public function getSitesForUser(string $username): array {
        $siteGroups = SiteGroupService::getInstance()->getGroupsForUser($username);
        $sites = [];
        foreach ($siteGroups as $group) {
            $sites = array_merge($sites, SiteGroupService::getInstance()->getGroupSites($group));
        }
        return array_unique($sites);
    }

    public function logout(string $username): void {
        unset($this->sessions[$username]);
    }
}
