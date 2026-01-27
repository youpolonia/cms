<?php
/**
 * Test Hook System
 */
class HookSystemTest {
    private bool $testMode = true;

    public function setTestMode(bool $enabled): void {
        $this->testMode = $enabled;
    }
}
