<?php
/**
 * Admin Security Module - Validates admin session integrity
 */
class AdminSecurity {
    private SessionService $session;
    private string $expectedUserAgent;
    private string $expectedIp;

    public function __construct(SessionService $session) {
        $this->session = $session;
        $this->expectedUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->expectedIp = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /**
     * Validates admin session integrity
     * @throws RuntimeException On validation failure
     */
    public function validate(): void {
        $this->validateSessionActive();
        $this->validateSessionExpiration();
        $this->validateSessionConsistency();
    }

    private function validateSessionActive(): void {
        if (!$this->session->has(AuthService::AUTH_USER_ID_KEY)) {
            throw new RuntimeException('Session not authenticated');
        }
    }

    private function validateSessionExpiration(): void {
        if ($this->session->isExpired()) {
            $this->session->destroy();
            throw new RuntimeException('Session expired');
        }
    }

    private function validateSessionConsistency(): void {
        $sessionUserAgent = $this->session->get('user_agent');
        $sessionIp = $this->session->get('ip_address');

        if ($sessionUserAgent !== $this->expectedUserAgent) {
            throw new RuntimeException('User agent mismatch');
        }

        if ($sessionIp !== $this->expectedIp) {
            throw new RuntimeException('IP address changed');
        }
    }
}
