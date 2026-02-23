<?php
declare(strict_types=1);

/**
 * Twitter/X Social Media Adapter
 * Uses X API v2 for posting tweets
 * OAuth 2.0 with PKCE
 */

require_once __DIR__ . '/base.php';

class TwitterAdapter extends SocialAdapter
{
    protected string $platform = 'twitter';

    private const API_BASE = 'https://api.twitter.com/2';
    private const AUTH_URL = 'https://twitter.com/i/oauth2/authorize';
    private const TOKEN_URL = 'https://api.twitter.com/2/oauth2/token';

    /**
     * Publish a tweet via X API v2
     */
    public function publish(string $content, ?string $mediaUrl = null, ?string $linkUrl = null): array
    {
        $account = $this->getAccount();
        if (!$account || empty($account['access_token'])) {
            return ['ok' => false, 'external_id' => null, 'error' => 'Twitter not connected'];
        }

        // Append link if provided and it fits
        if ($linkUrl !== null && $linkUrl !== '') {
            $withLink = $content . ' ' . $linkUrl;
            if (mb_strlen($withLink) <= 280) {
                $content = $withLink;
            }
        }

        // Truncate to 280 chars
        if (mb_strlen($content) > 280) {
            $content = mb_substr($content, 0, 277) . '...';
        }

        $payload = ['text' => $content];

        $resp = $this->httpRequest(
            self::API_BASE . '/tweets',
            'POST',
            [
                'Authorization: Bearer ' . $account['access_token'],
                'Content-Type: application/json',
            ],
            $payload
        );

        if ($resp['error']) {
            return ['ok' => false, 'external_id' => null, 'error' => $resp['error']];
        }

        $data = json_decode($resp['body'], true);

        if ($resp['code'] === 201 && !empty($data['data']['id'])) {
            return ['ok' => true, 'external_id' => $data['data']['id'], 'error' => null];
        }

        $errorMsg = $data['detail'] ?? $data['title'] ?? ('HTTP ' . $resp['code']);
        return ['ok' => false, 'external_id' => null, 'error' => 'Twitter API: ' . $errorMsg];
    }

    /**
     * Get OAuth 2.0 authorization URL (PKCE flow)
     */
    public function getAuthUrl(string $callbackUrl): string
    {
        $clientId = $this->getMeta('client_id', '');
        if (empty($clientId)) {
            return '#error=client_id_not_configured';
        }

        // Generate PKCE code verifier and challenge
        $codeVerifier = bin2hex(random_bytes(32));
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        // Store code verifier in session for the callback
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['twitter_code_verifier'] = $codeVerifier;
        }

        $params = [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $callbackUrl,
            'scope' => 'tweet.read tweet.write users.read offline.access',
            'state' => bin2hex(random_bytes(16)),
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Handle OAuth callback — exchange code for tokens
     */
    public function handleCallback(string $code, string $callbackUrl): bool
    {
        $clientId = $this->getMeta('client_id', '');
        $clientSecret = $this->getMeta('client_secret', '');
        $codeVerifier = $_SESSION['twitter_code_verifier'] ?? '';

        if (empty($clientId)) {
            error_log('[SOCIAL] Twitter: client_id not configured');
            return false;
        }

        $payload = http_build_query([
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'redirect_uri' => $callbackUrl,
            'code_verifier' => $codeVerifier,
        ]);

        $headers = ['Content-Type: application/x-www-form-urlencoded'];

        // If client_secret is set, use Basic auth
        if (!empty($clientSecret)) {
            $headers[] = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
        }

        $resp = $this->httpRequest(self::TOKEN_URL, 'POST', $headers, $payload);

        if ($resp['error'] || $resp['code'] !== 200) {
            error_log('[SOCIAL] Twitter token exchange failed: ' . ($resp['error'] ?? $resp['body']));
            return false;
        }

        $data = json_decode($resp['body'], true);
        if (empty($data['access_token'])) {
            error_log('[SOCIAL] Twitter: no access_token in response');
            return false;
        }

        $expires = null;
        if (!empty($data['expires_in'])) {
            $expires = date('Y-m-d H:i:s', time() + (int)$data['expires_in']);
        }

        $this->saveToken(
            $data['access_token'],
            $data['refresh_token'] ?? null,
            $expires
        );

        // Fetch and save username
        $userResp = $this->httpRequest(
            self::API_BASE . '/users/me',
            'GET',
            ['Authorization: Bearer ' . $data['access_token']]
        );

        if ($userResp['code'] === 200) {
            $userData = json_decode($userResp['body'], true);
            if (!empty($userData['data']['username'])) {
                $db = \core\Database::connection();
                $db->prepare("UPDATE social_accounts SET account_name = ? WHERE platform = 'twitter' AND active = 1")
                   ->execute(['@' . $userData['data']['username']]);
            }
        }

        // Clean up session
        unset($_SESSION['twitter_code_verifier']);

        return true;
    }

    /**
     * Check if Twitter is connected
     */
    public function isConnected(): bool
    {
        $account = $this->getAccount();
        if (!$account || empty($account['access_token'])) {
            return false;
        }

        // Check token expiry
        if (!empty($account['token_expires'])) {
            $expires = strtotime($account['token_expires']);
            if ($expires !== false && $expires < time()) {
                return false;
            }
        }

        return true;
    }
}
