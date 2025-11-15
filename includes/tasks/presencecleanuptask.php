<?php
namespace CMS\Tasks;

use CMS\Realtime\PresenceHandler;

class PresenceCleanupTask {
    private $presenceHandler;

    public function __construct(PresenceHandler $presenceHandler) {
        $this->presenceHandler = $presenceHandler;
    }

    public function run(): void {
        $this->presenceHandler->cleanupInactive();
    }
}
