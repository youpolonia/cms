<?php
namespace Services\Calendar;

use Services\Encryption\EncryptionService;

class GoogleCalendarService implements CalendarService {
    private $client;
    private $encryptionService;
    private $connectionData;

    public function __construct(array $connectionData, EncryptionService $encryptionService) {
        $this->connectionData = $connectionData;
        $this->encryptionService = $encryptionService;
        $this->initializeClient();
    }

    private function initializeClient() {
        // Initialize Google API client
        $this->client = new \Google\Client();
        $this->client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $this->client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $this->client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $this->client->addScope(\Google\Service\Calendar::CALENDAR);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        if (!empty($this->connectionData['access_token'])) {
            $accessToken = $this->encryptionService->decrypt($this->connectionData['access_token']);
            $this->client->setAccessToken(json_decode($accessToken, true));
        }
    }

    public function authenticate(string $authCode): bool {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($authCode);
            $this->storeTokens($token);
            return true;
        } catch (\Exception $e) {
            error_log('Google auth failed: ' . $e->getMessage());
            return false;
        }
    }

    private function storeTokens(array $tokens) {
        $this->connectionData['access_token'] = $this->encryptionService->encrypt(json_encode($tokens));
        
        if (isset($tokens['refresh_token'])) {
            $this->connectionData['refresh_token'] = $this->encryptionService->encrypt($tokens['refresh_token']);
        }
        
        $this->connectionData['expires_at'] = date('Y-m-d H:i:s', time() + $tokens['expires_in']);
    }

    public function refreshToken(): bool {
        try {
            $refreshToken = $this->encryptionService->decrypt($this->connectionData['refresh_token']);
            $this->client->refreshToken($refreshToken);
            $token = $this->client->getAccessToken();
            $this->storeTokens($token);
            return true;
        } catch (\Exception $e) {
            error_log('Token refresh failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getEvents(\DateTime $start, \DateTime $end): array {
        // Implementation to be added
        return [];
    }

    public function createEvent(array $eventData): bool {
        // Implementation to be added
        return false;
    }

    public function updateEvent(string $eventId, array $eventData): bool {
        // Implementation to be added
        return false;
    }

    public function deleteEvent(string $eventId): bool {
        // Implementation to be added
        return false;
    }

    public function getConnectionStatus(): array {
        return [
            'connected' => !empty($this->connectionData['access_token']),
            'provider' => 'google',
            'expires_at' => $this->connectionData['expires_at'] ?? null
        ];
    }

    public function disconnect(): bool {
        $this->connectionData = [
            'access_token' => null,
            'refresh_token' => null,
            'expires_at' => null
        ];
        return true;
    }
}
