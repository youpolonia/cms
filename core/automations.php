<?php
/**
 * Automations Helper
 * Manages automation tasks - listing, enabling/disabling, and running tasks
 */

/**
 * Get path to automations config file
 * @return string
 */
function automations_get_config_path(): string
{
    return defined('CMS_ROOT') ? CMS_ROOT . '/config/automations.json' : __DIR__ . '/../config/automations.json';
}

/**
 * Initialize automations config with default tasks if file doesn't exist
 * @return void
 */
function automations_init_defaults(): void
{
    $configPath = automations_get_config_path();

    if (file_exists($configPath)) {
        return;
    }

    $defaultAutomations = [
        [
            'id' => 'backup-task',
            'name' => 'Backup Task',
            'description' => 'Creates backup archives of config and memory-bank directories',
            'task_class' => 'BackupTask',
            'enabled' => false,
            'interval' => 'Daily',
            'last_run' => null,
        ],
        [
            'id' => 'email-queue-task',
            'name' => 'Email Queue Task',
            'description' => 'Processes queued emails for delivery',
            'task_class' => 'EmailQueueTask',
            'enabled' => false,
            'interval' => 'Every 15 minutes',
            'last_run' => null,
        ],
        [
            'id' => 'cache-refresher-task',
            'name' => 'Cache Refresher Task',
            'description' => 'Refreshes application cache',
            'task_class' => 'CacheRefresherTask',
            'enabled' => false,
            'interval' => 'Hourly',
            'last_run' => null,
        ],
        [
            'id' => 'session-cleaner-task',
            'name' => 'Session Cleaner Task',
            'description' => 'Cleans up expired user sessions',
            'task_class' => 'SessionCleanerTask',
            'enabled' => false,
            'interval' => 'Daily',
            'last_run' => null,
        ],
        [
            'id' => 'temp-cleaner-task',
            'name' => 'Temp File Cleaner Task',
            'description' => 'Removes old temporary files',
            'task_class' => 'TempCleanerTask',
            'enabled' => false,
            'interval' => 'Daily',
            'last_run' => null,
        ],
        [
            'id' => 'log-rotation-task',
            'name' => 'Log Rotation Task',
            'description' => 'Rotates and archives old log files',
            'task_class' => 'LogRotationTask',
            'enabled' => false,
            'interval' => 'Weekly',
            'last_run' => null,
        ],
    ];

    $configDir = dirname($configPath);
    if (!is_dir($configDir)) {
        @mkdir($configDir, 0755, true);
    }

    @file_put_contents($configPath, json_encode($defaultAutomations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

/**
 * List all automations
 * @return array
 */
function automations_list(): array
{
    automations_init_defaults();

    $configPath = automations_get_config_path();

    if (!file_exists($configPath)) {
        return [];
    }

    $json = @file_get_contents($configPath);
    if ($json === false) {
        return [];
    }

    $automations = json_decode($json, true);
    if (!is_array($automations)) {
        return [];
    }

    return $automations;
}

/**
 * Set automation enabled status
 * @param string $id
 * @param bool $enabled
 * @return bool
 */
function automations_set_enabled(string $id, bool $enabled): bool
{
    $automations = automations_list();
    $found = false;

    foreach ($automations as $key => $automation) {
        if (isset($automation['id']) && $automation['id'] === $id) {
            $automations[$key]['enabled'] = $enabled;
            $found = true;
            break;
        }
    }

    if (!$found) {
        return false;
    }

    $configPath = automations_get_config_path();
    $result = @file_put_contents($configPath, json_encode($automations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

    return $result !== false;
}

/**
 * Run automation immediately
 * @param string $id
 * @return bool
 */
function automations_run_now(string $id): bool
{
    $automations = automations_list();
    $taskClass = null;

    foreach ($automations as $key => $automation) {
        if (isset($automation['id']) && $automation['id'] === $id) {
            $taskClass = $automation['task_class'] ?? null;
            $automations[$key]['last_run'] = date('Y-m-d H:i:s');
            break;
        }
    }

    if ($taskClass === null) {
        return false;
    }

    if (!class_exists($taskClass) || !method_exists($taskClass, 'run')) {
        return false;
    }

    $configPath = automations_get_config_path();
    @file_put_contents($configPath, json_encode($automations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

    return $taskClass::run() !== false;
}
