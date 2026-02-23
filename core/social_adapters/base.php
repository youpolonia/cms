<?php
declare(strict_types=1);

/**
 * Abstract Social Media Adapter
 * Base class for all platform-specific social media adapters
 */

require_once __DIR__ . '/../database.php';

abstract class SocialAdapter
{
    /** @var string Platform identifier */
    protected string $platform = '';

    /**
     * Publish content to the platform
     *
     * @param string      $content  Post content text
     * @param string|null $mediaUrl Optional media URL (image/video)
     * @param string|null $linkUrl  Optional link to include
     * @return array ['ok' => bool, 'external_id' => string|null, 'error' => string|null]
     */
    abstract public function publish(string $content, ?string $mediaUrl = null, ?string $linkUrl = null): array;

    /**
     * Get the OAuth authorization URL for this platform
     *
     * @param string $callbackUrl The callback URL after auth
     * @return string Full authorization URL
     */
    abstract public function getAuthUrl(string $callbackUrl): string;

    /**
     * Handle OAuth callback — exchange code for tokens
     *
     * @param string $code        Authorization code from callback
     * @param string $callbackUrl The callback URL used during auth
     * @return bool True if tokens were saved successfully
     */
    abstract public function handleCallback(string $code, string $callbackUrl): bool;

    /**
     * Check if the platform account is connected and has valid tokens
     *
     * @return bool True if connected
     */
    abstract public function isConnected(): bool;

    /**
     * Get the stored account for this platform
     *
     * @return array|null Account row or null
     */
    protected function getAccount(): ?array
    {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform = ? AND active = 1 LIMIT 1");
        $stmt->execute([$this->platform]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get meta field from the account's meta JSON
     *
     * @param string $key     Meta key
     * @param mixed  $default Default value
     * @return mixed
     */
    protected function getMeta(string $key, mixed $default = null): mixed
    {
        $account = $this->getAccount();
        if (!$account) {
            return $default;
        }
        $meta = json_decode($account['meta'] ?? '{}', true);
        return $meta[$key] ?? $default;
    }

    /**
     * Save/update access tokens for this platform
     *
     * @param string      $accessToken  Access token
     * @param string|null $refreshToken Refresh token
     * @param string|null $expires      Expiry datetime (Y-m-d H:i:s)
     */
    protected function saveToken(string $accessToken, ?string $refreshToken = null, ?string $expires = null): void
    {
        $db = \core\Database::connection();
        $account = $this->getAccount();

        if ($account) {
            $sql = "UPDATE social_accounts SET access_token = ?, refresh_token = ?, token_expires = ?, updated_at = NOW() WHERE id = ?";
            $db->prepare($sql)->execute([$accessToken, $refreshToken, $expires, $account['id']]);
        } else {
            $sql = "INSERT INTO social_accounts (platform, account_name, access_token, refresh_token, token_expires, active, created_at)
                    VALUES (?, ?, ?, ?, ?, 1, NOW())";
            $db->prepare($sql)->execute([$this->platform, $this->platform, $accessToken, $refreshToken, $expires]);
        }
    }

    /**
     * Save meta data for this platform account
     *
     * @param array $meta Key-value pairs to merge into meta JSON
     */
    protected function saveMeta(array $meta): void
    {
        $db = \core\Database::connection();
        $account = $this->getAccount();

        if (!$account) {
            return;
        }

        $existing = json_decode($account['meta'] ?? '{}', true) ?: [];
        $merged = array_merge($existing, $meta);

        $db->prepare("UPDATE social_accounts SET meta = ?, updated_at = NOW() WHERE id = ?")
           ->execute([json_encode($merged), $account['id']]);
    }

    /**
     * Make a cURL request
     *
     * @param string $url     Request URL
     * @param string $method  HTTP method (GET, POST)
     * @param array  $headers HTTP headers
     * @param mixed  $body    Request body (will be JSON-encoded if array)
     * @return array ['code' => int, 'body' => string, 'error' => string|null]
     */
    protected function httpRequest(string $url, string $method = 'GET', array $headers = [], mixed $body = null): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return ['code' => 0, 'body' => '', 'error' => 'Failed to initialize cURL'];
        }

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            if ($body !== null) {
                if (is_array($body)) {
                    $body = json_encode($body);
                    $headers[] = 'Content-Type: application/json';
                }
                $opts[CURLOPT_POSTFIELDS] = $body;
            }
        }

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array($ch, $opts);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['code' => 0, 'body' => '', 'error' => 'cURL error: ' . $curlError];
        }

        return ['code' => $httpCode, 'body' => (string) $response, 'error' => null];
    }

    /**
     * Disconnect this platform (deactivate account)
     */
    public function disconnect(): bool
    {
        $db = \core\Database::connection();
        $account = $this->getAccount();
        if (!$account) {
            return false;
        }
        return $db->prepare("UPDATE social_accounts SET active = 0, access_token = NULL, refresh_token = NULL WHERE id = ?")
                   ->execute([$account['id']]);
    }
}
