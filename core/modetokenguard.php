<?php
require_once __DIR__.'/../utilities/tokenmonitor.php';
// Read fallback models configuration from markdown file if needed
$fallbackModelsPath = __DIR__.'/../cms_storage/fallback_models.md';
// We don't use require_once for markdown files as they're not PHP code
// If the content is needed, use file_get_contents() instead

class ModeTokenGuard {
    private static $modeLimits = [
        'code' => 30000,
        'architect' => 20000,
        'ask' => 15000,
        'debug' => 25000,
        'orchestrator' => 35000
    ];

    private static $fallbackModels = [
        'code' => ['ask', 'debug', 'orchestrator'],
        'architect' => ['code', 'ask'],
        'debug' => ['code', 'ask'],
        'orchestrator' => ['architect', 'code'],
        'pattern-reader' => ['code', 'debug']
    ];

    private static $modelPriorities = [
        'deepseek/deepseek-chat-v3-0324' => 1,
        'gemini-2.5-pro-preview' => 2,
        'local-llm' => 3,
        'reduced-functionality' => 4
    ];

    public static function checkModeLimit(string $mode, int $currentUsage): bool {
        if (!isset(self::$modeLimits[$mode])) {
            return true;
        }

        $threshold = (int)(self::$modeLimits[$mode] * 0.75);
        return TokenMonitor::checkUsage($currentUsage, $threshold);
    }

    public static function enforceModeLimits(): bool {
        $currentMode = self::detectCurrentMode();
        $currentUsage = self::getCurrentTokenUsage();
        
        if (!self::checkModeLimit($currentMode, $currentUsage)) {
            return self::triggerModeSafetyProtocol($currentMode);
        }
        return true;
    }

    public static function getCurrentMode(): string {
        // Implementation would interface with system API
        return 'code'; // Default fallback
    }

    private static function triggerModeSafetyProtocol(string $mode): bool {
        // 1. Save mode-specific state
        self::saveModeState($mode);
        
        // 2. Log emergency
        self::logEmergency($mode);
        
        // 3. Attempt fallback model switch
        if (isset(self::$fallbackModels[$mode])) {
            foreach (self::$fallbackModels[$mode] as $fallbackMode) {
                if (self::isFallbackAvailable($fallbackMode)) {
                    return self::requestModelSwitch($fallbackMode);
                }
            }
        }
        
        // 4. Emergency fallback to reduced functionality
        return self::activateEmergencyMode();
    }

    private static function isFallbackAvailable(string $mode): bool {
        $currentUsage = TokenMonitor::getCurrentUsage($mode);
        $limit = self::$modeLimits[$mode] ?? 0;
        return $currentUsage < ($limit * 0.8); // 80% threshold
    }

    private static function requestModelSwitch(string $targetMode): bool {
        // Implementation would interface with system API
        // Returns true if switch was successful
        file_put_contents(
            __DIR__.'/../logs/model_switches.log',
            date('Y-m-d H:i:s')." - Switching to $targetMode\n",
            FILE_APPEND
        );
        return true;
    }

    private static function activateEmergencyMode(): bool {
        file_put_contents(
            __DIR__.'/../logs/emergency_activations.log',
            date('Y-m-d H:i:s')." - ACTIVATED EMERGENCY MODE\n",
            FILE_APPEND
        );
        return true;
    }

    private static function saveModeState(string $mode): void {
        // Save current mode state to memory bank
        file_put_contents(
            __DIR__.'/../logs/mode_states/'.$mode.'.state',
            json_encode(['timestamp' => time()])
        );
    }

    private static function logEmergency(string $mode): void {
        $logEntry = date('Y-m-d H:i:s')." - Mode $mode exceeded token limits\n";
        file_put_contents(
            __DIR__.'/../logs/quota_log.md',
            $logEntry,
            FILE_APPEND
        );
    }

    private static function requestModeSwitch(string $targetMode): bool {
        // Implementation would interface with system API
        // Returns true if switch was successful
        return true;
    }
}
