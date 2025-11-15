<?php
namespace Services\Calendar;

use Services\Encryption\EncryptionService;

class OutlookCalendarService implements CalendarService {
    private $client;
    private $encryptionService;
    private $connectionData;

    public function __construct(array $connectionData, EncryptionService $encryptionService) {
        $this->connectionData = $connectionData;
        $this->encryptionService = $encryptionService;
        $this->initializeClient();
    }

    private function initializeClient() {
        $this->client = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $_ENV['OUTLOOK_CLIENT_ID'],
            'clientSecret'            => $_ENV['OUTLOOK_CLIENT_SECRET'],
            'redirectUri'             => $_ENV['OUTLOOK_REDIRECT_URI'],
            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                 => 'Calendars.ReadWrite offline_access'
        ]);

        if (!empty($this->connectionData['access_token'])) {
            $accessToken = $this->encryptionService->decrypt($this->connectionData['access_token']);
            $this->client->setAccessToken($accessToken);
        }
    }

    public function authenticate(string $authCode): bool {
        try {
            $token = $this->client->getAccessToken('authorization_code', [
                'code' => $authCode
            ]);
            $this->storeTokens($token);
            return true;
        } catch (\Exception $e) {
            error_log('Outlook auth failed: ' . $e->getMessage());
            return false;
        }
    }

    private function storeTokens($token) {
        $this->connectionData['access_token'] = $this->encryptionService->encrypt($token->getToken());
        
        if ($token->getRefreshToken()) {
            $this->connectionData['refresh_token'] = $this->encryptionService->encrypt($token->getRefreshToken());
        }
        
        $this->connectionData['expires_at'] = date('Y-m-d H:i:s', $token->getExpires());
    }

    public function refreshToken(): bool {
        try {
            $refreshToken = $this->encryptionService->decrypt($this->connectionData['refresh_token']);
            $token = $this->client->getAccessToken('refresh_token', [
                'refresh_token' => $refreshToken
            ]);
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
            'provider' => 'outlook',
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
